<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * Autenticar conexiones de broadcasting para canales privados/presence.
 * Delega en Laravel Broadcast::auth() para que el driver (Pusher) devuelva
 * el formato esperado: { "auth": "key:signature" }.
 * El SDK pusher_channels_flutter (Android) exige también "shared_secret"; para canales
 * no cifrados se envía null. La autorización por canal está en routes/channels.php.
 */
class BroadcastingController extends Controller
{
    public function authenticate(Request $request)
    {
        $result = Broadcast::auth($request);

        // Laravel devuelve array con 'auth' en éxito o Response en 403/404
        if (is_array($result) && array_key_exists('auth', $result)) {
            $result['shared_secret'] = $result['shared_secret'] ?? null;
            return response()->json($result);
        }

        return $result;
    }
} 