<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'sub_order_id',
        'cash_shift_id',
        'method',
        'currency',
        'amount',
        'amount_usd_equivalent',
        'exchange_rate',
        'igtf_amount',
        'reference_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_usd_equivalent' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'igtf_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'efectivo_usd' => '💵 Efectivo Divisas ($)',
            'efectivo_ves' => '💵 Efectivo Bolívares (Bs)',
            'pago_movil' => '📱 Pago Móvil',
            'punto_venta' => '💳 Punto de Venta (POS)',
            'zelle' => '⚡ Zelle USD',
            'jihova_wallet' => '👛 Billetera JIHOVA',
            default => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }
}
