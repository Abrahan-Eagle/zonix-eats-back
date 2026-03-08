# Lógica del módulo Phone – Tablas BD y roles

**Módulo único:** Cualquier rol que necesite número de teléfono lo tiene a través de este módulo. Una sola tabla de teléfonos, una sola API, una sola fuente de verdad.

---

## 1. Tablas de base de datos

### 1.1 `phones`

| Columna            | Tipo        | Descripción |
|--------------------|-------------|-------------|
| `id`               | bigint PK   | Id del teléfono |
| `profile_id`          | FK → profiles.id | **Dueño.** Siempre un perfil. CASCADE on delete. |
| `context`             | string(32)       | Uso: `personal`, `commerce`, `delivery_company`, `admin`. Default `personal`. |
| `commerce_id`         | FK nullable      | Si context=commerce, teléfono de ese comercio. CASCADE. |
| `delivery_company_id` | FK nullable      | Si context=delivery_company, teléfono de esa empresa. CASCADE. |
| `operator_code_id`    | FK → operator_codes.id | Código de operador (ej. 0412). CASCADE on delete. |
| `number`           | string(7)   | Solo 7 dígitos (número local). |
| `is_primary`       | boolean     | Un solo principal por perfil. Default false. |
| `status`           | boolean     | true = activo, false = “eliminado” (soft). Default true. |
| `approved`         | boolean     | Reservado (ej. verificación futura). Default false. |
| `created_at`, `updated_at` | timestamps | |

**Regla de unicidad:** La combinación `(operator_code_id, number)` no se repite (mismo número con mismo operador = un solo registro en el sistema).

**Límites (PhoneController):** personal 5 por perfil; commerce 4 por commerce_id; delivery_company 4 por delivery_company_id; admin 1 por perfil. Tabla incluye columnas `context`, `commerce_id`, `delivery_company_id` (ver migración 2026_03_06).

### 1.2 `operator_codes`

| Columna     | Tipo        | Descripción |
|-------------|-------------|-------------|
| `id`        | bigint PK   | |
| `code`      | string(4) unique | Ej: 0412, 0424. |
| `name`      | string      | Ej: Movilnet, Movistar. |
| `created_at`, `updated_at` | timestamps | |

Se usa para el desplegable al crear/editar teléfono y para armar el número completo (code + number).

### 1.3 Relación con `profiles`

- **profiles** 1 — N **phones** (un perfil tiene muchos teléfonos).
- No existe columna `phone` en `profiles`; se eliminó en favor de la tabla `phones`.
- El “teléfono principal” del perfil se obtiene: `phones` donde `profile_id` = X y `is_primary = true` y `status = true` (accessor `Profile::getPhoneAttribute()` → devuelve `full_number` de ese registro).

---

## 2. Quién es “dueño” del teléfono en BD

- **Dueño:** Siempre `profile_id`. Columna **context** indica uso: `personal`, `commerce`, `delivery_company`, `admin`.
- Si `context = commerce` → columna `commerce_id` (cada comercio puede tener varios teléfonos).
- Si `context = delivery_company` → `delivery_company_id`. Si `context = personal` o `admin` → ambas FKs null.

---

## 3. Roles y cómo obtienen / usan el teléfono

| Rol (código BD)     | ¿Tiene teléfono? | Cómo lo obtiene |
|---------------------|------------------|------------------|
| **users** (Buyer)   | Sí               | User → Profile → `phones`. El usuario gestiona sus teléfonos en el módulo Phone (lista/alta/edición/eliminación). |
| **commerce**        | Sí               | Commerce → Profile (por `profile_id`) → `phones`. Accessor `$commerce->phone` = teléfono principal del perfil. No tiene teléfonos “propios” del negocio; usa los del perfil del dueño. |
| **delivery_company**| Sí               | DeliveryCompany → Profile (por `profile_id`) → `phones`. `$deliveryCompany->phone` = teléfono principal del perfil. |
| **delivery_agent**  | Sí               | DeliveryAgent → Profile (por `profile_id`) → `phones`. `$deliveryAgent->phone` = teléfono principal del perfil. |
| **delivery**        | Sí               | Mismo modelo que delivery_agent (repartidor con o sin empresa). Perfil → phones. |
| **admin**           | Sí (si tiene perfil) | Mismo que users: User → Profile → phones. |

Resumen: **todos los que tienen teléfono lo tienen porque tienen un perfil**, y los teléfonos viven en `phones` con ese `profile_id`. Comercio, Delivery Company y Delivery Agent no tienen filas propias en `phones`; exponen el teléfono (principal) del perfil al que están vinculados.

---

## 4. Reglas de negocio (resumen)

- **Módulo único:** Un solo lugar (tabla `phones` + API de phones) para gestionar teléfonos; todos los roles que necesitan número dependen de este módulo.
- **Dueño en BD:** Siempre `profile_id`. No se usan FKs a commerce, delivery_company ni delivery_agent en `phones`.
- **Un principal** por (profile + context + entidad). **Límites:** 5 personales, 4 por comercio, 4 por empresa, 1 admin.
- **Unicidad:** `(operator_code_id, number)` único. **Propiedad:** Solo comercios/empresas cuyo `profile_id` sea el del usuario.
- **API:** GET index acepta query `context`, `commerce_id`, `delivery_company_id`. POST store requiere `context` y, si aplica, `commerce_id` o `delivery_company_id`.

---

## 5. Referencia en código

- Modelo: `App\Models\Phone` (constantes CONTEXT_*), relaciones `commerce()`, `deliveryCompany()`.
- Perfil: `Profile::getPhoneAttribute()` = principal personal. Commerce/DeliveryCompany: accessor prioriza phones con su context y FK; fallback profile->phone.
- API: `PhoneController`, rutas `api/phones` (auth:sanctum).

---

**Última actualización:** 6 Marzo 2026
