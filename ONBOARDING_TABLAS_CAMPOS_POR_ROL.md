# Onboarding: tablas y campos por rol (Role 0 y Role 1)

Análisis actualizado tras unificar teléfonos en tabla **phones**.  
Estado actual: **profiles** ya no tiene columna `phone`; **commerces** / **delivery_agents** / **delivery_companies** tampoco (el teléfono se obtiene de **profile → phones**).

---

## Rol 0 (users / comprador)

### Onboarding (al principio)

Objetivo: mínimo para usar la app y poder pedir (según .cursorrules: firstName, lastName, teléfono, photo_users; dirección “casa” para búsqueda).

| Tabla      | Campos a registrar en el onboarding | Quién los asigna |
|------------|-------------------------------------|-------------------|
| **profiles** | `firstName`, `lastName`, `photo_users` | Usuario (formulario). `user_id` lo pone el backend al crear el perfil. |
| **profiles** | Opcional en mismo flujo: `middleName`, `secondLastName`, `date_of_birth`, `sex`, `maritalStatus` | Usuario. |
| **profiles** | `status`: ej. `notverified` o `incompleteData` (backend). | Backend. |
| **phones**   | Un teléfono principal: `operator_code_id`, `number` (7 dígitos), `is_primary = true`, `status = true`. | Usuario elige operador (0412, 0414, etc.) y escribe número. `profile_id` lo pone el backend. |
| **addresses** | Una dirección “casa”: `street`, `latitude`, `longitude`, `city_id`, `is_default = true`. | Usuario (mapa + calle + ciudad). `profile_id` lo pone el backend. |
| **addresses** | Opcional en mismo paso: `house_number`, `postal_code`. | Usuario. |
| **addresses** | `status`: ej. `notverified` (backend). | Backend. |

Resumen Rol 0 – onboarding:
- **profiles:** firstName, lastName, photo_users (+ opcional: middleName, secondLastName, date_of_birth, sex, maritalStatus).
- **phones:** 1 registro con operator_code_id + number (is_primary = true).
- **addresses:** 1 registro “casa” con street, latitude, longitude, city_id, is_default = true.

No van en onboarding de comprador: **documents**, **payment_methods**, ni datos de comercio/delivery.

---

### Después del onboarding (Rol 0)

| Tabla      | Campos / datos a completar o usar después |
|------------|-------------------------------------------|
| **profiles** | Completar: middleName, secondLastName, date_of_birth, sex, maritalStatus; `address` (texto legacy, opcional); marcar `status` = completeData cuando corresponda; fcm_device_token, notification_preferences al usar la app. |
| **phones**   | Añadir más teléfonos si se desea; marcar `approved` cuando exista flujo de verificación. |
| **addresses** | Segunda dirección “entrega” (is_default = false) cuando vaya a pedir delivery; completar street, house_number, postal_code en direcciones ya creadas. |
| **documents** | No aplica para rol comprador en onboarding; solo si en el futuro se pide algún documento. |
| **payment_methods** | Métodos de pago para **pagar** (tarjeta, pago móvil, etc.): al hacer el primer pedido o en “Métodos de pago”. |

---

## Rol 1 (commerce)

El onboarding de commerce es el **registro como comercio**: primero existe el usuario (y puede que ya tenga perfil de comprador o no); al elegir rol comercio se crea **perfil + comercio** y se registra el teléfono en **phones**.

### Onboarding (registro comercio)

| Tabla      | Campos a registrar en el onboarding | Quién los asigna |
|------------|-------------------------------------|-------------------|
| **profiles** | `firstName`, `lastName`, `photo_users`, `address` (texto, dirección del titular). | Usuario. `user_id` lo pone el backend. |
| **profiles** | Opcional: middleName, secondLastName, date_of_birth, maritalStatus, sex. | Usuario. |
| **profiles** | `status`: ej. notverified (backend). | Backend. |
| **phones**   | Un teléfono: `operator_code_id`, `number`, `is_primary = true`, `status = true`. | Usuario. `profile_id` lo pone el backend (y el helper que crea el Phone al registrar comercio). |
| **commerces** | `business_name`, `business_type`, `tax_id`. | Usuario. `profile_id` lo pone el backend. |
| **commerces** | Opcional en mismo flujo: `image`, `address`, `open`, `schedule`. | Usuario. |

Resumen Rol 1 – onboarding:
- **profiles:** firstName, lastName, photo_users, address.
- **phones:** 1 registro (operator_code_id + number) para el perfil del comercio.
- **commerces:** business_name, business_type, tax_id (+ opcional: image, address, open, schedule).

No van en este onboarding: **addresses** (si el comercio quiere dirección con lat/lng se puede pedir después), **documents** (RIF, etc.), **payment_methods** (para recibir dinero).

---

### Después del onboarding (Rol 1)

| Tabla      | Campos / datos después |
|------------|-------------------------|
| **profiles** | Completar datos personales; status = completeData; fcm, notification_preferences. |
| **phones**   | Más teléfonos si hace falta; approved cuando haya verificación. |
| **addresses** | Dirección del local con lat/lng (street, house_number, postal_code, latitude, longitude, city_id) si se usa para mapa/radio. |
| **commerces** | Completar image, address, open, schedule; membership_type, membership_monthly_fee, commission_percentage los suele fijar admin/sistema. |
| **documents** | RIF y otros documentos fiscales (type, number_ci, front_image, issued_at, expires_at, etc.) para verificación. |
| **payment_methods** | Cuenta/teléfono para **recibir** pagos (tabla payment_methods con payable = Commerce). |

---

## Resumen visual

### Rol 0 (comprador)

| Momento   | Tablas       | Campos clave |
|-----------|--------------|--------------|
| Onboarding | profiles     | firstName, lastName, photo_users |
| Onboarding | phones       | operator_code_id, number (1 teléfono principal) |
| Onboarding | addresses    | street, latitude, longitude, city_id, is_default = true (dirección casa) |
| Después   | profiles     | middleName, secondLastName, date_of_birth, sex, maritalStatus, fcm, notification_preferences |
| Después   | addresses    | Segunda dirección (entrega); completar house_number, postal_code |
| Después   | payment_methods | Métodos para pagar (checkout o ajustes) |

### Rol 1 (commerce)

| Momento   | Tablas       | Campos clave |
|-----------|--------------|--------------|
| Onboarding | profiles     | firstName, lastName, photo_users, address |
| Onboarding | phones       | operator_code_id, number (1 teléfono) |
| Onboarding | commerces    | business_name, business_type, tax_id |
| Después   | profiles     | Completar datos; fcm, notification_preferences |
| Después   | addresses    | Dirección del local (lat, lng, city_id) si aplica |
| Después   | commerces    | image, address, open, schedule |
| Después   | documents    | RIF, documentos fiscales |
| Después   | payment_methods | Datos para recibir dinero |

---

## Notas técnicas

- **Teléfono:** Una sola tabla **phones** (por profile_id). En onboarding se crea al menos un registro con operator_code_id + number; el “teléfono” de comprador y de comercio se lee desde profile (accessor que usa phones).
- **profile_id / user_id:** Los asigna siempre el backend al crear perfil o al asociar comercio al perfil.
- **operator_codes:** Catálogo en BD (0412, 0414, etc.); el front debe listar códigos para que el usuario elija al registrar teléfono.
- **documents:** Solo “después” para commerce (y delivery), no en el onboarding inicial de comprador ni en el registro básico de comercio.
