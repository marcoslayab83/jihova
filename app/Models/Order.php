<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_number',
        'customer_id',
        'address_id',
        'total_items_amount',
        'total_delivery_fee',
        'platform_fee',
        'grand_total',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'customer_notes',
        'delivery_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'total_items_amount' => 'decimal:2',
            'total_delivery_fee' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'delivery_snapshot' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'card' => 'Tarjeta de Crédito / Débito',
            'mobile_pay' => 'Pago Móvil / Transferencia',
            'wallet' => 'Billetera JIHOVA',
            'cash_on_delivery' => 'Efectivo contra entrega',
            default => ucfirst(str_replace('_', ' ', (string) $this->payment_method)),
        };
    }
}
