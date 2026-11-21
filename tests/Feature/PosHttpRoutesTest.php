<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\CashShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosHttpRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected StoreBranch $branch;
    protected User $merchant;
    protected Product $product;
    protected BranchInventory $inventory;
    protected CashShiftService $shiftService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = User::factory()->create([
            'role' => UserRole::MerchantAdmin,
            'full_name' => 'Comerciante POS',
        ]);

        $category = Category::create([
            'name' => 'Empanadas',
            'slug' => 'empanadas',
            'description' => 'Empanadas operativas',
        ]);

        $this->store = Store::create([
            'owner_id' => $this->merchant->id,
            'category_id' => $category->id,
            'business_name' => 'Empanadas El Sol',
            'slug' => 'empanadas-el-sol',
            'phone' => '+584120001122',
            'status' => StoreStatus::Active,
            'commission_rate' => 10.00,
            'rating' => 5.0,
            'is_special_taxpayer' => true,
            'rif' => 'J-12345678-9',
            'bcv_rate' => 50.0000,
        ]);

        $this->branch = StoreBranch::create([
            'store_id' => $this->store->id,
            'branch_name' => 'Sede Principal',
            'address' => 'Av. Bolívar, Local 1',
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
            'name' => 'Empanada de Cazón',
            'slug' => 'empanada-cazon',
            'sku' => 'EMP-CAZON',
            'description' => 'Empanada fresca de cazón margariteño',
            'base_price' => 1.50,
            'cost_price' => 0.60,
            'is_active' => true,
        ]);

        $this->inventory = BranchInventory::create([
            'branch_id' => $this->branch->id,
            'product_id' => $this->product->id,
            'stock_quantity' => 40,
            'price' => 1.50,
            'is_available' => true,
        ]);

        $this->shiftService = app(CashShiftService::class);
    }

    public function test_pos_index_screen_renders_successfully_for_authenticated_merchant(): void
    {
        $response = $this->actingAs($this->merchant)
            ->get(route('merchant.pos.index', ['branch_id' => $this->branch->id]));

        $response->assertStatus(200);
        $response->assertSee('Empanadas El Sol');
        $response->assertSee('Sede Principal');
        $response->assertSee('EMP-CAZON');
    }

    public function test_can_open_shift_and_process_sale_via_http_api(): void
    {
        $register = $this->shiftService->getOrCreateDefaultRegister($this->branch);

        // 1. Apertura de caja vía POST
        $openResponse = $this->actingAs($this->merchant)
            ->postJson(route('merchant.pos.shifts.open'), [
                'register_id' => $register->id,
                'opening_balance_usd' => 30.00,
                'opening_balance_ves' => 400.00,
                'notes' => 'Apertura turno tarde',
            ]);

        $openResponse->assertStatus(200);
        $openResponse->assertJson(['success' => true]);
        $shiftId = $openResponse->json('shift.id');

        // 2. Ejecución de venta en mostrador (POS) con pago mixto e IGTF
        $saleResponse = $this->actingAs($this->merchant)
            ->postJson(route('merchant.pos.store'), [
                'branch_id' => $this->branch->id,
                'cash_shift_id' => $shiftId,
                'customer_doc' => 'V-18293847',
                'customer_name' => 'Pedro Castillo',
                'customer_phone' => '+584145556677',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'unit_price' => 1.50,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'efectivo_usd',
                        'currency' => 'USD',
                        'amount' => 3.09, // $3.00 subtotal + $0.09 (3% IGTF)
                        'reference_number' => null,
                    ],
                ],
                'notes' => 'Venta rápida mostrador',
            ]);

        $saleResponse->assertStatus(200);
        $saleResponse->assertJson(['success' => true]);
        $orderId = $saleResponse->json('receipt.order_id');

        // 3. Verificar que el inventario se descontó a 38
        $this->assertEquals(38, $this->inventory->fresh()->stock_quantity);

        // 4. Verificar impresión térmica de recibo (80mm)
        $printResponse = $this->actingAs($this->merchant)
            ->get(route('merchant.pos.print.receipt', ['order' => $orderId, 'format' => '80mm']));

        $printResponse->assertStatus(200);
        $printResponse->assertSee('Empanadas El Sol');
        $printResponse->assertSee('Pedro Castillo');
        $printResponse->assertSee('Empanada de Cazón');
        $printResponse->assertSee('IGTF (3% Divisas)');

        // 5. Cierre de caja (Corte Z)
        $closeResponse = $this->actingAs($this->merchant)
            ->postJson(route('merchant.pos.shifts.close', ['shift' => $shiftId]), [
                'actual_cash_usd' => 33.09,
                'actual_cash_ves' => 400.00,
                'closing_notes' => 'Cierre conforme sin diferencias',
            ]);

        $closeResponse->assertStatus(200);
        $closeResponse->assertJson(['success' => true]);
    }
}
