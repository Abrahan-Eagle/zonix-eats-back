<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        if (config('app.debug')) {
            Log::debug('BroadcastingController@authenticate', [
                'channel' => $request->channel_name,
                'socket_id' => $request->socket_id,
                'user_id' => $request->user()?->id ?? 'guest',
            ]);
        }
        $result = Broadcast::auth($request);

        // Laravel devuelve array con 'auth' en éxito o Response en 403/404
        if (is_array($result) && array_key_exists('auth', $result)) {
            Cache::increment('metrics:realtime:broadcast_auth_success_total');
            $result['shared_secret'] = $result['shared_secret'] ?? null;

            return response()->json($result);
        }

        $statusCode = method_exists($result, 'getStatusCode') ? $result->getStatusCode() : 500;
        if ($statusCode === 403) {
            Cache::increment('metrics:realtime:broadcast_auth_denied_total');
            Log::warning('Broadcast auth denied', [
                'channel' => $request->channel_name,
                'user_id' => $request->user()?->id ?? 'guest',
            ]);
        } else {
            Cache::increment('metrics:realtime:broadcast_auth_error_total');
        }

        return $result;
    }
}
