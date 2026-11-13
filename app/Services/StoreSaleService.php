<?php

namespace App\Services;

use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\StoreSaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreSaleService
{
    /**
     * @param  list<array{product_id: string, quantity: int}>  $items
     */
    public function register(
        StoreBranch $branch,
        User $user,
        array $items,
        string $channel,
        string $paymentMethod,
        ?string $customerName,
        ?string $customerPhone,
        ?string $paymentReference,
        ?string $notes,
    ): StoreSale {
        return DB::transaction(function () use (
            $branch,
            $user,
            $items,
            $channel,
            $paymentMethod,
            $customerName,
            $customerPhone,
            $paymentReference,
            $notes,
        ): StoreSale {
            $productIds = array_column($items, 'product_id');
            $inventories = BranchInventory::query()
                ->with('product')
                ->where('branch_id', $branch->id)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $lines = [];
            $subtotal = 0.0;
            $totalAmount = 0.0;
            $discountAmount = 0.0;

            foreach ($items as $item) {
                /** @var BranchInventory|null $inventory */
                $inventory = $inventories->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if (
                    ! $inventory
                    || ! $inventory->product
                    || ! $inventory->product->is_active
                    || $inventory->product->store_id !== $branch->store_id
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los artículos no está disponible en esta sucursal.',
                    ]);
                }

                if (! $inventory->is_available || $inventory->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$inventory->product->name} (disponible: {$inventory->stock_quantity}).",
                    ]);
                }

                $unitPrice = (float) $inventory->price;
                $regularUnitPrice = max((float) $inventory->product->base_price, $unitPrice);
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal = round($subtotal + ($regularUnitPrice * $quantity), 2);
                $totalAmount = round($totalAmount + $lineTotal, 2);
                $discountAmount = round($discountAmount + (($regularUnitPrice - $unitPrice) * $quantity), 2);

                $lines[] = [
                    'inventory' => $inventory,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'unit_cost' => $inventory->product->cost_price === null
                        ? null
                        : (float) $inventory->product->cost_price,
                ];
            }

            $sale = StoreSale::create([
                'store_id' => $branch->store_id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'sale_number' => 'VTA-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'channel' => $channel,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'sold_at' => now(),
            ]);

            foreach ($lines as $line) {
                /** @var BranchInventory $inventory */
                $inventory = $line['inventory'];
                $stockBefore = $inventory->stock_quantity;
                $inventory->decrement('stock_quantity', $line['quantity']);

                StoreSaleItem::create([
                    'store_sale_id' => $sale->id,
                    'product_id' => $inventory->product_id,
                    'product_name' => $inventory->product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                    'unit_cost' => $line['unit_cost'],
                ]);

                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'product_id' => $inventory->product_id,
                    'user_id' => $user->id,
                    'type' => 'exit',
                    'quantity' => $line['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $line['quantity'],
                    'reason' => "Venta {$sale->sale_number}",
                ]);
            }

            return $sale->load('items');
        });
    }
}
