<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalInvoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'store_branch_id',
        'order_id',
        'sub_order_id',
        'user_id',
        'document_type',
        'correlative_number',
        'control_number',
        'affected_document_number',
        'customer_rif_doc',
        'customer_name',
        'customer_address',
        'customer_phone',
        'exchange_rate',
        'amount_taxable_usd',
        'amount_taxable_ves',
        'amount_exempt_usd',
        'amount_exempt_ves',
        'vat_percentage',
        'vat_amount_usd',
        'vat_amount_ves',
        'igtf_percentage',
        'igtf_amount_usd',
        'igtf_amount_ves',
        'total_amount_usd',
        'total_amount_ves',
        'status',
        'issued_at',
        'annulled_at',
        'annulment_reason',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:4',
            'amount_taxable_usd' => 'decimal:2',
            'amount_taxable_ves' => 'decimal:2',
            'amount_exempt_usd' => 'decimal:2',
            'amount_exempt_ves' => 'decimal:2',
            'vat_percentage' => 'decimal:2',
            'vat_amount_usd' => 'decimal:2',
            'vat_amount_ves' => 'decimal:2',
            'igtf_percentage' => 'decimal:2',
            'igtf_amount_usd' => 'decimal:2',
            'igtf_amount_ves' => 'decimal:2',
            'total_amount_usd' => 'decimal:2',
            'total_amount_ves' => 'decimal:2',
            'issued_at' => 'datetime',
            'annulled_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'store_branch_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForStore(Builder $query, string $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeInvoices(Builder $query): Builder
    {
        return $query->where('document_type', 'invoice');
    }

    public function isAnnulled(): bool
    {
        return $this->status === 'annulled';
    }

    public function documentTypeLabel(): string
    {
        return match ($this->document_type) {
            'invoice' => 'Factura Fiscal',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            'delivery_note' => 'Nota de Entrega',
            default => 'Documento Fiscal',
        };
    }
}
