<?php

namespace App\Models;

use App\Enums\StoreStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'category_id',
        'business_name',
        'slug',
        'tagline',
        'description',
        'logo_url',
        'banner_url',
        'accent_color',
        'phone',
        'status',
        'commission_rate',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'status' => StoreStatus::class,
            'commission_rate' => 'decimal:2',
            'rating' => 'decimal:1',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(StoreBranch::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function adBanners(): HasMany
    {
        return $this->hasMany(MallAdBanner::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(StoreSale::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', StoreStatus::Active->value);
    }
}
