<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'accent_color' => $this->accent_color,
            'rating' => (float) $this->rating,
            'status' => $this->status?->value,
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'branches' => $this->whenLoaded('branches', fn () => $this->branches->map(fn ($branch): array => [
                'id' => $branch->id,
                'branch_name' => $branch->branch_name,
                'address' => $branch->address,
                'latitude' => (float) $branch->latitude,
                'longitude' => (float) $branch->longitude,
                'delivery_radius_km' => (float) $branch->delivery_radius_km,
                'base_delivery_fee' => (float) $branch->base_delivery_fee,
                'avg_prep_time_minutes' => (int) $branch->avg_prep_time_minutes,
                'is_open' => (bool) $branch->is_open,
            ])->values()),
        ];
    }
}
