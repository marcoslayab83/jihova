<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubOrderResource;
use App\Models\SubOrder;
use App\Services\DeliveryDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverApiController extends Controller
{
    public function __construct(private readonly DeliveryDispatchService $dispatch) {}

    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $profile = $this->dispatch->updateDriverLocation(
            $request->user(),
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $request->has('is_active') ? $request->boolean('is_active') : null,
            $request->has('is_available') ? $request->boolean('is_available') : null
        );

        return response()->json([
            'driver_id' => $profile->user_id,
            'latitude' => $profile->current_latitude,
            'longitude' => $profile->current_longitude,
            'is_active' => $profile->is_active,
            'is_available' => $profile->is_available,
            'last_ping_at' => $profile->last_ping_at?->toIso8601String(),
        ]);
    }

    public function advance(Request $request, SubOrder $subOrder): SubOrderResource
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([SubOrderStatus::OutForDelivery->value, SubOrderStatus::Delivered->value])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = $this->dispatch->advanceByDriver(
            $subOrder,
            SubOrderStatus::from($validated['status']),
            $request->user(),
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            $validated['notes'] ?? null
        );

        return new SubOrderResource($updated);
    }
}
