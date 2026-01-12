# Zonix Eats Backend - API Laravel

## 📋 Descripción

Backend de la aplicación Zonix Eats desarrollado en Laravel 10. Proporciona una API REST completa para la gestión de pedidos, productos, usuarios y comunicación en tiempo real mediante WebSocket.

## 🏗️ Arquitectura

```
app/
├── Http/
│   ├── Controllers/     # 50+ controladores organizados por módulos
│   │   ├── Authenticator/  # Autenticación
│   │   ├── Buyer/          # Funcionalidades de comprador
│   │   ├── Commerce/       # Funcionalidades de comercio
│   │   ├── Delivery/       # Funcionalidades de delivery
│   │   ├── Admin/          # Funcionalidades de administrador
│   │   └── ...
│   ├── Middleware/      # Middleware personalizado
│   │   ├── RoleMiddleware.php
│   │   └── ...
│   └── Requests/        # Validación de requests
├── Models/              # 25+ modelos Eloquent
├── Services/            # 9 servicios de negocio
│   ├── OrderService.php
│   ├── CartService.php
│   ├── ProductService.php
│   └── ...
├── Events/              # Eventos para broadcasting
└── Providers/           # Proveedores de servicios
```

**Patrón Arquitectónico:** MVC con separación de servicios

- **Controllers:** Manejan requests/responses HTTP
- **Services:** Contienen lógica de negocio
- **Models:** Representan entidades de base de datos
- **Events:** Para broadcasting y notificaciones

## 🛠️ Stack Tecnológico

### Framework y Lenguaje
- **Laravel:** 10.x
- **PHP:** 8.1+

### Dependencias Principales

**Core:**
- `laravel/framework: ^10.10` - Framework Laravel
- `laravel/sanctum: ^3.3` - Autenticación API

**Base de Datos:**
- `doctrine/dbal: ^3.10` - Database Abstraction Layer

**Imágenes y Media:**
- `intervention/image: ^3.9` - Procesamiento de imágenes
- `intervention/image-laravel: ^1.3` - Integración Laravel

**Notificaciones:**
- `kreait/laravel-firebase: ^5.10` - Firebase para push notifications
- `pusher/pusher-php-server: ^7.2` - Broadcasting

**Utilidades:**
- `simplesoftwareio/simple-qrcode: ^4.2` - Generación de códigos QR
- `guzzlehttp/guzzle: ^7.2` - Cliente HTTP

**Testing:**
- `phpunit/phpunit: ^10.1` - Framework de testing
- `fakerphp/faker: ^1.9.1` - Datos de prueba

## 🚀 Instalación y Configuración

### Prerrequisitos

- PHP 8.1+
- Composer
- MySQL 8.0+
- Redis (opcional, para cache y broadcasting)
- Node.js y npm (para Laravel Echo Server)

### Instalación

```bash
# 1. Clonar repositorio
cd zonix-eats-back

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zonix_eats
DB_USERNAME=root
DB_PASSWORD=

# 5. Ejecutar migraciones y seeders
php artisan migrate
php artisan db:seed

# 6. Crear enlace simbólico para storage
php artisan storage:link

# 7. Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000
```

### Configuración de Variables de Entorno

**Variables críticas en `.env`:**

```env
APP_NAME=ZonixEats
APP_ENV=local
APP_DEBUG=true
APP_URL=http://192.168.0.101:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zonix_eats
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,192.168.0.101
```

## 📊 Base de Datos

### Esquema Principal

**Tablas de Usuarios y Perfiles:**
- `users` - Usuarios del sistema
- `profiles` - Perfiles extendidos de usuario
- `addresses` - Direcciones de usuarios
- `phones` - Teléfonos de usuarios
- `documents` - Documentos de usuarios
- `operator_codes` - Códigos de operadores telefónicos

**Tablas de Comercios y Productos:**
- `commerces` - Comercios/Restaurantes
- `products` - Productos
- `categories` - Categorías de productos

