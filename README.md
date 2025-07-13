# Zonix Eats Backend - API Laravel

## 📋 Descripción

Backend de la aplicación Zonix Eats desarrollado en Laravel. Proporciona una API REST completa para la gestión de pedidos, productos, usuarios y comunicación en tiempo real.

## 🏗️ Arquitectura

```
app/
├── Http/
│   ├── Controllers/     # Controladores de la API
│   ├── Middleware/      # Middleware personalizado
│   └── Requests/        # Validación de requests
├── Models/              # Modelos Eloquent
├── Services/            # Servicios de negocio
└── Providers/           # Proveedores de servicios
```

## 🚀 Instalación

### Prerrequisitos
- PHP 8.1+
- Composer
- MySQL 8.0+
- Redis (opcional, para cache)

### Configuración

1. **Clonar y instalar dependencias**
```bash
composer install
```

2. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configurar base de datos**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zonix_eats
DB_USERNAME=root
DB_PASSWORD=
```

4. **Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed
```

5. **Iniciar servidor**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## 📊 Base de Datos

### Migraciones Principales

```bash
# Usuarios y perfiles
2024_09_06_195634_create_profiles_table.php
2024_09_06_204256_create_operator_codes_table.php
2024_09_06_204735_create_emails_table.php
2024_09_06_205858_create_phones_table.php
2024_09_07_222727_create_addresses_table.php
2024_09_07_225226_create_documents_table.php

# Comercios y productos
2025_05_23_000000_create_commerces_table.php
2025_05_23_000004_create_products_table.php

# Órdenes y pedidos
2025_05_23_000003_create_orders_table.php
2025_05_23_000005_create_order_items_table.php
2025_05_23_000006_create_order_deliveries_table.php

# Social y reviews
2025_05_23_000001_create_posts_table.php
2025_05_23_000002_create_post_likes_table.php
2025_05_23_000007_create_reviews_table.php

# Notificaciones y favoritos
2025_05_23_000008_create_notifications_table.php
2025_05_23_000009_create_favorites_table.php
```

## 🔐 Autenticación

### JWT Authentication
```php
// Configuración en config/auth.php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

### Endpoints de Autenticación

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
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
    "password_confirmation": "password"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

## 📱 API Endpoints

### 🔐 Autenticación
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login de usuario |
| POST | `/api/auth/register` | Registro de usuario |
| POST | `/api/auth/logout` | Logout de usuario |

### 🏪 Comercios
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/buyer/restaurants` | Listar restaurantes |
| GET | `/api/buyer/restaurants/{id}` | Detalles de restaurante |
| GET | `/api/buyer/restaurants/{id}/products` | Productos de restaurante |

### 🍕 Productos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/buyer/products` | Listar productos |
| GET | `/api/buyer/products/{id}` | Detalles de producto |
| POST | `/api/commerce/products` | Crear producto (commerce) |
| PUT | `/api/commerce/products/{id}` | Actualizar producto |
| DELETE | `/api/commerce/products/{id}` | Eliminar producto |

### 🛒 Carrito
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/buyer/cart` | Ver carrito |
| POST | `/api/buyer/cart/add` | Agregar al carrito |
| PUT | `/api/buyer/cart/update` | Actualizar carrito |
| DELETE | `/api/buyer/cart/remove/{id}` | Remover del carrito |
| DELETE | `/api/buyer/cart/clear` | Limpiar carrito |

### 📦 Órdenes
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/buyer/orders` | Listar órdenes del usuario |
| POST | `/api/buyer/orders` | Crear nueva orden |
| GET | `/api/buyer/orders/{id}` | Detalles de orden |
| PUT | `/api/buyer/orders/{id}/cancel` | Cancelar orden |

