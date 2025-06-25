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
    public function handle(Request $request, Closure $next, $role)
    {
        $userRole = Auth::user()->role ?? null;
        // Permitir 'users' como equivalente a 'buyer'
        if ($role === 'buyer' && $userRole === 'users') {
            return $next($request);
        }
        if (!Auth::check() || $userRole !== $role) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        return $next($request);
    }
}