**Tablas de Órdenes:**
- `orders` - Órdenes/Pedidos
- `order_items` - Items de órdenes
- `order_delivery` - Información de entrega

**Tablas de Delivery:**
- `delivery_companies` - Empresas de delivery
- `delivery_agents` - Agentes de entrega

**Tablas Sociales:**
- `posts` - Posts sociales
- `post_likes` - Likes en posts
- `reviews` - Reseñas/Calificaciones

**Tablas de Pagos:**
- `payment_methods` - Métodos de pago
- `banks` - Bancos

**Tablas de Sistema:**
- `notifications` - Notificaciones
- `chat_messages` - Mensajes de chat
- `promotions` - Promociones
- `coupons` - Cupones
- `countries` - Países
- `states` - Estados/Provincias
- `cities` - Ciudades

### Migraciones Principales

```bash
# Usuarios y perfiles
2024_09_06_195634_create_profiles_table.php
2024_09_06_204256_create_operator_codes_table.php
2024_09_06_205858_create_phones_table.php
2024_09_07_222727_create_addresses_table.php
2024_09_07_225226_create_documents_table.php

# Comercios y productos
2025_05_23_000000_create_commerces_table.php
2025_05_23_000004_create_products_table.php
2025_07_16_095604_create_categories_table.php

# Órdenes y pedidos
2025_05_23_000003_create_orders_table.php
2025_05_23_000005_create_order_items_table.php
2025_05_23_000006_create_order_deliveries_table.php

# Delivery
2025_05_23_000006_create_delivery_companies_table.php
2025_05_23_000007_create_delivery_agents_table.php

# Social y reviews
2025_05_23_000001_create_posts_table.php
2025_05_23_000002_create_post_likes_table.php
2025_05_26_113212_create_reviews_table.php

# Sistema
2025_07_13_123058_create_notifications_table.php
2025_07_13_142655_create_chat_messages_table.php
2025_07_13_142707_create_promotions_table.php
2025_07_13_142730_create_coupons_table.php

# Pagos
2025_07_18_000000_create_banks_table.php
2025_07_18_000001_create_payment_methods_table.php
```

### Relaciones Principales

- `User` → `Profile` (1:1)
- `User` → `Commerce` (1:1)
- `User` → `DeliveryAgent` (1:1)
- `Profile` → `Orders` (1:N)
- `Commerce` → `Products` (1:N)
- `Order` → `OrderItems` (1:N)
- `Order` → `OrderDelivery` (1:1)
- `Review` → `Reviewable` (Polimórfica)

## 🔐 Autenticación

### Laravel Sanctum

**Configuración:**
- Tokens almacenados en `personal_access_tokens`
- Tokens con expiración configurable
- Revocación de tokens en logout
- Stateful domains configurados

**Endpoints de Autenticación:**

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}

Response:
{
    "success": true,
    "data": {
        "user": { ... },
        "token": "1|..."
    }
}
```

#### Registro
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "Usuario",
    "email": "user@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "users"
}
```

#### Google OAuth
```http
POST /api/auth/google
Content-Type: application/json

{
    "data": {
        "sub": "google_id",
        "email": "user@gmail.com",
        "name": "Usuario",
        "picture": "https://..."
    }
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Obtener Usuario
```http
GET /api/auth/user
Authorization: Bearer {token}
```

## 📱 API Endpoints

### 🔐 Autenticación

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/login` | Login de usuario | No |
| POST | `/api/auth/register` | Registro de usuario | No |
| POST | `/api/auth/google` | Autenticación Google | No |
| POST | `/api/auth/logout` | Logout de usuario | Sí |
| GET | `/api/auth/user` | Obtener usuario actual | Sí |
| PUT | `/api/auth/user` | Actualizar perfil | Sí |
| PUT | `/api/auth/password` | Cambiar contraseña | Sí |
| POST | `/api/auth/refresh` | Refrescar token | Sí |

