<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreUser extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'store_users';

    protected $fillable = [
        'store_id',
        'user_id',
        'role',
        'store_branch_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(StoreBranch::class, 'store_branch_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['owner', 'manager'], true);
    }

    public function isCashier(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'cashier'], true);
    }

    public function isAccountant(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'accountant'], true);
    }
}
