# Variables de entorno — Zonix Glasses Backend

## Obligatorias (local)

| Variable | Archivo | Valor Zonix Glasses |
|----------|---------|---------------------|
| `APP_NAME` | `.env` | `Zonix Glasses` |
| `DB_DATABASE` | `.env` | `zonix_glasses` |
| `APP_KEY` | `.env` | Generar con `php artisan key:generate` |

## Config comercial (no `.env`)

Parámetros admin vía `PlatformConfig` en [DOMINIO_DATOS.md](DOMINIO_DATOS.md) — no variables de entorno:

| Clave | Default | Doc |
|-------|---------|-----|
| `hub_late_pickup_fee_percent` | 0.10 | [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) § retención hub |
| `iva_rate_display` | `[PENDIENTE legal]` | POLITICA checkout IVA |

## OCR / features opt-in

| Variable | Default | Notas |
|----------|---------|-------|
| `OPTICAL_OCR_ENABLED` | `false` | `config/optical.php` — requiere consentimiento [PRIVACIDAD_OPTICA.md](PRIVACIDAD_OPTICA.md) |

## Firebase (opcional hasta configurar FCM)

| Variable | Notas |
|----------|-------|
| `FIREBASE_CREDENTIALS` | JSON en `storage/app/` — **proyecto Firebase Glasses**, no reutilizar Eats |
| `FIREBASE_STORAGE_BUCKET` | Bucket del proyecto Glasses |

## Credenciales a rotar en producción

- Firebase JSON
- Google OAuth client IDs
- Pusher app
- `SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS` — incluir en `.env.example` al clonar (ver [CLONE_CHECKLIST.md](CLONE_CHECKLIST.md))

## Identificadores del producto

| Capa | Valor |
|------|-------|
| Backend BD | `zonix_glasses` |
| Android/iOS | `com.zonix.glasses` |
| Dart (front) | `zonix_glasses` |

Ver también `../zonix-glasses-front/.env.example`.
