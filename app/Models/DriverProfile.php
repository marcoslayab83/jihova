<?php

namespace App\Models;

use App\Concerns\SyncsSpatialPoint;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverProfile extends Model
{
    use HasFactory, SyncsSpatialPoint;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'license_plate',
        'current_latitude',
        'current_longitude',
        'current_location',
        'is_active',
        'is_available',
        'completed_deliveries',
        'last_ping_at',
    ];

    protected $hidden = [
        'current_location',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'current_latitude' => 'float',
            'current_longitude' => 'float',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'completed_deliveries' => 'integer',
            'last_ping_at' => 'datetime',
        ];
    }

    protected function spatialPointColumns(): array
    {
        return ['current_location', 'current_latitude', 'current_longitude'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(SubOrder::class, 'driver_id', 'user_id');
    }

    #[Scope]
    protected function readyForDispatch(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_available', true);
    }
}
