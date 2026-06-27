# Variables de entorno — Zonix Glasses Backend

## Obligatorias (local)

| Variable | Archivo | Valor Zonix Glasses |
|----------|---------|---------------------|
| `APP_NAME` | `.env` | `Zonix Glasses` |
| `DB_DATABASE` | `.env` | `zonix_glasses` |
| `APP_KEY` | `.env` | Generar con `php artisan key:generate` |

## Firebase (opcional hasta configurar FCM)

| Variable | Notas |
|----------|-------|
| `FIREBASE_CREDENTIALS` | JSON en `storage/app/` — **proyecto Firebase Glasses**, no reutilizar Eats |
| `FIREBASE_STORAGE_BUCKET` | Bucket del proyecto Glasses |

## Credenciales a rotar en producción

- Firebase JSON
- Google OAuth client IDs
- Pusher app
- `SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS`

## Identificadores del producto

| Capa | Valor |
|------|-------|
| Backend BD | `zonix_glasses` |
| Android/iOS | `com.zonix.glasses` |
| Dart (front) | `zonix_glasses` |

Ver también `../zonix-glasses-front/.env.example`.
