<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_order_code' => $this->sub_order_code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'commission_amount' => (float) $this->commission_amount,
            'estimated_delivery_at' => $this->estimated_delivery_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'branch' => $this->whenLoaded('branch', fn (): array => [
                'id' => $this->branch->id,
                'branch_name' => $this->branch->branch_name,
                'store_name' => $this->branch->store?->business_name,
                'store_slug' => $this->branch->store?->slug,
            ]),
            'driver' => $this->whenLoaded('driver', fn (): ?array => $this->driver ? [
                'id' => $this->driver->id,
                'full_name' => $this->driver->full_name,
                'phone' => $this->driver->phone,
            ] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'total_price' => (float) $item->total_price,
            ])->values()),
            'tracking_events' => $this->whenLoaded('trackingEvents', fn () => $this->trackingEvents->map(fn ($event): array => [
                'status' => $event->status,
                'timestamp' => $event->event_timestamp->toIso8601String(),
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'notes' => $event->notes,
            ])->values()),
        ];
    }
}
