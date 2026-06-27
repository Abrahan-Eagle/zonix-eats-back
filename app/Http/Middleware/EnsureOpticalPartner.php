<?php

namespace App\Http\Middleware;

use App\Models\OpticalPartner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpticalPartner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'optical_partner') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
                'error_code' => 'FORBIDDEN',
            ], 403);
        }

        $partner = $user->primaryOpticalPartner();
        if (! $partner) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario partner sin óptica asignada',
                'error_code' => 'PARTNER_NOT_LINKED',
            ], 403);
        }

        $request->attributes->set('optical_partner', $partner);

        return $next($request);
    }
}
