<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance_usd',
        'opening_balance_ves',
        'expected_cash_usd',
        'expected_cash_ves',
        'actual_cash_usd',
        'actual_cash_ves',
        'difference_usd',
        'difference_ves',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance_usd' => 'decimal:2',
            'opening_balance_ves' => 'decimal:2',
            'expected_cash_usd' => 'decimal:2',
            'expected_cash_ves' => 'decimal:2',
            'actual_cash_usd' => 'decimal:2',
            'actual_cash_ves' => 'decimal:2',
            'difference_usd' => 'decimal:2',
            'difference_ves' => 'decimal:2',
        ];
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Recalcula montos esperados en caja sumando las transacciones en efectivo
     */
    public function recalculateExpectedCash(): void
    {
        $cashUsd = (float) $this->opening_balance_usd + (float) $this->paymentTransactions()
            ->where('method', 'efectivo_usd')
            ->sum('amount');

        $cashVes = (float) $this->opening_balance_ves + (float) $this->paymentTransactions()
            ->where('method', 'efectivo_ves')
            ->sum('amount');

        $this->update([
            'expected_cash_usd' => $cashUsd,
            'expected_cash_ves' => $cashVes,
        ]);
    }
}
