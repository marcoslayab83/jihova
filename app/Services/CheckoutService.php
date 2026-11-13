<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\CustomerAddress;
use App\Models\DeliveryTrackingEvent;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly GeoLocationService $geo,
        private readonly CartService $cart,
    ) {}

    public function placeFromCart(
        User $customer,
        CustomerAddress $address,
        string $paymentMethod,
        ?string $customerNotes = null,
    ): Order {
        $rawItems = array_values($this->cart->rawItems());

        $order = $this->placeOrder($customer, $address, $rawItems, $paymentMethod, $customerNotes);
        $this->cart->clear();

        return $order;
    }

    /**
     * @param  list<array{branch_id: string, product_id: string, quantity: int}>  $requestedItems
     */
    public function placeOrder(
        User $customer,
        CustomerAddress $address,
        array $requestedItems,
        string $paymentMethod,
        ?string $customerNotes = null,
    ): Order {
        if ($requestedItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Tu carrito está vacío. Agrega al menos un artículo antes de pagar.',
            ]);
        }

        if ($address->user_id !== $customer->id) {
            throw ValidationException::withMessages([
                'address_id' => 'La dirección seleccionada no pertenece a tu cuenta.',
            ]);
        }

        return DB::transaction(function () use ($customer, $address, $requestedItems, $paymentMethod, $customerNotes): Order {
            $normalizedItems = [];
            foreach ($requestedItems as $item) {
                $qty = max(1, (int) $item['quantity']);
                $key = $item['branch_id'].':'.$item['product_id'];
                if (isset($normalizedItems[$key])) {
                    $normalizedItems[$key]['quantity'] += $qty;
                } else {
                    $normalizedItems[$key] = [
                        'branch_id' => (string) $item['branch_id'],
                        'product_id' => (string) $item['product_id'],
                        'quantity' => $qty,
                    ];
                }
            }

            $branchIds = array_values(array_unique(array_column($normalizedItems, 'branch_id')));
            $productIds = array_values(array_unique(array_column($normalizedItems, 'product_id')));

            $inventories = BranchInventory::query()
                ->with(['branch.store', 'product'])
                ->whereIn('branch_id', $branchIds)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn (BranchInventory $inv): string => $inv->branch_id.':'.$inv->product_id);

            $groupedByBranch = [];
            $totalItemsAmount = 0.0;

            foreach ($normalizedItems as $key => $entry) {
                /** @var BranchInventory|null $inventory */
                $inventory = $inventories->get($key);

                if (! $inventory || ! $inventory->branch || ! $inventory->product) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los productos seleccionados ya no existe en esa sucursal.',
                    ]);
                }

                $branch = $inventory->branch;
                $store = $branch->store;
                $product = $inventory->product;

                if ($store->status !== StoreStatus::Active || ! $branch->is_open) {
                    throw ValidationException::withMessages([
                        'items' => "{$store->business_name} ({$branch->branch_name}) no está recibiendo pedidos en este momento.",
                    ]);
                }

                if (! $product->is_active || ! $inventory->is_available || $inventory->stock_quantity < $entry['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "No hay stock suficiente para {$product->name} en {$branch->branch_name} (disponible: {$inventory->stock_quantity}).",
                    ]);
                }

                if (! $this->geo->isWithinRadius($branch, (float) $address->latitude, (float) $address->longitude)) {
                    throw ValidationException::withMessages([
                        'address_id' => "Tu dirección está fuera del radio de cobertura ({$branch->delivery_radius_km} km) de {$store->business_name} - {$branch->branch_name}.",
                    ]);
                }

                $unitPrice = (float) $inventory->price;
                $lineTotal = round($unitPrice * $entry['quantity'], 2);
                $totalItemsAmount = round($totalItemsAmount + $lineTotal, 2);

                if (! isset($groupedByBranch[$branch->id])) {
                    $groupedByBranch[$branch->id] = [
                        'branch' => $branch,
                        'store' => $store,
                        'subtotal' => 0.0,
                        'delivery_fee' => $this->geo->deliveryFeeForBranch($branch, (float) $address->latitude, (float) $address->longitude),
                        'eta_minutes' => $this->geo->estimatedMinutesForBranch($branch, (float) $address->latitude, (float) $address->longitude),
                        'lines' => [],
                    ];
                }

                $groupedByBranch[$branch->id]['subtotal'] = round($groupedByBranch[$branch->id]['subtotal'] + $lineTotal, 2);
                $groupedByBranch[$branch->id]['lines'][] = [
                    'inventory' => $inventory,
                    'product' => $product,
                    'quantity' => $entry['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $totalDeliveryFee = round(array_sum(array_column($groupedByBranch, 'delivery_fee')), 2);
            $platformFee = round(max(0.50, $totalItemsAmount * CartService::PLATFORM_FEE_RATE), 2);
            $grandTotal = round($totalItemsAmount + $totalDeliveryFee + $platformFee, 2);

            $isCashOnDelivery = $paymentMethod === 'cash_on_delivery';
            $paymentStatus = $isCashOnDelivery ? PaymentStatus::Unpaid : PaymentStatus::Paid;
            $paymentPrefix = strtoupper(substr($paymentMethod, 0, 4));

            $order = Order::create([
                'order_number' => 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'total_items_amount' => $totalItemsAmount,
                'total_delivery_fee' => $totalDeliveryFee,
                'platform_fee' => $platformFee,
                'grand_total' => $grandTotal,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentPrefix.'-'.strtoupper(Str::random(8)),
                'paid_at' => $isCashOnDelivery ? null : now(),
                'customer_notes' => $customerNotes,
                'delivery_snapshot' => [
                    'title' => $address->title,
                    'street_address' => $address->street_address,
                    'reference' => $address->reference,
                    'latitude' => (float) $address->latitude,
                    'longitude' => (float) $address->longitude,
                ],
            ]);

            $index = 1;
            foreach ($groupedByBranch as $group) {
                $branch = $group['branch'];
                $store = $group['store'];
                $commission = round($group['subtotal'] * ((float) $store->commission_rate / 100), 2);

                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'sub_order_code' => $order->order_number.'-T'.$index,
                    'branch_id' => $branch->id,
                    'driver_id' => null,
                    'status' => SubOrderStatus::Placed,
                    'subtotal' => $group['subtotal'],
                    'delivery_fee' => $group['delivery_fee'],
                    'commission_amount' => $commission,
                    'pickup_pin' => (string) random_int(1000, 9999),
                    'delivery_pin' => (string) random_int(1000, 9999),
                    'estimated_delivery_at' => now()->addMinutes($group['eta_minutes']),
                ]);

                foreach ($group['lines'] as $line) {
                    /** @var BranchInventory $inventory */
                    $inventory = $line['inventory'];
                    $stockBefore = $inventory->stock_quantity;
                    $inventory->decrement('stock_quantity', $line['quantity']);

                    OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['unit_price'],
                        'unit_cost' => $line['product']->cost_price === null
                            ? null
                            : (float) $line['product']->cost_price,
                        'quantity' => $line['quantity'],
                        'total_price' => $line['total_price'],
                    ]);

                    InventoryMovement::create([
                        'branch_id' => $inventory->branch_id,
                        'product_id' => $inventory->product_id,
                        'user_id' => $customer->id,
                        'type' => 'exit',
                        'quantity' => $line['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore - $line['quantity'],
                        'reason' => "Pedido online {$subOrder->sub_order_code}",
                    ]);
                }

                DeliveryTrackingEvent::create([
                    'sub_order_id' => $subOrder->id,
                    'status' => SubOrderStatus::Placed->value,
                    'event_timestamp' => now(),
                    'latitude' => (float) $branch->latitude,
                    'longitude' => (float) $branch->longitude,
                    'notes' => "Pedido recibido por {$store->business_name} ({$branch->branch_name}).",
                ]);

                $index++;
            }

            return $order->load([
                'address',
                'subOrders.branch.store',
                'subOrders.items',
                'subOrders.trackingEvents',
            ]);
        });
    }
}
