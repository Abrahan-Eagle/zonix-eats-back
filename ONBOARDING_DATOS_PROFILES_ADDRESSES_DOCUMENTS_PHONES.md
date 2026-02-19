# Onboarding: datos por tablas profiles, addresses, documents, phones

Resumen de qué registrar al inicio del onboarding, qué después, qué eliminar y qué falta, usando solo las tablas **profiles**, **addresses**, **documents** y **phones**.

---

## 1. Esquema actual (resumen)

### profiles
| Campo | Tipo | Notas |
|-------|------|--------|
| id | bigint PK | |
| user_id | FK users | |
| firstName, middleName, lastName, secondLastName | string | middleName, secondLastName nullable |
| photo_users | string nullable | |
| date_of_birth | date nullable | |
| maritalStatus | enum (married, divorced, single, widowed) | default single |
| sex | enum (F, M, O) | default M |
| status | enum (completeData, incompleteData, notverified) | default notverified |
| phone | string nullable | **Duplicado** con tabla phones |
| address | text nullable | **Legacy**: dirección en texto; la canónica es addresses |
| fcm_device_token, notification_preferences | | notificaciones |

### addresses
| Campo | Tipo | Notas |
|-------|------|--------|
| id | bigint PK | |
| profile_id | FK profiles | |
| street | string | |
| house_number, postal_code | string nullable | |
| latitude, longitude | decimal(10,7) | |
| status | enum (completeData, incompleteData, notverified) | default notverified |
| is_default | boolean | default false (casa vs entrega) |
| city_id | FK cities | |

### documents
| Campo | Tipo | Notas |
|-------|------|--------|
| id | bigint PK | |
| profile_id | FK profiles | |
| type | enum (ci, passport, rif, neighborhood_association) nullable | |
| number_ci | int nullable | |
| RECEIPT_N, sky | bigint nullable | |
| rif_url, taxDomicile | string nullable | |
| commune_register, community_rif | string nullable | |
| front_image | string nullable | **No existe back_image** en migración |
| issued_at, expires_at | date nullable | |
| approved | boolean | default false |
| status | boolean | default false (activo/inactivo) |

### phones
| Campo | Tipo | Notas |
|-------|------|--------|
| id | bigint PK | |
| profile_id | FK profiles | |
| operator_code_id | FK operator_codes | Código ej. 0412 |
| number | string(7) | Solo parte local |
| is_primary | boolean | default false |
| status | boolean | default true |
| approved | boolean | default false (en migración; **no está en fillable** del modelo) |

---

## 2. Qué registrar al principio del onboarding

Objetivo: **mínimo para poder usar la app** (comprador) según .cursorrules: `firstName`, `lastName`, `phone`, `photo_users` (required para orden).

### En **profiles** (obligatorio al inicio)
- **firstName** (required)
- **lastName** (required)
- **phone** (required para orden) — puede venir de `profiles.phone` o de tabla `phones`; hoy hay duplicidad.
- **photo_users** (required para orden)
- **user_id** (ya viene del usuario autenticado)
- Opcional en paso 1: middleName, secondLastName, date_of_birth, sex, maritalStatus.  
- **status**: inicial `notverified` o `incompleteData` hasta completar datos mínimos.

### En **addresses** (para comprador: dirección “casa” para búsqueda)
- Una dirección con **is_default = true** (casa):
  - street, house_number, postal_code (opcionales según UX)
  - latitude, longitude (recomendable desde el inicio para geolocalización)
  - city_id (recomendable)
  - status: ej. `notverified` o `incompleteData`
- No es obligatorio tener dirección completa el primer día; se puede pedir “ubicación aproximada” y luego afinar.

### En **phones**
- Si se usa tabla **phones** como fuente de verdad del teléfono:
  - Un registro: operator_code_id + number, is_primary = true, status = true.
- Requiere tener **operator_codes** (ej. 0412, 0424) en BD o un flujo para elegir operador.

### En **documents**
- **No** en onboarding inicial para rol comprador (users).  
- Documentos (CI, RIF, etc.) son para **commerce** / **delivery**; se piden **después**, en registro de comercio o repartidor.

**Resumen inicio onboarding (comprador):**  
Crear/actualizar **profile** (firstName, lastName, phone, photo_users) y, si se usa tabla phones, un **phone**; opcionalmente una **address** con is_default=true (aunque sea solo lat/lng + city_id). **documents** se dejan para después según rol.

---

## 3. Qué registrar después del onboarding inicial

### Perfil (profiles)
- middleName, secondLastName  
- date_of_birth, sex, maritalStatus  
- address (texto legacy): si se mantiene, rellenar desde addresses o dejar vacío y deprecar.  
- status → `completeData` cuando se cumplan requisitos.  
- fcm_device_token, notification_preferences (al usar la app).

### Direcciones (addresses)
- Segunda dirección: **is_default = false** (entrega), cuando el usuario vaya a pedir delivery.  
- Completar street, house_number, postal_code, city_id para direcciones ya creadas.  
- Ajustar lat/lng si el usuario edita la ubicación.

### Teléfonos (phones)
- Teléfonos adicionales (opcional).  
- approved: cuando exista flujo de verificación (después).

### Documentos (documents)
- **Commerce:** RIF, documentos fiscales (type rif, etc.), imágenes, issued_at, expires_at.  
- **Delivery:** CI/pasaporte, licencia, etc.  
- approved cuando admin/backend los valide.

