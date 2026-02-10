<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Rate limiting general para API (configurable desde .env)
        $apiLimit = env('API_RATE_LIMIT', 60);
        RateLimiter::for('api', function (Request $request) use ($apiLimit) {
            return Limit::perMinute((int)$apiLimit)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting para autenticación (configurable; 10 por minuto en desarrollo para evitar 429 en login/Google)
        $authLimit = env('AUTH_RATE_LIMIT', 10);
        RateLimiter::for('auth', function (Request $request) use ($authLimit) {
            return Limit::perMinute((int)$authLimit)->by($request->ip());
        });

        // Rate limiting para creación de recursos
        $createLimit = env('CREATE_RATE_LIMIT', 10);
        RateLimiter::for('create', function (Request $request) use ($createLimit) {
            return Limit::perMinute((int)$createLimit)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