### 🏪 Comercios/Restaurantes

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/buyer/restaurants` | Listar restaurantes | Sí | users |
| GET | `/api/buyer/restaurants/{id}` | Detalles de restaurante | Sí | users |

### 🍕 Productos

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/buyer/products` | Listar productos | Sí | users |
| GET | `/api/buyer/products/{id}` | Detalles de producto | Sí | users |
| GET | `/api/commerce/products` | Listar productos del comercio | Sí | commerce |
| POST | `/api/commerce/products` | Crear producto | Sí | commerce |
| PUT | `/api/commerce/products/{id}` | Actualizar producto | Sí | commerce |
| DELETE | `/api/commerce/products/{id}` | Eliminar producto | Sí | commerce |

### 🛒 Carrito

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/buyer/cart` | Ver carrito | Sí | users |
| POST | `/api/buyer/cart/add` | Agregar al carrito | Sí | users |
| PUT | `/api/buyer/cart/update-quantity` | Actualizar cantidad | Sí | users |
| DELETE | `/api/buyer/cart/{productId}` | Remover del carrito | Sí | users |
| POST | `/api/buyer/cart/notes` | Agregar notas | Sí | users |

**⚠️ PROBLEMA CRÍTICO:** `CartService` actualmente usa Session de PHP, lo cual no funciona en arquitectura stateless. **Requiere migración a base de datos.**

### 📦 Órdenes

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/buyer/orders` | Listar órdenes del usuario | Sí | users |
| POST | `/api/buyer/orders` | Crear nueva orden | Sí | users |
| GET | `/api/buyer/orders/{id}` | Detalles de orden | Sí | - |
| POST | `/api/buyer/orders/{id}/cancel` | Cancelar orden | Sí | users |
| POST | `/api/buyer/orders/{id}/payment-proof` | Subir comprobante | Sí | users |
| GET | `/api/commerce/orders` | Órdenes del comercio | Sí | commerce |
| GET | `/api/commerce/orders/{id}` | Detalles de orden | Sí | commerce |
| PUT | `/api/commerce/orders/{id}/status` | Actualizar estado | Sí | commerce |
| POST | `/api/commerce/orders/{id}/validate-payment` | Validar pago | Sí | commerce |
| GET | `/api/delivery/orders` | Órdenes disponibles | Sí | delivery |
| POST | `/api/delivery/orders/{id}/accept` | Aceptar orden | Sí | delivery |

**Estados de Orden:**
- `pending_payment` - Pendiente de pago
- `confirmed` - Confirmada
- `preparing` - En preparación
- `ready` - Lista para entrega
- `delivered` - Entregada
- `cancelled` - Cancelada

### ⭐ Reviews

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/buyer/reviews` | Listar reviews | Sí | users |
| POST | `/api/buyer/reviews` | Crear review | Sí | users |
| PUT | `/api/buyer/reviews/{id}` | Actualizar review | Sí | users |
| DELETE | `/api/buyer/reviews/{id}` | Eliminar review | Sí | users |

### 🔔 Notificaciones

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/notifications` | Listar notificaciones | Sí |
| POST | `/api/notifications/{id}/read` | Marcar como leída | Sí |
| DELETE | `/api/notifications/{id}` | Eliminar notificación | Sí |

### 📍 Geolocalización

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/location/update` | Actualizar ubicación | Sí |
| GET | `/api/location/nearby-places` | Lugares cercanos | Sí |
| POST | `/api/location/calculate-route` | Calcular ruta | Sí |
| POST | `/api/location/geocode` | Obtener coordenadas | Sí |

### 💬 Chat

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/chat/conversations` | Listar conversaciones | Sí |
| GET | `/api/chat/conversations/{id}/messages` | Mensajes de conversación | Sí |
| POST | `/api/chat/conversations/{id}/messages` | Enviar mensaje | Sí |
| POST | `/api/chat/conversations/{id}/read` | Marcar como leído | Sí |

