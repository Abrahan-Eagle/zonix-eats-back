# Zonix Eats Backend - API Laravel

## 📋 Descripción

Backend de la aplicación Zonix Eats desarrollado en Laravel 10. Proporciona una API REST completa para la gestión de pedidos, productos, usuarios y comunicación en tiempo real mediante Firebase Cloud Messaging (FCM) y Pusher.

## 📋 LÓGICA DE NEGOCIO Y DATOS REQUERIDOS POR ROL - MVP

### ❓ DECISIONES TOMADAS SEGÚN MEJORES PRÁCTICAS DE ECOMMERCE

#### 1. **Carrito Multi-Commerce: ¿Permitir productos de diferentes comercios?**

**✅ DECISIÓN: NO puede haber productos de diferentes comercios en el mismo carrito**

El carrito solo puede tener productos de UN SOLO comercio. Si el usuario intenta agregar un producto de otro comercio, el sistema limpia el carrito automáticamente.

**¿Qué significa esto?**

Imagina que tienes un carrito de compras. Tienes dos opciones:

**OPCIÓN A: Permitir múltiples comercios (Multi-Commerce)**
```
Tu carrito puede tener:
- Producto A del Comercio "Pizza Hut" ($10)
- Producto B del Comercio "McDonald's" ($8)
- Producto C del Comercio "Pizza Hut" ($5)
```
✅ **Ventaja:** El usuario puede comprar de varios comercios a la vez  
❌ **Desventaja:** Complica el proceso de pago (cada comercio tiene su propio proceso)  
❌ **Desventaja:** Complica el envío (cada comercio envía por separado)

**OPCIÓN B: Solo un comercio por carrito (Uni-Commerce)**
```
Tu carrito solo puede tener:
- Producto A del Comercio "Pizza Hut" ($10)
- Producto B del Comercio "Pizza Hut" ($8)
- Producto C del Comercio "Pizza Hut" ($5)

Si intentas agregar un producto de "McDonald's":
→ El sistema te pregunta: "¿Deseas limpiar el carrito y agregar este producto?"
```
✅ **Ventaja:** Proceso de pago más simple  
✅ **Ventaja:** Un solo proceso de envío  
✅ **Ventaja:** Mejor experiencia de usuario (más simple)

**Explicación:**
Actualmente el carrito puede tener productos de diferentes comercios. Por ejemplo:
- Producto A del Comercio 1
- Producto B del Comercio 2  
- Producto C del Comercio 1

**Opciones:**
- **Opción A:** Permitir múltiples comercios (como Amazon, donde puedes comprar de diferentes vendedores)
- **Opción B:** Solo un comercio por carrito (como Uber Eats, donde eliges un restaurante y solo productos de ese restaurante)

**Decisión según mejores prácticas:**
✅ **OPCIÓN B: Solo un comercio por carrito** (para MVP)
- **Razón:** Simplifica el proceso de checkout
- **Razón:** Cada comercio tiene su propio proceso de pago y envío
- **Razón:** Mejor experiencia de usuario (más simple)
- **Implementación:** Al agregar producto de diferente comercio, limpiar carrito anterior o mostrar advertencia

**Lógica de Implementación:**
```php
// Al agregar producto al carrito
if ($cart->items()->exists()) {
    $existingCommerceId = $cart->items()->first()->product->commerce_id;
    if ($existingCommerceId !== $newProduct->commerce_id) {
        // Limpiar carrito y agregar nuevo producto
        $cart->items()->delete();
        // O mostrar error y pedir confirmación
    }
}
```

---

#### 2. **Validación de Precio: ¿Validar que no cambió o aceptar cambios?**

**Explicación:**
Cuando el usuario agrega un producto al carrito con precio $10, pero al crear la orden el precio cambió a $12:
- **Opción A:** Validar que el precio no cambió y rechazar si cambió
- **Opción B:** Aceptar el nuevo precio y notificar al usuario

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Validar precio y recalcular** (para MVP)
- **Razón:** Protege al usuario de cambios de precio inesperados
- **Razón:** Evita problemas de confianza
- **Razón:** Mejor práctica en ecommerce (Amazon, MercadoLibre lo hacen)

**Implementación:**
```php
// Al crear orden, recalcular total desde productos actuales
$calculatedTotal = 0;
foreach ($validated['products'] as $product) {
    $productModel = Product::find($product['id']);
    $calculatedTotal += $productModel->price * $product['quantity'];
}

// Validar que coincida (margen de 0.01 por redondeo)
if (abs($calculatedTotal - $validated['total']) > 0.01) {
    return response()->json([
        'success' => false,
        'message' => 'El precio de algunos productos ha cambiado. Por favor, revisa tu carrito.',
        'recalculated_total' => $calculatedTotal
    ], 422);
}
```

---

#### 3. **Stock: ¿Implementar gestión de stock o solo validar available?**

**Explicación:**
- **Opción A:** Solo validar `available = true/false` (producto disponible o no)
- **Opción B:** Implementar gestión de stock con cantidades (tiene 10 unidades, se venden 2, quedan 8)

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Solo validar `available` para MVP** (agregar stock después)
- **Razón:** Más simple para MVP
- **Razón:** Funciona para productos que no requieren control de cantidad exacta
- **Razón:** Se puede agregar stock después sin romper funcionalidad actual

**Implementación MVP:**
```php
// Validar solo available
if (!$product->available) {
    throw new \Exception('Producto no está disponible');
}
```

**Futuro (Post-MVP):**
- Agregar campo `stock_quantity` a Product
- Descontar stock al crear orden
- Restaurar stock al cancelar orden
- Alertas de stock bajo

---

#### 4. **Delivery: ¿Mantener rol delivery o eliminarlo para MVP?**

**Explicación:**
- **Opción A:** Mantener rol delivery (repartidores propios)
- **Opción B:** Eliminar rol delivery (usar couriers externos o el comercio maneja su propio delivery)

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Mantener rol delivery para MVP** (pero simplificado)
- **Razón:** Permite control del proceso de entrega
- **Razón:** Mejor experiencia para comercios pequeños
- **Razón:** Se puede integrar con couriers externos después

**Implementación MVP:**
- Mantener rol `delivery`
- Simplificar: solo aceptar órdenes y marcar como entregado
- Eliminar tracking en tiempo real (agregar después)
- Eliminar asignación automática (agregar después)

---

#### 5. **Eventos: ¿Activar eventos de broadcasting o eliminarlos del MVP?**

**¿Qué significa esto?**

**Eventos de broadcasting = Notificaciones en tiempo real**

**Ejemplo:**
Cuando un usuario crea una orden, el sistema puede:
- **Con eventos:** Notificar inmediatamente al comercio (sin recargar página)
- **Sin eventos:** El comercio debe recargar la página para ver nuevas órdenes

**Decisión:** ✅ **SÍ - Eventos en tiempo real** (para MVP)

**Implementación:**
- ✅ **Firebase Cloud Messaging (FCM)** - Para notificaciones push a dispositivos móviles
- ✅ **Pusher** - Para broadcasting en tiempo real (web)
- ✅ Tabla `notifications` en BD - Para almacenar notificaciones
- ✅ `fcm_device_token` en profiles - Para enviar notificaciones push
- ✅ `notification_preferences` en profiles - Para preferencias del usuario

**Eventos activados:**
- `OrderCreated` → Notifica cuando se crea orden
- `OrderStatusChanged` → Notifica cuando cambia estado
- `PaymentValidated` → Notifica cuando se valida pago

**NO se usa WebSocket**, se usa Firebase y Pusher que ya están implementados en el proyecto.

---

#### 6. **Perfiles: ¿Requerir perfil completo o permitir datos mínimos?**

**¿Qué significa esto?**

**Datos Completos del Perfil:**
```json
{
  "firstName": "Juan",
  "lastName": "Pérez",
  "middleName": "Carlos",           // Opcional
  "secondLastName": "González",     // Opcional
  "date_of_birth": "1990-01-01",    // Opcional
  "maritalStatus": "single",         // Opcional
  "sex": "M",                        // Opcional
  "phone": "+1234567890",           // Requerido
  "address": "Calle Principal 123", // Requerido si delivery
  "photo_users": "url_foto.jpg"     // Opcional
}
```

**Datos Mínimos para Crear Orden:**
```json
{
  "firstName": "Juan",              // ✅ Requerido
  "lastName": "Pérez",             // ✅ Requerido
  "phone": "+1234567890",           // ✅ Requerido (para contacto)
  "address": "Calle Principal 123"  // ✅ Requerido SOLO si delivery_type = 'delivery'
}
```

**OPCIÓN A: Requerir perfil completo**
```
Usuario intenta crear orden:
→ Sistema verifica: ¿Tiene todos los datos?
→ Si falta algún dato → Rechaza orden
→ Muestra: "Debes completar tu perfil primero"
→ Usuario debe ir a perfil y completar TODO
→ Luego puede crear orden
```
❌ **Desventaja:** Bloquea primera compra  
❌ **Desventaja:** Menor conversión (más fricción)  
❌ **Desventaja:** Usuario puede abandonar

**OPCIÓN B: Permitir datos mínimos (completar después)**
```
Usuario intenta crear orden:
→ Sistema verifica: ¿Tiene datos mínimos? (firstName, lastName, phone, address si delivery)
→ Si tiene datos mínimos → Permite crear orden
→ Si falta algún dato mínimo → Rechaza y pide completar
→ Datos opcionales (date_of_birth, etc.) se pueden completar después
```
✅ **Ventaja:** No bloquea primera compra  
✅ **Ventaja:** Mejor conversión (menos fricción)  
✅ **Ventaja:** Usuario puede completar datos después

**Decisión según mejores prácticas:**
✅ **OPCIÓN: Datos mínimos para crear orden, completar después**
- **Razón:** No bloquear primera compra
- **Razón:** Mejor conversión (menos fricción)
- **Razón:** Completar datos durante el proceso de checkout

**Datos Mínimos Requeridos para Orden:**
```php
// Mínimos para crear orden
- firstName (required)
- lastName (required)
- phone (required) // Para contacto
- address (required si delivery_type = 'delivery')
```

**Datos Opcionales (completar después):**
- date_of_birth
- maritalStatus
- sex
- photo_users

**Implementación:**
```php
// Validar datos mínimos para orden
$requiredFields = ['firstName', 'lastName', 'phone'];
if ($deliveryType === 'delivery') {
    $requiredFields[] = 'address';
}

foreach ($requiredFields as $field) {
    if (empty($profile->$field)) {
        throw new \Exception("Se requiere {$field} para crear una orden");
    }
}
```

---

### 📋 RESUMEN DE DECISIONES MVP

