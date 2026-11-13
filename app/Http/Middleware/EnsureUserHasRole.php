<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Debes iniciar sesión para continuar.'], 401)
                : redirect()->route('login');
        }

        $allowedRoles = array_map(
            static fn (string $role): UserRole => UserRole::from($role),
            $roles
        );

        if (! $user->hasRole(...$allowedRoles)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
