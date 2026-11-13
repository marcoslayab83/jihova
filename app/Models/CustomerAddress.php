<?php

namespace App\Models;

use App\Concerns\SyncsSpatialPoint;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAddress extends Model
{
    use HasFactory, HasUuids, SyncsSpatialPoint;

    protected $fillable = [
        'user_id',
        'title',
        'street_address',
        'reference',
        'latitude',
        'longitude',
        'coordinates',
        'is_default',
    ];

    protected $hidden = [
        'coordinates',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_default' => 'boolean',
        ];
    }

    protected function spatialPointColumns(): array
    {
        return ['coordinates', 'latitude', 'longitude'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'address_id');
    }
}
