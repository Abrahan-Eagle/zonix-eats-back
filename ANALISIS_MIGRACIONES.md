# 📊 ANÁLISIS COMPLETO DE MIGRACIONES - ZONIX EATS BACKEND

**Fecha:** Enero 2025  
**Total de migraciones:** 61  
**Estado:** Necesita limpieza y actualización

---

## 🔴 PROBLEMAS CRÍTICOS IDENTIFICADOS

### 1. **MIGRACIONES DUPLICADAS** (Eliminar)

#### ❌ Duplicado 1: `is_default` en addresses
- `2026_01_15_111150_add_is_default_to_addresses_table.php` 
- `2026_01_15_112407_add_is_default_to_addresses_table.php` 
- **Acción:** Eliminar `2026_01_15_112407_*` (es duplicado)

#### ❌ Duplicado 2: `company_id` nullable en delivery_agents
- `2025_07_12_162625_make_company_id_nullable_in_delivery_agents_table.php` (simple)
- `2026_01_15_111148_make_company_id_nullable_in_delivery_agents_table.php` (completa con foreign key)
- **Acción:** Eliminar `2025_07_12_162625_*` (la segunda es más completa)

---

### 2. **ESTADOS ANTIGUOS EN ORDERS** (Actualizar)

**Migración:** `2025_05_23_000003_create_orders_table.php`

**Problema:**
```php
$table->enum('status', ['pending_payment', 'paid', 'preparing', 'on_way', 'delivered', 'cancelled']);
```

**Estados actuales según modelo de negocio:**
- ✅ `pending_payment` - Correcto
- ✅ `paid` - Correcto
- ❌ `preparing` - **DEPRECADO** → Debe ser `processing`
- ❌ `on_way` - **DEPRECADO** → Debe ser `shipped`
- ✅ `delivered` - Correcto
- ✅ `cancelled` - Correcto

**Acción:** Crear migración para actualizar enum: `['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled']`

---

### 3. **CAMPOS FALTANTES EN ORDERS** (Agregar)

**Tabla:** `orders`

**Campos faltantes según modelo de negocio:**

1. ✅ `delivery_fee` (decimal) - Costo de delivery que paga el cliente
2. ✅ `delivery_payment_amount` (decimal) - Cantidad que recibe delivery (100% del delivery_fee)
3. ✅ `commission_amount` (decimal) - Comisión de esta orden
4. ✅ `cancellation_penalty` (decimal) - Penalización si cancela después de paid
5. ✅ `cancelled_by` (string) - user_id, commerce_id, admin_id
6. ✅ `cancellation_reason` (text) - Ya existe, pero verificar
7. ✅ `estimated_delivery_time` (integer) - Tiempo estimado en minutos (máx 60)
8. ✅ `payment_proof_uploaded_at` (timestamp) - Cuándo se subió comprobante

**Campos existentes a verificar:**
- ✅ `payment_method` - Existe
- ✅ `reference_number` - Existe
- ✅ `payment_validated_at` - Existe
- ✅ `cancellation_reason` - Existe
- ✅ `delivery_address` - Existe
- ❌ `payment_proof` - Existe pero puede ser `payment_proof_url` (string) o file

**Acción:** Crear migración para agregar campos faltantes

---

### 4. **CAMPOS FALTANTES EN COMMERCES** (Agregar)

**Tabla:** `commerces`

**Campos faltantes según modelo de negocio:**

1. ✅ `membership_type` (enum: basic, premium, enterprise)
2. ✅ `membership_monthly_fee` (decimal)
3. ✅ `membership_expires_at` (timestamp)
4. ✅ `commission_percentage` (decimal)
5. ✅ `cancellation_count` (integer, default 0)
6. ✅ `last_cancellation_date` (timestamp, nullable)

**Acción:** Crear migración para agregar campos faltantes

---

### 5. **CAMPOS FALTANTES EN DELIVERY_AGENTS** (Agregar)

**Tabla:** `delivery_agents`

**Campos faltantes según modelo de negocio:**

1. ✅ `rejection_count` (integer, default 0)
2. ✅ `last_rejection_date` (timestamp, nullable)

**Campos existentes:**
- ✅ `current_latitude` - Existe (en migración `2026_01_14_102416_*`)
- ✅ `current_longitude` - Existe
- ✅ `last_location_update` - Existe
- ✅ `status` - Existe (renombrado de `estado`)
- ✅ `working` - Existe (renombrado de `trabajando`)

**Acción:** Crear migración para agregar campos faltantes

---

### 6. **TABLAS FALTANTES** (Crear)

#### Tabla 1: `disputes`
**Según README.md sección 12:**
```php
Schema::create('disputes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->morphs('reported_by'); // user_id, commerce_id, delivery_id
    $table->morphs('reported_against'); // user_id, commerce_id, delivery_id
    $table->enum('type', ['quality_issue', 'delivery_problem', 'payment_issue', 'other']);
    $table->text('description');
    $table->enum('status', ['pending', 'in_review', 'resolved', 'closed'])->default('pending');
    $table->text('admin_notes')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
});
```

#### Tabla 2: `delivery_payments` (Opcional)
**Según README.md sección 8:**
```php
Schema::create('delivery_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('delivery_agent_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 10, 2);
    $table->enum('status', ['pending_payment_to_delivery', 'paid_to_delivery'])->default('pending_payment_to_delivery');
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
```

#### Tabla 3: `commerce_invoices` (Opcional)
**Según README.md sección 3:**
```php
Schema::create('commerce_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
    $table->decimal('membership_fee', 10, 2);
    $table->decimal('commission_amount', 10, 2);
    $table->decimal('total', 10, 2);
    $table->date('invoice_date');
    $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
    $table->timestamps();
});
```

