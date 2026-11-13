<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'base_price',
        'cost_price',
        'profit_margin_percent',
        'discount_percent',
        'image_url',
        'ai_enhanced_image_url',
        'badge',
        'prep_note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'profit_margin_percent' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryRecords(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