| Decisión | Opción Elegida | Razón |
|----------|----------------|-------|
| Carrito Multi-Commerce | Solo un comercio por carrito | Simplifica checkout y UX |
| Validación de Precio | Validar y recalcular | Protege al usuario |
| Stock | AMBAS opciones (available O stock_quantity) | El comercio elige |
| Delivery | Sistema completo (propio, empresas, independientes) | Flexibilidad total |
| Eventos | Firebase + Pusher (NO WebSocket) | Ya implementado |
| Perfiles | Datos mínimos (USERS) vs completos (COMMERCE, DELIVERY) | Por rol |

---

### 📋 DATOS REQUERIDOS POR ACCIÓN Y ROL

#### 👤 ROL: USERS (Comprador/Cliente)

**Autenticación:**
- **Registro:** name, email, password, password_confirmation
- **Login:** email, password
- **Google OAuth:** data.sub, data.email, data.name

**Perfil - Datos Mínimos para Crear Orden:**
- **firstName** (required) - Nombre
- **lastName** (required) - Apellido
- **phone** (required) - Teléfono (para contacto)
- **photo_users** (required) - Foto de perfil (necesaria para que delivery pueda hacer la entrega)

**Direcciones - Sistema de 2 Direcciones:**
1. **Dirección Predeterminada (Casa):**
   - Dirección principal del usuario (casa)
   - Se guarda en tabla `addresses` con `is_default = true` (si existe campo)
   - Campos: `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`
   - Ubicación: GPS + inputs y selects para mayor precisión

2. **Dirección de Entrega (Pedido Actual):**
   - Dirección donde se está haciendo el pedido actual
   - Puede ser diferente a la dirección predeterminada
   - Se puede guardar temporalmente o como nueva dirección
   - Campos: `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`
   - Ubicación: GPS + inputs y selects para mayor precisión

**Perfil - Datos Opcionales:**
- `middleName` - Segundo nombre
- `secondLastName` - Segundo apellido
- `date_of_birth` - Fecha de nacimiento
- `maritalStatus` - Estado civil (married, divorced, single, widowed)
- `sex` - Sexo (F, M, O)
- `addresses[]` - Múltiples direcciones guardadas (tabla `addresses`)
  - `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`, `is_default`
- `phones[]` - Múltiples teléfonos (tabla `phones`)
  - `number`, `operator_code_id`, `is_primary`, `status`, `approved`
- `documents[]` - Documentos (tabla `documents`)
  - `type` (ci, passport, rif, neighborhood_association), `number_ci`, `front_image`, `issued_at`, `expires_at`, `approved`, `status`
- `user_locations[]` - Historial de ubicaciones (tabla `user_locations`)
  - `latitude`, `longitude`, `accuracy`, `altitude`, `speed`, `heading`, `address`
- `fcm_device_token` - Token para notificaciones push
- `notification_preferences` - Preferencias de notificaciones (json)

**Total:** 4 campos mínimos (firstName, lastName, phone, photo_users) + 2 direcciones (predeterminada + entrega) + campos opcionales

**Direcciones:**
- **Crear:** street, house_number, postal_code, latitude, longitude, city_id, is_default (opcional)
- **Actualizar:** Cualquier campo opcional
- **Nota:** La tabla `addresses` usa estructura con `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`, no `name`, `address_line_1`, `city`, `state`, `country`

**Carrito:**
- **Agregar:** product_id, quantity (min:1, max:100)
- **Actualizar cantidad:** product_id, quantity
- **Notas:** notes (opcional, max:500)

**Órdenes:**
- **Crear:** commerce_id, products[], delivery_type, delivery_address (si delivery), total, notes (opcional)
- **Cancelar:** reason (required, max:500)
- **Subir comprobante:** payment_proof (file), payment_method, reference_number

**Reviews:**
- **Crear:** reviewable_type, reviewable_id, rating (1-5), comentario (opcional)

---

#### 🏪 ROL: COMMERCE (Vendedor/Tienda)

**Perfil - Datos Completos Requeridos:**
- **firstName** (required) - Nombre del dueño/representante
- **lastName** (required) - Apellido del dueño/representante
- **phone** (required) - Teléfono de contacto
- **address** (required) - Dirección del comercio
- **business_name** (required) - Nombre del negocio/comercio
- **business_type** (required) - Tipo de negocio (restaurante, tienda, etc.)
- **tax_id** (required) - Número de identificación tributaria (RUC, NIT, etc.)

**Datos Opcionales (13+ campos):**

**Del Perfil (Profile):**
1. `middleName` - Segundo nombre
2. `secondLastName` - Segundo apellido
3. `photo_users` - Foto de perfil del dueño/representante
4. `date_of_birth` - Fecha de nacimiento
5. `maritalStatus` - Estado civil (married, divorced, single, widowed)
6. `sex` - Sexo (F, M, O)

**Del Comercio (Commerce):**
7. `commerce.image` - Imagen del comercio/logo
8. `commerce.phone` - Teléfono del comercio (adicional al del perfil)
9. `commerce.address` - Dirección del comercio (adicional al del perfil)
10. `commerce.open` - Si está abierto (boolean, default: false)
11. `commerce.schedule` - Horario de atención (json)

**Relaciones (Múltiples registros):**
12. `addresses[]` - Múltiples direcciones (tabla `addresses`)
    - `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`, `status`
13. `phones[]` - Múltiples teléfonos (tabla `phones`)
    - `number`, `operator_code_id`, `is_primary`, `status`, `approved`
14. `documents[]` - Documentos (tabla `documents`)
    - `type` (ci, passport, rif, neighborhood_association)
    - `number_ci`, `RECEIPT_N`, `sky`
    - `rif_url`, `taxDomicile`, `commune_register`, `community_rif`
    - `front_image`, `issued_at`, `expires_at`, `approved`, `status`

**Sistema:**
15. `fcm_device_token` - Token para notificaciones push
16. `notification_preferences` - Preferencias de notificaciones (json)

**Total:** 7 campos requeridos + 16 campos opcionales + múltiples direcciones/teléfonos/documentos

**Productos:**
- **Crear:** name, description, price, available (required), stock_quantity (opcional), image (opcional), category_id (opcional)
  - `available` = true/false (siempre requerido)
  - `stock_quantity` = número o null (opcional, si es null solo usa available)
- **Actualizar:** Cualquier campo opcional
- **Eliminar:** Solo validar que pertenece al commerce

**Órdenes:**
- **Validar pago:** is_valid (boolean), rejection_reason (si is_valid=false)
- **Actualizar estado:** status (paid, processing, shipped, cancelled)

**Delivery:**
- **Configurar delivery propio:** El comercio puede tener sus propios repartidores
- **Usar delivery de la plataforma:** Puede buscar empresas de delivery o motorizados independientes

**Dashboard:**
- Ningún dato requerido (usa usuario autenticado)

---

#### 🚚 ROL: DELIVERY (Jerarquía Completa)

**4.1. DELIVERY COMPANY (Empresa de Delivery)**

**Perfil - Datos Completos Requeridos:**
- **firstName** (required) - Nombre del representante
- **lastName** (required) - Apellido del representante
- **phone** (required) - Teléfono
- **address** (required) - Dirección
- **photo_users** (required) - Foto del representante
- **delivery_company.name** (required) - Nombre de la empresa
- **delivery_company.tax_id** (required) - CI/RUC de la empresa
- **delivery_company.phone** (required) - Teléfono de la empresa
- **delivery_company.address** (required) - Dirección de la empresa

**Datos Opcionales:**

**Del Perfil (Profile):**
- `middleName` - Segundo nombre
- `secondLastName` - Segundo apellido
- `date_of_birth` - Fecha de nacimiento
- `maritalStatus` - Estado civil
- `sex` - Sexo

**De la Empresa de Delivery (Delivery Company):**
- `delivery_company.image` - Logo de la empresa de delivery
- `delivery_company.phone` - Teléfono adicional de la empresa
- `delivery_company.address` - Dirección adicional de la empresa
- `delivery_company.open` - Si está abierta/disponible (boolean, default: false)
- `delivery_company.schedule` - Horario de atención (json)

**Relaciones (Múltiples registros):**
- `addresses[]` - Múltiples direcciones (tabla `addresses`)
  - `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`, `is_default`
- `phones[]` - Múltiples teléfonos (tabla `phones`)
  - `number`, `operator_code_id`, `is_primary`, `status`, `approved`
- `documents[]` - Documentos (tabla `documents`)
  - `type` (ci, passport, rif, neighborhood_association)
  - `number_ci`, `RECEIPT_N`, `sky`
  - `rif_url`, `taxDomicile`, `commune_register`, `community_rif`
  - `front_image`, `issued_at`, `expires_at`, `approved`, `status`

**Sistema:**
- `fcm_device_token` - Token para notificaciones push
- `notification_preferences` - Preferencias de notificaciones (json)

**4.2. DELIVERY AGENT (Motorizado - Puede ser de empresa o independiente)**

**Perfil - Datos Completos Requeridos:**
- **firstName** (required) - Nombre
- **lastName** (required) - Apellido
- **phone** (required) - Teléfono
- **address** (required) - Dirección
- **photo_users** (required) - Foto de perfil (necesaria para identificación)
- **vehicle_type** (required) - Tipo de vehículo (moto, auto, bicicleta, etc.)
- **license_number** (required) - Número de licencia de conducir

**Si pertenece a empresa:**
- **delivery_agent.company_id** (required) - ID de la empresa

**Si es independiente:**
- **delivery_agent.company_id** = null - No pertenece a ninguna empresa

**Datos Opcionales:**
- `middleName`, `secondLastName`, `photo_users`
- `date_of_birth`, `maritalStatus`, `sex`
- `delivery_agent.phone` - Teléfono adicional
- `delivery_agent.status` - Estado (activo, inactivo, suspendido)
- `delivery_agent.working` - Si está trabajando
- `delivery_agent.rating` - Calificación
- `user_locations[]` - Ubicaciones actuales (latitude, longitude)
- `addresses[]`, `phones[]`, `documents[]`
- `fcm_device_token`, `notification_preferences`

**Órdenes:**
- **Ver disponibles:** GET /api/delivery/orders/available
- **Aceptar:** POST /api/delivery/orders/{id}/accept
- **Ver asignadas:** GET /api/delivery/orders
- **Actualizar estado:** PUT /api/delivery/orders/{id}/status (shipped, delivered)
- **Actualizar ubicación:** PUT /api/delivery/location (latitude, longitude)

**⚠️ NOTA:** La migración actual tiene `company_id` como required. Se necesita hacer nullable para motorizados independientes.

---

#### 👨‍💼 ROL: ADMIN (Administrador)

