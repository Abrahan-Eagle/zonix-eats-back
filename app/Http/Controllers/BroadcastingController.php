<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * Autenticar conexiones de broadcasting para canales privados/presence.
 * Delega en Laravel Broadcast::auth() para que el driver (Pusher) devuelva
 * el formato esperado: { "auth": "key:signature" }.
 * La autorización por canal está definida en routes/channels.php.
 */
class BroadcastingController extends Controller
{
    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
} 