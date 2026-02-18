# Análisis de migraciones por rol: users (nivel 0) y commerce (nivel 1)

**Fecha:** 28 Enero 2025  
**Objetivo:** Documentar todas las tablas y campos de las migraciones del backend que se utilizan por el rol **users** (comprador, nivel 0) y por el rol **commerce** (comerciante / dueño de restaurante, nivel 1).

---

## 1. Resumen por rol

| Rol        | Nivel | Descripción              | Tablas propias / principales                                                                 |
|-----------|-------|---------------------------|------------------------------------------------------------------------------------------------|
| **users** | 0     | Comprador / Cliente      | `carts`, `cart_items`, `user_locations`, `post_likes`, `coupon_usages`; uso de `orders` (profile_id), `reviews`, `addresses` |
| **commerce** | 1  | Comerciante / Restaurante| `commerces`, `products`, `posts`, `commerce_invoices`; uso de `orders` (commerce_id), `categories` (catálogo) |

Ambos roles comparten: `users`, `profiles`, `addresses`, `phones`, `documents`, `notifications`, `chat_messages`, `countries`, `states`, `cities`, `payment_methods` (polimórfico), `disputes` (morph), `personal_access_tokens`, `roles`, `user_roles`.

---

## 2. Tablas de autenticación y sistema (ambos roles)

### 2.1 `users`
- **Uso:** users (login, identidad); commerce (login, identidad). Un usuario tiene un único registro; el campo `role` indica el rol por defecto.
- **Campos:**  
  `id`, `name`, `email`, `email_verified_at`, `password`, `google_id`, `given_name`, `family_name`, `profile_pic`, `AccessToken`, `completed_onboarding`, `role` (enum: admin, users, commerce, delivery_company, delivery_agent, delivery), `remember_token`, `created_at`, `updated_at`.

### 2.2 `roles`
- **Uso:** Catálogo de roles del sistema.
- **Campos:** `id`, `name`, `description`, `permissions` (json), `created_at`, `updated_at`.

### 2.3 `user_roles`
- **Uso:** Asignación usuario ↔ rol (permite múltiples roles por usuario).
- **Campos:** `id`, `user_id`, `role_id`, `created_at`, `updated_at`. Unique (`user_id`, `role_id`).

### 2.4 `personal_access_tokens`
- **Uso:** Tokens de API (Sanctum); tokenable = User.
- **Campos:** `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`.

### 2.5 `password_reset_tokens`
- **Uso:** Recuperación de contraseña (ambos roles si usan email/password).
- **Campos:** `email`, `token`, `created_at`.

---

## 3. Perfil y datos personales (ambos roles)

Un **users** tiene un perfil de comprador. Un **commerce** tiene un perfil de dueño; además tiene un registro en `commerces` ligado al mismo `profile_id`.

### 3.1 `profiles`
- **Uso:** users = perfil comprador (firstName, lastName, phone, photo_users, dirección base). commerce = perfil dueño del negocio (mismos campos; puede usar `address` para negocio o usar `commerces.address`).
- **Campos:**  
  `id`, `user_id`, `firstName`, `middleName`, `lastName`, `secondLastName`, `photo_users`, `date_of_birth`, `maritalStatus`, `sex`, `status`, `phone`, `address`, `fcm_device_token`, `notification_preferences` (json), `created_at`, `updated_at`.

**Requeridos por rol (según .cursorrules):**
- **users:** firstName, lastName, phone, photo_users (required para orden).
- **commerce:** firstName, lastName, phone, address, business_name, business_type, tax_id (los últimos tres están en `commerces`, no en `profiles`).

### 3.2 `addresses`
- **Uso:** users = direcciones del comprador (casa `is_default=true` + dirección de entrega). commerce = opcional para perfil/dirección fiscal.
- **Campos:**  
  `id`, `street`, `house_number`, `postal_code`, `latitude`, `longitude`, `status`, `is_default`, `profile_id`, `city_id`, `created_at`, `updated_at`.

### 3.3 `phones`
- **Uso:** Opcional para ambos (teléfonos adicionales por operador).
- **Campos:** `id`, `profile_id`, `operator_code_id`, `number`, `is_primary`, `status`, `approved`, `created_at`, `updated_at`.

### 3.4 `operator_codes`
- **Uso:** Catálogo para `phones`.
- **Campos:** `id`, `code`, `name`, `created_at`, `updated_at`.

### 3.5 `documents`
- **Uso:** users = opcional (CI, pasaporte). commerce = documentos fiscales / RIF (type rif, rif_url, taxDomicile, etc.).
- **Campos:**  
  `id`, `type` (ci, passport, rif, neighborhood_association), `number_ci`, `RECEIPT_N`, `sky`, `rif_url`, `taxDomicile`, `commune_register`, `community_rif`, `front_image`, `issued_at`, `expires_at`, `approved`, `status`, `profile_id`, `created_at`, `updated_at`.

---

## 4. Ubicación geográfica (catálogo y uso)

### 4.1 `countries`
- **Campos:** `id`, `sortname`, `name`, `phonecode`, `created_at`, `updated_at`.

