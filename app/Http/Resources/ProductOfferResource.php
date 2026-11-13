<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->resource['product'];
        $store = $this->resource['store'];
        $branch = $this->resource['branch'];
        $category = $this->resource['category'];

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'sku' => $product->sku,
                'image_url' => $product->image_url,
                'badge' => $product->badge,
            ],
            'store' => [
                'id' => $store->id,
                'business_name' => $store->business_name,
                'slug' => $store->slug,
                'rating' => (float) $store->rating,
            ],
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'branch' => [
                'id' => $branch->id,
                'branch_name' => $branch->branch_name,
                'is_open' => (bool) $branch->is_open,
            ],
            'price' => $this->resource['price'],
            'stock' => $this->resource['stock'],
            'distance_km' => $this->resource['distance_km'],
            'eta_minutes' => $this->resource['eta_minutes'],
            'delivery_fee' => $this->resource['delivery_fee'],
            'in_coverage' => $this->resource['in_coverage'],
        ];
    }
}
