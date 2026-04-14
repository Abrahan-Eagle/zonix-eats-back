<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  string  $role  Uno o más roles separados por coma (ej: delivery,delivery_agent,delivery_company)
     * @return mixed
     */
    /**
     * @param  string  ...$roles  Uno o más roles (Laravel pasa cada valor separado por coma como argumento)
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $userRole = Auth::user()->role ?? null;
        $allowedRoles = array_map('trim', $roles);
        if (! in_array($userRole, $allowedRoles, true)) {
            Log::warning('[RoleMiddleware] 403 — rol no permitido', [
                'user_id' => Auth::id(),
                'user_role' => $userRole,
                'allowed_roles' => $allowedRoles,
                'path' => $request->path(),
            ]);

            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