**Usuarios:**
- **Cambiar rol:** role (users, commerce, delivery, admin)
- **Suspender/Activar:** status (active, suspended)

**Comercios:**
- **Aprobar/Suspender:** open (boolean)

---

### 🔄 FLUJOS TRANSVERSALES

#### Flujo Completo: Crear Orden y Procesarla

1. **Usuario agrega productos al carrito**
   - Validar producto disponible
   - Validar commerce activo
   - Validar mismo commerce (si ya hay productos)

2. **Usuario crea orden**
   - Validar profile con datos mínimos (firstName, lastName, phone, address si delivery)
   - Validar todos los productos disponibles
   - Validar todos los productos del mismo commerce
   - Recalcular total y validar
   - Crear orden en transacción
   - Limpiar carrito

3. **Usuario sube comprobante**
   - Subir archivo
   - Guardar información de pago
   - Estado sigue `pending_payment`

4. **Comercio valida pago**
   - Si válido: `pending_payment` → `paid`
   - Si inválido: `pending_payment` → `cancelled`

5. **Comercio procesa orden**
   - `paid` → `processing` (inicia preparación/empaque)

6. **Comercio marca como enviado**
   - `processing` → `shipped` (listo para delivery)

7. **Delivery acepta orden**
   - Crear OrderDelivery
   - Estado sigue `shipped`

8. **Delivery marca como entregado**
   - `shipped` → `delivered`
   - Marcar delivery como disponible

---

### ✅ VALIDACIONES GLOBALES

**Autenticación:**
- Token Sanctum válido
- Token no expirado
- Usuario activo (no suspendido)

**Autorización:**
- Usuario tiene el role correcto
- Usuario puede acceder al recurso (propietario o admin)

**Datos:**
- Campos requeridos presentes
- Tipos de datos correctos
- Formatos válidos (email, fecha, etc.)
- Rangos válidos (min, max)

**Negocio:**
- Estados válidos según transiciones
- Recursos existen y están disponibles
- Reglas de negocio cumplidas

---

### 📝 ESTADOS DE ORDEN (MVP)

- `pending_payment` - Pendiente de pago
- `paid` - Pago validado
- `processing` - En procesamiento/empaque (antes "preparing")
- `shipped` - Enviado/en camino (antes "on_way")
- `delivered` - Entregado
- `cancelled` - Cancelado

**Transiciones Válidas:**
```
pending_payment → paid (validación de pago)
                → cancelled (cancelación)

paid → processing (comercio inicia)
     → cancelled (comercio cancela)

processing → shipped (comercio envía)
           → cancelled (comercio cancela)

shipped → delivered (delivery entrega)
```

**Reglas de Cancelación:**
- **Comprador:** Solo puede cancelar en `pending_payment`
- **Comercio:** Puede cancelar en `paid` o `processing`
- **Admin:** Puede cancelar en cualquier estado

---

## 🎯 MVP - MINIMUM VIABLE PRODUCT

#### 1. **Carrito Multi-Commerce: ¿Permitir productos de diferentes comercios?**

**Explicación:**
Actualmente el carrito puede tener productos de diferentes comercios. Por ejemplo:
- Producto A del Comercio 1
- Producto B del Comercio 2
- Producto C del Comercio 1

**Opciones:**
- **Opción A:** Permitir múltiples comercios (como Amazon, donde puedes comprar de diferentes vendedores)
- **Opción B:** Solo un comercio por carrito (como Uber Eats, donde eliges un restaurante y solo productos de ese restaurante)

**Decisión según mejores prácticas:**
✅ **OPCIÓN B: Solo un comercio por carrito** (para MVP)
- **Razón:** Simplifica el proceso de checkout
- **Razón:** Cada comercio tiene su propio proceso de pago y envío
- **Razón:** Mejor experiencia de usuario (más simple)
- **Implementación:** Al agregar producto de diferente comercio, limpiar carrito anterior o mostrar advertencia

**Lógica:**
```php
// Al agregar producto al carrito
if ($cart->items()->exists()) {
    $existingCommerceId = $cart->items()->first()->product->commerce_id;
    if ($existingCommerceId !== $newProduct->commerce_id) {
        // Opción 1: Limpiar carrito y agregar nuevo producto
        // Opción 2: Mostrar error y pedir confirmación
        throw new \Exception('El carrito contiene productos de otro comercio. ¿Desea limpiar el carrito?');
    }
}
```

---

#### 2. **Validación de Precio: ¿Validar que no cambió o aceptar cambios?**

**Explicación:**
Cuando el usuario agrega un producto al carrito con precio $10, pero al crear la orden el precio cambió a $12:
- **Opción A:** Validar que el precio no cambió y rechazar si cambió
- **Opción B:** Aceptar el nuevo precio y notificar al usuario

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Validar precio y recalcular** (para MVP)
- **Razón:** Protege al usuario de cambios de precio inesperados
- **Razón:** Evita problemas de confianza
- **Razón:** Mejor práctica en ecommerce (Amazon, MercadoLibre lo hacen)

**Implementación:**
```php
// Al crear orden, recalcular total desde productos actuales
$calculatedTotal = 0;
foreach ($validated['products'] as $product) {
    $productModel = Product::find($product['id']);
    $calculatedTotal += $productModel->price * $product['quantity'];
}

// Validar que coincida (margen de 0.01 por redondeo)
if (abs($calculatedTotal - $validated['total']) > 0.01) {
    return response()->json([
        'success' => false,
        'message' => 'El precio de algunos productos ha cambiado. Por favor, revisa tu carrito.',
        'recalculated_total' => $calculatedTotal
    ], 422);
}
```

---

#### 3. **Stock: ¿Implementar gestión de stock o solo validar available?**

**Explicación:**
- **Opción A:** Solo validar `available = true/false` (producto disponible o no)
- **Opción B:** Implementar gestión de stock con cantidades (tiene 10 unidades, se venden 2, quedan 8)

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Solo validar `available` para MVP** (agregar stock después)
- **Razón:** Más simple para MVP
- **Razón:** Funciona para productos que no requieren control de cantidad exacta
- **Razón:** Se puede agregar stock después sin romper funcionalidad actual

**Implementación MVP:**
```php
// Validar solo available
if (!$product->available) {
    throw new \Exception('Producto no está disponible');
}
```

**Futuro (Post-MVP):**
- Agregar campo `stock_quantity` a Product
- Descontar stock al crear orden
- Restaurar stock al cancelar orden
- Alertas de stock bajo

---

#### 4. **Delivery: ¿Mantener rol delivery o eliminarlo para MVP?**

**Explicación:**
- **Opción A:** Mantener rol delivery (repartidores propios)
- **Opción B:** Eliminar rol delivery (usar couriers externos o el comercio maneja su propio delivery)

**Decisión según mejores prácticas:**
✅ **OPCIÓN A: Mantener rol delivery para MVP** (pero simplificado)
- **Razón:** Permite control del proceso de entrega
- **Razón:** Mejor experiencia para comercios pequeños
- **Razón:** Se puede integrar con couriers externos después

**Implementación MVP:**
- Mantener rol `delivery`
- Simplificar: solo aceptar órdenes y marcar como entregado
- Eliminar tracking en tiempo real (agregar después)
- Eliminar asignación automática (agregar después)

---

#### 5. **Eventos: ¿Activar eventos de broadcasting o eliminarlos del MVP?**

**¿Qué significa esto?**

**Eventos de broadcasting = Notificaciones en tiempo real**

**Ejemplo:**
Cuando un usuario crea una orden, el sistema puede:
- **Con eventos:** Notificar inmediatamente al comercio (sin recargar página)
- **Sin eventos:** El comercio debe recargar la página para ver nuevas órdenes

**Decisión:** ✅ **SÍ - Eventos en tiempo real** (para MVP)

**Implementación:**
- ✅ **Firebase Cloud Messaging (FCM)** - Para notificaciones push a dispositivos móviles
- ✅ **Pusher** - Para broadcasting en tiempo real (web)
- ✅ Tabla `notifications` en BD - Para almacenar notificaciones
- ✅ `fcm_device_token` en profiles - Para enviar notificaciones push
- ✅ `notification_preferences` en profiles - Para preferencias del usuario

**Eventos activados:**
- `OrderCreated` → Notifica cuando se crea orden
- `OrderStatusChanged` → Notifica cuando cambia estado
- `PaymentValidated` → Notifica cuando se valida pago

**NO se usa WebSocket**, se usa Firebase y Pusher que ya están implementados en el proyecto.

---

#### 6. **Perfiles: ¿Requerir perfil completo o permitir datos mínimos?**

**Explicación:**
**Datos Completos:**
```json
{
  "firstName": "Juan",
  "lastName": "Pérez",
  "date_of_birth": "1990-01-01",
  "maritalStatus": "single",
  "sex": "M",
  "phone": "+1234567890",
  "address": "Calle Principal 123"
}
```

**Datos Mínimos:**
```json
{
  "firstName": "Juan",
  "lastName": "Pérez",
  "phone": "+1234567890"
}
```

**Decisión según mejores prácticas:**
✅ **OPCIÓN: Datos mínimos para crear orden, completar después**
- **Razón:** No bloquear primera compra
- **Razón:** Mejor conversión (menos fricción)
- **Razón:** Completar datos durante el proceso de checkout

**Datos Mínimos Requeridos para Orden:**
```php
// Mínimos para crear orden
- firstName (required)
- lastName (required)
- phone (required) // Para contacto
- address (required si delivery_type = 'delivery')
```

**Datos Opcionales (completar después):**
- date_of_birth
- maritalStatus
- sex
- photo_users

**Implementación:**
```php
// Validar datos mínimos para orden
$requiredFields = ['firstName', 'lastName', 'phone'];
if ($deliveryType === 'delivery') {
    $requiredFields[] = 'address';
}

foreach ($requiredFields as $field) {
    if (empty($profile->$field)) {
        throw new \Exception("Se requiere {$field} para crear una orden");
    }
}
```

---

### 📋 RESUMEN DE DECISIONES MVP

| Decisión | Opción Elegida | Razón |
|----------|----------------|-------|
| Carrito Multi-Commerce | Solo un comercio por carrito | Simplifica checkout y UX |
| Validación de Precio | Validar y recalcular | Protege al usuario |
| Stock | AMBAS opciones (available O stock_quantity) | El comercio elige |
| Delivery | Sistema completo (propio, empresas, independientes) | Flexibilidad total |
| Eventos | Firebase + Pusher (NO WebSocket) | Ya implementado |
| Perfiles | Datos mínimos (USERS) vs completos (COMMERCE, DELIVERY) | Por rol |

---

## 🎯 MVP - MINIMUM VIABLE PRODUCT

### 📊 Definición del MVP

