<?php

namespace App\Http\Controllers;

use App\Enums\StoreStatus;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\MallAdBanner;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use App\Services\GeoLocationService;
use App\Services\MallCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MallController extends Controller
{
    public function __construct(
        private readonly MallCatalogService $catalog,
        private readonly GeoLocationService $geo,
        private readonly CartService $cart,
    ) {}

    public function home(Request $request): View
    {
        $location = $this->cart->activeLocation($request->user()?->id);
        $data = $this->catalog->explore([], $location);

        $savedAddresses = $request->user()
            ? CustomerAddress::query()->where('user_id', $request->user()->id)->orderByDesc('is_default')->get()
            : collect();

        $heroAdBanners = MallAdBanner::query()
            ->active()
            ->forHero()
            ->with(['store', 'product'])
            ->latest()
            ->take(4)
            ->get();

        $vitrinaAdBanners = MallAdBanner::query()
            ->active()
            ->forVitrina()
            ->with(['store', 'product'])
            ->latest()
            ->take(6)
            ->get();

        return view('mall.home', [
            'categories' => $data['categories'],
            'stores' => $data['stores'],
            'featuredOffers' => $data['offers']->take(12),
            'activeLocation' => $location,
            'presetZones' => $this->geo->presetZones(),
            'savedAddresses' => $savedAddresses,
            'cartSummary' => $this->cart->summary($location['latitude'], $location['longitude']),
            'heroAdBanners' => $heroAdBanners,
            'vitrinaAdBanners' => $vitrinaAdBanners,
        ]);
    }

    public function clickAdBanner(MallAdBanner $adBanner): RedirectResponse
    {
        $adBanner->increment('clicks_count');

        if ($adBanner->target_url) {
            return redirect()->away($adBanner->target_url);
        }

        if ($adBanner->product) {
            return redirect()->route('mall.products.show', [$adBanner->store, $adBanner->product]);
        }

        return redirect()->route('mall.stores.show', $adBanner->store);
    }

    public function explore(Request $request): View
    {
        $location = $this->cart->activeLocation($request->user()?->id);
        $filters = [
            'q' => $request->string('q')->toString(),
            'category' => $request->string('category')->toString(),
            'store' => $request->string('store')->toString(),
            'open_only' => $request->boolean('open_only'),
            'within_radius' => $request->boolean('within_radius'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->string('sort', 'relevance')->toString(),
        ];

        $data = $this->catalog->explore($filters, $location);

        return view('mall.explore', [
            'categories' => $data['categories'],
            'stores' => $data['stores'],
            'offers' => $data['offers'],
            'filters' => $filters,
            'activeLocation' => $location,
            'presetZones' => $this->geo->presetZones(),
            'cartSummary' => $this->cart->summary($location['latitude'], $location['longitude']),
        ]);
    }

    public function stores(Request $request): View
    {
        $location = $this->cart->activeLocation($request->user()?->id);
        $categorySlug = $request->string('category')->toString();

        $data = $this->catalog->explore(['category' => $categorySlug], $location);

        return view('mall.stores', [
            'categories' => $data['categories'],
            'stores' => $data['stores'],
            'selectedCategory' => $categorySlug,
            'activeLocation' => $location,
        ]);
    }

    public function showStore(Request $request, Store $store): View
    {
        if ($store->status !== StoreStatus::Active) {
            abort(404);
        }

        $location = $this->cart->activeLocation($request->user()?->id);
        $store->load(['category', 'branches']);

        $selectedBranchId = $request->string('branch')->toString();
        $activeBranch = $store->branches->firstWhere('id', $selectedBranchId)
            ?? $this->catalog->pickBestBranch($store->branches, $location['latitude'], $location['longitude']);

        $products = Product::query()
            ->active()
            ->where('store_id', $store->id)
            ->with(['category', 'inventoryRecords'])
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($activeBranch): array {
                $inventory = $activeBranch
                    ? $product->inventoryRecords->firstWhere('branch_id', $activeBranch->id)
                    : null;

                return [
                    'product' => $product,
                    'inventory' => $inventory,
                    'price' => $inventory ? (float) $inventory->price : (float) $product->base_price,
                    'stock' => $inventory ? (int) $inventory->stock_quantity : 0,
                    'is_available' => $inventory !== null
                        && $inventory->is_available
                        && $inventory->stock_quantity > 0
                        && ($activeBranch?->is_open ?? false),
                ];
            });

        $branchMetrics = $activeBranch ? [
            'distance_km' => $this->geo->distanceToBranchKm($activeBranch, $location['latitude'], $location['longitude']),
            'eta_minutes' => $this->geo->estimatedMinutesForBranch($activeBranch, $location['latitude'], $location['longitude']),
            'delivery_fee' => $this->geo->deliveryFeeForBranch($activeBranch, $location['latitude'], $location['longitude']),
            'in_coverage' => $this->geo->isWithinRadius($activeBranch, $location['latitude'], $location['longitude']),
        ] : null;

        return view('mall.store-show', [
            'store' => $store,
            'activeBranch' => $activeBranch,
            'branchMetrics' => $branchMetrics,
            'products' => $products,
            'activeLocation' => $location,
            'cartSummary' => $this->cart->summary($location['latitude'], $location['longitude']),
        ]);
    }

    public function showProduct(Request $request, Store $store, Product $product): View
    {
        if ($store->status !== StoreStatus::Active || $product->store_id !== $store->id || ! $product->is_active) {
            abort(404);
        }

        $location = $this->cart->activeLocation($request->user()?->id);
        $store->load('category');
        $product->load(['category', 'inventoryRecords.branch']);

        $branchOffers = $product->inventoryRecords
            ->filter(fn ($inv): bool => $inv->branch !== null)
            ->map(function ($inv) use ($location): array {
                $branch = $inv->branch;

                return [
                    'inventory' => $inv,
                    'branch' => $branch,
                    'price' => (float) $inv->price,
                    'stock' => (int) $inv->stock_quantity,
                    'is_available' => $inv->is_available && $inv->stock_quantity > 0 && $branch->is_open,
                    'distance_km' => $this->geo->distanceToBranchKm($branch, $location['latitude'], $location['longitude']),
                    'eta_minutes' => $this->geo->estimatedMinutesForBranch($branch, $location['latitude'], $location['longitude']),
                    'delivery_fee' => $this->geo->deliveryFeeForBranch($branch, $location['latitude'], $location['longitude']),
                    'in_coverage' => $this->geo->isWithinRadius($branch, $location['latitude'], $location['longitude']),
                ];
            })
            ->sortBy('distance_km')
            ->values();

        $relatedProducts = Product::query()
            ->active()
            ->where('store_id', $store->id)
            ->where('id', '!=', $product->id)
            ->with(['inventoryRecords.branch'])
            ->limit(4)
            ->get();

        return view('mall.product-show', [
            'store' => $store,
            'product' => $product,
            'branchOffers' => $branchOffers,
            'relatedProducts' => $relatedProducts,
            'activeLocation' => $location,
        ]);
    }

    public function updateLocation(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['nullable', 'uuid'],
            'zone_key' => ['nullable', 'string'],
            'label' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (! empty($validated['address_id']) && $request->user()) {
            $address = CustomerAddress::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($validated['address_id']);

            $this->cart->setLocation(
                $address->title.' · '.$address->street_address,
                (float) $address->latitude,
                (float) $address->longitude,
                $address->id
            );
        } elseif (! empty($validated['zone_key'])) {
            $zone = collect($this->geo->presetZones())->firstWhere('key', $validated['zone_key']);
            if ($zone) {
                $this->cart->setLocation($zone['label'], $zone['latitude'], $zone['longitude']);
            }
        } elseif (isset($validated['latitude'], $validated['longitude'])) {
            $this->cart->setLocation(
                $validated['label'] ?? 'Ubicación personalizada',
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'location' => $this->cart->activeLocation($request->user()?->id),
            ]);
        }

        return back()->with('status', 'Ubicación de entrega actualizada.');
    }

    public function progress(): View
    {
        $counts = [
            'categories' => Category::count(),
            'stores' => Store::count(),
            'products' => Product::count(),
        ];

        return view('mall.progress', [
            'counts' => $counts,
        ]);
    }
}
