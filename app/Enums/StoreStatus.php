<?php

namespace App\Enums;

enum StoreStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case ClosedTemporarily = 'closed_temporarily';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de aprobación',
            self::Active => 'Activa',
            self::Suspended => 'Suspendida',
            self::ClosedTemporarily => 'Cerrada temporalmente',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-500/15 text-amber-300 border-amber-400/35',
            self::Active => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/35',
            self::Suspended => 'bg-[#FF3366]/15 text-[#FF6B8B] border-[#FF3366]/35',
            self::ClosedTemporarily => 'bg-white/10 text-slate-300 border-white/20',
        };
    }
}
