<?php

namespace App\Enums;

enum SubOrderStatus: string
{
    case Placed = 'placed';
    case AcceptedByStore = 'accepted_by_store';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case DriverAssigned = 'driver_assigned';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Placed => 'Recibido',
            self::AcceptedByStore => 'Aceptado por tienda',
            self::Preparing => 'En preparación',
            self::ReadyForPickup => 'Listo para recoger',
            self::DriverAssigned => 'Repartidor asignado',
            self::OutForDelivery => 'En camino a tu casa',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Placed => 'bg-white/10 text-slate-200 border-white/20',
            self::AcceptedByStore => 'bg-[#6C28FE]/20 text-violet-300 border-[#6C28FE]/45',
            self::Preparing => 'bg-amber-500/15 text-amber-300 border-amber-400/35',
            self::ReadyForPickup => 'bg-fuchsia-500/15 text-fuchsia-300 border-fuchsia-400/35',
            self::DriverAssigned => 'bg-[#00F0FF]/15 text-[#00F0FF] border-[#00F0FF]/35',
            self::OutForDelivery => 'bg-sky-500/15 text-sky-300 border-sky-400/35',
            self::Delivered => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/35',
            self::Cancelled => 'bg-[#FF3366]/15 text-[#FF6B8B] border-[#FF3366]/35',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::Placed => 1,
            self::AcceptedByStore => 2,
            self::Preparing => 3,
            self::ReadyForPickup => 4,
            self::DriverAssigned => 5,
            self::OutForDelivery => 6,
            self::Delivered => 7,
            self::Cancelled => 0,
        };
    }

    /**
     * @return list<self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::Placed => [self::AcceptedByStore, self::Cancelled],
            self::AcceptedByStore => [self::Preparing, self::Cancelled],
            self::Preparing => [self::ReadyForPickup, self::Cancelled],
            self::ReadyForPickup => [self::DriverAssigned, self::Cancelled],
            self::DriverAssigned => [self::OutForDelivery, self::Cancelled],
            self::OutForDelivery => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNextStatuses(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled], true);
    }
}