### ⭐ Reviews
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/buyer/reviews` | Listar reviews |
| POST | `/api/buyer/reviews` | Crear review |
| PUT | `/api/buyer/reviews/{id}` | Actualizar review |
| DELETE | `/api/buyer/reviews/{id}` | Eliminar review |

### 🔔 Notificaciones
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/notifications` | Listar notificaciones |
| PUT | `/api/notifications/{id}/read` | Marcar como leída |
| DELETE | `/api/notifications/{id}` | Eliminar notificación |

### 📍 Geolocalización
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/location/update` | Actualizar ubicación |
| GET | `/api/location/nearby` | Lugares cercanos |
| POST | `/api/location/route` | Calcular ruta |

### ❤️ Favoritos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/favorites` | Listar favoritos |
| POST | `/api/favorites` | Agregar favorito |
| DELETE | `/api/favorites/{id}` | Remover favorito |

## 🏪 Roles y Permisos

### Niveles de Usuario
- **Nivel 0**: Cliente (comprar, ver productos)
- **Nivel 1**: Comercio (gestionar productos, ver pedidos)
- **Nivel 2**: Delivery (entregar pedidos)
- **Nivel 3**: Admin (gestión completa)

### Middleware de Roles
```php
// Verificar rol de comercio
Route::middleware(['auth:sanctum', 'role:commerce'])->group(function () {
    Route::get('/commerce/dashboard', [DashboardController::class, 'index']);
});

// Verificar rol de delivery
Route::middleware(['auth:sanctum', 'role:delivery'])->group(function () {
    Route::get('/delivery/orders', [OrderController::class, 'deliveryOrders']);
});
```

## 🔄 WebSocket Events

### Eventos de Notificación
```php
// Enviar notificación
event(new OrderStatusChanged($order));

// Escuchar en frontend
Echo.private(`user.${userId}`)
    .listen('OrderStatusChanged', (e) => {
        console.log('Orden actualizada:', e.order);
    });
```

### Eventos de Chat
```php
// Enviar mensaje
event(new ChatMessageSent($message));

// Escuchar en frontend
Echo.private(`chat.${orderId}`)
    .listen('ChatMessageSent', (e) => {
        console.log('Nuevo mensaje:', e.message);
    });
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
```

### Estructura de Tests
```
tests/
├── Feature/          # Tests de integración
│   ├── AuthTest.php
│   ├── OrderTest.php
│   └── ProductTest.php
└── Unit/             # Tests unitarios
    ├── OrderServiceTest.php
    └── ProductServiceTest.php
```

## 📊 Seeders

### Datos de Prueba
```bash
# Ejecutar todos los seeders
php artisan db:seed

# Seeders específicos
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CommerceSeeder
php artisan db:seed --class=ProductSeeder
```

### Datos Incluidos
- Usuarios de prueba (cliente, comercio, delivery, admin)
- Comercios con productos
- Órdenes de ejemplo
- Reviews y notificaciones

## 🔧 Configuración Avanzada

### Cache
```php
// Configurar Redis para cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue
```php
// Configurar colas para tareas pesadas
QUEUE_CONNECTION=redis
```

### Broadcasting
```php
// Configurar WebSocket
BROADCAST_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🐛 Troubleshooting

### Problemas Comunes

1. **Error de conexión a base de datos**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Error de permisos**
   ```bash
   chmod -R 775 storage/
   chmod -R 775 bootstrap/cache/
   ```

3. **Error de autenticación**
   ```bash
   php artisan config:clear
   php artisan route:clear
   ```

## 📈 Monitoreo

### Logs
```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs de errores
tail -f storage/logs/laravel-*.log
```

### Métricas
- Requests por minuto
- Tiempo de respuesta promedio
- Errores 4xx/5xx
- Uso de memoria

## 🔒 Seguridad

### CORS
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### Rate Limiting
```php
// Limitar requests por IP
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/auth/login', [AuthController::class, 'login']);
});
```

---

**Versión**: 1.0.0  
**Laravel**: 10.x  
**PHP**: 8.1+  
**Última actualización**: Julio 2024
