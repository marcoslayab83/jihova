<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Enums\UserRole;
use App\Models\BranchInventory;
use App\Models\DeliveryTrackingEvent;
use App\Models\DriverProfile;
use App\Models\InventoryMovement;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryDispatchService
{
    public function __construct(private readonly GeoLocationService $geo) {}

    public function canManageBranch(User $user, StoreBranch $branch): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (! $user->isMerchant()) {
            return false;
        }

        $branch->loadMissing('store');

        return $branch->store->owner_id === $user->id;
    }

    public function advanceByMerchant(
        SubOrder $subOrder,
        SubOrderStatus $targetStatus,
        User $actor,
        ?string $notes = null,
    ): SubOrder {
        $subOrder->loadMissing(['branch.store', 'order']);

        if (! $this->canManageBranch($actor, $subOrder->branch)) {
            throw new AuthorizationException('No tienes permisos para gestionar pedidos de esta sucursal.');
        }

        $allowed = [
            SubOrderStatus::AcceptedByStore,
            SubOrderStatus::Preparing,
            SubOrderStatus::ReadyForPickup,
            SubOrderStatus::Cancelled,
        ];

        if (! in_array($targetStatus, $allowed, true) || ! $subOrder->status->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'status' => "No se puede cambiar el estado de '{$subOrder->status->label()}' a '{$targetStatus->label()}'.",
            ]);
        }

        return DB::transaction(function () use ($subOrder, $targetStatus, $notes): SubOrder {
            $locked = SubOrder::query()
                ->with(['branch.store'])
                ->where('id', $subOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'El pedido ya avanzó de estado.',
                ]);
            }

            if ($targetStatus === SubOrderStatus::Cancelled) {
                $this->restoreInventory($locked, $actor);
            }

            $locked->update(['status' => $targetStatus]);

            $defaultNote = match ($targetStatus) {
                SubOrderStatus::AcceptedByStore => "La tienda {$locked->branch->store->business_name} aceptó tu pedido.",
                SubOrderStatus::Preparing => 'La tienda está preparando y empacando tus artículos.',
                SubOrderStatus::ReadyForPickup => 'Pedido empacado y listo en mostrador para el repartidor del centro comercial.',
                SubOrderStatus::Cancelled => 'Pedido cancelado por el comercio.',
                default => 'Estado actualizado por el comercio.',
            };

            DeliveryTrackingEvent::create([
                'sub_order_id' => $locked->id,
                'status' => $targetStatus->value,
                'event_timestamp' => now(),
                'latitude' => (float) $locked->branch->latitude,
                'longitude' => (float) $locked->branch->longitude,
                'notes' => $notes ?: $defaultNote,
            ]);

            return $locked->fresh(['branch.store', 'driver.driverProfile', 'items', 'trackingEvents']);
        });
    }

    public function autoAssignNearestDriver(SubOrder $subOrder, User $actor): SubOrder
    {
        $subOrder->loadMissing('branch.store');

        if (! $this->canManageBranch($actor, $subOrder->branch) && ! $actor->isPlatformAdmin()) {
            throw new AuthorizationException('No autorizado para despachar este pedido.');
        }

        if ($subOrder->status !== SubOrderStatus::ReadyForPickup) {
            throw ValidationException::withMessages([
                'status' => 'El pedido debe estar listo para recoger antes de asignar un repartidor.',
            ]);
        }

        $branchLat = (float) $subOrder->branch->latitude;
        $branchLng = (float) $subOrder->branch->longitude;

        $candidates = DriverProfile::query()
            ->readyForDispatch()
            ->with('user')
            ->get()
            ->sortBy(function (DriverProfile $profile) use ($branchLat, $branchLng): float {
                if ($profile->current_latitude === null || $profile->current_longitude === null) {
                    return 999.0;
                }

                return $this->geo->distanceKm(
                    (float) $profile->current_latitude,
                    (float) $profile->current_longitude,
                    $branchLat,
                    $branchLng
                );
            });

        $bestDriver = $candidates->first()?->user;

        if (! $bestDriver) {
            throw ValidationException::withMessages([
                'driver' => 'No hay repartidores disponibles en línea en este instante. El pedido permanece en la bolsa de envíos.',
            ]);
        }

        return $this->claimByDriver($subOrder, $bestDriver);
    }

    public function claimByDriver(SubOrder $subOrder, User $driver): SubOrder
    {
        if (! $driver->hasRole(UserRole::Driver, UserRole::PlatformAdmin)) {
            throw new AuthorizationException('Solo repartidores autorizados pueden tomar envíos.');
        }

        return DB::transaction(function () use ($subOrder, $driver): SubOrder {
            $locked = SubOrder::query()
                ->with(['branch.store'])
                ->where('id', $subOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== SubOrderStatus::ReadyForPickup || $locked->driver_id !== null) {
                throw ValidationException::withMessages([
                    'sub_order' => 'Este envío ya fue tomado por otro repartidor o aún no está listo.',
                ]);
            }

            $profile = DriverProfile::query()
                ->where('user_id', $driver->id)
                ->lockForUpdate()
                ->first();

            $locked->update([
                'driver_id' => $driver->id,
                'status' => SubOrderStatus::DriverAssigned,
            ]);

            if ($profile) {
                $profile->update([
                    'is_active' => true,
                    'is_available' => false,
                    'last_ping_at' => now(),
                ]);
            }

            $lat = $profile?->current_latitude ?? (float) $locked->branch->latitude;
            $lng = $profile?->current_longitude ?? (float) $locked->branch->longitude;

            DeliveryTrackingEvent::create([
                'sub_order_id' => $locked->id,
                'status' => SubOrderStatus::DriverAssigned->value,
                'event_timestamp' => now(),
                'latitude' => $lat,
                'longitude' => $lng,
                'notes' => "Repartidor {$driver->full_name} asignado y dirigiéndose a {$locked->branch->store->business_name}.",
            ]);

            return $locked->fresh(['branch.store', 'driver.driverProfile', 'items', 'trackingEvents']);
        });
    }

    public function advanceByDriver(
        SubOrder $subOrder,
        SubOrderStatus $targetStatus,
        User $driver,
        ?float $lat = null,
        ?float $lng = null,
        ?string $notes = null,
    ): SubOrder {
        if (! $driver->isPlatformAdmin() && $subOrder->driver_id !== $driver->id) {
            throw new AuthorizationException('Este envío pertenece a otro repartidor.');
        }

        $allowed = [SubOrderStatus::OutForDelivery, SubOrderStatus::Delivered];
        if (! in_array($targetStatus, $allowed, true) || ! $subOrder->status->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'status' => "Transición de delivery inválida desde '{$subOrder->status->label()}'.",
            ]);
        }

        return DB::transaction(function () use ($subOrder, $targetStatus, $driver, $lat, $lng, $notes): SubOrder {
            $locked = SubOrder::query()
                ->with(['branch.store', 'order'])
                ->where('id', $subOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'El estado del envío ya cambió.',
                ]);
            }

            $profile = DriverProfile::query()
                ->where('user_id', $locked->driver_id)
                ->lockForUpdate()
                ->first();

            $deliveryCoords = $locked->order->delivery_snapshot ?? [];
            $eventLat = $lat ?? ($targetStatus === SubOrderStatus::Delivered
                ? (float) ($deliveryCoords['latitude'] ?? $locked->branch->latitude)
                : (float) ($profile?->current_latitude ?? $locked->branch->latitude));
            $eventLng = $lng ?? ($targetStatus === SubOrderStatus::Delivered
                ? (float) ($deliveryCoords['longitude'] ?? $locked->branch->longitude)
                : (float) ($profile?->current_longitude ?? $locked->branch->longitude));

            if ($profile) {
                $profileUpdate = [
                    'current_latitude' => $eventLat,
                    'current_longitude' => $eventLng,
                    'last_ping_at' => now(),
                ];

                if ($targetStatus === SubOrderStatus::Delivered) {
                    $profileUpdate['completed_deliveries'] = $profile->completed_deliveries + 1;
                }

                $profile->update($profileUpdate);
            }

            $updateData = ['status' => $targetStatus];
            if ($targetStatus === SubOrderStatus::Delivered) {
                $updateData['delivered_at'] = now();
            }
            $locked->update($updateData);

            if ($targetStatus === SubOrderStatus::Delivered) {
                if ($profile) {
                    $hasOtherActive = SubOrder::query()
                        ->where('driver_id', $profile->user_id)
                        ->whereIn('status', [
                            SubOrderStatus::DriverAssigned->value,
                            SubOrderStatus::OutForDelivery->value,
                        ])
                        ->exists();

                    if (! $hasOtherActive) {
                        $profile->update(['is_available' => true]);
                    }
                }

                $order = $locked->order;
                if ($order->payment_status === PaymentStatus::Unpaid) {
                    $hasUndelivered = SubOrder::query()
                        ->where('order_id', $order->id)
                        ->whereNotIn('status', [SubOrderStatus::Delivered->value, SubOrderStatus::Cancelled->value])
                        ->exists();

                    if (! $hasUndelivered) {
                        $order->update([
                            'payment_status' => PaymentStatus::Paid,
                            'paid_at' => now(),
                        ]);
                    }
                }
            }

            $defaultNote = $targetStatus === SubOrderStatus::OutForDelivery
                ? "Repartidor {$driver->full_name} retiró el pedido de {$locked->branch->store->business_name} y va rumbo a tu dirección."
                : "Pedido entregado en destino por {$driver->full_name}. ¡Gracias por comprar en JIHOVA!";

            DeliveryTrackingEvent::create([
                'sub_order_id' => $locked->id,
                'status' => $targetStatus->value,
                'event_timestamp' => now(),
                'latitude' => $eventLat,
                'longitude' => $eventLng,
                'notes' => $notes ?: $defaultNote,
            ]);

            return $locked->fresh(['branch.store', 'driver.driverProfile', 'items', 'trackingEvents', 'order']);
        });
    }

    public function updateDriverLocation(
        User $driver,
        float $lat,
        float $lng,
        ?bool $isActive = null,
        ?bool $isAvailable = null,
    ): DriverProfile {
        $profile = DriverProfile::query()->firstOrCreate(
            ['user_id' => $driver->id],
            [
                'vehicle_type' => 'motorcycle',
                'is_active' => true,
                'is_available' => true,
            ]
        );

        $profile->update([
            'current_latitude' => $lat,
            'current_longitude' => $lng,
            'is_active' => $isActive ?? $profile->is_active,
            'is_available' => $isAvailable ?? $profile->is_available,
            'last_ping_at' => now(),
        ]);

        return $profile->fresh();
    }

    private function restoreInventory(SubOrder $subOrder, User $actor): void
    {
        $subOrder->loadMissing('items');

        foreach ($subOrder->items as $item) {
            $inventory = BranchInventory::query()
                ->where('branch_id', $subOrder->branch_id)
                ->where('product_id', $item->product_id)
                ->lockForUpdate()
                ->firstOrFail();
            $stockBefore = $inventory->stock_quantity;
            $inventory->increment('stock_quantity', $item->quantity);

            InventoryMovement::create([
                'branch_id' => $subOrder->branch_id,
                'product_id' => $item->product_id,
                'user_id' => $actor->id,
                'type' => 'entry',
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $item->quantity,
                'reason' => "Devolución por cancelación {$subOrder->sub_order_code}",
            ]);
        }
    }
}
