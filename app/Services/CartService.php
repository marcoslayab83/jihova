<?php

namespace App\Services;

use App\Enums\StoreStatus;
use App\Models\BranchInventory;
use App\Models\CustomerAddress;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const SESSION_CART_KEY = 'mall_cart';

    public const SESSION_LOCATION_KEY = 'mall_location';

    public const PLATFORM_FEE_RATE = 0.03;

    public function __construct(private readonly GeoLocationService $geo) {}

    /**
     * @return array{label: string, latitude: float, longitude: float, address_id: ?string}
     */
    public function activeLocation(?string $userId = null): array
    {
        $stored = session(self::SESSION_LOCATION_KEY);

        if (is_array($stored) && isset($stored['latitude'], $stored['longitude'])) {
            return [
                'label' => (string) ($stored['label'] ?? GeoLocationService::DEFAULT_ZONE_LABEL),
                'latitude' => (float) $stored['latitude'],
                'longitude' => (float) $stored['longitude'],
                'address_id' => $stored['address_id'] ?? null,
            ];
        }

        if ($userId !== null) {
            $defaultAddress = CustomerAddress::query()
                ->where('user_id', $userId)
                ->orderByDesc('is_default')
                ->first();

            if ($defaultAddress) {
                $location = [
                    'label' => $defaultAddress->title.' · '.$defaultAddress->street_address,
                    'latitude' => (float) $defaultAddress->latitude,
                    'longitude' => (float) $defaultAddress->longitude,
                    'address_id' => $defaultAddress->id,
                ];
                session([self::SESSION_LOCATION_KEY => $location]);

                return $location;
            }
        }

        return [
            'label' => GeoLocationService::DEFAULT_ZONE_LABEL,
            'latitude' => GeoLocationService::DEFAULT_LAT,
            'longitude' => GeoLocationService::DEFAULT_LNG,
            'address_id' => null,
        ];
    }

    public function setLocation(string $label, float $latitude, float $longitude, ?string $addressId = null): void
    {
        session([
            self::SESSION_LOCATION_KEY => [
                'label' => $label,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address_id' => $addressId,
            ],
        ]);
    }

    /**
     * @return array<string, array{branch_id: string, product_id: string, quantity: int}>
     */
    public function rawItems(): array
    {
        $items = session(self::SESSION_CART_KEY, []);

        return is_array($items) ? $items : [];
    }

    public function addItem(string $branchId, string $productId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $inventory = BranchInventory::query()
            ->with(['branch.store', 'product'])
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (! $inventory || ! $inventory->branch || ! $inventory->product) {
            throw ValidationException::withMessages([
                'product_id' => 'El producto seleccionado no está disponible en esa sucursal.',
            ]);
        }

        if ($inventory->branch->store->status !== StoreStatus::Active || ! $inventory->branch->is_open) {
            throw ValidationException::withMessages([
                'branch_id' => 'Esta sucursal está cerrada temporalmente.',
            ]);
        }

        if (! $inventory->product->is_active || ! $inventory->is_available || $inventory->stock_quantity < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Este producto se encuentra agotado en esta sucursal.',
            ]);
        }

        $items = $this->rawItems();
        $key = $this->key($branchId, $productId);
        $newQuantity = ($items[$key]['quantity'] ?? 0) + $quantity;

        if ($newQuantity > $inventory->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Solo quedan {$inventory->stock_quantity} unidades disponibles en {$inventory->branch->branch_name}.",
            ]);
        }

        $items[$key] = [
            'branch_id' => $branchId,
            'product_id' => $productId,
            'quantity' => $newQuantity,
        ];

        session([self::SESSION_CART_KEY => $items]);

        return $this->summary();
    }

    public function updateQuantity(string $branchId, string $productId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeItem($branchId, $productId);
        }

        $inventory = BranchInventory::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (! $inventory || ! $inventory->is_available || $quantity > $inventory->stock_quantity) {
            $max = $inventory?->stock_quantity ?? 0;

            throw ValidationException::withMessages([
                'quantity' => "Stock máximo disponible en esta sucursal: {$max}.",
            ]);
        }

        $items = $this->rawItems();
        $key = $this->key($branchId, $productId);
        $items[$key] = [
            'branch_id' => $branchId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ];

        session([self::SESSION_CART_KEY => $items]);

        return $this->summary();
    }

    public function removeItem(string $branchId, string $productId): array
    {
        $items = $this->rawItems();
        unset($items[$this->key($branchId, $productId)]);
        session([self::SESSION_CART_KEY => $items]);

        return $this->summary();
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_CART_KEY);
    }

    /**
     * @return array{
     *     items_count: int,
     *     stores_count: int,
     *     total_items_amount: float,
     *     total_delivery_fee: float,
     *     platform_fee: float,
     *     grand_total: float,
     *     store_groups: Collection<int, array<string, mixed>>,
     *     warnings: list<string>
     * }
     */
    public function summary(?float $customerLat = null, ?float $customerLng = null): array
    {
        $rawItems = $this->rawItems();
        if ($rawItems === []) {
            return [
                'items_count' => 0,
                'stores_count' => 0,
                'total_items_amount' => 0.0,
                'total_delivery_fee' => 0.0,
                'platform_fee' => 0.0,
                'grand_total' => 0.0,
                'store_groups' => collect(),
                'warnings' => [],
            ];
        }

        $location = $this->activeLocation();
        $lat = $customerLat ?? $location['latitude'];
        $lng = $customerLng ?? $location['longitude'];

        $branchIds = array_values(array_unique(array_column($rawItems, 'branch_id')));
        $productIds = array_values(array_unique(array_column($rawItems, 'product_id')));

        $inventories = BranchInventory::query()
            ->with(['branch.store.category', 'product.category'])
            ->whereIn('branch_id', $branchIds)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy(fn (BranchInventory $inv): string => $this->key($inv->branch_id, $inv->product_id));

        $warnings = [];
        $grouped = [];
        $itemsCount = 0;
        $totalItemsAmount = 0.0;

        foreach ($rawItems as $key => $entry) {
            /** @var BranchInventory|null $inventory */
            $inventory = $inventories->get($key);

            if (! $inventory || ! $inventory->branch || ! $inventory->product) {
                $warnings[] = 'Un producto de tu carrito ya no está disponible.';

                continue;
            }

            $qty = min((int) $entry['quantity'], max(1, (int) $inventory->stock_quantity));
            if ($inventory->stock_quantity < (int) $entry['quantity']) {
                $warnings[] = "Ajustamos {$inventory->product->name} al stock disponible ({$inventory->stock_quantity}).";
            }

            $unitPrice = (float) $inventory->price;
            $lineTotal = round($unitPrice * $qty, 2);
            $branchId = $inventory->branch_id;

            if (! isset($grouped[$branchId])) {
                $branch = $inventory->branch;
                $grouped[$branchId] = [
                    'branch' => $branch,
                    'store' => $branch->store,
                    'distance_km' => $this->geo->distanceToBranchKm($branch, $lat, $lng),
                    'eta_minutes' => $this->geo->estimatedMinutesForBranch($branch, $lat, $lng),
                    'delivery_fee' => $this->geo->deliveryFeeForBranch($branch, $lat, $lng),
                    'within_radius' => $this->geo->isWithinRadius($branch, $lat, $lng),
                    'subtotal' => 0.0,
                    'items' => [],
                ];
            }

            $grouped[$branchId]['items'][] = [
                'inventory' => $inventory,
                'product' => $inventory->product,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'is_available' => $inventory->is_available
                    && $inventory->stock_quantity >= $qty
                    && $inventory->branch->is_open
                    && $inventory->branch->store->status === StoreStatus::Active
                    && $inventory->product->is_active,
            ];
            $grouped[$branchId]['subtotal'] = round($grouped[$branchId]['subtotal'] + $lineTotal, 2);

            $itemsCount += $qty;
            $totalItemsAmount = round($totalItemsAmount + $lineTotal, 2);
        }

        $storeGroups = collect(array_values($grouped));
        $totalDeliveryFee = round((float) $storeGroups->sum('delivery_fee'), 2);
        $platformFee = $totalItemsAmount > 0 ? round(max(0.50, $totalItemsAmount * self::PLATFORM_FEE_RATE), 2) : 0.0;
        $grandTotal = round($totalItemsAmount + $totalDeliveryFee + $platformFee, 2);

        return [
            'items_count' => $itemsCount,
            'stores_count' => $storeGroups->count(),
            'total_items_amount' => $totalItemsAmount,
            'total_delivery_fee' => $totalDeliveryFee,
            'platform_fee' => $platformFee,
            'grand_total' => $grandTotal,
            'store_groups' => $storeGroups,
            'warnings' => $warnings,
        ];
    }

    private function key(string $branchId, string $productId): string
    {
        return "{$branchId}:{$productId}";
    }
}
