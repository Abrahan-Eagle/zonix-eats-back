<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URLs de servicios externos (sin hardcode en controladores)
    |--------------------------------------------------------------------------
    */
    'osrm_base_url' => env('ZONIX_OSRM_BASE_URL', 'http://router.project-osrm.org'),

    'nominatim_reverse_url' => env('ZONIX_NOMINATIM_REVERSE_URL', 'https://nominatim.openstreetmap.org/reverse'),
    'nominatim_search_url' => env('ZONIX_NOMINATIM_SEARCH_URL', 'https://nominatim.openstreetmap.org/search'),

    /*
    |--------------------------------------------------------------------------
    | Valores por defecto de negocio (configurables por entorno)
    |--------------------------------------------------------------------------
    */
    'default_delivery_fee' => (float) (env('ZONIX_DEFAULT_DELIVERY_FEE', 5.00)),
    'default_preparation_time_minutes' => (int) (env('ZONIX_DEFAULT_PREPARATION_TIME_MINUTES', 12)),

    // Fallbacks solo cuando en BD no hay coords (ej. comercio sin dirección). Producción: todo viene de GPS/BD.
    'default_commerce_lat' => (float) (env('ZONIX_DEFAULT_COMMERCE_LAT', 10.1620)),
    'default_commerce_lng' => (float) (env('ZONIX_DEFAULT_COMMERCE_LNG', -68.0074)),

    /*
    |--------------------------------------------------------------------------
    | Analytics: fallbacks cuando no hay datos
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'delivery_time_comparison_period1' => (float) (env('ZONIX_ANALYTICS_DELIVERY_TIME_P1', 32.5)),
        'delivery_time_comparison_period2' => (float) (env('ZONIX_ANALYTICS_DELIVERY_TIME_P2', 28.5)),
        'avg_preparation_fallback_minutes' => (float) (env('ZONIX_ANALYTICS_AVG_PREP_FALLBACK', 12.5)),
        'satisfaction_fallback_rating' => (float) (env('ZONIX_ANALYTICS_SATISFACTION_FALLBACK', 4.5)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeders: IDs y valores de demo (evitar hardcode en seeders)
    |--------------------------------------------------------------------------
    */
    'seeder' => [
        'demo_order_id' => (int) (env('ZONIX_SEEDER_DEMO_ORDER_ID', 4)),
        'default_delivery_fee' => (float) (env('ZONIX_SEEDER_DEFAULT_DELIVERY_FEE', 5.00)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ruta repartidor → cliente: waypoint opcional (solo demo o punto fijo).
    | En producción dejar sin definir: la ruta usa solo coords reales (repartidor desde
    | GPS → BD, cliente desde dirección guardada en BD). Si se define, OSRM dibuja la
    | ruta pasando por ese punto.
    |--------------------------------------------------------------------------
    */
    'tracking_waypoint_lat' => env('ZONIX_TRACKING_WAYPOINT_LAT') !== null && env('ZONIX_TRACKING_WAYPOINT_LAT') !== '' ? (float) env('ZONIX_TRACKING_WAYPOINT_LAT') : null,
    'tracking_waypoint_lng' => env('ZONIX_TRACKING_WAYPOINT_LNG') !== null && env('ZONIX_TRACKING_WAYPOINT_LNG') !== '' ? (float) env('ZONIX_TRACKING_WAYPOINT_LNG') : null,

];