---

## 4. Qué campos o datos eliminar / deprecar

### Eliminar o unificar
1. **profiles.phone vs tabla phones**  
   - Hoy: profile tiene `phone` (string) y además existe tabla `phones`.  
   - **Recomendación:** elegir una sola fuente de verdad:  
     - **Opción A:** Quitar `profiles.phone` y usar solo tabla `phones` (un phone principal por perfil).  
     - **Opción B:** Mantener solo `profiles.phone` y no usar tabla phones para el teléfono principal (más simple para onboarding).  
   - Si se mantienen ambos, hay que sincronizar y documentar cuál es “el” teléfono para órdenes y validaciones.

2. **profiles.address (texto)**  
   - La dirección canónica es **addresses** (con street, city_id, lat/lng, is_default).  
   - **Recomendación:** deprecar `profiles.address`; no usarlo en onboarding ni en nuevas features. Rellenar desde `addresses` si algo legacy lo sigue leyendo.

### Limpiar en documents
3. **Document.backImage()**  
   - El modelo tiene accessor `backImage()` pero en migración **no existe columna back_image**.  
   - **Recomendación:** eliminar el accessor o añadir migración `back_image` si se va a usar reverso del documento.

4. **Document: scope active**  
   - DocumentController usa `Document::active()` pero el modelo **no define scopeActive**.  
   - **Recomendación:** añadir en Document algo como `scopeActive($q) { return $q->where('status', true); }` o dejar de usar `->active()` y filtrar por status en el controlador.

### Inconsistencias a corregir (no “eliminar” pero sí alinear)
5. **Buyer AddressController vs tabla addresses**  
   - El controlador usa: name, address_line_1, address_line_2, city, state, country, delivery_instructions.  
   - La tabla tiene: street, house_number, postal_code, latitude, longitude, city_id, is_default.  
   - **Recomendación:** o bien se añade migración para name, address_line_1, address_line_2, state, country, delivery_instructions (y se mantiene city_id para relación), o bien se cambia el controlador para usar solo street, house_number, postal_code, city_id, lat/lng, is_default y obtener “nombre” y “ciudad/estado/país” vía relación City/State/Country. La segunda opción es más coherente con el esquema actual y con .cursorrules.

---

## 5. Qué falta (campos o lógica)

### Backend / BD
1. **Document**  
   - Añadir `scopeActive` (o equivalente) usado en DocumentController.  
   - Decidir si existe `back_image`; si sí, migración; si no, quitar accessor `backImage()`.

2. **Phone**  
   - Migración tiene `approved`; el modelo no lo tiene en **fillable**. Añadir `approved` a fillable (y usarlo cuando haya verificación).

3. **Addresses**  
   - Alinear Buyer AddressController con el esquema real (street, house_number, postal_code, city_id, lat/lng, is_default) o ampliar esquema con columnas que use el controlador; actualizar modelo Address y respuestas JSON (incl. formato de dirección vía City/State/Country).

4. **Onboarding explícito**  
   - No hay un endpoint único “completar onboarding” que cree/actualice profile + (opcional) address + (opcional) phone en una transacción. Sería útil para el flujo: registro → onboarding (profile + 1 address + 1 phone) → home.

### Frontend / onboarding
5. **Pantallas**  
   - Paso 1: nombre, apellido, teléfono, foto (y si se usa phones: operador + número).  
   - Paso 2 (o mismo paso): dirección “casa” (mapa + city o street/postal_code), guardar en addresses con is_default=true.  
   - No pedir documentos en onboarding comprador; sí en flujos commerce/delivery.

6. **Fuente del teléfono**  
   - Si el backend unifica en tabla phones: el formulario debe enviar operator_code_id + number y crear/actualizar Phone; y opcionalmente mantener profile.phone sincronizado o dejarlo de solo lectura desde phones.  
   - Si se unifica en profile.phone: el formulario envía solo phone (string) y no usar tabla phones para el principal.

---

## 6. Resumen por tabla

| Tabla      | Al inicio onboarding     | Después                         | Eliminar / deprecar     | Falta |
|-----------|---------------------------|----------------------------------|---------------------------|--------|
| **profiles** | firstName, lastName, phone, photo_users, user_id, status | middleName, secondLastName, date_of_birth, sex, maritalStatus, address (legacy), fcm, notification_preferences | Unificar con phones; deprecar profile.address | - |
| **addresses** | Una con is_default=true (street, house_number, postal_code, lat, lng, city_id) o solo lat/lng+city_id | Segunda dirección (entrega), completar datos | - | Alinear Buyer AddressController con esquema (o añadir columnas) |
| **documents** | No para comprador         | Para commerce/delivery: type, number_ci, front_image, fechas, approved | backImage() o columna back_image; scopeActive | scopeActive; decidir back_image |
| **phones**    | Uno principal (operator_code_id, number, is_primary) si se usa tabla | Más teléfonos; approved cuando haya verificación | Duplicidad con profile.phone | approved en fillable del modelo Phone |

Con esto puedes definir en el front qué pantallas del onboarding escriben en cada tabla y qué campos pedir “al principio” vs “después”, y en backend qué migraciones y cambios de modelo/controlador hacer para que todo sea coherente.