### 💳 Pagos

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/payments/methods` | Métodos de pago disponibles | Sí |
| POST | `/api/payments/methods` | Agregar método de pago | Sí |
| POST | `/api/payments/process` | Procesar pago | Sí |
| GET | `/api/payments/history` | Historial de pagos | Sí |

### 👥 Perfiles

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/profile` | Obtener perfil | Sí |
| PUT | `/api/profile` | Actualizar perfil | Sí |
| GET | `/api/profiles` | Listar perfiles | Sí |
| POST | `/api/profiles` | Crear perfil | Sí |
| GET | `/api/profiles/{id}` | Detalles de perfil | Sí |
| PUT | `/api/profiles/{id}` | Actualizar perfil | Sí |

## 🏪 Roles y Permisos

### Roles del Sistema

- **users** (Nivel 0): Cliente/Comprador
  - Ver productos y restaurantes
  - Agregar al carrito
  - Realizar pedidos
  - Ver historial de pedidos
  - Calificar productos
  - Chat con restaurante
  - Notificaciones
  - Geolocalización
  - Favoritos

- **commerce** (Nivel 1): Comercio/Restaurante
  - Gestionar productos
  - Ver pedidos
  - Actualizar estado de pedidos
  - Validar pagos
  - Chat con clientes
  - Dashboard y reportes

- **delivery** (Nivel 2): Repartidor/Delivery
  - Ver pedidos asignados
  - Aceptar/rechazar pedidos
  - Actualizar ubicación
  - Marcar como entregado
  - Historial de entregas

- **transport** (Nivel 3): Agencia de Transporte
  - Gestión de flota
  - Asignación de conductores
  - Rutas y métricas

- **affiliate** (Nivel 4): Afiliado a Delivery
  - Dashboard de afiliado
  - Comisiones
  - Estadísticas

- **admin** (Nivel 5): Administrador
  - Gestión completa del sistema
  - Usuarios y roles
  - Reportes globales
  - Configuración del sistema

### Middleware de Roles

```php
// Verificar rol de comercio
Route::middleware(['auth:sanctum', 'role:commerce'])->group(function () {
    Route::get('/commerce/dashboard', [DashboardController::class, 'index']);
});

// Verificar rol de delivery
Route::middleware(['auth:sanctum', 'role:delivery'])->group(function () {
    Route::get('/delivery/orders', [OrderController::class, 'index']);
});
```

**IMPORTANTE:** El middleware `RoleMiddleware` actualmente solo verifica igualdad exacta. Para mejoras futuras, considerar sistema de permisos más granular.

## 🔄 WebSocket y Broadcasting

### Configuración

**Laravel Echo Server:**
- Puerto: 6001
- Driver: Redis (recomendado) o Pusher
- Autenticación: Sanctum tokens

**Configuración en `.env`:**
```env
BROADCAST_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Eventos Implementados

1. **OrderCreated** - Nueva orden creada
2. **OrderStatusChanged** - Estado de orden cambiado
3. **PaymentValidated** - Pago validado
4. **NewMessage** - Nuevo mensaje de chat
5. **DeliveryLocationUpdated** - Ubicación de delivery actualizada
6. **NotificationCreated** - Nueva notificación

### Canales

- `private-user.{userId}` - Notificaciones de usuario
- `private-order.{orderId}` - Actualizaciones de orden
- `private-chat.{orderId}` - Chat de orden
- `private-commerce.{commerceId}` - Notificaciones de comercio
- `private-delivery.{agentId}` - Notificaciones de delivery

### Uso de Eventos

```php
// Disparar evento
event(new OrderStatusChanged($order));

// El evento debe implementar ShouldBroadcast
class OrderStatusChanged implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->order->profile->user_id);
    }
    
    public function broadcastWith()
    {
        return [
            'order' => $this->order->load(['commerce', 'orderItems']),
        ];
    }
}
```

### Autenticación de Broadcasting

```php
// routes/api.php
Route::post('/broadcasting/auth', [BroadcastingController::class, 'authenticate'])
    ->middleware('auth:sanctum');
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=OrderControllerTest

# Tests con coverage
php artisan test --coverage

