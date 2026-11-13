<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_items_amount' => (float) $this->total_items_amount,
            'total_delivery_fee' => (float) $this->total_delivery_fee,
            'platform_fee' => (float) $this->platform_fee,
            'grand_total' => (float) $this->grand_total,
            'payment_status' => $this->payment_status->value,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'delivery_snapshot' => $this->delivery_snapshot,
            'created_at' => $this->created_at?->toIso8601String(),
            'sub_orders' => SubOrderResource::collection($this->whenLoaded('subOrders')),
        ];
    }
}
