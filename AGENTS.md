# AGENTS.md - Zonix Eats Backend (Laravel API)

> Instrucciones para AI coding agents trabajando en el backend de Zonix Eats.
> Para documentación detallada de lógica de negocio, ver `README.md`.
> **Para reglas de mantenimiento y coherencia de skills, ver [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md).**

## Project Overview

| Métrica                  | Valor                                              |
| ------------------------ | -------------------------------------------------- |
| **Framework**            | Laravel 10.x / PHP 8.1+                            |
| **Base de Datos**        | MySQL                                              |
| **Versión**              | 1.0.0                                              |
| **Estado**               | ✅ MVP Completado - En desarrollo activo           |
| **Endpoints**            | 233+ rutas REST                                    |
| **Controladores**        | 54                                                 |
| **Modelos**              | 35                                                 |
| **Migraciones**          | 51                                                 |
| **Tests**                | 206+ pasaron ✅, 0 fallaron                        |
| **Seguridad**            | Sanctum + RBAC + Rate Limiting + Upload validation |
| **Última actualización** | 11 Febrero 2026                                    |

### Cambios recientes (documentar aquí los avances)

- **11 Feb 2026:** Validación de cupón: API espera `code` y `order_amount`; respuestas de error con `message`/`errors`. Seeders: orden "en entrega" con repartidor asignado; `OrderDeliverySeeder` evita duplicar asignaciones. Broadcasting: auth devuelve `shared_secret` para canales privados Pusher.

---

## Setup Commands