# Tests de un directorio
php artisan test tests/Feature/
```

### Tests Implementados (30+)

**Feature Tests:**
- `AuthenticationTest.php` - Autenticación
- `OrderControllerTest.php` - Controlador de órdenes
- `OrderTest.php` - Modelo de órdenes
- `CartControllerTest.php` - Controlador de carrito
- `CartServiceTest.php` - Servicio de carrito
- `ProductControllerTest.php` - Controlador de productos
- `CommerceOrderTest.php` - Órdenes de comercio
- `DeliveryOrderTest.php` - Órdenes de delivery
- `ReviewServiceTest.php` - Servicio de reseñas
- `TrackingServiceTest.php` - Servicio de tracking
- `WebSocketTest.php` - WebSocket
- Y más...

### Estructura de Tests

```
tests/
├── Feature/          # Tests de integración
│   ├── AuthenticationTest.php
│   ├── OrderTest.php
│   ├── CartControllerTest.php
│   └── ...
└── Unit/             # Tests unitarios
    └── ExampleTest.php
```

### Ejemplo de Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order()
    {
        $user = User::factory()->create(['role' => 'users']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/orders', [
            'commerce_id' => 1,
            'products' => [
                ['id' => 1, 'quantity' => 2],
            ],
            'delivery_type' => 'pickup',
            'total' => 50.00,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }
}
```

## 📊 Seeders

### Ejecutar Seeders

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Seeders específicos
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CommerceSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=OrderSeeder
```

### Datos Incluidos

- Usuarios de prueba (cliente, comercio, delivery, admin)
- Comercios con productos
- Órdenes de ejemplo
- Reviews y notificaciones
- Categorías de productos
- Métodos de pago

## 🔧 Configuración Avanzada

### Cache con Redis

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=1
```

**Uso:**
```php
Cache::put('key', 'value', 3600);
Cache::get('key');
```

### Queue con Redis

```env
QUEUE_CONNECTION=redis
```

**Ejecutar worker:**
```bash
php artisan queue:work
```

### Broadcasting con Redis

```env
BROADCAST_DRIVER=redis
```

**Iniciar Laravel Echo Server:**
```bash
npx laravel-echo-server start
```

### Storage

**Crear enlace simbólico:**
```bash
php artisan storage:link
```

**Configuración en `config/filesystems.php`:**
- `public` - Archivos públicos accesibles
- `local` - Archivos locales privados

## 🔒 Seguridad

### CORS

**⚠️ CRÍTICO:** Actualmente configurado con `allowed_origins: ['*']`

**Configuración actual (`config/cors.php`):**
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // ⚠️ RIESGO DE SEGURIDAD
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

**Recomendación para producción:**
```php
'allowed_origins' => [
    'https://zonix.uniblockweb.com',
    'https://app.zonix.uniblockweb.com',
],
```

### Rate Limiting

**⚠️ NO implementado en endpoints críticos**

**Recomendación:**
```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/auth/login', [AuthController::class, 'login']);
    Route::post('/api/auth/register', [AuthController::class, 'register']);
});
```

### Validación de Input

**SIEMPRE usar Form Requests:**
```php
// app/Http/Requests/StoreOrderRequest.php
public function rules()
{
    return [
        'commerce_id' => 'required|exists:commerces,id',
        'products' => 'required|array|min:1',
        'products.*.id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
    ];
}
```

### Protección SQL Injection

✅ **Protegido por Eloquent ORM** - Usa prepared statements automáticamente

### Protección XSS

✅ **Laravel escapa output por defecto** - Usar `{!! !!}` solo cuando sea necesario y confiable

## 📈 Performance

### Optimizaciones Implementadas

- ✅ Eager Loading con `with()`
- ✅ Índices en foreign keys
- ✅ Connection pooling automático

### Optimizaciones Pendientes

- ⚠️ **Agregar índices faltantes:**
  - `orders.status`
  - `orders.created_at`
  - `products.commerce_id`
  - `products.is_available`

