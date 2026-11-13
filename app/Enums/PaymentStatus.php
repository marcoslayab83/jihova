<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Pago contra entrega',
            self::Paid => 'Pagado',
            self::Refunded => 'Reembolsado',
            self::Failed => 'Fallido',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-amber-500/15 text-amber-300 border-amber-400/35',
            self::Paid => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/35',
            self::Refunded => 'bg-[#00F0FF]/15 text-[#00F0FF] border-[#00F0FF]/35',
            self::Failed => 'bg-[#FF3366]/15 text-[#FF6B8B] border-[#FF3366]/35',
        };
    }
}