```bash
# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed   # Reset completo

# Servidor de desarrollo
php artisan serve                  # Puerto 8000

# Tests
php artisan test                   # Todos (206+ tests)
php artisan test --filter=OrderTest  # Tests específicos
php artisan test --coverage        # Con coverage

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Available Skills

Use estas skills para patrones detallados bajo demanda:

| Skill                             | Descripción                         | Ruta                                                                                                               |
| --------------------------------- | ----------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `laravel-specialist`              | Patrones Laravel, Eloquent, Sanctum | [.agents/skills/laravel-specialist/SKILL.md](.agents/skills/laravel-specialist/SKILL.md)                           |
| `api-design-principles`           | Diseño de API REST, convenciones    | [.agents/skills/api-design-principles/SKILL.md](.agents/skills/api-design-principles/SKILL.md)                     |
| `architecture-patterns`           | Patrones arquitectónicos, SOLID     | [.agents/skills/architecture-patterns/SKILL.md](.agents/skills/architecture-patterns/SKILL.md)                     |
| `clean-code-principles`           | Código limpio, legibilidad          | [.agents/skills/clean-code-principles/SKILL.md](.agents/skills/clean-code-principles/SKILL.md)                     |
| `code-review-excellence`          | Revisión de código, estándares      | [.agents/skills/code-review-excellence/SKILL.md](.agents/skills/code-review-excellence/SKILL.md)                   |
| `error-handling-patterns`         | Manejo de errores, excepciones      | [.agents/skills/error-handling-patterns/SKILL.md](.agents/skills/error-handling-patterns/SKILL.md)                 |
| `security`                        | Seguridad web, vulnerabilidades     | [.agents/skills/security/SKILL.md](.agents/skills/security/SKILL.md)                                               |
| `security-requirement-extraction` | Requisitos de seguridad             | [.agents/skills/security-requirement-extraction/SKILL.md](.agents/skills/security-requirement-extraction/SKILL.md) |
| `mysql-best-practices`            | MySQL, queries, índices             | [.agents/skills/mysql-best-practices/SKILL.md](.agents/skills/mysql-best-practices/SKILL.md)                       |
| `systematic-debugging`            | Debugging metódico                  | [.agents/skills/systematic-debugging/SKILL.md](.agents/skills/systematic-debugging/SKILL.md)                       |
| `test-driven-development`         | TDD workflow                        | [.agents/skills/test-driven-development/SKILL.md](.agents/skills/test-driven-development/SKILL.md)                 |
| `e2e-testing-patterns`            | Testing end-to-end                  | [.agents/skills/e2e-testing-patterns/SKILL.md](.agents/skills/e2e-testing-patterns/SKILL.md)                       |
| `webapp-testing`                  | Testing de aplicaciones web         | [.agents/skills/webapp-testing/SKILL.md](.agents/skills/webapp-testing/SKILL.md)                                   |
| `software-architecture`           | Arquitectura de software            | [.agents/skills/software-architecture/SKILL.md](.agents/skills/software-architecture/SKILL.md)                     |
| `code-review-playbook`            | Playbook de code review             | [.agents/skills/code-review-playbook/SKILL.md](.agents/skills/code-review-playbook/SKILL.md)                       |
| `github-code-review`              | Code review en GitHub               | [.agents/skills/github-code-review/SKILL.md](.agents/skills/github-code-review/SKILL.md)                           |
| `stripe-integration`              | Integración de pagos Stripe         | [.agents/skills/stripe-integration/SKILL.md](.agents/skills/stripe-integration/SKILL.md)                           |
| `sql-optimization-patterns`       | Optimización SQL, EXPLAIN, índices  | [.agents/skills/sql-optimization-patterns/SKILL.md](.agents/skills/sql-optimization-patterns/SKILL.md)             |
| `frontend-design`                 | Diseño frontend (Bootstrap views)   | [.agents/skills/frontend-design/SKILL.md](.agents/skills/frontend-design/SKILL.md)                                 |
| `git-commit`                      | Conventional commits, git workflow  | [.agents/skills/git-commit/SKILL.md](.agents/skills/git-commit/SKILL.md)                                           |
| `github-actions-templates`        | CI/CD con GitHub Actions            | [.agents/skills/github-actions-templates/SKILL.md](.agents/skills/github-actions-templates/SKILL.md)               |
| `skill-creator`                   | Crear nuevas skills                 | [.agents/skills/skill-creator/SKILL.md](.agents/skills/skill-creator/SKILL.md)                                     |

### Custom Skills

| Skill                   | Descripción                            | Ruta                                                                                           |
| ----------------------- | -------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `zonix-payments`        | Modelo de pagos y comisiones Zonix     | [.agents/skills/zonix-payments.md](.agents/skills/zonix-payments.md)                           |
| `zonix-order-lifecycle` | Estados de orden, transiciones, cancel | [.agents/skills/zonix-order-lifecycle/SKILL.md](.agents/skills/zonix-order-lifecycle/SKILL.md) |
| `zonix-delivery-system` | Haversine, OSRM, zonas, tracking       | [.agents/skills/zonix-delivery-system/SKILL.md](.agents/skills/zonix-delivery-system/SKILL.md) |
| `zonix-realtime-events` | Pusher, FCM, broadcasting, canales     | [.agents/skills/zonix-realtime-events/SKILL.md](.agents/skills/zonix-realtime-events/SKILL.md) |
| `zonix-api-patterns`    | Response format, roles, middleware     | [.agents/skills/zonix-api-patterns/SKILL.md](.agents/skills/zonix-api-patterns/SKILL.md)       |

---

## Auto-invoke Skills

Al realizar estas acciones, SIEMPRE invocar la skill correspondiente PRIMERO:

| Acción                                | Skill                             |
| ------------------------------------- | --------------------------------- |
| Crear/modificar controladores o rutas | `laravel-specialist`              |
| Crear/modificar modelos Eloquent      | `laravel-specialist`              |
| Diseñar nuevos endpoints API          | `api-design-principles`           |
| Crear migraciones de BD               | `mysql-best-practices`            |
| Optimizar queries o agregar índices   | `mysql-best-practices`            |
| Agregar autenticación o autorización  | `security`                        |
| Implementar validaciones de seguridad | `security-requirement-extraction` |
| Refactorizar código existente         | `architecture-patterns`           |
| Crear o modificar tests               | `test-driven-development`         |
| Debuggear un error                    | `systematic-debugging`            |
| Revisar código de un PR               | `code-review-excellence`          |
| Manejar errores y excepciones         | `error-handling-patterns`         |
| Implementar lógica de pagos           | `zonix-payments` (custom)         |
| Trabajar con estados/flujo de órdenes | `zonix-order-lifecycle` (custom)  |
| Calcular distancias, rutas, o zonas   | `zonix-delivery-system` (custom)  |
| Implementar eventos o broadcasting    | `zonix-realtime-events` (custom)  |
| Crear endpoints o response format     | `zonix-api-patterns` (custom)     |
| Optimizar queries SQL o usar EXPLAIN  | `sql-optimization-patterns`       |
| Modificar views Blade o Bootstrap     | `frontend-design`                 |
| Hacer git commit                      | `git-commit`                      |
| Crear/modificar GitHub Actions CI/CD  | `github-actions-templates`        |
| Crear nuevas skills para el proyecto  | `skill-creator`                   |

---

## Collaboration Rules

**IMPORTANTE: El usuario es el líder del proyecto.**

1. **SIEMPRE PREGUNTAR** antes de realizar cualquier acción
2. **NUNCA crear archivos nuevos** si es para editar código existente
3. **SIEMPRE sugerir detalladamente** qué hacer y esperar aprobación
4. **NUNCA hacer push/merge a git** sin orden explícita del usuario
5. **Solo hacer commits locales** cuando se realicen cambios
6. **El usuario prueba primero** y da la orden cuando está seguro
7. **Skills personalizadas (`zonix-*`)**: Los agentes pueden proponer crear o actualizar skills nuevas SOLO cuando detecten patrones repetitivos o reglas de negocio importantes que aún no estén cubiertas. Siempre deben:
   - Explicar por qué la skill es necesaria.
   - Describir brevemente el contenido propuesto.
   - Pedir tu aprobación antes de crear/editar la skill.

---

## Architecture

### Estructura del Proyecto

```
zonix-eats-back/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # 54 controladores organizados por módulo
│   │   │   ├── Authenticator/   # Autenticación
│   │   │   ├── Buyer/           # Funcionalidades de comprador
│   │   │   ├── Commerce/        # Funcionalidades de comercio
│   │   │   ├── Delivery/        # Funcionalidades de delivery
│   │   │   ├── Admin/           # Funcionalidades de administrador
│   │   │   └── ...
│   │   ├── Middleware/          # Middleware personalizado (RoleMiddleware)
│   │   └── Requests/            # Form Requests para validación
│   ├── Models/                  # 35 modelos Eloquent
│   ├── Services/                # 9 servicios de negocio
│   │   ├── OrderService.php
│   │   ├── CartService.php
│   │   ├── ProductService.php
│   │   ├── DeliveryAssignmentService.php
│   │   └── ...
│   ├── Events/                  # Eventos para broadcasting
│   └── Providers/               # Service providers
├── database/
│   ├── migrations/              # 51 migraciones
│   ├── seeders/                 # Seeders de datos
│   └── factories/               # 27 factories
├── routes/
│   └── api.php                  # 233+ endpoints REST
├── tests/
│   └── Feature/                 # 206+ tests
└── config/                      # Configuración
```

### Patrón Arquitectónico

**MVC con separación de servicios:**

- **Controllers** → Manejan requests/responses HTTP (delgados)
- **Services** → Contienen lógica de negocio (gruesos)
- **Models** → Representan entidades de base de datos + relaciones
- **Events** → Broadcasting con Firebase + Pusher (NO WebSocket)
- **Form Requests** → Validación de datos de entrada

**Principios:** SRP, Dependency Injection, Separation of Concerns, DRY

---

## Code Style

### Naming Conventions

| Tipo       | Convención       | Ejemplo              |
| ---------- | ---------------- | -------------------- |
| Archivos   | snake_case       | `order_service.php`  |
| Clases     | PascalCase       | `OrderService`       |
| Métodos    | camelCase        | `getUserOrders()`    |
| Variables  | camelCase        | `$orderId`           |
| Constantes | UPPER_SNAKE_CASE | `MAX_RETRY_ATTEMPTS` |

### Controller Pattern

```php
<?php
namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->getUserOrders();
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
```

### Service Pattern

```php
<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function getUserOrders()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return collect();
        }

        return Order::where('profile_id', $profile->id)
            ->with(['commerce', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

### Key Rules

1. **Lógica de negocio en Services, NUNCA en Controllers**
2. **SIEMPRE usar Form Requests para validación**
3. **SIEMPRE usar `with()` para eager loading** (evitar N+1)
4. **SIEMPRE usar `DB::transaction()` para operaciones críticas**
5. **SIEMPRE usar `config()` en vez de `env()` en controllers** (compatible con `config:cache`)
6. **Uploads: SIEMPRE validar `max:5120`** (5MB máximo)

---

## Testing

```bash
php artisan test                       # Todos (206+ tests)
php artisan test --filter=OrderTest    # Específico
php artisan test --coverage            # Coverage
```

### Test Pattern

```php
<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order()
    {
        $user = User::factory()->create(['role' => 'users']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/buyer/orders', [...]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }
}
```

---

## API Conventions

### Response Format

```php
// Éxito
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operación exitosa'
], 200);

// Error
return response()->json([
    'success' => false,
    'message' => 'Mensaje de error',
    'errors' => $errors
], 400);
```

### Status Codes

`200` OK, `201` Created, `400` Bad Request, `401` Unauthorized, `403` Forbidden, `404` Not Found, `422` Validation Error, `500` Server Error

### Pagination

**CRÍTICO:** Siempre paginar endpoints de listado:

```php
$perPage = $request->get('per_page', 15);
$orders = Order::paginate($perPage);

return response()->json([
    'success' => true,
    'data' => $orders->items(),
    'pagination' => [
        'current_page' => $orders->currentPage(),
        'per_page' => $orders->perPage(),
        'total' => $orders->total(),
        'last_page' => $orders->lastPage(),
    ]
]);
```

---

## Roles & Authentication

### Roles (4 roles, nada más)

| Rol        | Level | Descripción                                                                |
| ---------- | ----- | -------------------------------------------------------------------------- |
| `users`    | 0     | Cliente/Comprador                                                          |
| `commerce` | 1     | Comercio/Restaurante                                                       |
| `delivery` | 2     | Repartidor (Company o Agent, puede ser independiente: `company_id = null`) |
| `admin`    | 3     | Administrador                                                              |

**IMPORTANTE:** Solo existen estos 4 roles. Los roles `transport` y `affiliate` fueron eliminados.

### Laravel Sanctum

- Tokens con expiración 24h (configurable: `SANCTUM_TOKEN_EXPIRATION`)
- Rate limiting: `throttle:auth` (auth), `throttle:create` (órdenes)
- CORS configurable: `CORS_ALLOWED_ORIGINS` en `.env`

### Middleware

```php
Route::middleware(['auth:sanctum', 'role:commerce'])->group(function () {
    Route::get('/commerce/dashboard', [DashboardController::class, 'index']);
});
```

---

## Real-time Events

**Firebase Cloud Messaging (FCM) + Pusher. NO WebSocket.**

### Events

| Evento                    | Descripción                       |
| ------------------------- | --------------------------------- |
| `OrderCreated`            | Nueva orden creada                |
| `OrderStatusChanged`      | Estado de orden cambiado          |
| `PaymentValidated`        | Pago validado                     |
| `NewMessage`              | Nuevo mensaje de chat             |
| `DeliveryLocationUpdated` | Ubicación de delivery actualizada |
| `NotificationCreated`     | Nueva notificación                |

### Channels (Pusher)

- `private-user.{userId}` — Notificaciones de usuario
- `private-order.{orderId}` — Actualizaciones de orden
- `private-chat.{orderId}` — Chat de orden
- `private-commerce.{commerceId}` — Notificaciones de comercio

---

## Business Rules (MVP)

### Decisiones Clave

1. **Carrito:** NO puede haber productos de diferentes comercios (uni-commerce)
2. **Validación de Precio:** Recalcular y validar contra total enviado (margen 0.01 por redondeo)
3. **Stock:** AMBAS opciones (`available` Y `stock_quantity`) - Validar siempre available, si tiene stock_quantity validar cantidad
4. **Delivery:** Sistema completo (propio, empresas, independientes) + Asignación autónoma con expansión de área
5. **Eventos:** Firebase + Pusher (NO WebSocket)
6. **Perfiles:** Datos mínimos (USERS) vs completos (COMMERCE, DELIVERY)
7. **photo_users:** Required estricto (bloquea creación de orden)
8. **Geolocalización Comercios:** Búsqueda inicial 1-1.5km, expansión automática a 4-5km
9. **Asignación Delivery:** Autónoma con expansión automática de área (1-1.5km → 4-5km → continua)
10. **Cancelación:** Límite 5 minutos O hasta validación de pago
11. **Reembolsos:** Manual (no automático)

### Order States

```
pending_payment → paid → processing → shipped → delivered
                → cancelled (solo en pending_payment, paid, processing)
```

### 💰 Modelo de Negocio

**Costos y Precios:**

- **Costo Delivery:** Híbrido (Base fija $2.00 + $0.50/km después de 2 km) - Configurable por admin
- **Quién paga delivery:** Cliente (se agrega al total de la orden)
- **Membresía/Comisión:** Membresía mensual obligatoria (base) + Comisión % sobre ventas del mes (extra)
    - Ejemplo: $100/mes + 10% de ventas = $100 + $500 (si vendió $5,000) = $600 total
- **Mínimo pedido:** No hay mínimo
- **Tarifa servicio:** No hay (solo subtotal + delivery)
- **Propinas:** No permitidas

**Pagos:**

- **Métodos:** Todos (efectivo, transferencia, tarjeta, pago móvil, digitales)
- **Quién recibe:** Comercio directamente (con sus datos bancarios)
- **Manejo:** Tiempo real (validación manual de comprobante)
- **Pago a delivery:** Del comercio (después de recibir pago del cliente) → **Delivery recibe 100% del delivery_fee** (Opción A confirmada)

**Límites:**

- **Distancia máxima:** 60 minutos de tiempo estimado de viaje
- **Quejas/Disputas:** Sistema de tickets con admin (tabla `disputes`)

**Horarios:**

- **Comercios:** Definen horarios + campo `open` manual
- **Delivery:** 24/7 según disponibilidad (campo `working`)

### Penalizaciones y Tiempos Límite

**Cancelaciones:**

- **Comercio:** Puede cancelar en `paid`/`processing` con justificación. Penalización si excede límite (5 cancelaciones/30 días)
- **Cliente:** Solo en `pending_payment`, límite 5 minutos. Penalización si crea múltiples órdenes sin pagar
- **Comisión en cancelación:** Si comercio cancela después de `paid`, se cobra comisión como penalización adicional

**Delivery rechaza:**

- Debe justificar. Penalización si rechaza 3-5 órdenes seguidas sin justificación válida
- Ideal: Bajar switch `working = false` si no está disponible

**Tiempos límite:**

- Cliente sube comprobante: 5 minutos (cancelación automática si no sube)
- Comercio valida pago: 5 minutos (cancelación automática si no valida)
- Notificaciones automáticas antes de timeout

**Rating/Reviews:**

- Obligatorio después de orden `delivered`
- Comercio y delivery se califican por separado
- No editables ni eliminables

**Promociones:**

- Manual (comercio y admin pueden crear)
- Código promocional O automático según tipo

### Direcciones y Geolocalización

**Tabla `addresses`:**

- **Perfil (usuario):** `profile_id` + `role` (ej. usuario, comercio). Para USERS: 2 direcciones (casa `is_default=true` + entrega).
- **Establecimiento (comercio):** `commerce_id` sin `profile_id` (opción onboarding). Dirección del local vinculada solo al comercio.

**USERS tiene 2 direcciones:**

1. **Predeterminada (Casa):** `is_default = true` en tabla `addresses`
    - **Uso:** Base para búsqueda de comercios por geolocalización
    - **Ubicación:** GPS + inputs y selects para mayor precisión
2. **Entrega (Pedido):** Puede ser diferente, se guarda temporalmente o como nueva dirección
    - **Ubicación:** GPS + inputs y selects para mayor precisión

**Búsqueda de Comercios por Geolocalización:**

- **Rango inicial:** 1-1.5 km desde dirección predeterminada del usuario
- **Expansión automática:** Si no hay comercios abiertos, expande automáticamente a 4-5 km
- **Expansión manual:** Usuario puede ampliar rango si desea buscar más lejos
- **Cálculo:** Haversine para calcular distancia entre coordenadas GPS

### Campos Requeridos por Rol

**USERS:** firstName, lastName, phone, photo_users (required)
**COMMERCE:** 7 campos requeridos + 16 opcionales
**DELIVERY COMPANY:** 9 campos requeridos + campos opcionales (igual estructura que COMMERCE)
**DELIVERY AGENT:** 7 campos requeridos + campos opcionales

**IMPORTANTE:** Ver README.md sección completa "📋 DATOS REQUERIDOS POR ACCIÓN Y ROL" para detalles específicos de cada campo.

---

## Análisis Exhaustivo

**Ubicación:** `ANALISIS_EXHAUSTIVO.md` (raíz del proyecto)
**Versión de Prompts:** 2.0 - Basada en Experiencia Real

Este documento contiene un análisis exhaustivo completo del proyecto realizado en Diciembre 2024, cubriendo:

1. Arquitectura y Estructura
2. Código y Calidad
3. Lógica de Negocio
4. Base de Datos
5. Seguridad (OWASP Top 10 completo)
6. Performance (bottlenecks, quick wins, métricas)
7. Testing (cobertura, estrategia, plan de mejora)
8. Backend/API
9. DevOps e Infraestructura
10. Documentación
11. **Verificación de Coherencia entre Archivos** ⭐
12. Estado y Mantenibilidad
13. Oportunidades y Mejoras

### PROMPT MAESTRO - ANÁLISIS COMPLETO v2.0

```
Realiza un ANÁLISIS COMPLETO Y EXHAUSTIVO del proyecto Zonix Eats Backend.

INSTRUCCIONES GENERALES:
- Explora TODA la estructura del proyecto sin dejar áreas sin revisar
- Lee y analiza los archivos más importantes de cada módulo
- Identifica patrones, anti-patrones y code smells
- Proporciona ejemplos concretos de código cuando sea relevante (formato: archivo:línea)
- Prioriza hallazgos por criticidad (crítico, alto, medio, bajo)
- Sugiere mejoras específicas y accionables con estimación de esfuerzo
- **VERIFICA COHERENCIA** entre diferentes archivos de documentación (README, AGENTS.md, etc.)

METODOLOGÍA DE ANÁLISIS:

FASE 1: EXPLORACIÓN INICIAL
1. Mapear estructura completa de directorios y archivos
2. Identificar archivos de configuración clave (composer.json, .env.example, etc.)
3. Leer archivos de documentación principales (README.md, AGENTS.md, CHANGELOG.md, etc.)
4. Identificar stack tecnológico completo y versiones
5. Mapear dependencias principales y secundarias

FASE 2: ANÁLISIS PROFUNDO POR ÁREA
1. ARQUITECTURA Y ESTRUCTURA
2. CÓDIGO Y CALIDAD
3. LÓGICA DE NEGOCIO
4. BASE DE DATOS
5. SEGURIDAD (OWASP Top 10)
6. PERFORMANCE
7. TESTING (206+ tests pasaron, 0 fallaron)
8. BACKEND/API (233+ rutas verificadas)
9. DEVOPS E INFRAESTRUCTURA
10. DOCUMENTACIÓN
11. ESTADO Y MANTENIBILIDAD
12. OPORTUNIDADES Y MEJORAS

Para cada sección, proporciona:
- Análisis detallado con hallazgos específicos (con ubicaciones de archivos)
- Fortalezas (✅), Debilidades (⚠️ o ❌)
- Recomendaciones priorizadas con Impacto, Esfuerzo y Prioridad
- Métricas cuantificables

FORMATO DE SALIDA:
1. RESUMEN EJECUTIVO: Estado, fortalezas top 5, mejoras top 5, score mantenibilidad (X/10)
2. ANÁLISIS POR SECCIÓN con subsecciones numeradas
3. CHECKLIST DE VERIFICACIÓN FINAL
```

**Prompts específicos disponibles (v2.0):** Arquitectónico, Código/Calidad, Lógica de Negocio, Base de Datos, Seguridad (OWASP Top 10), Performance, Testing, Backend/API, DevOps, Documentación, Verificación de Coherencia, Estado/Mantenibilidad, Oportunidades/Mejoras.

### Checklist de Verificación Final

- ✅ Todas las 14 secciones principales fueron analizadas
- ✅ Se verificó coherencia entre diferentes archivos de documentación
- ✅ Se identificaron y corrigieron discrepancias encontradas
- ✅ Las métricas mencionadas son consistentes en toda la documentación
- ✅ Se incluyeron métricas cuantificables cuando fue posible
- ✅ Se proporcionaron estimaciones de esfuerzo para mejoras sugeridas
- ✅ Se completó el checklist OWASP Top 10 completo
- ✅ Se identificaron quick wins (alto impacto, bajo esfuerzo)
- ✅ Se creó un roadmap técnico con corto/medio/largo plazo

**Cuándo actualizar:** Después de cambios arquitectónicos importantes, cada 3-6 meses, o antes de releases mayores.

---

## Pending Improvements

### 🟡 ALTO

- Paginación en 30+ endpoints que usan `->get()` sin paginación
- Índices BD: `orders.status`, `orders.created_at`, `products.commerce_id`
- Refactorizar God Classes: `AnalyticsController` (1,130 líneas), `PaymentController` (815 líneas)

### 🟢 MEDIO

- Swagger/OpenAPI docs
- Redis caching
- Queues para emails/procesamiento pesado
- Permisos granulares (mejora de roles)

---

**Documentación completa de lógica de negocio:** Ver `README.md`
**Última actualización:** 11 Febrero 2026
