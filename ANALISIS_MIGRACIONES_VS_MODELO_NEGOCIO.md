# 📊 ANÁLISIS COMPLETO: MIGRACIONES vs MODELO DE NEGOCIO

**Fecha:** Enero 2025  
**Total migraciones:** 47  
**Estado:** Análisis comparativo con modelo de negocio

---

## 🔍 METODOLOGÍA

Comparación de:
1. **Tablas requeridas** según modelo de negocio (README.md)
2. **Campos requeridos** en cada tabla según modelo
3. **Campos faltantes** en migraciones actuales
4. **Campos innecesarios** que no se usan según modelo
5. **Tablas que no se usan** o son redundantes

---

## 📋 TABLAS REQUERIDAS SEGÚN MODELO DE NEGOCIO

### ✅ Tablas Core (Existen y están correctas)

1. **users** ✅
2. **profiles** ✅
3. **addresses** ✅
4. **phones** ✅
5. **documents** ✅
6. **roles** ✅
7. **commerces** ✅
8. **products** ✅
9. **orders** ✅
10. **order_items** ✅
11. **carts** ✅
12. **cart_items** ✅
13. **delivery_companies** ✅
14. **delivery_agents** ✅
15. **order_delivery** ✅
16. **reviews** ✅
17. **disputes** ✅ (Creada recientemente)
18. **delivery_payments** ✅ (Creada recientemente)
19. **commerce_invoices** ✅ (Creada recientemente)
20. **promotions** ✅
21. **notifications** ✅
22. **chat_messages** ✅
23. **categories** ✅
24. **payment_methods** ✅
25. **user_locations** ✅

### ⚠️ Tablas a Revisar

1. **coupons** ⚠️ - ¿Es lo mismo que `promotions`? Según modelo: "Promociones: Manual (comercio/admin), código promocional o automático"
2. **delivery_zones** ⚠️ - No mencionada en modelo de negocio. ¿Se usa?
3. **posts** ⚠️ - No mencionada en modelo de negocio. ¿Se usa?
4. **post_likes** ⚠️ - No mencionada en modelo de negocio. ¿Se usa?
5. **banks** ⚠️ - No mencionada explícitamente. ¿Se usa?
6. **user_payment_methods** ⚠️ - No mencionada explícitamente. ¿Se usa?
7. **delivery_payment_methods** ⚠️ - No mencionada explícitamente. ¿Se usa?

---

## 🔴 CAMPOS FALTANTES SEGÚN MODELO DE NEGOCIO

### 1. **PROFILES** - Campos Faltantes

**Según modelo de negocio:**
- ✅ `firstName` - Existe
- ✅ `lastName` - Existe
- ✅ `phone` - Existe
- ✅ `photo_users` - Existe (pero es nullable, debería ser required para delivery/commerce)
- ✅ `fcm_device_token` - Existe
- ✅ `notification_preferences` - Existe

**⚠️ PROBLEMA:** `photo_users` es nullable pero según modelo:
- **USERS:** Required para crear orden (necesaria para delivery)
- **COMMERCE:** Required (foto del dueño/representante)
- **DELIVERY:** Required (foto para identificación)

**Acción:** Verificar si se valida en código, no solo en BD

---

### 2. **COMMERCES** - Campos Faltantes

**Según modelo de negocio REQUERIDOS:**
- ✅ `business_name` - Existe (nullable, debería ser required)
- ✅ `business_type` - Existe (nullable, debería ser required)
- ❌ `tax_id` - **FALTANTE** (Número de identificación tributaria - RUC, NIT, etc.)

**Según modelo de negocio OPCIONALES:**
- ✅ `image` - Existe
- ✅ `phone` - Existe
- ✅ `address` - Existe
- ✅ `open` - Existe
- ✅ `schedule` - Existe
- ✅ `membership_type` - Existe (agregado recientemente)
- ✅ `membership_monthly_fee` - Existe (agregado recientemente)
- ✅ `membership_expires_at` - Existe (agregado recientemente)
- ✅ `commission_percentage` - Existe (agregado recientemente)
- ✅ `cancellation_count` - Existe (agregado recientemente)
- ✅ `last_cancellation_date` - Existe (agregado recientemente)

**Acción:** Agregar `tax_id` a commerces

---

### 3. **DELIVERY_COMPANIES** - Campos Faltantes

**Según modelo de negocio REQUERIDOS:**
- ✅ `name` - Existe
- ✅ `tax_id` - Existe (antes 'ci')
- ✅ `phone` - Existe
- ✅ `address` - Existe
- ✅ `image` - Existe (agregado)
- ✅ `open` - Existe (agregado)
- ✅ `schedule` - Existe (agregado)

**✅ COMPLETO** - Todos los campos requeridos existen

---

### 4. **DELIVERY_AGENTS** - Campos Faltantes

