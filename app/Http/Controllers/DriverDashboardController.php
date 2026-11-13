<?php

namespace App\Http\Controllers;

use App\Enums\SubOrderStatus;
use App\Models\DriverProfile;
use App\Models\SubOrder;
use App\Services\DeliveryDispatchService;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverDashboardController extends Controller
{
    public function __construct(
        private readonly DeliveryDispatchService $dispatch,
        private readonly GeoLocationService $geo,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = DriverProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'vehicle_type' => 'motorcycle',
                'current_latitude' => GeoLocationService::DEFAULT_LAT,
                'current_longitude' => GeoLocationService::DEFAULT_LNG,
                'is_active' => true,
                'is_available' => true,
                'last_ping_at' => now(),
            ]
        );

        $availablePool = SubOrder::query()
            ->where('status', SubOrderStatus::ReadyForPickup->value)
            ->whereNull('driver_id')
            ->with(['branch.store', 'order.customer', 'items'])
            ->latest()
            ->get()
            ->map(function (SubOrder $subOrder) use ($profile): array {
                $snapshot = $subOrder->order->delivery_snapshot ?? [];
                $destLat = (float) ($snapshot['latitude'] ?? $subOrder->branch->latitude);
                $destLng = (float) ($snapshot['longitude'] ?? $subOrder->branch->longitude);

                $pickupDistance = $profile->current_latitude !== null
                    ? $this->geo->distanceKm(
                        (float) $profile->current_latitude,
                        (float) $profile->current_longitude,
                        (float) $subOrder->branch->latitude,
                        (float) $subOrder->branch->longitude
                    )
                    : null;

                $tripDistance = $this->geo->distanceKm(
                    (float) $subOrder->branch->latitude,
                    (float) $subOrder->branch->longitude,
                    $destLat,
                    $destLng
                );

                return [
                    'sub_order' => $subOrder,
                    'pickup_distance_km' => $pickupDistance,
                    'trip_distance_km' => $tripDistance,
                ];
            });

        $myActiveDeliveries = SubOrder::query()
            ->where('driver_id', $user->id)
            ->whereIn('status', [
                SubOrderStatus::DriverAssigned->value,
                SubOrderStatus::OutForDelivery->value,
            ])
            ->with(['branch.store', 'order.customer', 'items', 'trackingEvents'])
            ->latest()
            ->get();

        $myCompletedDeliveries = SubOrder::query()
            ->where('driver_id', $user->id)
            ->where('status', SubOrderStatus::Delivered->value)
            ->with(['branch.store', 'order.customer'])
            ->latest('delivered_at')
            ->limit(10)
            ->get();

        return view('portals.driver', [
            'profile' => $profile,
            'availablePool' => $availablePool,
            'myActiveDeliveries' => $myActiveDeliveries,
            'myCompletedDeliveries' => $myCompletedDeliveries,
            'presetZones' => $this->geo->presetZones(),
        ]);
    }

    public function toggleShift(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = DriverProfile::query()->where('user_id', $user->id)->firstOrFail();

        $nextActive = ! $profile->is_active;
        $profile->update([
            'is_active' => $nextActive,
            'is_available' => $nextActive,
            'last_ping_at' => now(),
        ]);

        return back()->with('status', $nextActive ? 'Turno iniciado: ahora estás en línea para recibir envíos.' : 'Turno pausado.');
    }

    public function updateLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $this->dispatch->updateDriverLocation(
            $request->user(),
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            true
        );

        return back()->with('status', 'Ubicación GPS del repartidor actualizada.');
    }

    public function claimSubOrder(Request $request, SubOrder $subOrder): RedirectResponse
    {
        $claimed = $this->dispatch->claimByDriver($subOrder, $request->user());

        return back()->with('status', "¡Envío {$claimed->sub_order_code} asignado! Dirígete a {$claimed->branch->store->business_name} (PIN retiro: {$claimed->pickup_pin}).");
    }

    public function advanceSubOrder(Request $request, SubOrder $subOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([SubOrderStatus::OutForDelivery->value, SubOrderStatus::Delivered->value])],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = $this->dispatch->advanceByDriver(
            $subOrder,
            SubOrderStatus::from($validated['status']),
            $request->user(),
            null,
            null,
            $validated['notes'] ?? null
        );

        return back()->with('status', "Entrega {$updated->sub_order_code} actualizada a: {$updated->status->label()}.");
    }
}