### 4.2 `states`
- **Campos:** `id`, `name`, `countries_id`, `created_at`, `updated_at`.

### 4.3 `cities`
- **Campos:** `id`, `name`, `state_id`, `created_at`, `updated_at`.

Usados en `addresses.city_id` (users y eventualmente commerce para direcciones de perfil).

---

## 5. Rol USERS (nivel 0 – Comprador)

### 5.1 Tablas donde el comprador es “dueño” o actor principal

| Tabla | FK / identificación | Campos relevantes para users |
|-------|----------------------|-------------------------------|
| **carts** | `profile_id` (comprador) | `id`, `profile_id`, `notes`, `created_at`, `updated_at`. Un carrito por perfil (unique `profile_id`). |
| **cart_items** | `cart_id` → Cart → profile | `id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`. Unique (`cart_id`, `product_id`). |
| **user_locations** | `profile_id` | `id`, `profile_id`, `latitude`, `longitude`, `accuracy`, `altitude`, `speed`, `heading`, `address`, `recorded_at`, `created_at`, `updated_at`. Ubicación en tiempo real del comprador. |
| **post_likes** | `profile_id` | `id`, `profile_id`, `post_id`, `created_at`, `updated_at`. Like a posts de comercios. |
| **coupon_usages** | `profile_id`, `order_id` | `id`, `coupon_id`, `profile_id`, `order_id`, `discount_amount`, `used_at`, `created_at`, `updated_at`. Unique (`coupon_id`, `profile_id`, `order_id`). |

### 5.2 Tablas donde el comprador participa (orders, reviews, chat, notifications)

| Tabla | Uso para users |
|-------|------------------|
| **orders** | `profile_id` = comprador. Campos que importan al comprador: `profile_id`, `commerce_id`, `delivery_type`, `status`, `total`, `delivery_fee`, `delivery_address`, `notes`, `payment_method`, `payment_proof`, `reference_number`, `receipt_url`, `payment_validated_at`, `payment_proof_uploaded_at`, `cancellation_reason`, `estimated_delivery_time`, `created_at`, `updated_at`. |
| **order_items** | Solo lectura para el comprador (líneas de su orden). `order_id`, `product_id`, `quantity`, `unit_price`. |
| **reviews** | `profile_id` = quien califica (comprador). `reviewable` = commerce o delivery_agent. Campos: `profile_id`, `order_id`, `reviewable_type`, `reviewable_id`, `rating`, `comment`. |
| **notifications** | `profile_id` = comprador. `id`, `profile_id`, `title`, `body`, `type`, `read_at`, `data`, `created_at`, `updated_at`. |
| **chat_messages** | `sender_id` = profile (comprador cuando `sender_type = 'customer'`). Campos: `order_id`, `sender_id`, `sender_type` (customer, restaurant, delivery_agent), `recipient_type`, `content`, `type`, `read_at`, `created_at`, `updated_at`. |

### 5.3 Otras tablas que usa el comprador (solo lectura o uso indirecto)

- **products**: lectura (catálogo del comercio); no es “dueño”.
- **commerces**: lectura (listado, detalle, horarios, open).
- **categories**: lectura (filtros de productos).
- **coupons**: lectura y uso; `assigned_to_profile_id` opcional para cupones asignados a un perfil.
- **payment_methods**: `payable_type`/`payable_id` pueden ser Profile (métodos guardados del comprador) o Commerce (métodos del comercio para pagar).
- **disputes**: puede ser `reported_by` (morph) como comprador.

### 5.4 Resumen de tablas por flujo (users)

- **Onboarding / perfil:** `users`, `profiles`, `addresses`, `countries`, `states`, `cities`, `documents`, `phones`, `operator_codes`.
- **Carrito:** `carts` (profile_id), `cart_items`, `products`, `commerces`.
- **Órdenes:** `orders`, `order_items`, `products`, `commerces`, `profiles`, `addresses` (entrega).
- **Pagos:** `orders` (payment_*), `payment_methods` (payable = Commerce o Profile).
- **Reputación:** `reviews` (profile_id = comprador).
- **Chat:** `chat_messages` (sender_id = profile, sender_type = customer).
- **Notificaciones:** `notifications` (profile_id).
- **Ubicación:** `user_locations` (profile_id), `addresses` (casa/entrega).
- **Promociones / cupones:** `coupons`, `coupon_usages` (profile_id), `promotions` (lectura si aplica).
- **Social:** `posts`, `post_likes` (profile_id).
- **Quejas:** `disputes` (reported_by = comprador).

---

## 6. Rol COMMERCE (nivel 1 – Comerciante)

### 6.1 Tablas propias del comercio

