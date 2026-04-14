<?php

/*
|--------------------------------------------------------------------------
| API Routes — Split by domain
|--------------------------------------------------------------------------
| Each file handles its own middleware and prefix groups.
*/

use Illuminate\Support\Facades\Log;

if (config('app.debug')) {
    // path() evita filtrar tokens/query sensibles en logs (fullUrl exponía credenciales en query).
    Log::debug('🌐 Incoming API Request: '.request()->method().' '.request()->path(), [
        'ip' => request()->ip(),
        'agent' => request()->userAgent(),
    ]);
}

require __DIR__.'/api/public.php';
require __DIR__.'/api/auth.php';
require __DIR__.'/api/buyer.php';
require __DIR__.'/api/commerce.php';
require __DIR__.'/api/delivery.php';
require __DIR__.'/api/delivery-company.php';
require __DIR__.'/api/admin.php';
require __DIR__.'/api/common.php';
