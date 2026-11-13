<?php

namespace App\Models;

use App\Concerns\SyncsSpatialPoint;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreBranch extends Model
{
    use HasFactory, HasUuids, SyncsSpatialPoint;

    protected $fillable = [
        'store_id',
        'branch_name',
        'address',
        'latitude',
        'longitude',
        'location',
        'delivery_radius_km',
        'base_delivery_fee',
        'avg_prep_time_minutes',
        'is_open',
    ];

    protected $hidden = [
        'location',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'delivery_radius_km' => 'decimal:2',
            'base_delivery_fee' => 'decimal:2',
            'avg_prep_time_minutes' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    protected function spatialPointColumns(): array
    {
        return ['location', 'latitude', 'longitude'];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(BranchInventory::class, 'branch_id');
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class, 'branch_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(StoreSale::class, 'branch_id');
    }

    #[Scope]
    protected function open(Builder $query): Builder
    {
        return $query->where('is_open', true);
    }
}
