<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function storeMemberships(): HasMany
    {
        return $this->hasMany(StoreUser::class, 'user_id');
    }

    public function assignedStores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->withPivot(['role', 'store_branch_id', 'is_active'])
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function driverDeliveries(): HasMany
    {
        return $this->hasMany(SubOrder::class, 'driver_id');
    }

    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class, 'user_id');
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'user_id');
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isMerchant(): bool
    {
        return in_array($this->role, [UserRole::MerchantAdmin, UserRole::MerchantStaff], true);
    }

    public function isDriver(): bool
    {
        return $this->role === UserRole::Driver;
    }

    public function isPlatformAdmin(): bool
    {
        return $this->role === UserRole::PlatformAdmin;
    }

    /**
     * Verifica si el usuario tiene acceso a la tienda dada (Dueño, Empleado con rol activo, o Admin Global)
     */
    public function canAccessStore(Store $store): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        if ($store->owner_id === $this->id) {
            return true;
        }

        return $this->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Verifica si el usuario posee un sub-rol específico dentro de la tienda (owner, manager, cashier, accountant)
     */
    public function hasStoreRole(Store $store, string ...$roles): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        if ($store->owner_id === $this->id && (empty($roles) || in_array('owner', $roles, true))) {
            return true;
        }

        $allowedRoles = empty($roles) ? ['owner', 'manager', 'cashier', 'accountant'] : $roles;

        return $this->storeMemberships()
            ->where('store_id', $store->id)
            ->whereIn('role', $allowedRoles)
            ->where('is_active', true)
            ->exists();
    }
}
