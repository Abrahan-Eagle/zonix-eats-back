# Roles & Authentication — Zonix Eats Backend

## Roles (6 roles)

| Rol                | Descripción |
| ------------------ | ----------- |
| `users`            | Cliente/Comprador |
| `commerce`         | Comercio/Restaurante |
| `delivery_company` | Empresa que administra repartidores (motorizados) |
| `delivery_agent`   | Repartidor vinculado a una empresa (`company_id` no nulo) |
| `delivery`         | Repartidor autónomo (sin empresa, `company_id` nulo) |
| `admin`            | Administrador |

**IMPORTANTE:** Rutas `/api/delivery/*` permiten **delivery_agent** y **delivery** (motorizados). Los roles `transport` y `affiliate` fueron eliminados.

## Laravel Sanctum

- Tokens con expiración 24h (configurable: `SANCTUM_TOKEN_EXPIRATION`)
- Rate limiting: `throttle:auth` (auth), `throttle:create` (órdenes)
- CORS configurable: `CORS_ALLOWED_ORIGINS` en `.env`

## Middleware

```php
Route::middleware(['auth:sanctum', 'role:commerce'])->group(function () {
    Route::get('/commerce/dashboard', [DashboardController::class, 'index']);
});
```
