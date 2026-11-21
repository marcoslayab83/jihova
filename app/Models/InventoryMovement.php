<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'branch_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_delta',
        'stock_before',
        'stock_after',
        'balance_after',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_delta' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'balance_after' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
