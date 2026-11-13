<?php

namespace App\Services;

use App\Enums\StoreStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MallCatalogService
{
    public function __construct(private readonly GeoLocationService $geo) {}

    /**
     * @param  array{q?: ?string, category?: ?string, store?: ?string, open_only?: bool, within_radius?: bool, max_price?: ?float, sort?: ?string}  $filters
     * @param  array{latitude: float, longitude: float}  $location
     * @return array{
     *     categories: Collection<int, Category>,
     *     stores: Collection<int, array<string, mixed>>,
     *     offers: Collection<int, array<string, mixed>>
     * }
     */
    public function explore(array $filters, array $location): array
    {
        $lat = (float) $location['latitude'];
        $lng = (float) $location['longitude'];
        $term = trim((string) ($filters['q'] ?? ''));
        $categorySlug = trim((string) ($filters['category'] ?? ''));
        $storeSlug = trim((string) ($filters['store'] ?? ''));
        $openOnly = (bool) ($filters['open_only'] ?? false);
        $withinRadius = (bool) ($filters['within_radius'] ?? false);
        $maxPrice = isset($filters['max_price']) && $filters['max_price'] !== '' ? (float) $filters['max_price'] : null;
        $sort = (string) ($filters['sort'] ?? 'relevance');

        $categories = Category::ordered()->get();

        $stores = Store::query()
            ->active()
            ->with(['category', 'branches'])
            ->when($categorySlug !== '', function (Builder $query) use ($categorySlug): void {
                $query->whereHas('category', fn (Builder $catQuery) => $catQuery->where('slug', $categorySlug));
            })
            ->when($storeSlug !== '', fn (Builder $query) => $query->where('slug', $storeSlug))
            ->when($term !== '', function (Builder $query) use ($term): void {
                $escaped = $this->escapeLike($term);
                $query->where(function (Builder $sub) use ($escaped): void {
                    $sub->where('business_name', 'like', "%{$escaped}%")
                        ->orWhere('tagline', 'like', "%{$escaped}%")
                        ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$escaped}%"))
                        ->orWhereHas('products', function (Builder $p) use ($escaped): void {
                            $p->where('is_active', true)
                                ->where(function (Builder $pq) use ($escaped): void {
                                    $pq->where('name', 'like', "%{$escaped}%")
                                        ->orWhere('description', 'like', "%{$escaped}%");
                                });
                        });
                });
            })
            ->get()
            ->map(function (Store $store) use ($lat, $lng): array {
                $bestBranch = $this->pickBestBranch($store->branches, $lat, $lng);
                $distance = $bestBranch ? $this->geo->distanceToBranchKm($bestBranch, $lat, $lng) : null;
                $eta = $bestBranch ? $this->geo->estimatedMinutesForBranch($bestBranch, $lat, $lng) : null;
                $inCoverage = $bestBranch ? $this->geo->isWithinRadius($bestBranch, $lat, $lng) : false;

                return [
                    'store' => $store,
                    'best_branch' => $bestBranch,
                    'open_branches_count' => $store->branches->where('is_open', true)->count(),
                    'distance_km' => $distance,
                    'eta_minutes' => $eta,
                    'in_coverage' => $inCoverage,
                    'delivery_fee' => $bestBranch ? $this->geo->deliveryFeeForBranch($bestBranch, $lat, $lng) : null,
                ];
            })
            ->when($openOnly, fn (Collection $col) => $col->filter(fn (array $item) => $item['open_branches_count'] > 0))
            ->when($withinRadius, fn (Collection $col) => $col->filter(fn (array $item) => $item['in_coverage']))
            ->values();

        $productsQuery = Product::query()
            ->active()
            ->with([
                'store.category',
                'category',
                'inventoryRecords' => function ($query) use ($maxPrice): void {
                    $query->available()
                        ->when($maxPrice !== null, fn ($q) => $q->where('price', '<=', $maxPrice))
                        ->with('branch');
                },
            ])
            ->whereHas('store', fn (Builder $q) => $q->where('status', StoreStatus::Active->value))
            ->when($categorySlug !== '', function (Builder $query) use ($categorySlug): void {
                $query->where(function (Builder $sub) use ($categorySlug): void {
                    $sub->whereHas('category', fn (Builder $c) => $c->where('slug', $categorySlug))
                        ->orWhereHas('store.category', fn (Builder $c) => $c->where('slug', $categorySlug));
                });
            })
            ->when($storeSlug !== '', function (Builder $query) use ($storeSlug): void {
                $query->whereHas('store', fn (Builder $s) => $s->where('slug', $storeSlug));
            })
            ->when($term !== '', function (Builder $query) use ($term): void {
                $escaped = $this->escapeLike($term);
                $driver = $query->getConnection()->getDriverName();

                $query->where(function (Builder $sub) use ($term, $escaped, $driver): void {
                    if (in_array($driver, ['mysql', 'mariadb'], true) && mb_strlen($term) >= 3) {
                        $sub->orWhereFullText(['name', 'description'], $term);
                    }

                    $sub->orWhere('name', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%")
                        ->orWhere('sku', 'like', "%{$escaped}%")
                        ->orWhereHas('store', fn (Builder $s) => $s->where('business_name', 'like', "%{$escaped}%"))
                        ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$escaped}%"));
                });
            });

        $offers = $productsQuery->get()
            ->map(function (Product $product) use ($lat, $lng, $openOnly, $withinRadius): ?array {
                $candidates = $product->inventoryRecords->filter(function (BranchInventory $inv) use ($openOnly, $withinRadius, $lat, $lng): bool {
                    if (! $inv->branch) {
                        return false;
                    }

                    if ($openOnly && ! $inv->branch->is_open) {
                        return false;
                    }

                    if ($withinRadius && ! $this->geo->isWithinRadius($inv->branch, $lat, $lng)) {
                        return false;
                    }

                    return true;
                });

                if ($candidates->isEmpty()) {
                    return null;
                }

                // Prefer open branches inside coverage first, then closest branch.
                $bestInventory = $candidates->sortBy(function (BranchInventory $inv) use ($lat, $lng): array {
                    $branch = $inv->branch;
                    $inRadius = $this->geo->isWithinRadius($branch, $lat, $lng) ? 0 : 1;
                    $closedPenalty = $branch->is_open ? 0 : 1;
                    $distance = $this->geo->distanceToBranchKm($branch, $lat, $lng);

                    return [$closedPenalty, $inRadius, $distance];
                })->first();

                if (! $bestInventory || ! $bestInventory->branch) {
                    return null;
                }

                $branch = $bestInventory->branch;
                $distance = $this->geo->distanceToBranchKm($branch, $lat, $lng);

                return [
                    'product' => $product,
                    'store' => $product->store,
                    'category' => $product->category,
                    'inventory' => $bestInventory,
                    'branch' => $branch,
                    'price' => (float) $bestInventory->price,
                    'stock' => (int) $bestInventory->stock_quantity,
                    'distance_km' => $distance,
                    'eta_minutes' => $this->geo->estimatedMinutesForBranch($branch, $lat, $lng),
                    'delivery_fee' => $this->geo->deliveryFeeForBranch($branch, $lat, $lng),
                    'in_coverage' => $this->geo->isWithinRadius($branch, $lat, $lng),
                ];
            })
            ->filter()
            ->values();

        $offers = match ($sort) {
            'price_asc' => $offers->sortBy('price')->values(),
            'price_desc' => $offers->sortByDesc('price')->values(),
            'fastest' => $offers->sortBy('eta_minutes')->values(),
            'rating' => $offers->sortByDesc(fn (array $offer) => (float) $offer['store']->rating)->values(),
            default => $offers->sortBy(function (array $offer): array {
                $closedPenalty = $offer['branch']->is_open ? 0 : 1;
                $coveragePenalty = $offer['in_coverage'] ? 0 : 1;

                return [$closedPenalty, $coveragePenalty, $offer['eta_minutes']];
            })->values(),
        };

        return [
            'categories' => $categories,
            'stores' => $stores,
            'offers' => $offers,
        ];
    }

    /**
     * @param  Collection<int, StoreBranch>  $branches
     */
    public function pickBestBranch(Collection $branches, float $lat, float $lng): ?StoreBranch
    {
        if ($branches->isEmpty()) {
            return null;
        }

        return $branches->sortBy(function (StoreBranch $branch) use ($lat, $lng): array {
            $closedPenalty = $branch->is_open ? 0 : 1;
            $coveragePenalty = $this->geo->isWithinRadius($branch, $lat, $lng) ? 0 : 1;
            $distance = $this->geo->distanceToBranchKm($branch, $lat, $lng);

            return [$closedPenalty, $coveragePenalty, $distance];
        })->first();
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