**Según modelo de negocio REQUERIDOS:**
- ✅ `company_id` - Existe (nullable para independientes)
- ✅ `vehicle_type` - Existe (nullable, pero según modelo es required)
- ✅ `license_number` - Existe (nullable, pero según modelo es required)
- ✅ `current_latitude` - Existe
- ✅ `current_longitude` - Existe
- ✅ `rejection_count` - Existe (agregado recientemente)
- ✅ `last_rejection_date` - Existe (agregado recientemente)

**⚠️ PROBLEMA:** `vehicle_type` y `license_number` son nullable pero según modelo son required

**Acción:** Verificar si se valida en código, no solo en BD

---

### 5. **ORDERS** - Campos Faltantes

**Según modelo de negocio:**
- ✅ `delivery_fee` - Existe (agregado recientemente)
- ✅ `delivery_payment_amount` - Existe (agregado recientemente)
- ✅ `commission_amount` - Existe (agregado recientemente)
- ✅ `cancellation_penalty` - Existe (agregado recientemente)
- ✅ `cancelled_by` - Existe (agregado recientemente)
- ✅ `estimated_delivery_time` - Existe (agregado recientemente)
- ✅ `payment_proof_uploaded_at` - Existe (agregado recientemente)
- ✅ `payment_method` - Existe
- ✅ `reference_number` - Existe
- ✅ `payment_validated_at` - Existe
- ✅ `cancellation_reason` - Existe
- ✅ `delivery_address` - Existe

**✅ COMPLETO** - Todos los campos requeridos existen

---

### 6. **PRODUCTS** - Campos Faltantes

**Según modelo de negocio:**
- ✅ `available` - Existe (required)
- ✅ `stock_quantity` - Existe (nullable, opcional)
- ✅ `category_id` - Existe (nullable, opcional)

**✅ COMPLETO** - Todos los campos requeridos existen

---

### 7. **REVIEWS** - Campos Faltantes

**Según modelo de negocio:**
- ✅ `profile_id` - Existe
- ✅ `reviewable_type` - Existe (morphs)
- ✅ `reviewable_id` - Existe (morphs)
- ✅ `rating` - Existe
- ✅ `comentario` - Existe (pero debería ser 'comment' en inglés)

**⚠️ PROBLEMA:** 
- Campo `comentario` está en español, debería ser `comment`
- Según modelo: "Comercio y Delivery se califican por separado" - El morphs permite esto ✅
- Según modelo: "Obligatorio después de orden entregada" - No hay campo `order_id` para validar

**Acción:** 
- Renombrar `comentario` a `comment`
- Agregar `order_id` para validar que se califica después de orden entregada

---

### 8. **ORDER_DELIVERY** - Campos Faltantes

**Según modelo de negocio:**
- ✅ `order_id` - Existe
- ✅ `agent_id` - Existe
- ✅ `status` - Existe (consolidado)
- ✅ `costo_envio` - Existe (pero debería ser `delivery_fee` en inglés)

**⚠️ PROBLEMA:** 
- Campo `costo_envio` está en español, debería ser `delivery_fee`
- Campo `notas` está en español, debería ser `notes`

**Acción:** Renombrar campos a inglés

---

## 🟡 CAMPOS INNECESARIOS O A REVISAR

### 1. **ORDERS**
- `receipt_url` - ¿Se usa? No mencionado en modelo de negocio

### 2. **PRODUCTS**
- Todos los campos parecen necesarios ✅

### 3. **DELIVERY_AGENTS**
- `phone` - Ya existe en `profiles`, ¿es necesario duplicar?

### 4. **DELIVERY_COMPANIES**
- `active` - ¿Es diferente de `open`? Según modelo solo se usa `open`

---

## 🔵 TABLAS QUE NO SE USAN SEGÚN MODELO

### 1. **coupons** ⚠️
**Análisis:**
- Existe tabla `promotions` que según modelo maneja "código promocional o automático"
- `coupons` parece ser redundante con `promotions`
- **Pregunta:** ¿Se usa `coupons` o solo `promotions`?
- **Recomendación:** Si no se usa, eliminar. Si se usa, documentar diferencia.

### 2. **delivery_zones** ⚠️
**Análisis:**
- No mencionada en modelo de negocio
- Modelo dice: "Asignación autónoma con expansión de área" (no menciona zonas)
- **Pregunta:** ¿Se usa para algo o es código legacy?
- **Recomendación:** Si no se usa, eliminar.

### 3. **posts** y **post_likes** ⚠️
**Análisis:**
- No mencionadas en modelo de negocio
- Parecen ser para feed/social, no parte del MVP
- **Pregunta:** ¿Se usan o son Post-MVP?
- **Recomendación:** Si no se usan en MVP, mover a Post-MVP o eliminar.

### 4. **banks** ⚠️
**Análisis:**
- No mencionada explícitamente en modelo
- Modelo dice: "Comercio coloca sus datos bancarios" pero no especifica tabla
- **Pregunta:** ¿Se usa para almacenar datos bancarios?
- **Recomendación:** Si se usa, documentar. Si no, eliminar.

