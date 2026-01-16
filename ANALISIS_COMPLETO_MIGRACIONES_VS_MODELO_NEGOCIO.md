# 📊 ANÁLISIS COMPLETO: MIGRACIONES VS MODELO DE NEGOCIO

**Fecha:** 16 de Enero 2025  
**Objetivo:** Comparar todas las migraciones con el modelo de negocio documentado en `README.md` y `.cursorrules`

---

## ✅ TABLAS CORE - VERIFICADAS Y CORRECTAS

### 1. **users** ✅
- **Estado:** Correcto
- **Campos clave:** `name`, `email`, `password`, `google_id`, `role`, `completed_onboarding`
- **Nota:** `role` enum incluye todos los roles necesarios

### 2. **profiles** ✅
- **Estado:** Correcto (pero requiere validación en código)
- **Campos requeridos según modelo:**
  - ✅ `firstName` (required) - Presente
  - ✅ `lastName` (required) - Presente
  - ✅ `phone` (required) - Presente pero nullable
  - ⚠️ `photo_users` (required para USERS, COMMERCE, DELIVERY) - **NULLABLE** - Requiere validación en código
- **Campos opcionales:** `middleName`, `secondLastName`, `date_of_birth`, `maritalStatus`, `sex`
- **Campos de notificaciones:** ✅ `fcm_device_token`, `notification_preferences`

**⚠️ ACCIÓN REQUERIDA:**
- `photo_users` debe ser validado como required en código para roles USERS, COMMERCE, DELIVERY
- `phone` debe ser validado como required en código

