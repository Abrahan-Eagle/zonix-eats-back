# Zonix Eats Backend

## Descripción
Backend de la plataforma de ecommerce de comida rápida Zonix Eats, desarrollado en Laravel. Permite la gestión de usuarios, comercios, productos, órdenes y entregas.

## Instalación

```bash
cd zonix-eats-back
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
npm install && npm run build # si usas assets
```

## Variables de Entorno
- DB_DATABASE, DB_USERNAME, DB_PASSWORD
- APP_KEY, APP_ENV, APP_DEBUG
- API_URL_LOCAL, API_URL_PROD

## Endpoints Principales
- Autenticación: /api/auth/*
- Buyer: /api/buyer/*
- Commerce: /api/commerce/*
- Delivery: /api/delivery/*
- Admin: /api/admin/*
- Perfiles: /api/profiles/*

## Testing
```bash
php artisan test
```

## Seeders y Datos de Prueba
Ejecuta `php artisan migrate:fresh --seed` para poblar la base de datos con datos realistas, incluyendo imágenes de productos de TheMealDB.

## Buenas Prácticas
- No subas .env a git
- Usa HTTPS en producción
- Valida y protege rutas

## Créditos
- Imágenes de productos: [TheMealDB](https://www.themealdb.com/api.php)
