<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSaleItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_sale_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'unit_cost',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(StoreSale::class, 'store_sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
