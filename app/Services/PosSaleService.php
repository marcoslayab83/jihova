<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\CashShift;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\StoreSaleItem;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosSaleService
{
    public function __construct(
        protected BcvExchangeService $exchangeService
    ) {}

    /**
     * Procesa una venta en mostrador (POS) con inventario unificado, pagos mixtos e IGTF.
     *
     * @param  StoreBranch  $branch  Sucursal donde se realiza la venta física
     * @param  User  $cashier  Usuario cajero autenticado
     * @param  CashShift  $shift  Turno de caja abierto
     * @param  array{doc?: ?string, name?: ?string, phone?: ?string, email?: ?string}  $customerData  Datos del cliente (Cédula/RIF, etc.)
     * @param  list<array{product_id: string, quantity: int, unit_price?: ?float}>  $items  Líneas de productos
     * @param  list<array{method: string, currency: string, amount: float, reference_number?: ?string, notes?: ?string}>  $payments  Pagos recibidos
     * @param  string|null  $notes  Notas internas de la orden
     * @return array Resumen detallado de la venta procesada con recibo
     */
    public function processSale(
        StoreBranch $branch,
        User $cashier,
        CashShift $shift,
        array $customerData,
        array $items,
        array $payments,
        ?string $notes = null
    ): array {
        if (! $shift->isOpen() || $shift->register->store_branch_id !== $branch->id) {
            throw ValidationException::withMessages([
                'shift' => 'El turno de caja especificado no pertenece a esta sucursal o ya está cerrado.',
            ]);
        }

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Debe agregar al menos un producto al carrito de venta.',
            ]);
        }

        if (empty($payments)) {
            throw ValidationException::withMessages([
                'payments' => 'Debe ingresar al menos un método de pago para completar la venta.',
            ]);
        }

        return DB::transaction(function () use (
            $branch,
            $cashier,
            $shift,
            $customerData,
            $items,
            $payments,
            $notes
        ): array {
            $store = $branch->store;
            $rate = $this->exchangeService->getStoreRate($store);
            $isSpecialTaxpayer = (bool) $store->is_special_taxpayer;

            // 1. Bloqueo pesimista y validación de inventario
            $productIds = array_column($items, 'product_id');
            $inventories = BranchInventory::query()
                ->with('product')
                ->where('branch_id', $branch->id)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $lines = [];
            $subtotalUsd = 0.00;

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (int) $item['quantity'];

                /** @var BranchInventory|null $inventory */
                $inventory = $inventories->get($productId);

                if (
                    ! $inventory
                    || ! $inventory->product
                    || ! $inventory->product->is_active
                    || $inventory->product->store_id !== $branch->store_id
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los productos seleccionados no pertenece a esta tienda o está inactivo.',
                    ]);
                }

                if (! $inventory->is_available || $inventory->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para '{$inventory->product->name}'. Disponible: {$inventory->stock_quantity}, solicitado: {$quantity}.",
                    ]);
                }

                $unitPrice = isset($item['unit_price']) && (float) $item['unit_price'] > 0
                    ? (float) $item['unit_price']
                    : (float) $inventory->price;

                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotalUsd = round($subtotalUsd + $lineTotal, 2);

                $lines[] = [
                    'inventory' => $inventory,
                    'product' => $inventory->product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'unit_cost' => $inventory->product->cost_price !== null ? (float) $inventory->product->cost_price : null,
                ];
            }

            // 2. Cálculo de IGTF (3% únicamente sobre divisas para contribuyentes especiales)
            $igtfBreakdown = $this->exchangeService->calculateIgtf($payments, $isSpecialTaxpayer, $rate);
            $igtfUsd = $igtfBreakdown['igtf_usd'];
            $totalDueUsd = round($subtotalUsd + $igtfUsd, 2);
            $totalDueVes = $this->exchangeService->usdToVes($totalDueUsd, $rate);

            // 3. Validación y equivalencia de pagos
            $totalPaidUsd = 0.00;
            $processedPayments = [];

            foreach ($payments as $payment) {
                $method = strtolower($payment['method'] ?? 'efectivo_usd');
                $currency = strtoupper($payment['currency'] ?? 'USD');
                $amount = round((float) ($payment['amount'] ?? 0), 2);

                if ($amount <= 0) {
                    continue;
                }

                $usdEquivalent = $currency === 'VES'
                    ? $this->exchangeService->vesToUsd($amount, $rate)
                    : $amount;

                $totalPaidUsd = round($totalPaidUsd + $usdEquivalent, 2);

                // Calcular el IGTF específico de esta línea de pago
                $lineIgtf = ($isSpecialTaxpayer && in_array($method, ['efectivo_usd', 'zelle'], true))
                    ? round($amount * 0.03, 2)
                    : 0.00;

                $processedPayments[] = [
                    'method' => $method,
                    'currency' => $currency,
                    'amount' => $amount,
                    'amount_usd_equivalent' => $usdEquivalent,
                    'exchange_rate' => $rate,
                    'igtf_amount' => $lineIgtf,
                    'reference_number' => $payment['reference_number'] ?? null,
                    'notes' => $payment['notes'] ?? null,
                ];
            }

            if ($totalPaidUsd < round($totalDueUsd - 0.02, 2)) {
                $missingUsd = round($totalDueUsd - $totalPaidUsd, 2);
                $missingVes = $this->exchangeService->usdToVes($missingUsd, $rate);
                throw ValidationException::withMessages([
                    'payments' => "El monto pagado (\${$totalPaidUsd}) es menor al total (\${$totalDueUsd}). Faltan \${$missingUsd} (Bs. {$missingVes}).",
                ]);
            }

            // 4. Cálculo de Vuelto / Cambio
            $changeBreakdown = $this->exchangeService->calculateChange($totalDueUsd, $totalPaidUsd, $rate);

            // 5. Creación de la Orden Unificada (Order)
            $orderNumber = 'POS-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
            $customerDoc = $customerData['doc'] ?? 'V-00000000';
            $customerName = $customerData['name'] ?? 'Cliente Mostrador';
            $customerPhone = $customerData['phone'] ?? null;
            $primaryPaymentMethod = count($processedPayments) === 1
                ? $processedPayments[0]['method']
                : 'pos_mixed';

            $order = Order::create([
                'order_number' => $orderNumber,
                'channel' => 'pos_counter',
                'customer_id' => $cashier->id,
                'customer_doc' => $customerDoc,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'address_id' => null, // Venta en mostrador sin dirección
                'total_items_amount' => $subtotalUsd,
                'total_delivery_fee' => 0.00,
                'platform_fee' => 0.00,
                'grand_total' => $totalDueUsd,
                'igtf_amount' => $igtfUsd,
                'bcv_exchange_rate' => $rate,
                'payment_status' => PaymentStatus::Paid,
                'payment_method' => $primaryPaymentMethod,
                'payment_reference' => count($processedPayments) === 1 ? ($processedPayments[0]['reference_number'] ?? null) : 'MIXTO',
                'cash_shift_id' => $shift->id,
                'paid_at' => now(),
                'customer_notes' => $notes,
            ]);

            // 6. Creación de la SubOrden (SubOrder)
            $subOrder = SubOrder::create([
                'order_id' => $order->id,
                'sub_order_code' => 'SUB-'.$orderNumber,
                'channel' => 'pos_counter',
                'branch_id' => $branch->id,
                'driver_id' => null,
                'status' => SubOrderStatus::Delivered, // Entregado en mostrador de inmediato
                'payment_status' => 'paid',
                'subtotal' => $subtotalUsd,
                'delivery_fee' => 0.00,
                'commission_amount' => 0.00,
                'igtf_amount' => $igtfUsd,
                'bcv_exchange_rate' => $rate,
                'delivered_at' => now(),
            ]);

            // 7. Descuento atómico de inventario, OrderItems y Kardex
            $createdItems = [];
            foreach ($lines as $line) {
                /** @var BranchInventory $inventory */
                $inventory = $line['inventory'];
                $stockBefore = (int) $inventory->stock_quantity;
                $qty = $line['quantity'];
                $stockAfter = $stockBefore - $qty;

                // Descontar inventario
                $inventory->decrement('stock_quantity', $qty);

                // Crear Item de la Orden
                $orderItem = OrderItem::create([
                    'sub_order_id' => $subOrder->id,
                    'product_id' => $inventory->product_id,
                    'product_name' => $inventory->product->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $qty,
                    'total_price' => $line['total_price'],
                    'unit_cost' => $line['unit_cost'],
                ]);

                // Registrar en el Kardex de Auditoría
                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'product_id' => $inventory->product_id,
                    'user_id' => $cashier->id,
                    'type' => 'pos_sale',
                    'quantity' => $qty,
                    'quantity_delta' => -$qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'balance_after' => $stockAfter,
                    'reference_type' => SubOrder::class,
                    'reference_id' => $subOrder->id,
                    'reason' => "Venta Mostrador POS {$orderNumber}",
                ]);

                $createdItems[] = [
                    'product_name' => $inventory->product->name,
                    'sku' => $inventory->product->sku,
                    'quantity' => $qty,
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                    'unit_price_ves' => $this->exchangeService->usdToVes($line['unit_price'], $rate),
                    'total_price_ves' => $this->exchangeService->usdToVes($line['total_price'], $rate),
                ];
            }

            // 8. Registro de transacciones de pago multimoneda
            foreach ($processedPayments as $payData) {
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'sub_order_id' => $subOrder->id,
                    'cash_shift_id' => $shift->id,
                    'method' => $payData['method'],
                    'currency' => $payData['currency'],
                    'amount' => $payData['amount'],
                    'amount_usd_equivalent' => $payData['amount_usd_equivalent'],
                    'exchange_rate' => $payData['exchange_rate'],
                    'igtf_amount' => $payData['igtf_amount'],
                    'reference_number' => $payData['reference_number'],
                    'notes' => $payData['notes'],
                ]);
            }

            // 9. Recomputar totales de efectivo en el turno de caja
            $shift->recalculateExpectedCash();

            // 10. Compatibilidad retrospectiva con StoreSale / StoreSaleItem
            $storeSale = StoreSale::create([
                'store_id' => $branch->store_id,
                'branch_id' => $branch->id,
                'user_id' => $cashier->id,
                'sale_number' => 'VTA-'.$orderNumber,
                'channel' => 'in_store',
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'payment_method' => $primaryPaymentMethod,
                'payment_reference' => $order->payment_reference,
                'subtotal' => $subtotalUsd,
                'discount_amount' => 0.00,
                'total_amount' => $totalDueUsd,
                'notes' => $notes,
                'sold_at' => now(),
            ]);

            foreach ($lines as $line) {
                StoreSaleItem::create([
                    'store_sale_id' => $storeSale->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                    'unit_cost' => $line['unit_cost'],
                ]);
            }

            return [
                'success' => true,
                'order_id' => $order->id,
                'sub_order_id' => $subOrder->id,
                'order_number' => $orderNumber,
                'sold_at' => now()->format('d/m/Y h:i A'),
                'store' => [
                    'name' => $store->business_name,
                    'rif' => $store->rif ?? 'J-00000000-0',
                    'is_special_taxpayer' => $isSpecialTaxpayer,
                    'phone' => $store->phone,
                    'address' => $branch->address,
                    'branch_name' => $branch->branch_name,
                    'printer_type' => $branch->invoice_printer_type ?? '80mm',
                ],
                'cashier' => [
                    'name' => $cashier->full_name,
                    'register_name' => $shift->register->name,
                ],
                'customer' => [
                    'doc' => $customerDoc,
                    'name' => $customerName,
                    'phone' => $customerPhone,
                ],
                'exchange_rate' => $rate,
                'items' => $createdItems,
                'totals' => [
                    'subtotal_usd' => $subtotalUsd,
                    'subtotal_ves' => $this->exchangeService->usdToVes($subtotalUsd, $rate),
                    'igtf_usd' => $igtfUsd,
                    'igtf_ves' => $this->exchangeService->usdToVes($igtfUsd, $rate),
                    'total_due_usd' => $totalDueUsd,
                    'total_due_ves' => $totalDueVes,
                    'total_paid_usd' => $totalPaidUsd,
                    'total_paid_ves' => $this->exchangeService->usdToVes($totalPaidUsd, $rate),
                    'change_usd' => $changeBreakdown['change_usd'],
                    'change_ves' => $changeBreakdown['change_ves'],
                ],
                'payments' => $processedPayments,
            ];
        });
    }

    /**
     * Consulta los pedidos web entrantes en tiempo real para esta sucursal
     * (con soporte para polling reactivo y comanda)
     */
    public function getLiveOnlineOrders(StoreBranch $branch): array
    {
        $orders = SubOrder::query()
            ->with(['order.customer', 'order.address', 'items.product', 'driver'])
            ->where('branch_id', $branch->id)
            ->where('channel', 'online')
            ->whereIn('status', [
                SubOrderStatus::Placed,
                SubOrderStatus::AcceptedByStore,
                SubOrderStatus::Preparing,
                SubOrderStatus::ReadyForPickup,
            ])
            ->latest()
            ->take(20)
            ->get();

        return $orders->map(function (SubOrder $subOrder) use ($branch) {
            $store = $branch->store;
            $rate = $this->exchangeService->getStoreRate($store);

            return [
                'sub_order_id' => $subOrder->id,
                'sub_order_code' => $subOrder->sub_order_code,
                'order_number' => $subOrder->order->order_number,
                'status' => $subOrder->status->value,
                'status_label' => $subOrder->status->label(),
                'status_color' => $subOrder->status->badgeClasses(),
                'created_at_human' => $subOrder->created_at->diffForHumans(),
                'created_at' => $subOrder->created_at->format('h:i A'),
                'customer' => [
                    'name' => $subOrder->order->customer?->full_name ?? 'Cliente Web',
                    'phone' => $subOrder->order->customer?->phone ?? 'Sin teléfono',
                ],
                'delivery_address' => $subOrder->order->address?->street_address ?? 'Entrega Express',
                'subtotal_usd' => (float) $subOrder->subtotal,
                'subtotal_ves' => $this->exchangeService->usdToVes((float) $subOrder->subtotal, $rate),
                'items_count' => $subOrder->items->sum('quantity'),
                'items' => $subOrder->items->map(fn (OrderItem $item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ]),
                'notes' => $subOrder->order->customer_notes,
            ];
        })->toArray();
    }

    /**
     * Actualiza el estado de un pedido online entrante desde el POS
     */
    public function updateOnlineOrderStatus(SubOrder $subOrder, string $newStatus): SubOrder
    {
        $enumStatus = SubOrderStatus::tryFrom($newStatus);

        if (! $enumStatus) {
            throw ValidationException::withMessages([
                'status' => "El estado '{$newStatus}' no es válido.",
            ]);
        }

        $subOrder->update([
            'status' => $enumStatus,
        ]);

        return $subOrder->fresh();
    }
}
