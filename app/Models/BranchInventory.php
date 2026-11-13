<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchInventory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'branch_inventory';

    protected $fillable = [
        'branch_id',
        'product_id',
        'price',
        'stock_quantity',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'branch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    #[Scope]
    protected function available(Builder $query): Builder
    {
        return $query->where('is_available', true)->where('stock_quantity', '>', 0);
    }
}