- ⚠️ **Implementar caching:**
  - Cachear queries frecuentes
  - Cachear respuestas de API
  - Cachear datos de configuración

- ⚠️ **Agregar paginación:**
  - Implementar en todos los endpoints de listado
  - Límite por defecto: 15-20 items

### Queries Optimizadas

**Ejemplo con Eager Loading:**
```php
Order::with(['commerce', 'orderItems.product', 'orderDelivery'])
    ->where('profile_id', $profileId)
    ->get();
```

## 🐛 Problemas Conocidos

### 🔴 Críticos

1. **CartService usa Session**
   - **Problema:** No funciona en arquitectura stateless
   - **Ubicación:** `app/Services/CartService.php`
   - **Solución:** Migrar a base de datos (tablas `carts` y `cart_items`)

2. **CORS muy permisivo**
   - **Problema:** `allowed_origins: ['*']` es riesgo de seguridad
   - **Ubicación:** `config/cors.php`
   - **Solución:** Restringir a dominios específicos

3. **Falta Rate Limiting**
   - **Problema:** Endpoints críticos sin protección
   - **Solución:** Implementar rate limiting en auth y creación

### 🟡 Altos

4. **Archivos Duplicados**
   - `City copy.php` y `State copy.php` en Models
   - **Solución:** Eliminar archivos duplicados

5. **Falta Paginación**
   - Algunos endpoints sin límites
   - **Solución:** Agregar paginación a todos los listados

6. **Falta Caching**
   - Queries repetitivos sin cache
   - **Solución:** Implementar Redis cache

## 🧹 Comandos Útiles

### Limpiar Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Optimizar

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Migraciones

```bash
# Ejecutar migraciones
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Rollback todas las migraciones
php artisan migrate:reset

# Refrescar y seedear
php artisan migrate:fresh --seed
```

### Generar Código

```bash
# Crear controlador
php artisan make:controller Buyer/OrderController

# Crear modelo
php artisan make:model Order

# Crear migración
php artisan make:migration create_orders_table

# Crear seeder
php artisan make:seeder OrderSeeder

# Crear Form Request
php artisan make:request StoreOrderRequest
```

## 📈 Monitoreo

### Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver logs de errores
tail -f storage/logs/laravel-*.log

