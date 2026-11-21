<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreBranch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    /**
     * Garantiza el aislamiento Multi-Tenant de Tiendas y valida sub-roles
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $requiredSubRole  Sub-rol requerido (owner, manager, cashier, accountant)
     */
    public function handle(Request $request, Closure $next, ?string $requiredSubRole = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'No autenticado.'], 401)
                : redirect()->route('login');
        }

        // Si es PlatformAdmin, tiene acceso global de auditoría
        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        // 1. Intentar resolver la tienda objetivo a partir de la ruta o payload
        /** @var Store|null $store */
        $store = null;

        if ($request->route('store') instanceof Store) {
            $store = $request->route('store');
        } elseif (is_string($request->route('store'))) {
            $store = Store::find($request->route('store'));
        } elseif ($request->route('branch') instanceof StoreBranch) {
            $store = $request->route('branch')->store;
        } elseif ($request->has('store_id')) {
            $store = Store::find($request->input('store_id'));
        } elseif ($request->has('branch_id')) {
            $branch = StoreBranch::with('store')->find($request->input('branch_id'));
            $store = $branch?->store;
        }

        // Si no se especifica una tienda particular (ej. /panel-tienda general o creación de primera tienda)
        if (! $store) {
            return $next($request);
        }

        // 2. Validar pertenencia y aislamiento Multi-Tenant
        if (! $user->canAccessStore($store)) {
            abort(403, 'Aislamiento de Tienda: No tiene autorización para acceder a los datos de este comercio.');
        }

        // 3. Validar sub-rol específico si fue solicitado
        if ($requiredSubRole && ! $user->hasStoreRole($store, $requiredSubRole)) {
            abort(403, "Se requiere el sub-rol '{$requiredSubRole}' para realizar esta acción contable o de caja.");
        }

        return $next($request);
    }
}
