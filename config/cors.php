<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * Orígenes permitidos (navegadores). No usar '*' con credenciales (Sanctum cookie/Authorization).
     * En producción: CORS_ALLOWED_ORIGINS=https://tu-dominio.com,https://www.tu-dominio.com
     * Si la variable no está definida: orígenes locales típicos (desarrollo), no '*'.
     */
    'allowed_origins' => (function () {
        $raw = env('CORS_ALLOWED_ORIGINS');
        if ($raw !== null && $raw !== '') {
            return array_map('trim', explode(',', $raw));
        }

        return [
            'http://localhost',
            'http://127.0.0.1',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ];
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Con Access-Control-Allow-Origin: * el navegador no envía credenciales; forzar false si hay comodín.
    'supports_credentials' => (function () {
        $raw = env('CORS_ALLOWED_ORIGINS');
        if ($raw !== null && $raw !== '') {
            $list = array_map('trim', explode(',', $raw));
            if (in_array('*', $list, true)) {
                return false;
            }
        }

        return env('CORS_SUPPORTS_CREDENTIALS', true);
    })(),

];