El MVP (Minimum Viable Product) incluye las funcionalidades **mínimas y críticas** necesarias para que el ecommerce sea funcional y operativo. Se prioriza lo esencial para lanzar al mercado.

---

### ✅ FUNCIONALIDADES INCLUIDAS EN EL MVP

#### 👤 ROL: USERS (Comprador) - MVP

**Autenticación y Perfil:**
- ✅ Login/Registro (email y Google OAuth)
- ✅ Gestión de perfil básico
- ✅ Gestión de direcciones de envío

**Catálogo y Búsqueda:**
- ✅ Ver productos disponibles
- ✅ Ver tiendas/vendedores
- ✅ Búsqueda básica de productos
- ✅ Filtros por categoría

**Carrito y Compras:**
- ✅ Agregar productos al carrito
- ✅ Modificar cantidad en carrito
- ✅ Eliminar productos del carrito
- ✅ Crear orden desde carrito
- ✅ Ver historial de órdenes
- ✅ Detalles de orden

**Pagos:**
- ✅ Métodos de pago básicos (transferencia, efectivo)
- ✅ Subir comprobante de pago
- ✅ Ver estado de pago

**Soporte:**
- ✅ Chat básico con vendedor (por orden)
- ✅ Ver notificaciones

**Excluido del MVP:**
- ❌ Wishlist (agregar después)
- ❌ Comparación de productos
- ❌ Devoluciones (agregar después)
- ❌ Facturas (agregar después)
- ❌ Suscripciones
- ❌ Gamificación avanzada

---

#### 🏪 ROL: COMMERCE (Vendedor) - MVP

**Dashboard:**
- ✅ Vista general de órdenes pendientes
- ✅ Ingresos del día/mes
- ✅ Total de productos
- ✅ Últimas órdenes

**Productos:**
- ✅ Crear producto
- ✅ Editar producto
- ✅ Eliminar producto
- ✅ Listar productos
- ✅ Activar/desactivar producto
- ✅ Gestión básica de categorías

**Órdenes:**
- ✅ Ver todas las órdenes
- ✅ Ver detalles de orden
- ✅ Actualizar estado de orden (paid → processing → shipped → delivered)
- ✅ Validar comprobante de pago
- ✅ Marcar orden como enviada

**Analytics Básicos:**
- ✅ Ingresos totales
- ✅ Órdenes completadas
- ✅ Productos más vendidos

**Excluido del MVP:**
- ❌ Gestión de inventario/stock (agregar después)
- ❌ Variantes de productos (agregar después)
- ❌ Gestión de shipping (usar básico)
- ❌ Impuestos (agregar después)
- ❌ Facturación (agregar después)
- ❌ Devoluciones (agregar después)

---

#### 🚚 ROL: DELIVERY (Repartidor) - MVP

**Órdenes:**
- ✅ Ver órdenes disponibles para entregar
- ✅ Aceptar orden
- ✅ Ver órdenes asignadas
- ✅ Actualizar estado (shipped → delivered)
- ✅ Marcar como entregado

**Tracking:**
- ✅ Ver ubicación de entrega
- ✅ Ver detalles de orden

**Excluido del MVP:**
- ❌ Tracking en tiempo real (agregar después)
- ❌ Integración con couriers externos (agregar después)
- ❌ Asignación automática (agregar después)

**Nota:** Si no hay delivery propio, este rol puede eliminarse o simplificarse.

---

#### 👨‍💼 ROL: ADMIN (Administrador) - MVP

**Usuarios:**
- ✅ Listar usuarios
- ✅ Ver detalles de usuario
- ✅ Cambiar rol de usuario
- ✅ Suspender/activar usuarios

**Comercios:**
- ✅ Listar comercios
- ✅ Ver detalles de comercio
- ✅ Aprobar/suspender comercios

**Órdenes:**
- ✅ Ver todas las órdenes
- ✅ Ver detalles de orden
- ✅ Filtrar por estado

**Reportes Básicos:**
- ✅ Estadísticas generales (usuarios, órdenes, ingresos)
- ✅ Distribución de usuarios por rol
- ✅ Health del sistema

**Excluido del MVP:**
- ❌ Gestión de impuestos (agregar después)
- ❌ Gestión de shipping (agregar después)
- ❌ Políticas de devolución (agregar después)
- ❌ Atributos globales (agregar después)

---

### 📋 ESTADOS DE ORDEN - MVP

**Estados Mínimos Necesarios:**
1. `pending_payment` - Pendiente de pago
2. `paid` - Pago validado
3. `processing` - En procesamiento/empaque (antes "preparing")
4. `shipped` - Enviado (antes "on_way")
5. `delivered` - Entregado
6. `cancelled` - Cancelado

**Flujo MVP:**
```
pending_payment → paid → processing → shipped → delivered
                ↓
            cancelled
```

---

### 🗄️ MODELOS Y TABLAS - MVP

#### Modelos Críticos (Mantener):
- ✅ `User` - Usuarios
- ✅ `Profile` - Perfiles
- ✅ `Commerce` - Tiendas/Vendedores
- ✅ `Product` - Productos
- ✅ `Category` - Categorías
- ✅ `Cart` - Carritos
- ✅ `CartItem` - Items del carrito
- ✅ `Order` - Órdenes
- ✅ `OrderItem` - Items de orden
- ✅ `OrderDelivery` - Información de envío
- ✅ `PaymentMethod` - Métodos de pago
- ✅ `Address` - Direcciones
- ✅ `Review` - Reseñas
- ✅ `Notification` - Notificaciones
- ✅ `ChatMessage` - Mensajes de chat

#### Modelos a Adaptar:
- ⚠️ `DeliveryAgent` → Adaptar a `ShippingProvider` o eliminar si no hay delivery propio
- ⚠️ `DeliveryCompany` → Evaluar si mantener

#### Modelos a Agregar Después (No MVP):
- ❌ `ProductVariant` - Variantes de productos
- ❌ `Inventory` - Gestión de inventario
- ❌ `Wishlist` - Lista de deseos
- ❌ `Return` - Devoluciones
- ❌ `Invoice` - Facturas
- ❌ `TaxRate` - Tasas de impuestos
- ❌ `ShippingMethod` - Métodos de envío

---

### 🔧 SERVICIOS - MVP

#### Servicios Críticos (Mantener):
- ✅ `OrderService` - Gestión de órdenes
- ✅ `CartService` - Gestión de carrito
- ✅ `ProductService` - Gestión de productos
- ✅ `RestaurantService` → Renombrar a `StoreService` o `VendorService`

#### Servicios a Adaptar:
- ⚠️ `DeliveryAssignmentService` → Adaptar o eliminar si no hay delivery propio
- ⚠️ `TrackingService` → Adaptar para tracking de paquetes

#### Servicios a Agregar Después (No MVP):
- ❌ `InventoryService` - Gestión de inventario
- ❌ `ShippingService` - Gestión de envíos
- ❌ `TaxService` - Cálculo de impuestos
- ❌ `InvoiceService` - Generación de facturas

---

### 📡 ENDPOINTS API - MVP

#### Endpoints Críticos por Rol:

**USERS (Buyer):**
```
POST   /api/auth/login
POST   /api/auth/register
POST   /api/auth/google
GET    /api/auth/user
POST   /api/auth/logout

GET    /api/buyer/products
GET    /api/buyer/products/{id}
GET    /api/buyer/stores (antes restaurants)
GET    /api/buyer/stores/{id}

GET    /api/buyer/cart
POST   /api/buyer/cart/add
PUT    /api/buyer/cart/update-quantity
DELETE /api/buyer/cart/{productId}

POST   /api/buyer/orders
GET    /api/buyer/orders
GET    /api/buyer/orders/{id}
POST   /api/buyer/orders/{id}/cancel
POST   /api/buyer/orders/{id}/payment-proof

GET    /api/buyer/addresses
POST   /api/buyer/addresses
PUT    /api/buyer/addresses/{id}
DELETE /api/buyer/addresses/{id}

GET    /api/notifications
POST   /api/notifications/{id}/read
```

**COMMERCE:**
```
GET    /api/commerce/dashboard
GET    /api/commerce/products
POST   /api/commerce/products
PUT    /api/commerce/products/{id}
DELETE /api/commerce/products/{id}

GET    /api/commerce/orders
GET    /api/commerce/orders/{id}
PUT    /api/commerce/orders/{id}/status
POST   /api/commerce/orders/{id}/validate-payment

GET    /api/commerce/analytics
```

**DELIVERY:**
```
GET    /api/delivery/orders/available
GET    /api/delivery/orders
POST   /api/delivery/orders/{id}/accept
PUT    /api/delivery/orders/{id}/status
```

**ADMIN:**
```
GET    /api/admin/users
GET    /api/admin/users/{id}
PUT    /api/admin/users/{id}/role
GET    /api/admin/commerces
GET    /api/admin/orders
GET    /api/admin/statistics
```

---

### 🚀 PLAN DE IMPLEMENTACIÓN MVP

#### Fase 1: Adaptación y Limpieza (1 semana)
1. ✅ Renombrar `RestaurantController` → `StoreController`
2. ✅ Cambiar estado `preparing` → `processing`
3. ✅ Cambiar estado `on_way` → `shipped`
4. ✅ Adaptar terminología de "restaurante" a "tienda"
5. ✅ Limpiar código no usado
6. ✅ Actualizar documentación

#### Fase 2: Funcionalidades Críticas USERS (2 semanas)
1. ✅ Asegurar que carrito funciona correctamente
2. ✅ Verificar flujo completo de orden
3. ✅ Implementar gestión de direcciones
4. ✅ Mejorar búsqueda de productos
5. ✅ Chat básico funcional

#### Fase 3: Funcionalidades Críticas COMMERCE (2 semanas)
1. ✅ Dashboard funcional
2. ✅ CRUD completo de productos
3. ✅ Gestión de órdenes
4. ✅ Validación de pagos
5. ✅ Analytics básicos

#### Fase 4: Funcionalidades DELIVERY (1 semana)
1. ✅ Aceptar órdenes
2. ✅ Actualizar estado
3. ✅ Ver órdenes asignadas

#### Fase 5: Funcionalidades ADMIN (1 semana)
1. ✅ Gestión de usuarios
2. ✅ Gestión de comercios
3. ✅ Reportes básicos

#### Fase 6: Testing y Ajustes (1 semana)
1. ✅ Tests de integración
2. ✅ Pruebas de flujos completos
3. ✅ Corrección de bugs
4. ✅ Optimización de performance

**Total estimado: 8 semanas (~2 meses)**

---

### ⚠️ LIMITACIONES DEL MVP

