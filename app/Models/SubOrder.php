<?php

namespace App\Models;

use App\Enums\SubOrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'sub_order_code',
        'channel',
        'branch_id',
        'driver_id',
        'status',
        'payment_status',
        'subtotal',
        'delivery_fee',
        'commission_amount',
        'igtf_amount',
        'bcv_exchange_rate',
        'pickup_pin',
        'delivery_pin',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubOrderStatus::class,
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'igtf_amount' => 'decimal:2',
            'bcv_exchange_rate' => 'decimal:4',
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'branch_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(DeliveryTrackingEvent::class)->orderBy('event_timestamp');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isPosSale(): bool
    {
        return $this->channel === 'pos_counter';
    }
}
