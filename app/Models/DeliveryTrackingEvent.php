<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTrackingEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'sub_order_id',
        'status',
        'event_timestamp',
        'latitude',
        'longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_timestamp' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }
}