# Limpiar logs antiguos
php artisan log:clear
```

### Métricas Recomendadas

- Requests por minuto
- Tiempo de respuesta promedio
- Errores 4xx/5xx
- Uso de memoria
- Queries lentas

**Recomendación:** Implementar APM (Sentry, New Relic, etc.)

## 🔄 Mejoras Críticas Pendientes

### 🔴 Acción Inmediata

1. **Migrar Carrito de Session a Base de Datos**
   - Crear tablas `carts` y `cart_items`
   - Actualizar `CartService`
   - Actualizar endpoints

2. **Restringir CORS**
   - Cambiar `allowed_origins: ['*']` a dominios específicos

3. **Implementar Rate Limiting**
   - Agregar a endpoints de autenticación
   - Agregar a endpoints de creación

### 🟡 Próximas Semanas

4. **Agregar Paginación**
   - Implementar en todos los endpoints de listado

5. **Agregar Índices a BD**
   - `orders.status`, `orders.created_at`
   - `products.commerce_id`, `products.is_available`

6. **Implementar Caching**
   - Redis para queries frecuentes
   - Cachear respuestas de API

### 🟢 Mejoras Futuras

7. **Mejorar Sistema de Roles**
   - Permisos granulares
   - Múltiples roles por usuario

8. **Implementar Swagger/OpenAPI**
   - Documentación de API interactiva

9. **Eliminar Archivos Duplicados**
   - `City copy.php`, `State copy.php`

## 📊 Análisis Exhaustivo del Proyecto

### Documento de Análisis Completo

**Ubicación:** `ANALISIS_EXHAUSTIVO.md` (raíz del proyecto WorksPageZonixEats)  
**Versión de Prompts:** 2.0 - Basada en Experiencia Real

Este documento contiene un análisis exhaustivo completo del proyecto realizado en Diciembre 2024, cubriendo todas las áreas del sistema:

1. **Arquitectura y Estructura** - Patrones, stack tecnológico, organización
2. **Código y Calidad** - Code smells, patrones, complejidad
3. **Lógica de Negocio** - Entidades, flujos, servicios
4. **Base de Datos** - Esquema, performance, integridad
5. **Seguridad** - Autenticación, vulnerabilidades, OWASP Top 10 completo
6. **Performance** - Bottlenecks, optimizaciones, escalabilidad, métricas
7. **Testing** - Cobertura, estrategia, calidad, plan de mejora
8. **Backend/API** - Endpoints, diseño, documentación
9. **DevOps e Infraestructura** - CI/CD, deployment, monitoring
10. **Documentación** - Estado, calidad, mejoras
11. **Verificación de Coherencia** ⭐ **NUEVO** - Coherencia entre archivos de documentación
12. **Estado y Mantenibilidad** - Deuda técnica, métricas, score
13. **Oportunidades y Mejoras** - Roadmap técnico priorizado, quick wins

### Realizar Nuevo Análisis Exhaustivo

Cuando se solicite un análisis exhaustivo del proyecto, usar los **prompts completos v2.0** disponibles. El análisis debe seguir esta metodología:

**FASE 1: EXPLORACIÓN INICIAL**
- Mapear estructura completa de directorios y archivos
- Identificar archivos de configuración clave
- Leer archivos de documentación principales
- Identificar stack tecnológico completo y versiones

**FASE 2: ANÁLISIS PROFUNDO POR ÁREA**
- Explorar TODA la estructura del proyecto sin dejar áreas sin revisar
- Leer y analizar los archivos más importantes de cada módulo
- Identificar patrones, anti-patrones y code smells
- Proporcionar ejemplos concretos de código (formato: archivo:línea)
- Priorizar hallazgos por criticidad (crítico, alto, medio, bajo)
- Sugerir mejoras específicas con impacto/esfuerzo/prioridad

**FASE 3: VERIFICACIÓN DE COHERENCIA** ⭐ **CRÍTICO**
- Comparar métricas mencionadas en diferentes documentos
- Verificar que números y estadísticas coincidan entre README y .cursorrules
- Identificar discrepancias y corregirlas o documentar razones
- Asegurar que el estado del proyecto sea consistente en toda la documentación

**Ver:** `.cursorrules` para el prompt maestro completo v2.0 con todas las instrucciones detalladas.

### Actualizar Análisis

**Cuándo actualizar:**
- Después de cambios arquitectónicos importantes
- Después de implementar mejoras críticas identificadas
- Cada 3-6 meses o cuando se solicite
- Antes de releases mayores

**Cómo actualizar:**
1. Revisar cambios desde último análisis
2. Ejecutar análisis exhaustivo siguiendo los prompts completos
3. Actualizar `ANALISIS_EXHAUSTIVO.md` con nuevos hallazgos
4. Actualizar fecha de última actualización en este README

## 📚 Referencias

- **Laravel Docs:** https://laravel.com/docs/10.x
- **Sanctum Docs:** https://laravel.com/docs/10.x/sanctum
- **Eloquent Docs:** https://laravel.com/docs/10.x/eloquent
- **Testing Docs:** https://laravel.com/docs/10.x/testing
- **Broadcasting Docs:** https://laravel.com/docs/10.x/broadcasting
- **Análisis Exhaustivo:** Ver `ANALISIS_EXHAUSTIVO.md` en raíz del proyecto

## 📞 Soporte

Para soporte técnico o preguntas sobre el proyecto, contactar al equipo de desarrollo.

## 📄 Licencia

Este proyecto es privado y confidencial.

---

**Versión:** 1.0.0  
**Laravel:** 10.x  
**PHP:** 8.1+  
**Última actualización:** Diciembre 2024  
**Estado:** MVP Completado ✅ - En desarrollo activo