### 3. **addresses** ✅
- **Estado:** Correcto
- **Campos:** `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `city_id`, `is_default`, `status`
- **Nota:** `is_default` permite marcar dirección predeterminada (casa)

### 4. **commerces** ⚠️
- **Estado:** Parcialmente correcto (campos nullable que deberían ser required)
- **Campos requeridos según modelo:**
  - ✅ `profile_id` (required) - Presente
  - ⚠️ `business_name` (required) - **NULLABLE** - Requiere validación en código
  - ⚠️ `business_type` (required) - **NULLABLE** - Requiere validación en código
  - ✅ `tax_id` (required) - **NULLABLE** - Recién agregado, requiere validación en código
- **Campos opcionales:** ✅ `image`, `phone`, `address`, `open`, `schedule`
- **Campos de membresía:** ✅ `membership_type`, `membership_monthly_fee`, `membership_expires_at`
- **Campos de comisión:** ✅ `commission_percentage`
- **Campos de penalización:** ✅ `cancellation_count`, `last_cancellation_date`

**⚠️ ACCIÓN REQUERIDA:**
- `business_name` debe ser validado como required en código
- `business_type` debe ser validado como required en código
- `tax_id` debe ser validado como required en código

### 5. **products** ✅
- **Estado:** Correcto
- **Campos:** `commerce_id`, `category_id`, `name`, `description`, `price`, `image`, `available`, `stock_quantity`
- **Nota:** `available` siempre requerido, `stock_quantity` opcional (ambas opciones según modelo)

### 6. **orders** ✅
- **Estado:** Correcto
- **Campos clave:**
  - ✅ `status` enum: `pending_payment`, `paid`, `processing`, `shipped`, `delivered`, `cancelled`
  - ✅ `delivery_fee` (costo que paga el cliente)
  - ✅ `delivery_payment_amount` (100% del delivery_fee que recibe delivery)
  - ✅ `commission_amount` (comisión de esta orden)
  - ✅ `cancellation_penalty` (penalización si cancela después de paid)
  - ✅ `cancelled_by` (user_id, commerce_id, admin_id)
  - ✅ `estimated_delivery_time` (máx 60 minutos)
  - ✅ `payment_proof`, `payment_method`, `reference_number`
  - ✅ `payment_validated_at`, `payment_proof_uploaded_at`
  - ✅ `cancellation_reason`, `delivery_address`

### 7. **order_items** ✅
- **Estado:** Correcto
- **Campos:** `order_id`, `product_id`, `quantity`, `unit_price`

### 8. **order_delivery** ✅
- **Estado:** Correcto (renombrado a inglés)
- **Campos:** `order_id`, `agent_id`, `status`, `delivery_fee`, `notes`
- **Nota:** Campos renombrados de español a inglés (`costo_envio` → `delivery_fee`, `notas` → `notes`)

### 9. **delivery_companies** ✅
- **Estado:** Correcto
- **Campos requeridos según modelo:**
  - ✅ `name` (required) - Presente
  - ✅ `tax_id` (required) - Presente
  - ✅ `phone` (required) - Presente
  - ✅ `address` (required) - Presente
- **Campos opcionales:** ✅ `image`, `open`, `schedule`, `active`

### 10. **delivery_agents** ⚠️
- **Estado:** Parcialmente correcto (campos nullable que deberían ser required)
- **Campos requeridos según modelo:**
  - ✅ `profile_id` (required) - Presente
  - ⚠️ `vehicle_type` (required) - **NULLABLE** - Requiere validación en código
  - ⚠️ `license_number` (required) - **NULLABLE** - Requiere validación en código
  - ✅ `company_id` (nullable para independientes) - Correcto
- **Campos de ubicación:** ✅ `current_latitude`, `current_longitude`, `last_location_update`
- **Campos de tracking:** ✅ `rejection_count`, `last_rejection_date`
- **Campos de estado:** ✅ `status`, `working`

**⚠️ ACCIÓN REQUERIDA:**
- `vehicle_type` debe ser validado como required en código
- `license_number` debe ser validado como required en código

### 11. **reviews** ✅
- **Estado:** Correcto (renombrado a inglés)
- **Campos:** `profile_id`, `order_id`, `reviewable` (morphs), `rating`, `comment`
- **Nota:** `order_id` agregado para validar que se califica después de orden entregada
- **Nota:** `comentario` renombrado a `comment`

### 12. **carts** ✅
- **Estado:** Correcto
- **Campos:** `user_id`, `notes`
- **Nota:** Unique en `user_id` asegura un carrito por usuario

### 13. **cart_items** ✅
- **Estado:** Correcto
- **Campos:** `cart_id`, `product_id`, `quantity`
- **Nota:** Unique en `[cart_id, product_id]` evita duplicados

### 14. **categories** ✅
- **Estado:** Correcto
- **Campos:** `name`, `description`

### 15. **disputes** ✅
- **Estado:** Correcto
- **Campos:** `order_id`, `reported_by` (morphs), `reported_against` (morphs), `type`, `description`, `status`, `admin_notes`, `resolved_at`
- **Nota:** Sistema de tickets/quejas según modelo de negocio

### 16. **delivery_payments** ✅
- **Estado:** Correcto
- **Campos:** `order_id`, `delivery_agent_id`, `amount`, `status`, `paid_at`, `notes`
- **Nota:** Trackea pagos a delivery (100% del delivery_fee)

### 17. **commerce_invoices** ✅
- **Estado:** Correcto
- **Campos:** `commerce_id`, `membership_fee`, `commission_amount`, `total`, `invoice_date`, `due_date`, `status`, `paid_at`, `notes`
- **Nota:** Facturas mensuales (membresía + comisiones)

---

## ⚠️ TABLAS PARA REVISAR (USO EN MVP)

### 18. **promotions** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `title`, `description`, `discount_type`, `discount_value`, `minimum_order`, `maximum_discount`, `start_date`, `end_date`, `is_active`
- **Nota:** Promociones manuales según modelo de negocio

### 19. **coupons** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `code`, `title`, `description`, `discount_type`, `discount_value`, `minimum_order`, `usage_limit`, `start_date`, `end_date`, `is_public`, `assigned_to_profile_id`
- **Nota:** Códigos promocionales según modelo de negocio

### 20. **delivery_zones** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `name`, `center_latitude`, `center_longitude`, `radius`, `delivery_fee`, `delivery_time`, `is_active`
- **Nota:** Zonas de delivery (puede ser legacy o complementario al sistema de expansión automática)

### 21. **posts** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `commerce_id`, `tipo`, `media_url`, `description`, `name`, `price`
- **Nota:** Posts de comercios (puede ser Post-MVP, pero se usa)

### 22. **post_likes** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `profile_id`, `post_id`
- **Nota:** Likes de posts (puede ser Post-MVP, pero se usa)

### 23. **banks** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `name`, `code`, `type`, `swift_code`, `is_active`
- **Nota:** Bancos para métodos de pago

### 24. **payment_methods** ✅ SE USA
- **Estado:** Correcto, se usa en código
- **Campos:** `payable` (morphs), `bank_id`, `type`, `brand`, `last4`, `account_number`, `phone`, `email`, `is_default`, `is_active`
- **Nota:** Métodos de pago unificados (comercio, usuario, delivery)

### 25. **documents** ✅
- **Estado:** Correcto
- **Campos:** `profile_id`, `type`, `number_ci`, `front_image`, `issued_at`, `expires_at`, `approved`, `status`
- **Nota:** Documentos de perfiles (CI, pasaporte, RIF, etc.)

### 26. **phones** ✅
- **Estado:** Correcto
- **Campos:** `profile_id`, `operator_code_id`, `number`, `is_primary`, `status`, `approved`
- **Nota:** Múltiples teléfonos por perfil

### 27. **operator_codes** ✅
- **Estado:** Correcto
- **Campos:** `code`, `name`
- **Nota:** Códigos de operadores telefónicos

### 28. **user_locations** ✅
- **Estado:** Correcto
- **Campos:** `profile_id`, `latitude`, `longitude`, `accuracy`, `altitude`, `speed`, `heading`, `address`, `recorded_at`
- **Nota:** Historial de ubicaciones GPS

### 29. **countries**, **states**, **cities** ✅
- **Estado:** Correcto
- **Nota:** Estructura geográfica para direcciones

### 30. **roles** ✅
- **Estado:** Correcto
- **Nota:** Roles del sistema

### 31. **notifications** ✅
- **Estado:** Correcto
- **Nota:** Notificaciones del sistema

### 32. **chat_messages** ✅
- **Estado:** Correcto
- **Nota:** Chat en tiempo real dentro de órdenes

---

## ❌ CAMPOS FALTANTES CRÍTICOS

### 1. **commerces.tax_id** ✅ RESUELTO
- **Estado:** ✅ Agregado en `create_commerces_table`
- **Acción:** Ya consolidado

### 2. **reviews.order_id** ✅ RESUELTO
- **Estado:** ✅ Agregado en `create_reviews_table`
- **Acción:** Ya consolidado

### 3. **reviews.comment** ✅ RESUELTO
- **Estado:** ✅ Renombrado de `comentario` a `comment` en `create_reviews_table`
- **Acción:** Ya consolidado

### 4. **order_delivery.delivery_fee** ✅ RESUELTO
- **Estado:** ✅ Renombrado de `costo_envio` a `delivery_fee` en `create_order_delivery_table`
- **Acción:** Ya consolidado

### 5. **order_delivery.notes** ✅ RESUELTO
- **Estado:** ✅ Renombrado de `notas` a `notes` en `create_order_delivery_table`
- **Acción:** Ya consolidado

---

## ⚠️ CAMPOS NULLABLE QUE REQUIEREN VALIDACIÓN EN CÓDIGO

### 1. **profiles.photo_users** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required para USERS, COMMERCE, DELIVERY
- **Migración:** Nullable
- **Estado actual:** ✅ Ya validado en `OrderController.php` para creación de órdenes
- **Acción pendiente:** Validar también en registro/actualización de perfiles para roles COMMERCE y DELIVERY

### 2. **profiles.phone** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required para todos los roles
- **Migración:** Nullable
- **Acción:** Validar en código como required

### 3. **commerces.business_name** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required
- **Migración:** Nullable
- **Acción:** Validar en código como required

### 4. **commerces.business_type** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required
- **Migración:** Nullable
- **Acción:** Validar en código como required

### 5. **commerces.tax_id** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required
- **Migración:** Nullable (recién agregado)
- **Acción:** Validar en código como required

### 6. **delivery_agents.vehicle_type** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required
- **Migración:** Nullable
- **Acción:** Validar en código como required

### 7. **delivery_agents.license_number** ⚠️ REQUIERE VALIDACIÓN
- **Modelo de negocio:** Required
- **Migración:** Nullable
- **Acción:** Validar en código como required

---

## 📋 RESUMEN DE ACCIONES REQUERIDAS

### ✅ COMPLETADAS
1. ✅ Agregado `commerces.tax_id`
2. ✅ Agregado `reviews.order_id`
3. ✅ Renombrado `reviews.comentario` → `comment`
4. ✅ Renombrado `order_delivery.costo_envio` → `delivery_fee`
5. ✅ Renombrado `order_delivery.notas` → `notes`

### ⚠️ PENDIENTES (Validación en Código)
1. ⚠️ Validar `profiles.photo_users` required para COMMERCE y DELIVERY (ya validado para USERS en OrderController)
2. ⚠️ Validar `profiles.phone` required para todos los roles
3. ⚠️ Validar `commerces.business_name` required
4. ⚠️ Validar `commerces.business_type` required
5. ⚠️ Validar `commerces.tax_id` required
6. ⚠️ Validar `delivery_agents.vehicle_type` required
7. ⚠️ Validar `delivery_agents.license_number` required

### ✅ VERIFICADAS (Todas se usan)
- ✅ `coupons` - Se usa
- ✅ `delivery_zones` - Se usa
- ✅ `posts` - Se usa
- ✅ `post_likes` - Se usa
- ✅ `banks` - Se usa
- ✅ `payment_methods` - Se usa

---

## 📊 ESTADÍSTICAS FINALES

- **Total de migraciones:** 47
- **Tablas core verificadas:** 17 ✅
- **Tablas adicionales verificadas:** 15 ✅
- **Campos faltantes críticos:** 0 (todos resueltos) ✅
- **Campos nullable que requieren validación:** 7 ⚠️
- **Tablas para revisar:** 0 (todas se usan) ✅

---

## ✅ CONCLUSIÓN

**Estado general:** ✅ **EXCELENTE**

Las migraciones están **bien estructuradas y alineadas con el modelo de negocio**. Los campos faltantes críticos han sido agregados y los campos en español han sido renombrados a inglés.

**Única acción pendiente:** Validar en código los campos nullable que según el modelo de negocio son required. Esto es una decisión de diseño válida (mantener nullable en BD pero validar en código) para permitir flexibilidad en el futuro.

**Recomendación:** Implementar validaciones en los controladores/requests correspondientes para asegurar que los campos required según el modelo de negocio sean validados correctamente.
