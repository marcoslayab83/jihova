<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductOfferResource;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use App\Models\Store;
use App\Services\GeoLocationService;
use App\Services\MallCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogApiController extends Controller
{
    public function __construct(private readonly MallCatalogService $catalog) {}

    public function categories(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::ordered()->get());
    }

    public function stores(Request $request): AnonymousResourceCollection
    {
        $categorySlug = $request->string('category')->toString();

        $stores = Store::query()
            ->active()
            ->with(['category', 'branches'])
            ->when($categorySlug !== '', function ($query) use ($categorySlug): void {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            })
            ->orderByDesc('rating')
            ->get();

        return StoreResource::collection($stores);
    }

    public function showStore(Store $store): StoreResource
    {
        abort_unless($store->status === StoreStatus::Active, 404);
        $store->load(['category', 'branches']);

        return new StoreResource($store);
    }

    public function search(Request $request): JsonResponse
    {
        $location = [
            'latitude' => (float) $request->input('lat', GeoLocationService::DEFAULT_LAT),
            'longitude' => (float) $request->input('lng', GeoLocationService::DEFAULT_LNG),
        ];

        $result = $this->catalog->explore([
            'q' => $request->string('q')->toString(),
            'category' => $request->string('category')->toString(),
            'store' => $request->string('store')->toString(),
            'open_only' => $request->boolean('open_only'),
            'within_radius' => $request->boolean('within_radius'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->string('sort', 'relevance')->toString(),
        ], $location);

        return response()->json([
            'location' => $location,
            'stores' => StoreResource::collection($result['stores']->pluck('store')),
            'offers' => ProductOfferResource::collection($result['offers']),
        ]);
    }
}
