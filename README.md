# Zonix Glasses — Backend (Laravel)

API Laravel para Zonix Glasses: auth Sanctum + Google, RBAC (`user`/`admin`), perfiles, direcciones, teléfonos, documentos, geo, notificaciones (FCM/Pusher) y métodos de pago.

## Requisitos

- PHP 8.1+, Composer
- MySQL (`DB_DATABASE=zonix_glasses`)
- (Opcional) Firebase, Pusher

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan test
```

Usuarios seed: `admin@zonix-glasses.local` / `user@zonix-glasses.local` (password: `password`).

## Estructura

- `app/Http/Controllers/` — API y panel web
- `routes/api/` — rutas modulares
- `database/migrations/` — esquema
- `.agents/skills/zonix-glasses-*` — skills de dominio (globales JARVIS en `~/.cursor/skills/`)

## Frontend hermano

Ver `../zonix-glasses-front` (Flutter, paquete `zonix_glasses`).

## Arquitectura

```
Flutter (zonix-glasses-front)  ──REST/Sanctum──►  Laravel API (zonix-glasses-back)
```

## Documentación IA

- `AGENTS.md`, `.cursorrules`, `docs/active_context.md`
- Biblioteca global: `/var/www/html/proyectos/AIPP/jarvis-skills-library`
