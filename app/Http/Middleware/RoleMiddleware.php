<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    /**
     * @param  string  $role  Uno o más roles separados por coma (ej: delivery,delivery_agent)
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $userRole = Auth::user()->role ?? null;
        $allowedRoles = array_map('trim', explode(',', $role));
        if (!Auth::check() || !in_array($userRole, $allowedRoles, true)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        return $next($request);
    }
}
