<?php

/*
|--------------------------------------------------------------------------
| API Routes — Scaffold core
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Log;

if (config('app.debug')) {
    Log::debug('🌐 Incoming API Request: '.request()->method().' '.request()->path(), [
        'ip' => request()->ip(),
        'agent' => request()->userAgent(),
    ]);
}

require __DIR__.'/api/public.php';
require __DIR__.'/api/auth.php';
require __DIR__.'/api/admin.php';
require __DIR__.'/api/partner.php';
require __DIR__.'/api/common.php';
