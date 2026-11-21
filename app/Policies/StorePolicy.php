<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    /**
     * Determina si el usuario puede listar tiendas en el panel
     */
    public function viewAny(User $user): bool
    {
        return $user->isPlatformAdmin() || $user->stores()->exists() || $user->storeMemberships()->where('is_active', true)->exists();
    }

    /**
     * Determina si el usuario puede ver los datos de una tienda específica (Aislamiento Multi-Tenant)
     */
    public function view(User $user, Store $store): bool
    {
        return $user->canAccessStore($store);
    }

    /**
     * Determina si el usuario puede modificar la configuración de la tienda
     */
    public function update(User $user, Store $store): bool
    {
        return $user->isPlatformAdmin() || $user->hasStoreRole($store, 'owner', 'manager');
    }

    /**
     * Determina si el usuario puede gestionar empleados y sub-roles (owner, cashier, accountant)
     */
    public function manageStaff(User $user, Store $store): bool
    {
        return $user->isPlatformAdmin() || $user->hasStoreRole($store, 'owner');
    }

    /**
     * Determina si el usuario puede gestionar libros contables, retenciones y facturación fiscal SENIAT
     */
    public function manageFinance(User $user, Store $store): bool
    {
        return $user->isPlatformAdmin() || $user->hasStoreRole($store, 'owner', 'manager', 'accountant');
    }

    /**
     * Determina si el usuario puede operar el Punto de Venta (POS) y cobrar en mostrador
     */
    public function operatePos(User $user, Store $store): bool
    {
        return $user->isPlatformAdmin() || $user->hasStoreRole($store, 'owner', 'manager', 'cashier');
    }
}