| Tabla | FK / identificación | Campos relevantes para commerce |
|-------|----------------------|----------------------------------|
| **commerces** | `profile_id` (dueño) | `id`, `profile_id`, `business_name`, `business_type`, `tax_id`, `image`, `phone`, `address`, `open`, `schedule` (json), `membership_type`, `membership_monthly_fee`, `membership_expires_at`, `commission_percentage`, `cancellation_count`, `last_cancellation_date`, `created_at`, `updated_at`. |
| **products** | `commerce_id` | `id`, `commerce_id`, `category_id`, `name`, `description`, `price`, `image`, `available`, `stock_quantity`, `created_at`, `updated_at`. |
| **posts** | `commerce_id` | `id`, `commerce_id`, `tipo`, `media_url`, `description`, `name`, `price`, `created_at`, `updated_at`. |
| **commerce_invoices** | `commerce_id` | `id`, `commerce_id`, `membership_fee`, `commission_amount`, `total`, `invoice_date`, `due_date`, `status`, `paid_at`, `notes`, `created_at`, `updated_at`. |

### 6.2 Tablas donde el comercio participa (orders, chat, notifications)

| Tabla | Uso para commerce |
|-------|--------------------|
| **orders** | `commerce_id` = comercio vendedor. Campos relevantes: `commerce_id`, `profile_id` (comprador), `delivery_type`, `status`, `total`, `delivery_fee`, `commission_amount`, `cancellation_penalty`, `cancelled_by`, `payment_proof`, `payment_method`, `reference_number`, `payment_validated_at`, `delivery_address`, `notes`, `cancellation_reason`, `created_at`, `updated_at`. |
| **order_items** | Líneas de órdenes del comercio; `product_id` pertenece a `products.commerce_id`. |
| **notifications** | `profile_id` = perfil del dueño (mismo user que el commerce). |
| **chat_messages** | `sender_id` = profile del dueño cuando `sender_type = 'restaurant'`. |

### 6.3 Catálogos y otras tablas que usa commerce

| Tabla | Uso para commerce |
|-------|--------------------|
| **categories** | Catálogo global; `products.category_id` opcional. `id`, `name`, `description`. |
| **promotions** | En migración no hay `commerce_id`; si en código está asociado a comercio, sería por lógica o tabla pivote. Campos: `title`, `description`, `discount_type`, `discount_value`, `minimum_order`, `maximum_discount`, `image_url`, `banner_url`, `start_date`, `end_date`, `terms_conditions`, `priority`, `is_active`. |
| **payment_methods** | `payable` = Commerce para recibir pagos (bank_id, type, account_number, etc.). |
| **banks** | Catálogo para `payment_methods.bank_id`. |
| **disputes** | `reported_by` o `reported_against` puede ser el comercio (morph). |
| **reviews** | `reviewable_type`/`reviewable_id` = Commerce cuando el comprador califica al comercio. |

### 6.4 Resumen de tablas por flujo (commerce)

- **Onboarding / perfil:** `users`, `profiles`, `addresses`, `documents` (RIF/tributario), `phones`, `operator_codes`, `countries`, `states`, `cities`.
- **Negocio:** `commerces` (profile_id), `categories` (para productos).
- **Catálogo:** `products` (commerce_id), `categories`.
- **Publicación:** `posts` (commerce_id).
- **Órdenes:** `orders` (commerce_id), `order_items`, `products`.
- **Facturación plataforma:** `commerce_invoices` (commerce_id).
- **Pagos recibidos:** `orders` (payment_*), `payment_methods` (payable = Commerce), `banks`.
- **Chat:** `chat_messages` (sender_type = restaurant).
- **Notificaciones:** `notifications` (profile_id del dueño).
- **Reputación:** `reviews` (reviewable = Commerce).
- **Quejas:** `disputes` (reported_by / reported_against = commerce).

---

## 7. Tablas no usadas por users ni commerce (directamente)

- **delivery_agents**, **delivery_companies**, **order_delivery**, **delivery_zones**, **delivery_payments**, **delivery_payment_methods**: rol delivery.
- **cache**, **cache_locks**, **jobs**, **job_batches**, **failed_jobs**: Laravel / sistema.
- **user_roles**: asignación de roles (admin/usuario asignador).

Las tablas **user_payment_methods** y **delivery_payment_methods** fueron reemplazadas por la tabla unificada **payment_methods** (morph `payable`), según migraciones 2025_07_20_*.

---

## 8. Notas de implementación

1. **Carrito:** El carrito está asociado al perfil del comprador (`profile_id`). Solo `profiles` y `user_roles` referencian a `users`; el resto del dominio va a `profiles`. Se usa `Cart::getOrCreateForProfile($profile->id)`; el usuario debe tener perfil para usar el carrito.
2. **Órdenes:** Siempre tienen `profile_id` (comprador) y `commerce_id` (comercio). Validaciones de negocio (stock, total, dirección de entrega) usan `profile_id` y direcciones del perfil.
3. **payment_methods:** La relación polimórfica `payable` permite que un Profile (comprador) tenga métodos guardados y que un Commerce tenga métodos para recibir pagos; ambos roles usan la misma tabla.
4. **promotions:** La migración actual no tiene `commerce_id`; si las promociones son por comercio, debe existir relación en modelo o en otra tabla (no reflejada en migraciones revisadas).

---

**Documento generado a partir de las migraciones en `database/migrations/` del proyecto zonix-eats-back.**