**No incluye (agregar después):**
- ❌ Gestión de inventario/stock
- ❌ Variantes de productos
- ❌ Wishlist
- ❌ Devoluciones/reembolsos
- ❌ Facturación
- ❌ Impuestos
- ❌ Shipping avanzado
- ❌ Tracking en tiempo real
- ❌ Cupones avanzados
- ❌ Gamificación completa
- ❌ Posts sociales (evaluar si mantener)

---

### ✅ CRITERIOS DE ÉXITO DEL MVP

**Funcionalidad:**
- ✅ Usuario puede registrarse e iniciar sesión
- ✅ Usuario puede ver productos y agregar al carrito
- ✅ Usuario puede crear una orden
- ✅ Vendedor puede ver y gestionar órdenes
- ✅ Vendedor puede validar pagos
- ✅ Vendedor puede actualizar estado de orden
- ✅ Delivery puede aceptar y entregar órdenes (si aplica)
- ✅ Admin puede gestionar usuarios y comercios

**Performance:**
- ✅ Tiempo de respuesta API < 500ms
- ✅ Carga de productos < 2 segundos
- ✅ Creación de orden < 3 segundos

**Calidad:**
- ✅ Tests pasando > 90%
- ✅ Sin errores críticos
- ✅ Documentación actualizada

---

### 📝 CHECKLIST MVP

#### Backend
- [ ] Autenticación completa (login, registro, Google)
- [ ] CRUD de productos
- [ ] Carrito funcional
- [ ] Creación de órdenes
- [ ] Gestión de órdenes por vendedor
- [ ] Validación de pagos
- [ ] Estados de orden correctos
- [ ] Chat básico
- [ ] Notificaciones
- [ ] Dashboard de vendedor
- [ ] Analytics básicos
- [ ] Gestión de usuarios (admin)
- [ ] Tests > 90% cobertura

#### Frontend
- [ ] Pantallas de autenticación
- [ ] Catálogo de productos
- [ ] Carrito de compras
- [ ] Checkout
- [ ] Historial de órdenes
- [ ] Dashboard de vendedor
- [ ] Gestión de productos
- [ ] Gestión de órdenes
- [ ] Chat
- [ ] Notificaciones

---

### 🎯 PRÓXIMOS PASOS DESPUÉS DEL MVP

**Fase 2 (Post-MVP):**
1. Gestión de inventario
2. Variantes de productos
3. Wishlist
4. Devoluciones básicas

**Fase 3:**
1. Facturación
2. Impuestos
3. Shipping avanzado
4. Tracking en tiempo real

**Fase 4:**
1. Cupones avanzados
2. Gamificación
3. Recomendaciones
4. Analytics avanzados

## 🏗️ Arquitectura

