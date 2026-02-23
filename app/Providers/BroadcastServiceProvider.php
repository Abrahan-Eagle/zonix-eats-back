<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Si el driver es pusher pero no hay llaves, evitamos registrar rutas y canales 
        // para prevenir errores fatales durante el inicio de la app (ej. en migraciones).
        if (config('broadcasting.default') === 'pusher' && !config('broadcasting.connections.pusher.key')) {
            return;
        }

        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