### 5. **user_payment_methods** y **delivery_payment_methods** ⚠️
**Análisis:**
- Modelo dice: "Cliente elige UN método por orden" (no guarda métodos guardados)
- Modelo dice: "Comercio coloca sus datos bancarios" (no menciona métodos guardados)
- **Pregunta:** ¿Se usan o son Post-MVP?
- **Recomendación:** Si no se usan en MVP, mover a Post-MVP o eliminar.

---

## ✅ RESUMEN DE ACCIONES REQUERIDAS

### 🔴 CRÍTICO - Agregar Campos Faltantes

1. **commerces.tax_id** (string, required)
   - Número de identificación tributaria (RUC, NIT, etc.)
   - Según modelo: Required para commerce

2. **reviews.order_id** (foreignId, nullable)
   - Para validar que se califica después de orden entregada
   - Según modelo: "Obligatorio después de orden entregada"

3. **reviews.comment** (text, nullable)
   - Renombrar `comentario` a `comment` (inglés)

4. **order_delivery.delivery_fee** (decimal)
   - Renombrar `costo_envio` a `delivery_fee` (inglés)

5. **order_delivery.notes** (text)
   - Renombrar `notas` a `notes` (inglés)

---

### 🟡 IMPORTANTE - Validar Campos Nullable

1. **profiles.photo_users**
   - Actual: nullable
   - Modelo: Required para USERS (crear orden), COMMERCE, DELIVERY
   - **Acción:** Verificar validación en código (OrderController ya valida)

2. **commerces.business_name**
   - Actual: nullable
   - Modelo: Required
   - **Acción:** Cambiar a required o validar en código

3. **commerces.business_type**
   - Actual: nullable
   - Modelo: Required
   - **Acción:** Cambiar a required o validar en código

4. **delivery_agents.vehicle_type**
   - Actual: nullable
   - Modelo: Required
   - **Acción:** Cambiar a required o validar en código

5. **delivery_agents.license_number**
   - Actual: nullable
   - Modelo: Required
   - **Acción:** Cambiar a required o validar en código

---

### 🟢 OPCIONAL - Limpiar Tablas No Usadas

1. **coupons** - Evaluar si se usa o es redundante con `promotions`
2. **delivery_zones** - Evaluar si se usa
3. **posts** y **post_likes** - Evaluar si se usan en MVP
4. **banks** - Evaluar si se usa
5. **user_payment_methods** y **delivery_payment_methods** - Evaluar si se usan

---

## 📊 ESTADÍSTICAS

### Tablas Requeridas según Modelo: ~25
### Tablas Existentes: 47
### Tablas a Revisar: 7
### Campos Faltantes Críticos: 5
### Campos a Renombrar: 3
### Campos Nullable a Validar: 5

---

## ✅ CHECKLIST DE ACCIONES

- [x] Agregar `commerces.tax_id` (required) ✅ **COMPLETADO** - Consolidado en `create_commerces_table`
- [x] Agregar `reviews.order_id` (foreignId, nullable) ✅ **COMPLETADO** - Consolidado en `create_reviews_table`
- [x] Renombrar `reviews.comentario` → `reviews.comment` ✅ **COMPLETADO** - Consolidado en `create_reviews_table`
- [x] Renombrar `order_delivery.costo_envio` → `order_delivery.delivery_fee` ✅ **COMPLETADO** - Consolidado en `create_order_delivery_table`
- [x] Renombrar `order_delivery.notas` → `order_delivery.notes` ✅ **COMPLETADO** - Consolidado en `create_order_delivery_table`
- [x] Validar `profiles.photo_users` required en código ✅ **COMPLETADO** - Ya validado en OrderController
- [ ] Validar `commerces.business_name` required ⚠️ **PENDIENTE** - Validar en código o cambiar a required
- [ ] Validar `commerces.business_type` required ⚠️ **PENDIENTE** - Validar en código o cambiar a required
- [ ] Validar `delivery_agents.vehicle_type` required ⚠️ **PENDIENTE** - Validar en código o cambiar a required
- [ ] Validar `delivery_agents.license_number` required ⚠️ **PENDIENTE** - Validar en código o cambiar a required
- [x] Evaluar si `coupons` se usa ✅ **SE USA** - Hay modelos y controladores
- [x] Evaluar si `delivery_zones` se usa ✅ **SE USA** - Hay modelo y controladores
- [x] Evaluar si `posts` y `post_likes` se usan ✅ **SE USA** - Hay modelo Post
- [x] Evaluar si `banks` se usa ✅ **SE USA** - Hay modelo y controlador
- [ ] Evaluar si `user_payment_methods` y `delivery_payment_methods` se usan ⚠️ **PENDIENTE** - Revisar uso en MVP

---

## 📝 NOTAS

- **Validación en código vs BD:** Algunos campos son nullable en BD pero required según modelo. Se valida en código (ej: `photo_users` en OrderController).
- **Compatibilidad:** Algunas migraciones de renombrado se mantienen para compatibilidad con datos existentes.
- **Post-MVP:** Algunas tablas pueden ser para funcionalidades Post-MVP (ej: posts, coupons avanzados).
