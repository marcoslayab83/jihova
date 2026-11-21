<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalPurchase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'user_id',
        'supplier_rif',
        'supplier_name',
        'invoice_number',
        'control_number',
        'purchase_date',
        'exchange_rate',
        'amount_taxable_usd',
        'amount_taxable_ves',
        'amount_exempt_usd',
        'amount_exempt_ves',
        'vat_percentage',
        'vat_amount_usd',
        'vat_amount_ves',
        'total_amount_usd',
        'total_amount_ves',
        'vat_withheld_amount_usd',
        'vat_withheld_amount_ves',
        'islr_withheld_amount_usd',
        'islr_withheld_amount_ves',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'exchange_rate' => 'decimal:4',
            'amount_taxable_usd' => 'decimal:2',
            'amount_taxable_ves' => 'decimal:2',
            'amount_exempt_usd' => 'decimal:2',
            'amount_exempt_ves' => 'decimal:2',
            'vat_percentage' => 'decimal:2',
            'vat_amount_usd' => 'decimal:2',
            'vat_amount_ves' => 'decimal:2',
            'total_amount_usd' => 'decimal:2',
            'total_amount_ves' => 'decimal:2',
            'vat_withheld_amount_usd' => 'decimal:2',
            'vat_withheld_amount_ves' => 'decimal:2',
            'islr_withheld_amount_usd' => 'decimal:2',
            'islr_withheld_amount_ves' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withholdingVouchers(): HasMany
    {
        return $this->hasMany(WithholdingVoucher::class);
    }
}
