<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\SubOrderStatus;
use App\Enums\UserRole;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\BcvExchangeService;
use App\Services\CashShiftService;
use App\Services\PosSaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosOmnichannelSaleTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected StoreBranch $branch;
    protected User $merchant;
    protected Product $product;
    protected BranchInventory $inventory;
    protected PosSaleService $posSaleService;
    protected CashShiftService $shiftService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = User::factory()->create([
            'role' => UserRole::MerchantAdmin,
            'full_name' => 'Cajero Carlos',
        ]);

        $category = Category::create([
            'name' => 'Desayunos',
            'slug' => 'desayunos',
            'description' => 'Desayunos criollos',
        ]);

        $this->store = Store::create([
            'owner_id' => $this->merchant->id,
            'category_id' => $category->id,
            'business_name' => 'Cuadrados Food',
            'slug' => 'cuadrados-food',
            'phone' => '+584122758427',
            'status' => StoreStatus::Active,
            'commission_rate' => 10.00,
            'rating' => 5.0,
            'is_special_taxpayer' => true, // Contribuyente especial (aplica IGTF 3%)
            'rif' => 'J-50123456-7',
            'bcv_rate' => 50.0000, // Tasa BCV 50 Bs/$
        ]);

        $this->branch = StoreBranch::create([
            'store_id' => $this->store->id,
            'branch_name' => 'Sede Maracay',
            'address' => 'Maracay Centro, Las Acacias',
            'latitude' => 10.2469,
            'longitude' => -67.5958,
            'delivery_radius_km' => 5.0,
            'base_delivery_fee' => 2.00,
            'avg_prep_time_minutes' => 15,
            'is_open' => true,
            'pos_enabled' => true,
            'invoice_printer_type' => '80mm',
        ]);

        $this->product = Product::create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'name' => 'Pastelito de Carne Mechada',
            'slug' => 'pastelito-carne-mechada',
            'sku' => 'PAS-CARNE',
            'description' => 'Delicioso pastelito artesanal frito',
            'base_price' => 2.00,
            'cost_price' => 0.80,
            'is_active' => true,
        ]);

        $this->inventory = BranchInventory::create([
            'branch_id' => $this->branch->id,
            'product_id' => $this->product->id,
            'stock_quantity' => 25,
            'price' => 2.00,
            'is_available' => true,
        ]);

        $this->posSaleService = app(PosSaleService::class);
        $this->shiftService = app(CashShiftService::class);
    }

    public function test_can_open_shift_process_pos_sale_with_igtf_and_mixed_payments_then_close_shift(): void
    {
        // 1. Apertura de caja con fondo inicial en USD y VES
        $register = $this->shiftService->getOrCreateDefaultRegister($this->branch);
        $shift = $this->shiftService->openShift(
            $register,
            $this->merchant,
            openingBalanceUsd: 50.00,
            openingBalanceVes: 500.00,
            notes: 'Apertura turno matutino'
        );

        $this->assertTrue($shift->isOpen());
        $this->assertEquals(50.00, (float) $shift->opening_balance_usd);
        $this->assertEquals(500.00, (float) $shift->opening_balance_ves);

        // 2. Venta de 3 pastelitos ($2.00 c/u = $6.00 subtotal)
        // Pago mixto:
        // - $4.00 en efectivo USD (aplica 3% IGTF = $0.12)
        // - Bs. 106.00 en Pago Móvil ($2.12 equiv a tasa 50)
        // Total a pagar: $6.00 + $0.12 (IGTF) = $6.12 USD
        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 3,
                'unit_price' => 2.00,
            ],
        ];

        $payments = [
            [
                'method' => 'efectivo_usd',
                'currency' => 'USD',
                'amount' => 4.00,
                'reference_number' => null,
            ],
            [
                'method' => 'pago_movil',
                'currency' => 'VES',
                'amount' => 106.00, // 106 / 50 = $2.12
                'reference_number' => 'REF-PM-998822',
            ],
        ];

        $customerData = [
            'doc' => 'V-19876543',
            'name' => 'Maria Fernandez',
            'phone' => '+584141234567',
        ];

        $result = $this->posSaleService->processSale(
            $this->branch,
            $this->merchant,
            $shift,
            $customerData,
            $items,
            $payments,
            'Cliente frecuente'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(6.00, $result['totals']['subtotal_usd']);
        $this->assertEquals(0.12, $result['totals']['igtf_usd']); // 3% sobre los $4 en divisas
        $this->assertEquals(6.12, $result['totals']['total_due_usd']);

        // 3. Verificar que el inventario se descontó en tiempo real de 25 a 22
        $this->inventory->refresh();
        $this->assertEquals(22, $this->inventory->stock_quantity);

        // 4. Verificar que se grabó el movimiento Kardex de auditoría
        $movement = InventoryMovement::where('product_id', $this->product->id)
            ->where('branch_id', $this->branch->id)
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('pos_sale', $movement->type);
        $this->assertEquals(-3, $movement->quantity_delta);
        $this->assertEquals(25, $movement->stock_before);
        $this->assertEquals(22, $movement->stock_after);
        $this->assertEquals(SubOrder::class, $movement->reference_type);

        // 5. Verificar la Orden y SubOrden
        $order = Order::find($result['order_id']);
        $this->assertNotNull($order);
        $this->assertEquals('pos_counter', $order->channel);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertEquals('V-19876543', $order->customer_doc);
        $this->assertNull($order->address_id); // Venta física sin dirección
        $this->assertEquals(6.12, (float) $order->grand_total);

        $subOrder = SubOrder::find($result['sub_order_id']);
        $this->assertEquals('pos_counter', $subOrder->channel);
        $this->assertEquals(SubOrderStatus::Delivered, $subOrder->status);
        $this->assertEquals(2, $order->paymentTransactions()->count());

        // 6. Arqueo y Cierre de Caja (Corte Z)
        // Fondo inicial: $50 USD y Bs. 500
        // Venta en efectivo: +$4.00 USD, +Bs. 0
        // Esperado en caja: $54.00 USD y Bs. 500
        $shift->refresh();
        $this->assertEquals(54.00, (float) $shift->expected_cash_usd);
        $this->assertEquals(500.00, (float) $shift->expected_cash_ves);

        // El cajero cuenta físicamente $54.00 USD y Bs. 500
        $closedShift = $this->shiftService->closeShift(
            $shift,
            actualCashUsd: 54.00,
            actualCashVes: 500.00,
            closingNotes: 'Cuadre perfecto'
        );

        $this->assertEquals('closed', $closedShift->status);
        $this->assertEquals(0.00, (float) $closedShift->difference_usd);
        $this->assertEquals(0.00, (float) $closedShift->difference_ves);

        // 7. Generar reporte resumen de corte
        $summary = $this->shiftService->getShiftSummary($closedShift);
        $this->assertEquals(1, $summary['total_sales_count']);
        $this->assertEquals(4.00, $summary['payments_by_method']['efectivo_usd']['amount']);
        $this->assertEquals(106.00, $summary['payments_by_method']['pago_movil']['amount']);
    }

    public function test_can_fetch_live_online_orders_for_pos_branch(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::Customer,
            'full_name' => 'Juan Perez',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-WEB-123456',
            'channel' => 'online',
            'customer_id' => $customer->id,
            'address_id' => null,
            'total_items_amount' => 10.00,
            'total_delivery_fee' => 2.00,
            'platform_fee' => 0.50,
            'grand_total' => 12.50,
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'wallet',
        ]);

        $subOrder = SubOrder::create([
            'order_id' => $order->id,
            'sub_order_code' => 'SUB-WEB-123456',
            'channel' => 'online',
            'branch_id' => $this->branch->id,
            'status' => SubOrderStatus::Placed,
            'subtotal' => 10.00,
            'delivery_fee' => 2.00,
            'commission_amount' => 1.00,
        ]);

        $liveOrders = $this->posSaleService->getLiveOnlineOrders($this->branch);

        $this->assertCount(1, $liveOrders);
        $this->assertEquals($subOrder->id, $liveOrders[0]['sub_order_id']);
        $this->assertEquals('placed', $liveOrders[0]['status']);
        $this->assertEquals('Juan Perez', $liveOrders[0]['customer']['name']);

        // Aceptar el pedido desde el POS
        $updated = $this->posSaleService->updateOnlineOrderStatus($subOrder, 'accepted_by_store');
        $this->assertEquals(SubOrderStatus::AcceptedByStore, $updated->status);
    }
}
