<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithholdingVoucher extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'fiscal_purchase_id',
        'fiscal_invoice_id',
        'voucher_type',
        'voucher_number',
        'period_year_month',
        'voucher_date',
        'subject_rif',
        'subject_name',
        'exchange_rate',
        'taxable_amount_ves',
        'tax_amount_ves',
        'withholding_percentage',
        'withheld_amount_ves',
        'withheld_amount_usd',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'date',
            'exchange_rate' => 'decimal:4',
            'taxable_amount_ves' => 'decimal:2',
            'tax_amount_ves' => 'decimal:2',
            'withholding_percentage' => 'decimal:2',
            'withheld_amount_ves' => 'decimal:2',
            'withheld_amount_usd' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(FiscalPurchase::class, 'fiscal_purchase_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FiscalInvoice::class, 'fiscal_invoice_id');
    }
}
