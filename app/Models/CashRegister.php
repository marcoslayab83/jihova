<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashRegister extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_branch_id',
        'name',
        'status',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'store_branch_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function activeShift(): HasOne
    {
        return $this->hasOne(CashShift::class)->where('status', 'open')->latestOfMany();
    }

    public function isOpen(): bool
    {
        return $this->shifts()->where('status', 'open')->exists();
    }
}