---

### 7. **TABLAS A REVISAR** (Evaluar si se necesitan)

#### Tabla 1: `coupons`
**Estado:** Existe migración `2025_07_13_142730_create_coupons_table.php`

**Análisis:**
- También existe `promotions` table
- Según modelo de negocio: "Promociones: Manual (comercio/admin), código promocional O automático"
- **Pregunta:** ¿`coupons` y `promotions` son lo mismo o diferentes?
- **Recomendación:** Si son lo mismo, eliminar `coupons` y usar solo `promotions`

#### Tabla 2: `delivery_zones`
**Estado:** Existe migración `2026_01_14_120849_create_delivery_zones_table.php`

**Análisis:**
- Según modelo de negocio: "Delivery: Asignación autónoma con expansión de área"
- No se menciona `delivery_zones` en el README
- **Pregunta:** ¿Se usa para algo o es código legacy?
- **Recomendación:** Si no se usa, eliminar. Si se usa, documentar en README

---

## 📋 PLAN DE ACCIÓN

### Fase 1: Limpieza (Eliminar duplicados)
1. ❌ Eliminar `2026_01_15_112407_add_is_default_to_addresses_table.php`
2. ❌ Eliminar `2025_07_12_162625_make_company_id_nullable_in_delivery_agents_table.php`

### Fase 2: Actualizar Estados (Orders)
3. ✅ Crear migración para actualizar enum de `status` en orders

### Fase 3: Agregar Campos Faltantes
4. ✅ Crear migración para agregar campos en `orders`
5. ✅ Crear migración para agregar campos en `commerces`
6. ✅ Crear migración para agregar campos en `delivery_agents`

### Fase 4: Crear Tablas Faltantes
7. ✅ Crear migración para `disputes`
8. ⚠️ Crear migración para `delivery_payments` (opcional)
9. ⚠️ Crear migración para `commerce_invoices` (opcional)

### Fase 5: Revisar Tablas Existentes
10. ❓ Decidir sobre `coupons` vs `promotions`
11. ❓ Decidir sobre `delivery_zones`

---

## ✅ CHECKLIST FINAL

- [x] Eliminar migraciones duplicadas ✅ **COMPLETADO**
- [x] Actualizar estados en orders ✅ **COMPLETADO** (`2026_01_16_092546_update_orders_status_enum_to_new_states.php`)
- [x] Agregar campos faltantes en orders ✅ **COMPLETADO** (`2026_01_16_092548_add_missing_fields_to_orders_table.php`)
- [x] Agregar campos faltantes en commerces ✅ **COMPLETADO** (`2026_01_16_092549_add_membership_and_commission_fields_to_commerces_table.php`)
- [x] Agregar campos faltantes en delivery_agents ✅ **COMPLETADO** (`2026_01_16_092550_add_rejection_tracking_to_delivery_agents_table.php`)
- [x] Crear tabla disputes ✅ **COMPLETADO** (`2026_01_16_092551_create_disputes_table.php`)
- [x] Crear tabla delivery_payments (opcional) ✅ **COMPLETADO** (`2026_01_16_092552_create_delivery_payments_table.php`)
- [x] Crear tabla commerce_invoices (opcional) ✅ **COMPLETADO** (`2026_01_16_092553_create_commerce_invoices_table.php`)
- [ ] Decidir sobre coupons vs promotions ⚠️ **PENDIENTE** (evaluar si se usa)
- [ ] Decidir sobre delivery_zones ⚠️ **PENDIENTE** (evaluar si se usa)
- [ ] Verificar que todas las migraciones estén ordenadas cronológicamente
- [ ] Probar todas las migraciones en ambiente de desarrollo

---

## 📝 NOTAS

- **Total migraciones actuales:** 59 (61 - 2 eliminadas)
- **Migraciones eliminadas:** 2 ✅
- **Migraciones creadas:** 7 ✅
- **Total migraciones finales:** 66

**IMPORTANTE:** Hacer backup de base de datos antes de ejecutar migraciones en producción.

---

## ✅ ESTADO ACTUAL (Enero 2025)

### Completado:
1. ✅ Eliminados 2 duplicados:
   - `2026_01_15_112407_add_is_default_to_addresses_table.php`
   - `2025_07_12_162625_make_company_id_nullable_in_delivery_agents_table.php`

2. ✅ Creadas 7 nuevas migraciones:
   - `2026_01_16_092546_update_orders_status_enum_to_new_states.php` - Actualiza estados antiguos
   - `2026_01_16_092548_add_missing_fields_to_orders_table.php` - 8 campos nuevos
   - `2026_01_16_092549_add_membership_and_commission_fields_to_commerces_table.php` - 6 campos nuevos
   - `2026_01_16_092550_add_rejection_tracking_to_delivery_agents_table.php` - 2 campos nuevos
   - `2026_01_16_092551_create_disputes_table.php` - Tabla de quejas/tickets
   - `2026_01_16_092552_create_delivery_payments_table.php` - Tabla de pagos a delivery
   - `2026_01_16_092553_create_commerce_invoices_table.php` - Tabla de facturas mensuales

### Pendiente:
- ⚠️ Evaluar si `coupons` se usa o es redundante con `promotions`
- ⚠️ Evaluar si `delivery_zones` se usa o es código legacy
- ⚠️ Ejecutar migraciones en ambiente de desarrollo
- ⚠️ Verificar que todo funcione correctamente