```
app/
├── Http/
│   ├── Controllers/     # 52 controladores organizados por módulos (verificado)
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
├── Models/              # 31 modelos Eloquent (verificado)
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
APP_URL=http://192.168.27.12:8000

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

**Tablas de Carrito:**
- `carts` - Carritos de compra de usuarios
- `cart_items` - Items del carrito

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
**Estados Válidos (MVP):**
- `pending_payment` - Pendiente de pago
- `paid` - Pago validado
- `processing` - En procesamiento/empaque
- `shipped` - Enviado/en camino
- `delivered` - Entregada
- `cancelled` - Cancelada

**Estados Deprecated (Ya no usados):**
- ~~`confirmed`~~ - ❌ DEPRECATED: Reemplazado por `paid` directamente
- ~~`preparing`~~ - ❌ DEPRECATED: Reemplazado por `processing`
- ~~`on_way`~~ - ❌ DEPRECATED: Reemplazado por `shipped`
- ~~`ready`~~ - ❌ DEPRECATED: No se usa en el flujo MVP actual
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

### Roles del Sistema (MVP)

**Roles implementados y funcionales:**
- **users** (Level 0): Cliente/Comprador ✅
  - Ver productos y restaurantes
  - Agregar al carrito
  - Realizar pedidos
  - Ver historial de pedidos
  - Calificar productos
  - Chat con restaurante
  - Notificaciones
  - Geolocalización
  - Favoritos
  - Rutas: `/api/buyer/*`

- **commerce** (Level 1): Comercio/Restaurante ✅
  - Gestionar productos
  - Ver pedidos
  - Actualizar estado de pedidos
  - Validar pagos
  - Chat con clientes
  - Dashboard y reportes
  - Rutas: `/api/commerce/*`

- **delivery** (Level 2): Repartidor/Delivery ✅
  - Ver pedidos asignados
  - Aceptar/rechazar pedidos
  - Actualizar ubicación
  - Marcar como entregado
  - Historial de entregas
  - Rutas: `/api/delivery/*`

- **admin** (Level 3): Administrador ✅
  - Gestión completa del sistema
  - Usuarios y roles
  - Reportes globales
  - Configuración del sistema
  - Rutas: `/api/admin/*`

**IMPORTANTE:** Solo existen estos 4 roles. Los roles `transport` y `affiliate` fueron eliminados del código.

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

### Tests Implementados (VERIFICADO)

**Resultado de ejecución:** `php artisan test --testsuite=Feature`
- ✅ **201 tests pasaron** (736 assertions)
- ✅ **Todos los tests pasan** (227 tests, 845 assertions)
- ⏱️ **Duración:** 10.50 segundos

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

## 🗺️ ROADMAP MVP - PLAN DE ACCIÓN PRIORIZADO

**Estado actual:** ~72% completado  
**Objetivo:** Llegar al 100% del MVP  
**Tiempo estimado:** 6-9 semanas (~1.5-2 meses)  
**Nota:** Se excluyeron `transport` y `affiliate` del MVP

### 🔴 FASE 1: CRÍTICO - Funcionalidad Core (4-6 semanas)

1. ✅ **Corregir Tests Fallando** (COMPLETADO) - Todos los tests pasan (216+ tests)
2. ✅ **Migrar Carrito de Session a BD** (COMPLETADO) - Migrado a tablas `carts` y `cart_items`
3. ✅ **TODOs Commerce Service** (COMPLETADO) - Frontend: 12 métodos implementados
4. ✅ **TODOs Payment Service** (COMPLETADO) - Frontend: 11 métodos implementados
5. ✅ **TODOs Delivery Service** (COMPLETADO) - Backend: 3 endpoints nuevos, Frontend: 11 métodos implementados, Tests: 11 tests creados
6. ✅ **TODOs Chat Service** (COMPLETADO) - Backend: ChatController completo con Firebase, Frontend: 9 métodos implementados, Tests: 12 tests creados

### 🟡 FASE 2: ALTA PRIORIDAD - Seguridad y Calidad (2-3 semanas)

7. ✅ **Restringir CORS** (COMPLETADO) - Configurado desde `.env` con `CORS_ALLOWED_ORIGINS`
8. ✅ **Rate Limiting** (COMPLETADO) - Configurado desde `.env` con `API_RATE_LIMIT`, `AUTH_RATE_LIMIT`, `CREATE_RATE_LIMIT`
9. ✅ **Paginación en Endpoints** (COMPLETADO) - Agregada a UserController, AdminOrderController, OrderService, RestaurantService
10. ✅ **TODOs Admin Service** (COMPLETADO) - Backend: 8 endpoints nuevos, Frontend: 12 métodos implementados
11. ✅ **TODOs Notification Service** (COMPLETADO) - Backend: 3 endpoints nuevos, Frontend: 3 métodos implementados, Migración: notification_preferences agregado
12. ✅ **Índices BD Faltantes** (COMPLETADO) - Agregados índices en: orders (status, created_at, profile_id, commerce_id, compuestos), profiles (status), notifications (profile_id, created_at), chat_messages (order_id, created_at), users (created_at)

### 🟢 FASE 3: MEDIA PRIORIDAD - Optimizaciones (1-2 semanas)

13. ✅ **TODOs Analytics Service** (COMPLETADO) - Backend: AnalyticsController con 13 endpoints, Frontend: 11 métodos implementados
14. ✅ **TODO Location Service** (COMPLETADO) - Backend: getDeliveryRoutes implementado, Frontend: getDeliveryRoutes implementado
15. ✅ **Limpiar Código Comentado** (COMPLETADO) - Frontend: ~330 líneas eliminadas de main.dart
16. ✅ **Eager Loading Faltante** (COMPLETADO) - Backend: Eager loading agregado en AnalyticsController, LocationController, Commerce/OrderController, Buyer/OrderController, NotificationController
17. ✅ **Analytics Commerce** (COMPLETADO) - Backend: CommerceAnalyticsController con 6 endpoints, Frontend: CommerceReportsPage conectado con API real, DashboardController mejorado

### 🔵 FASE 4: BAJA PRIORIDAD - Mejoras Adicionales (2-3 semanas)

17. **Documentación API (Swagger)** (1 semana)
18. **Caching** (1 semana)
19. **Internacionalización i18n** (1-2 semanas)
20. **Mejorar Sistema de Roles** (3-5 días)

**Total TODOs para MVP:** 68 líneas (excluyendo transport y affiliate)

---

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

## 🧠 LÓGICA DE NEGOCIO

### 📊 Entidades Principales y Relaciones

#### Modelo de Usuarios y Perfiles
```
User (users table)
├── Profile (profiles table) - 1:1
│   ├── Addresses (addresses) - 1:N
│   ├── Phones (phones) - 1:N
│   ├── Documents (documents) - 1:N
│   └── UserLocations (user_locations) - 1:N
├── Commerce (commerces) - 1:1 (si role = commerce)
├── DeliveryAgent (delivery_agents) - 1:1 (si role = delivery)
└── Cart (carts) - 1:1
    └── CartItems (cart_items) - 1:N
```

#### Modelo de Órdenes
```
Order (orders table)
├── Profile (buyer) - N:1
├── Commerce (seller) - N:1
├── OrderItems (order_items) - 1:N
│   └── Product - N:1
├── OrderDelivery (order_delivery) - 1:1
│   └── DeliveryAgent - N:1
└── ChatMessages (chat_messages) - 1:N
```

### 🔄 Flujos de Negocio Principales

#### 1. Flujo de Creación de Orden (Buyer)

**1.1 Agregar Productos al Carrito**
- `CartService::addToCart()` - Agrega productos al carrito del usuario
- Validación: Producto existe y está disponible
- Si el producto ya existe, incrementa cantidad

**1.2 Crear Orden desde Carrito**
- `Buyer/OrderController::store()`
- Validaciones:
  - Usuario autenticado con role `users`
  - Profile completo (`status = 'completeData'`)
  - Productos válidos y disponibles
  - Commerce existe
- Estado inicial: `pending_payment`
- Crea `Order` y `OrderItems` (attach products)
- Evento: `OrderCreated` (comentado)

**1.3 Subir Comprobante de Pago**
- `Buyer/OrderController::uploadPaymentProof()`
- Almacena imagen del comprobante
- Estado permanece: `pending_payment` (hasta validación)

#### 2. Flujo de Validación de Pago (Commerce)

**2.1 Validar Comprobante**
- `Commerce/OrderController::validatePayment()`
- Validaciones:
  - Usuario es dueño del commerce
  - Orden pertenece al commerce
- Si válido:
  - Estado: `pending_payment` → `paid`
  - `payment_validated_at` = now()
- Si rechazado:
  - Estado: `pending_payment` → `cancelled`
  - `cancellation_reason` = motivo
- Evento: `PaymentValidated` (comentado)

#### 3. Flujo de Preparación (Commerce)

**3.1 Actualizar Estado de Orden**
- `Commerce/OrderController::updateStatus()`
- Estados permitidos: `pending_payment`, `paid`, `processing`, `shipped`, `delivered`, `cancelled`
- Transiciones:
  - `paid` → `processing` (comercio inicia preparación)
  - `processing` → `shipped` (listo para entrega)
- Evento: `OrderStatusChanged` (comentado)

#### 4. Flujo de Delivery (Delivery Agent)

**4.1 Ver Órdenes Disponibles**
- `Delivery/OrderController::availableOrders()`
- Filtro: `status = 'paid'` y sin `orderDelivery`

**4.2 Aceptar Orden**
- `Delivery/OrderController::acceptOrder()`
- Validaciones:
  - Orden no asignada
  - Usuario es delivery agent
  - Estado orden: `shipped` (listo para delivery)
- Crea `OrderDelivery` con `status = 'assigned'`
- Estado orden: Permanece `shipped` (no cambia al aceptar)

**4.3 Actualizar Estado de Entrega**
- `Delivery/OrderController::updateOrderStatus()`
- Estados: `shipped`, `delivered`
- Transición: `shipped` → `delivered`
- Libera al delivery agent (`working = true`)

#### 5. Flujo de Cancelación

**5.1 Cancelación por Comprador**
- `OrderService::cancelOrder()`
- Solo si: `status = 'pending_payment'`
- Estado: `pending_payment` → `cancelled`

**5.2 Cancelación por Comercio**
- `Commerce/OrderController::updateStatus()`
- Puede cancelar en cualquier estado (validación pendiente)

### 📋 Estados de Orden

**Estados Implementados (MVP):**
- `pending_payment` - Pendiente de pago (inicial)
- `paid` - Pago validado
- `processing` - En procesamiento/empaque (antes "preparing")
- `shipped` - Enviado/en camino (antes "on_way")
- `delivered` - Entregada
- `cancelled` - Cancelada

**Diagrama de Estados:**
```
pending_payment
    ├──→ paid (validación de pago)
    │       ├──→ processing (comercio inicia preparación)
    │       │       └──→ shipped (listo para delivery)
    │       │               └──→ delivered (entregado)
    │       └──→ cancelled (rechazo de pago o cancelación por comercio)
    └──→ cancelled (cancelación por comprador)
```

### 🔧 Servicios de Negocio

#### OrderService
- `getUserOrders()` - Lista órdenes del comprador con paginación
- `createOrder()` - Crea orden con productos
- `getOrderDetails()` - Detalles de orden específica
- `cancelOrder()` - Cancela orden pendiente

#### CartService
- `getOrCreateCart()` - Obtiene o crea carrito del usuario
- `addToCart()` - Agrega producto al carrito
- `updateQuantity()` - Actualiza cantidad
- `removeFromCart()` - Elimina producto
- `addNotes()` - Agrega notas al carrito
- `clearCart()` - Limpia carrito
- `formatCartResponse()` - Formatea respuesta compatible con frontend

**Nota:** Migrado de Session a Base de Datos (tablas `carts` y `cart_items`)

#### DeliveryAssignmentService
- `assignDeliveryToOrder()` - Asigna delivery automáticamente (más cercano)
- `releaseDeliveryAgent()` - Libera agente al completar entrega
- `getNearbyAgents()` - Obtiene agentes cercanos
- `reassignOrdersFromAgent()` - Reasigna órdenes si agente no disponible

### ⚠️ Reglas de Negocio

#### Validaciones Críticas

**Creación de Orden:**
- Usuario debe tener `role = 'users'`
- Profile debe existir y tener `status = 'completeData'`
- Productos deben existir y estar disponibles
- Commerce debe existir
- Total debe ser >= 0

**Validación de Pago:**
- Solo el dueño del commerce puede validar
- Orden debe pertenecer al commerce
- Solo órdenes en `pending_payment` pueden ser validadas

**Cancelación:**
- Comprador solo puede cancelar en `pending_payment`
- Comercio puede cancelar en cualquier estado (revisar lógica)

**Asignación de Delivery:**
- Solo órdenes en `paid` y sin `orderDelivery` están disponibles
- Delivery agent debe estar `working = true` y `status = 'active'`

#### Cálculos de Negocio

**Total de Orden:**
- Suma de `(product.quantity * product.unit_price)` de todos los items
- Calculado en frontend y validado en backend

**Distancia y Tiempo de Entrega:**
- Usa OSRM (Open Source Routing Machine) para cálculo real
- Implementado en `DeliveryController::getRoutes()`

### 🔗 Integraciones Externas

#### OSRM (Open Source Routing Machine)
- Usado para calcular distancia y tiempo de rutas
- Endpoint: `http://router.project-osrm.org/route/v1/driving/`
- Implementado en `DeliveryController::getRoutes()`
- Timeout: 5 segundos con fallback a valores por defecto

#### Firebase
- `FirebaseService` para notificaciones push
- Integrado con FCM (Firebase Cloud Messaging)

### 📊 Métricas y Analytics

El sistema calcula:
- Revenue total (solo órdenes `delivered`)
- Tasa de éxito de delivery
- Tiempo promedio de preparación
- Tiempo promedio de entrega
- Órdenes activas (`paid`, `processing`, `shipped`)

### ⚠️ Problemas e Inconsistencias Detectados

#### 1. Estados de Orden Inconsistentes
- README menciona `confirmed` y `ready` que no existen en código
- `DeliveryAssignmentService` usa `assigned` e `in_transit` no usados
- Validación en `Commerce/OrderController` permite `paid` pero no `confirmed`

**Recomendación:** Unificar estados y actualizar documentación.

#### 2. Lógica de Cancelación
- Comercio puede cancelar en cualquier estado sin validación
- No hay límite de tiempo para cancelar
- No se maneja reembolso

**Recomendación:** Agregar reglas de cancelación por estado y tiempo.

#### 3. Asignación Automática de Delivery
- `DeliveryAssignmentService::assignDeliveryToOrder()` no se usa en controladores
- Los delivery agents aceptan órdenes manualmente
- No hay sistema de asignación automática activo

**Recomendación:** Implementar asignación automática o eliminar código no usado.

#### 4. Eventos Comentados
- `OrderCreated` comentado en `Buyer/OrderController`
- `PaymentValidated` comentado en `Commerce/OrderController`
- `OrderStatusChanged` comentado en `Commerce/OrderController`

**Recomendación:** Activar eventos o eliminar código comentado.

### 🚀 Recomendaciones de Mejora

#### Críticas
1. **Unificar Estados de Orden**
   - Definir estados oficiales
   - Actualizar validaciones en todos los controladores
   - Actualizar documentación

2. **Implementar Máquina de Estados**
   - Validar transiciones de estado
   - Prevenir transiciones inválidas
   - Agregar historial de cambios de estado

3. **Activar Eventos de Broadcasting**
   - Descomentar eventos
   - Configurar WebSocket correctamente
   - Notificar cambios en tiempo real

#### Altas
4. **Mejorar Lógica de Cancelación**
   - Reglas por estado
   - Límites de tiempo
   - Manejo de reembolsos

5. **Implementar Asignación Automática de Delivery**
   - Usar `DeliveryAssignmentService` en flujo real
   - O eliminar código no usado

6. **Agregar Validaciones de Negocio**
   - Stock de productos
   - Horarios de comercio
   - Zonas de delivery

---

## 🛒 ADAPTACIÓN PARA ECOMMERCE GENERAL - ANÁLISIS POR ROL

Este análisis cubre **TODOS los roles** del sistema (users, commerce, delivery, admin) para identificar qué funcionalidades son específicas de delivery de comida y cuáles son genéricas de ecommerce.

---

### 👤 ROL: USERS (Comprador/Cliente)

#### ❌ QUITAR/ADAPTAR (Específico de Delivery de Comida)

1. **RestaurantController** → **StoreController** o **VendorController**
   - Cambiar nombre: "Restaurantes" → "Tiendas" o "Vendedores"
   - Mantener funcionalidad pero adaptar terminología

2. **ScheduledOrderController** (Órdenes Programadas)
   - **Evaluar:** ¿Mantener para ecommerce? (puede ser útil para suscripciones)
   - **Opcional:** Mantener si hay productos recurrentes

3. **OrderTrackingController** con tracking en tiempo real
   - **Adaptar:** De tracking de delivery agent → tracking de courier/shipping
   - Mantener funcionalidad pero cambiar fuente de datos

4. **ChatController** por orden (típico de comida)
   - **Evaluar:** ¿Mantener para ecommerce?
   - **Alternativa:** Chat general de soporte en lugar de por orden

5. **GamificationController** (puntos, badges)
   - **Evaluar:** Si es específico de comida o genérico
   - **Mantener:** Si es genérico (puntos por compras)

6. **LoyaltyController** basado en órdenes de comida
   - **Adaptar:** Mantener pero cambiar métricas si es necesario

#### ✅ AGREGAR (Ecommerce General)

1. **WishlistController** ⚠️ CRÍTICO
   - Agregar productos a lista de deseos
   - Notificaciones de precio/stock

2. **ProductComparisonController** ⚠️ MEDIO
   - Comparar productos lado a lado
   - Atributos comparables

3. **ReturnRequestController** ⚠️ ALTO
   - Solicitar devolución de productos
   - Estado de devolución
   - RMA (Return Merchandise Authorization)

4. **InvoiceController** ⚠️ ALTO
   - Descargar facturas
   - Historial de facturas
   - Facturas PDF

5. **SubscriptionController** (si hay productos recurrentes)
   - Suscripciones a productos
   - Renovación automática

6. **ProductReviewController** mejorado
   - Fotos en reviews
   - Verificación de compra
   - Helpful votes

---

### 🏪 ROL: COMMERCE (Vendedor/Tienda)

#### ❌ QUITAR/ADAPTAR (Específico de Delivery de Comida)

1. **DeliveryRequestController** (Solicitar delivery)
   - **Quitar:** Si no hay delivery propio
   - **Adaptar:** A "ShippingRequest" si se gestiona envío propio

2. **OrderController::updateStatus()** con estado `preparing`
   - **Cambiar:** `preparing` → `processing` o `packaging`
   - Mantener lógica pero adaptar nombres

3. **AnalyticsController** con métricas de comida
   - **Adaptar:** Métricas de tiempo de preparación → tiempo de procesamiento
   - Mantener estructura pero cambiar métricas

4. **DashboardController** con métricas de restaurante
   - **Adaptar:** De "restaurante" a "tienda" genérica
   - Cambiar terminología pero mantener funcionalidad

#### ✅ AGREGAR (Ecommerce General)

1. **InventoryController** ⚠️ CRÍTICO
   - Gestión de stock
   - Alertas de stock bajo
   - Ajustes de inventario
   - Historial de movimientos

2. **ProductVariantController** ⚠️ CRÍTICO
   - Crear/editar variantes de productos
   - Stock por variante
   - Precios por variante

3. **ShippingController** ⚠️ ALTO
   - Configurar métodos de envío
   - Zonas de envío
   - Costos de envío
   - Tiempos de entrega

4. **TaxController** ⚠️ MEDIO
   - Configurar tasas de impuestos
   - Impuestos por región
   - Exenciones fiscales

5. **InvoiceController** ⚠️ ALTO
   - Generar facturas
   - Configurar datos fiscales
   - Plantillas de factura

6. **ReturnManagementController** ⚠️ ALTO
   - Gestionar devoluciones
   - Aprobar/rechazar devoluciones
   - Procesar reembolsos

7. **ProductAttributeController** ⚠️ MEDIO
   - Gestionar atributos (color, talla, etc.)
   - Atributos personalizados

8. **CouponManagementController** mejorado
   - Cupones por categoría
   - Cupones por producto
   - Cupones de envío gratis

9. **BulkOperationsController** ⚠️ MEDIO
   - Operaciones masivas de productos
   - Importar/exportar productos
   - Actualizaciones masivas

---

### 🚚 ROL: DELIVERY (Repartidor/Courier)

#### ❌ QUITAR/ADAPTAR (Específico de Delivery de Comida)

1. **DeliveryController** con tracking en tiempo real
   - **Adaptar:** De "delivery agent" a "courier" o "shipping provider"
   - Cambiar modelo pero mantener funcionalidad

2. **OrderController** con aceptación manual de órdenes
   - **Evaluar:** ¿Mantener para delivery propio?
   - **Alternativa:** Integración con couriers externos (FedEx, DHL, etc.)

3. Tracking de ubicación en tiempo real
   - **Adaptar:** De tracking de agente → tracking de paquete
   - Usar tracking numbers de couriers

#### ✅ AGREGAR/ADAPTAR (Ecommerce General)

1. **ShippingProviderController** (si hay delivery propio)
   - Gestionar couriers propios
   - Asignar envíos
   - Tracking de envíos

2. **CourierIntegrationController** (si se integra con couriers externos)
   - Integración con FedEx, DHL, UPS, etc.
   - Sincronización de tracking
   - Etiquetas de envío

**Nota:** Si no hay delivery propio, este rol puede **eliminarse** o convertirse en integración con servicios externos.

---

### 👨‍💼 ROL: ADMIN (Administrador)

#### ❌ QUITAR/ADAPTAR (Específico de Delivery de Comida)

1. **ReportController** con métricas de comida
   - **Adaptar:** Métricas de restaurantes → métricas de tiendas
   - Cambiar terminología

2. Gestión de delivery agents
   - **Adaptar:** A gestión de shipping providers
   - O eliminar si se usan couriers externos

#### ✅ AGREGAR (Ecommerce General)

1. **TaxManagementController** ⚠️ ALTO
   - Gestionar tasas de impuestos globales
   - Configuración fiscal
   - Reglas de impuestos

2. **ShippingManagementController** ⚠️ ALTO
   - Gestionar métodos de envío globales
   - Zonas de envío
   - Integraciones con couriers

3. **CategoryManagementController** mejorado
   - Jerarquía de categorías
   - Atributos por categoría
   - Filtros por categoría

4. **AttributeManagementController** ⚠️ MEDIO
   - Gestionar atributos globales
   - Atributos reutilizables

5. **InvoiceTemplateController** ⚠️ MEDIO
   - Plantillas de factura
   - Personalización de facturas

6. **ReturnPolicyController** ⚠️ MEDIO
   - Políticas de devolución
   - Tiempos de devolución
   - Condiciones de devolución

7. **CommissionController** (si hay marketplace)
   - Comisiones por venta
   - Pagos a vendedores

---

### 📊 FUNCIONALIDADES TRANSVERSALES

#### ❌ QUITAR/ADAPTAR

1. **Posts y PostLikes** (Red Social)
   - **Evaluar:** ¿Necesario para ecommerce?
   - **Opcional:** Mantener solo si hay comunidad

2. **Chat por Orden**
   - **Adaptar:** A chat de soporte general
   - O eliminar si no es necesario

3. **Tracking en tiempo real de delivery agents**
   - **Adaptar:** A tracking de paquetes con couriers

#### ✅ AGREGAR

1. **Sistema de Notificaciones mejorado**
   - Notificaciones de stock bajo
   - Notificaciones de precio
   - Notificaciones de envío

2. **Sistema de Búsqueda avanzada**
   - Filtros por atributos
   - Búsqueda por SKU
   - Búsqueda por categoría

3. **Sistema de Recomendaciones**
   - Productos relacionados
   - "Clientes que compraron X también compraron Y"
   - Recomendaciones basadas en historial

---

### 📋 RESUMEN POR PRIORIDAD

#### 🔴 CRÍTICO - Implementar Primero

**USERS:**
- WishlistController
- ReturnRequestController
- InvoiceController

**COMMERCE:**
- InventoryController
- ProductVariantController
- ShippingController
- ReturnManagementController

**ADMIN:**
- TaxManagementController
- ShippingManagementController

#### 🟡 ALTO - Implementar Después

**USERS:**
- ProductReviewController mejorado

**COMMERCE:**
- InvoiceController
- CouponManagementController mejorado
- ProductAttributeController

**ADMIN:**
- ReturnPolicyController
- CategoryManagementController mejorado

#### 🟢 MEDIO - Mejoras

**USERS:**
- ProductComparisonController
- SubscriptionController (si aplica)

**COMMERCE:**
- BulkOperationsController
- TaxController

**ADMIN:**
- AttributeManagementController
- InvoiceTemplateController

---

### 🔄 PLAN DE MIGRACIÓN POR ROL

#### Fase 1: Adaptar Terminología (1 semana)
- Cambiar "Restaurant" → "Store"/"Vendor"
- Cambiar "preparing" → "processing"
- Adaptar métricas de comida a ecommerce

#### Fase 2: Implementar Críticos USERS (2 semanas)
- Wishlist
- Devoluciones
- Facturas

#### Fase 3: Implementar Críticos COMMERCE (3 semanas)
- Inventario
- Variantes
- Shipping
- Devoluciones

#### Fase 4: Implementar ADMIN (1 semana)
- Gestión de impuestos
- Gestión de shipping

#### Fase 5: Mejoras y Optimizaciones (2 semanas)
- Búsqueda avanzada
- Recomendaciones
- Atributos de productos

---

## 🛒 ADAPTACIÓN PARA ECOMMERCE GENERAL - DETALLES TÉCNICOS

### ❌ DATOS/FLUJOS A QUITAR (Específicos de Delivery de Comida)

#### 1. Delivery Agents Específicos
**Actual:**
- `DeliveryAgent` con ubicación en tiempo real
- `OrderDelivery` con asignación de agentes
- `DeliveryAssignmentService` con cálculo de distancia a agentes

**Recomendación:**
- **Quitar:** Lógica de asignación automática de delivery agents
- **Mantener:** Estructura de `OrderDelivery` pero adaptarla a shipping genérico
- **Cambiar:** `DeliveryAgent` → `ShippingProvider` o `CourierService`

#### 2. Flujos de Preparación de Comida
**Actual:**
- Estados `preparing` (específico de restaurantes)
- Validación de pago con comprobante (típico de comida)
- Horarios de comercio (`schedule` en Commerce)

**Recomendación:**
- **Quitar:** Estado `preparing` (reemplazar por `processing` genérico)
- **Adaptar:** Validación de pago a pagos online automáticos
- **Mantener:** Horarios pero como "horarios de atención" genéricos

#### 3. OSRM para Delivery de Comida
**Actual:**
- Cálculo de distancia en tiempo real para delivery agents
- Rutas optimizadas para repartidores

**Recomendación:**
- **Mantener:** OSRM pero para cálculo de costos de envío
- **Adaptar:** De cálculo de ruta de delivery → cálculo de shipping cost

#### 4. Posts Sociales (Red Social)
**Actual:**
- `Post` y `PostLike` (funcionalidad de red social)
- Favoritos de posts

**Recomendación:**
- **Evaluar:** Si es necesario para ecommerce general
- **Opcional:** Mantener solo si hay comunidad de productos

---

### ✅ DATOS/FLUJOS A AGREGAR (Ecommerce General)

#### 1. Gestión de Inventario/Stock ⚠️ CRÍTICO
**Actual:**
- Product solo tiene `available` (boolean)
- No hay control de cantidad

**Agregar:**
```php
// En Product model
'stock_quantity' => 'integer',        // Cantidad disponible
'low_stock_threshold' => 'integer',   // Umbral de stock bajo
'manage_stock' => 'boolean',          // Si se gestiona stock
'stock_status' => 'enum',             // in_stock, out_of_stock, on_backorder
'sku' => 'string',                    // SKU único del producto
```

**Lógica:**
- Validar stock al agregar al carrito
- Descontar stock al crear orden
- Restaurar stock al cancelar orden
- Alertas de stock bajo

#### 2. Variantes de Productos ⚠️ CRÍTICO
**Actual:**
- Product es simple, sin variantes

**Agregar:**
```php
// Nueva tabla: product_variants
- product_id
- name (ej: "Talla M", "Color Rojo")
- sku
- price (precio adicional o base)
- stock_quantity
- attributes (JSON: {size: "M", color: "red"})
```

**Lógica:**
- Product puede tener múltiples variantes
- Carrito con variantes específicas
- Stock por variante

#### 3. Wishlist de Productos ⚠️ ALTO
**Actual:**
- Solo favoritos de posts (red social)

**Agregar:**
```php
// Nueva tabla: wishlists
- user_id
- product_id
- created_at
```

**Lógica:**
- Agregar/quitar productos de wishlist
- Notificar cuando producto vuelve a stock
- Notificar cuando producto baja de precio

#### 4. Gestión de Devoluciones/Reembolsos ⚠️ ALTO
**Actual:**
- Existe ruta `/refund` pero no revisada completamente

**Agregar:**
```php
// Nueva tabla: returns
- order_id
- user_id
- reason
- status (pending, approved, rejected, refunded)
- refund_amount
- items (JSON: productos devueltos)
```

**Lógica:**
- Solicitud de devolución por usuario
- Aprobación/rechazo por comercio
- Reembolso automático o manual
- Restaurar stock al aprobar devolución

#### 5. Facturación ⚠️ ALTO
**Actual:**
- No hay sistema de facturación

**Agregar:**
```php
// Nueva tabla: invoices
- order_id
- invoice_number (único)
- subtotal
- tax_amount
- shipping_cost
- discount_amount
- total
- billing_address (JSON)
- status (draft, issued, paid, cancelled)
- pdf_path
```

**Lógica:**
- Generar factura automática al pagar
- PDF descargable
- Numeración secuencial
- Datos fiscales del comercio

#### 6. Impuestos ⚠️ MEDIO
**Actual:**
- No hay cálculo de impuestos

**Agregar:**
```php
// Nueva tabla: tax_rates
- name
- rate (porcentaje)
- type (percentage, fixed)
- applicable_to (all, products, shipping)
- country_id (opcional)
- state_id (opcional)
```

**Lógica:**
- Calcular impuestos según ubicación
- Aplicar diferentes tasas por región
- Mostrar impuestos desglosados

#### 7. Gestión de Envíos (Shipping) ⚠️ ALTO
**Actual:**
- `OrderDelivery` muy específico de delivery agents

**Agregar:**
```php
// Adaptar OrderDelivery o crear Shipping
- order_id
- shipping_method (standard, express, overnight)
- shipping_provider (courier name)
- tracking_number
- cost
- estimated_delivery_date
- actual_delivery_date
- status (pending, shipped, in_transit, delivered)
```

**Lógica:**
- Múltiples métodos de envío
- Cálculo de costo según peso/volumen/distancia
- Tracking de envíos
- Integración con couriers

#### 8. Atributos de Productos ⚠️ MEDIO
**Actual:**
- Product sin atributos estructurados

**Agregar:**
```php
// Nueva tabla: product_attributes
- product_id
- attribute_name (ej: "Color", "Talla", "Material")
- attribute_value (ej: "Rojo", "M", "Algodón")
```

**Lógica:**
- Filtros por atributos
- Búsqueda avanzada
- Comparación de productos

#### 9. Historial de Búsquedas ⚠️ BAJO
**Agregar:**
```php
// Nueva tabla: search_history
- user_id
- search_term
- results_count
- created_at
```

**Lógica:**
- Guardar búsquedas del usuario
- Sugerencias basadas en historial
- Analytics de búsquedas

#### 10. Recomendaciones de Productos ⚠️ BAJO
**Agregar:**
- Productos relacionados
- "Clientes que compraron X también compraron Y"
- Recomendaciones basadas en historial
- Productos vistos recientemente

#### 11. Cupones Mejorados (Ya existe pero mejorar)
**Actual:**
- `Coupon` existe pero puede mejorarse

**Mejorar:**
- Cupones por categoría
- Cupones por producto específico
- Cupones de envío gratis
- Cupones de primera compra
- Límite de uso por usuario

#### 12. Múltiples Direcciones de Envío ⚠️ MEDIO
**Actual:**
- `Address` existe pero no está claro si se usa para envío

**Mejorar:**
- Marcar dirección como "default"
- Direcciones de facturación separadas
- Guardar múltiples direcciones por usuario

#### 13. Carrito Persistente (Ya implementado ✅)
**Actual:**
- Carrito en base de datos (migrado de Session)

**Mantener:**
- ✅ Ya está implementado correctamente

#### 14. Reviews Mejorados (Ya existe pero mejorar)
**Actual:**
- `Review` existe con rating y comentario

**Mejorar:**
- Fotos en reviews
- Verificación de compra (solo compradores pueden review)
- Helpful votes en reviews
- Respuestas del comercio

---

### 📋 RESUMEN: PRIORIDADES PARA ECOMMERCE

#### 🔴 CRÍTICO (Implementar primero)
1. ✅ **Gestión de Inventario/Stock** - Sin esto no es ecommerce viable
2. ✅ **Variantes de Productos** - Necesario para productos con opciones
3. ✅ **Facturación** - Requisito legal en muchos países
4. ✅ **Gestión de Devoluciones** - Necesario para confianza del cliente

#### 🟡 ALTO (Implementar después)
5. ✅ **Wishlist de Productos** - Mejora experiencia de usuario
6. ✅ **Gestión de Envíos (Shipping)** - Adaptar OrderDelivery actual
7. ✅ **Impuestos** - Necesario para ventas internacionales

#### 🟢 MEDIO (Mejoras)
8. ✅ **Atributos de Productos** - Para búsqueda avanzada
9. ✅ **Múltiples Direcciones** - Mejora UX
10. ✅ **Cupones Mejorados** - Ya existe, solo mejorar

#### 🔵 BAJO (Opcional)
11. ✅ **Historial de Búsquedas** - Nice to have
12. ✅ **Recomendaciones** - Mejora conversión
13. ✅ **Reviews Mejorados** - Ya existe, solo mejorar

---

### 🔄 PLAN DE MIGRACIÓN

#### Fase 1: Quitar/Adaptar (1-2 semanas)
1. Adaptar `DeliveryAgent` → `ShippingProvider` (opcional, mantener si hay delivery propio)
2. Cambiar estado `preparing` → `processing`
3. Adaptar `OrderDelivery` para shipping genérico
4. Evaluar si mantener Posts sociales

#### Fase 2: Agregar Críticos (3-4 semanas)
1. Implementar gestión de stock
2. Implementar variantes de productos
3. Implementar facturación
4. Implementar devoluciones

#### Fase 3: Agregar Altos (2-3 semanas)
1. Wishlist de productos
2. Shipping mejorado
3. Impuestos

#### Fase 4: Mejoras (1-2 semanas)
1. Atributos de productos
2. Múltiples direcciones
3. Cupones mejorados

---

## ✅ Correcciones Recientes (Enero 2025)

### 🔧 Depuración y Mejoras de Lógica de Negocio

**Problemas Críticos Corregidos:**

1. ✅ **Buyer/OrderController::store()** - Completamente refactorizado
   - ✅ Validación de stock/disponibilidad de productos
   - ✅ Transacciones DB en creación de orden
   - ✅ Eliminado código de testing mezclado con producción
   - ✅ Eliminado código de debug (logs innecesarios)
   - ✅ Validación de precio recalculado (protege contra manipulación)
   - ✅ Validación de datos mínimos de perfil (firstName, lastName, phone, address si delivery)
   - ✅ Validación de commerce activo
   - ✅ Validación de mismo commerce para todos los productos
   - ✅ Limpieza automática de carrito al crear orden
   - ✅ Eventos activados (OrderCreated)

2. ✅ **CartService** - Validaciones mejoradas
   - ✅ Validación de mismo commerce (limpia carrito si es diferente)
   - ✅ Validación de producto disponible (`available = true`)
   - ✅ Validación de commerce activo (`open = true`)
   - ✅ Validación de cantidad máxima (1-100)
   - ✅ Limpieza automática de productos no disponibles en `formatCartResponse()`

3. ✅ **Estados de Orden Unificados**
   - ✅ `preparing` → `processing` (en todos los controladores)
   - ✅ `on_way` → `shipped` (en todos los controladores)
   - ✅ Transiciones validadas en `Commerce/OrderController::updateStatus()`
   - ✅ Estados actualizados en: AnalyticsController, CommerceAnalyticsController, DashboardController, DeliveryController, PaymentController, LocationController, AdminOrderController

4. ✅ **Eventos Activados**
   - ✅ `OrderCreated` - Se emite al crear orden
   - ✅ `OrderStatusChanged` - Se emite al cambiar estado
   - ✅ `PaymentValidated` - Se emite al validar/rechazar pago

5. ✅ **Validaciones de Negocio Implementadas**
   - ✅ Carrito solo permite productos del mismo commerce
   - ✅ Precio se recalcula y valida (no confía en frontend)
   - ✅ Solo se valida `available` (stock completo para post-MVP)
   - ✅ Datos mínimos de perfil requeridos para crear orden
   - ✅ Cancelación solo permitida en `pending_payment` (comprador)

**Archivos Modificados:**
- `app/Http/Controllers/Buyer/OrderController.php`
- `app/Services/CartService.php`
- `app/Services/OrderService.php`
- `app/Http/Controllers/Commerce/OrderController.php`
- `app/Http/Controllers/Delivery/OrderController.php`
- `app/Http/Controllers/Delivery/DeliveryController.php`
- `app/Http/Controllers/Commerce/DashboardController.php`
- `app/Http/Controllers/Admin/AdminOrderController.php`
- `app/Http/Controllers/Analytics/AnalyticsController.php`
- `app/Http/Controllers/Commerce/AnalyticsController.php`
- `app/Http/Controllers/Payment/PaymentController.php`
- `app/Http/Controllers/Location/LocationController.php`

---

## ✅ Correcciones Recientes (Enero 2025)

### Errores Críticos Corregidos:
- ✅ **AnalyticsController:** Valores hardcoded reemplazados por cálculos reales (average_preparation_time, order_acceptance_rate)
- ✅ **AnalyticsController:** Método `getDeliveryTimes()` completamente implementado con distribución
- ✅ **DeliveryController:** Integración OSRM para cálculo real de distancia y tiempo de rutas
- ✅ **UserController:** Validación de roles actualizada (solo 4 roles válidos: users, commerce, delivery, admin)
- ✅ **Limpieza:** Código comentado eliminado de routes/api.php

### Roles del Sistema:
Solo existen **4 roles válidos**:
- **users** (Level 0): Cliente/Comprador
- **commerce** (Level 1): Comercio/Restaurante  
- **delivery** (Level 2): Repartidor/Delivery
- **admin** (Level 3): Administrador

Los roles `transport` y `affiliate` fueron eliminados del código.

## 📞 Soporte

Para soporte técnico o preguntas sobre el proyecto, contactar al equipo de desarrollo.

## 📄 Licencia

Este proyecto es privado y confidencial.

---

**Versión:** 1.0.0  
**Laravel:** 10.x  
**PHP:** 8.1+  
**Última actualización:** Enero 2025  
**Estado:** ✅ MVP Completado - En desarrollo activo  
**Tests:** 204 pasaron ✅, 0 fallaron ✅  
**Errores críticos:** ✅ Todos corregidos
