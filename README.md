# Zonix Eats Backend

Backend de Laravel para la aplicación de comida rápida Zonix Eats.

## Estructura del Proyecto

El proyecto sigue una arquitectura de dominio/feature con los siguientes directorios principales:

- `app/Http/Controllers/` - Controladores organizados por dominio
- `app/Models/` - Modelos de Eloquent
- `app/Services/` - Lógica de negocio
- `database/factories/` - Factories para testing
- `database/seeders/` - Seeders para datos de prueba
- `tests/` - Tests organizados por tipo y dominio

## Roles del Sistema

El sistema maneja los siguientes roles de usuario:

- **admin** - Administrador del sistema
- **users** - Clientes/compradores (antes "buyer")
- **commerce** - Dueños de restaurantes/comercios
- **delivery_company** - Empresas de delivery
- **delivery_agent** - Repartidores individuales
- **delivery** - Alias para delivery_agent

## Tests Implementados

### Tests de Roles (WorkingRoleTest.php)

✅ **Tests que funcionan correctamente:**

1. **Acceso a features por rol:**
   - `test_users_can_access_client_features` - Verifica que usuarios pueden acceder a productos, restaurantes, carrito y órdenes
   - `test_commerce_can_access_restaurant_features` - Verifica que comercios pueden acceder a órdenes, productos y dashboard
   - `test_delivery_agent_can_access_agent_features` - Verifica que repartidores pueden acceder a órdenes asignadas

2. **Autorización y permisos:**
   - `test_users_cannot_access_admin_features` - Verifica que usuarios no pueden acceder a features de admin
   - `test_commerce_cannot_access_delivery_features` - Verifica que comercios no pueden acceder a features de delivery
   - `test_delivery_cannot_access_commerce_features` - Verifica que delivery no puede acceder a features de commerce

3. **Autenticación:**
   - `test_unauthenticated_users_cannot_access_protected_endpoints` - Verifica que endpoints protegidos requieren autenticación
   - `test_users_can_authenticate_with_sanctum` - Verifica autenticación de usuarios
   - `test_commerce_can_authenticate_with_sanctum` - Verifica autenticación de comercios
   - `test_delivery_can_authenticate_with_sanctum` - Verifica autenticación de repartidores

4. **Verificación de roles:**
   - `test_user_has_correct_role_after_creation` - Verifica que los factories crean usuarios con roles correctos
   - `test_role_middleware_works_correctly` - Verifica que el middleware de roles funciona correctamente
   - `test_can_create_users_with_different_roles` - Verifica creación de usuarios con diferentes roles

5. **Logout:**
   - `test_users_can_logout` - Verifica logout de usuarios
   - `test_commerce_can_logout` - Verifica logout de comercios
   - `test_delivery_can_logout` - Verifica logout de repartidores

### Tests Existentes que Funcionan

✅ **Tests de funcionalidad específica:**
- `AdminRoleTest` - Tests básicos de admin
- `DeliveryRoleTest` - Tests básicos de delivery
- `CartControllerTest` - Tests del carrito
- `CartServiceTest` - Tests del servicio de carrito
- `CommerceProductControllerTest` - Tests de productos de comercio
- `OrderControllerTest` - Tests de órdenes
- `OrderBroadcastTest` - Tests de broadcasting de órdenes
- `ProductControllerTest` - Tests de productos
- `RestaurantControllerTest` - Tests de restaurantes
- `ReviewServiceTest` - Tests de reseñas
- `PostServiceTest` - Tests de posts
- `TrackingServiceTest` - Tests de tracking
- `EcommerceFlowTest` - Tests del flujo de ecommerce

### Resumen de Cobertura

**Tests Exitosos:** 77 tests pasando
**Tests Fallando:** 21 tests fallando
**Total de Assertions:** 249

**Cobertura por Rol:**
- ✅ **Users (Clientes):** Acceso a productos, restaurantes, carrito, órdenes, autenticación, logout
- ✅ **Commerce:** Acceso a órdenes, productos, dashboard, autenticación, logout
- ✅ **Delivery:** Acceso a órdenes asignadas, autenticación, logout
- ✅ **Admin:** Tests básicos funcionando
- ⚠️ **Autenticación completa:** Algunos endpoints de login/registro no implementados

### Problemas Identificados

1. **Relación `roles` no definida** en el modelo User (afecta endpoints de admin)
2. **Endpoints de login/registro** no implementados completamente
3. **Validaciones específicas** en algunos servicios necesitan ajustes
4. **Algunos endpoints** de perfiles y órdenes requieren implementación completa

### Próximos Pasos

1. **Implementar endpoints faltantes** de autenticación y registro
2. **Corregir relación `roles`** en el modelo User
3. **Completar validaciones** en servicios de órdenes y productos
4. **Implementar tests de integración** más completos
5. **Agregar tests de edge cases** y manejo de errores

## Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos de roles
php artisan test --filter=WorkingRoleTest

# Ejecutar tests de un rol específico
php artisan test --filter="test_users_can_access_client_features"
```

## Middleware de Roles

El sistema utiliza middleware personalizado para controlar el acceso por roles:

```php
Route::middleware('role:users')->prefix('buyer')->group(function () {
    // Rutas para usuarios/clientes
});

Route::middleware('role:commerce')->prefix('commerce')->group(function () {
    // Rutas para comercios
});

Route::middleware('role:delivery')->prefix('delivery')->group(function () {
    // Rutas para repartidores
});

Route::middleware('role:admin')->prefix('admin')->group(function () {
    // Rutas para administradores
});
```

## Factories

Los factories están configurados para crear usuarios con roles específicos:

```php
// Crear usuario con rol específico
$user = User::factory()->buyer()->create();
$commerce = User::factory()->commerce()->create();
$delivery = User::factory()->deliveryAgent()->create();
$admin = User::factory()->admin()->create();
```

## Configuración

1. Copiar `.env.example` a `.env`
2. Configurar base de datos
3. Ejecutar migraciones: `php artisan migrate`
4. Ejecutar seeders: `php artisan db:seed`
5. Instalar dependencias: `composer install`

## Desarrollo

- **Framework:** Laravel 10
- **Base de datos:** MySQL
- **Autenticación:** Laravel Sanctum
- **Testing:** PHPUnit
- **API:** RESTful con JSON responses
