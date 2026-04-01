# AGENTS.md - Zonix Eats Backend (Laravel API)

> Instrucciones para AI coding agents trabajando en el backend de Zonix Eats.
> Para documentación detallada de lógica de negocio, ver `README.md`.
> **Para reglas de mantenimiento y coherencia de skills, ver [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md).**

## Contexto de sesión

**Al iniciar o retomar trabajo:** Leer [docs/active_context.md](docs/active_context.md) si existe, para recuperar el estado de la última sesión (cambios recientes, áreas tocadas, próximos pasos). Así la IA mantiene contexto sin que el usuario tenga que pedirlo.

---

## Project Overview

| Métrica                  | Valor                                              |
| ------------------------ | -------------------------------------------------- |
| **Framework**            | Laravel 10.x / PHP 8.1+                            |
| **Base de Datos**        | MySQL                                              |
| **Versión**              | 1.0.0                                              |
| **Estado**               | ✅ MVP Completado - En desarrollo activo           |
| **Endpoints**            | 290 rutas REST                                     |
| **Controladores**        | 82                                                 |
| **Modelos**              | 41                                                 |
| **Migraciones**          | 55                                                 |
| **Tests**                | 269 pasaron ✅, 0 fallaron                         |
| **Seguridad**            | Sanctum + RBAC + Rate Limiting + Upload validation |
| **Última actualización** | 31 Marzo 2026                                      |

### Cambios recientes (documentar aquí los avances)

- **1 Abr 2026:** Cierre módulo Tiempo Real y Notificaciones (backend) — hardening y contrato v1 aplicados para eventos críticos: `OrderCreated` queda solo en canal privado de commerce (sin canal público), `OrderStatusChanged/PaymentValidated/NotificationCreated/DeliveryLocationUpdated/OrderPendingAssignment` ahora incluyen `event_id`, `schema_version` y `occurred_at`; `OrderPendingAssignment` amplía payload para UI de asignación (`commerce_name`, `delivery_address`, `delivery_fee`); observabilidad mínima añadida con contadores de auth broadcasting (success/denied/error) y métricas base de emisión/fallos de notificaciones/FCM en `NotificationService`. Validación: `php artisan test --filter=WebSocketTest` 9 OK (32 assertions).
- **1 Abr 2026:** Cierre módulo Métodos de Pago (backend) — estado 10/10 verificable: hardening de rutas legacy `buyer/payments/*` (rol + rate limit), deprecación explícita con headers (`X-API-Deprecated`, `X-API-Replacement`, `Sunset`) y gate por flag (`LEGACY_PAYMENT_PROCESSING_ENABLED`) para desactivar procesamiento legacy; `OrderController.uploadPaymentProof` reforzado con bloqueo de reemplazo cuando el pago ya está validado (409), sync legacy opcional por flag (`SYNC_LEGACY_ORDER_PAYMENT_FIELDS`) y logs estructurados de upload; `Commerce/OrderController.validatePayment` con validación de comprobante existente, controles de idempotencia (no doble validación/rechazo) y logs de validación/rechazo. Contrato de respuesta mantenido canónico + alias legacy compatible. Certificación final: `php artisan test` (34 tests / 186 assertions) en verde para bloque pagos/órdenes/multirol.
- **1 Abr 2026:** Cierre módulo saneamiento Factories/Seeders (backend): `cart_items` ahora siempre incluye `line_id` en factory/seeders (`CartItemFactory`, `CartItemSeeder`, `ZonixDemoSeeder`) evitando violaciones de integridad por unique `(cart_id, line_id)`; `UserFactory::deliveryAgent()` corregido a rol `delivery_agent`; `CommerceSeeder` blindado para no mutar rol de `user_id=1`; seeders demo de delivery documentados para uso principal/complementario sin duplicación accidental. Validación: `php artisan migrate:fresh --seed` OK + smoke tests (`CartControllerTest`, `OrderTest`, `OrderPaymentTest`, `AdminRoleTest`) en verde.
- **31 Mar 2026:** Cierre formal módulo Catálogo (backend) — estado 10/10 técnico: contrato canónico consolidado con `data.items` (manteniendo compatibilidad legacy), `OrderController@index` alineado a envelope estándar buyer (`success/message/data/items/pagination` + alias transitorio), cobertura de regresión ampliada (`OrderTest` contrato de listado) y certificación de suites críticas de catálogo/carrito/orden en verde (31 tests / 162 assertions en bloque forense).
- **31 Mar 2026:** Forense catálogo (backend) ejecutado y aplicado: (1) `RestaurantController` unificado a response envelope (`success/data/message`) con `per_page` acotado, (2) `CartController` ahora mapea errores de negocio a 4xx con `error_code` (evita 500 en reglas esperadas), (3) `OrderController` endurecido para concurrencia de stock con `lockForUpdate` + revalidación en transacción, (4) `SearchController` con validación explícita de filtros y 422 para parámetros inválidos, además de exigir comercios abiertos en búsqueda de productos, (5) mitigación de N+1 en comercios (`phones` eager-load + accessor optimizado). Certificación: `php artisan test` 284 OK.
- **31 Mar 2026:** Corrección integral de bugs (backend): (1) `AddressController` (Profiles) ahora valida acceso por dueño también para direcciones de comercio (`commerce_id`) y evita reasignar `profile_id` por usuarios no-admin en update, (2) `DocumentController` con hardening anti-IDOR en `store/show/update/destroy` (owner/admin), y update flexible sin exigir `type/profile_id` cuando ya existe documento, (3) `ProfileController` bloquea `createCommerce/createDeliveryAgent/createDeliveryCompany` si `user_id` no coincide con el usuario autenticado (403), (4) `Buyer/AddressController` alineado al esquema canónico (`street/house_number/city_id`) con compatibilidad para payload legacy. Certificación: `php artisan test` completo 279 OK.
- **31 Mar 2026:** Cierre módulo Onboarding Buyer+Commerce (backend): (1) hardening de ownership en `ProfileController` y `AddressController` (solo dueño o admin en perfiles/direcciones compartidas), (2) `addCommerceToProfile` ahora rechaza perfiles ajenos (403), (3) contrato `profile_id` alineado en direcciones/documentos (canónico `profiles.id`, con fallback legacy), (4) pruebas de certificación reforzadas con casos de ownership (`ProfileControllerTest`, nuevo `AddressControllerTest`). Verificación: `php artisan test` 272 OK.
- **31 Mar 2026:** Diagnóstico y remediación: (1) Alineación estados de orden — `DeliveryAssignmentService` ya no asigna `'assigned'` a `orders.status` (crea `order_delivery`); `OrderTrackingController` y `OrderNotificationSubscriber` remapeados a enum canónico (`pending_payment, paid, processing, shipped, delivered, cancelled`). (2) Validación uploads en `CommercePromotionController` (image|mimes|max:5120). (3) Métricas AGENTS.md actualizadas a conteos reales (290 rutas, 82 controllers, 41 modelos, 55 migraciones). (4) Frontend: modelo Order default corregido a `'pending_payment'`, getters alineados; ~18 deps muertas eliminadas de pubspec; catches vacíos reemplazados con debugPrint en 10 servicios.
- **20 Mar 2026:** Jarvis — Backlog producto/técnico persistido en `docs/active_context.md` (sección **Backlog candidato (no implementado)** + **Prioridad sugerida** para siguiente iteración; espejo en frontend). Sin cambios de código; referencia para implementar después con OK explícito.
- **20 Mar 2026:** Cierre tareas pendientes Delivery Model: orders.delivery_company_id; al marcar shipped (delivery) se asigna empresa y Job auto-asignación (agente más cercano + timeout 60s + notificación company); GET /api/delivery-company/orders/pending; evento OrderPendingAssignment y canal company.{id}; calculateDeliveryFee acepta commerce_id; frontend tab Pendientes + flujo asignar, checkout con delivery_fee calculado (API), UI payout % agente y default % empresa. Tests backend 269 OK, frontend 250 OK.
- **20 Mar 2026:** Plan Delivery Model Rebuild (FASE 0-5): Rutas `/api/delivery/*` solo para delivery_agent/delivery; DeliveryController consolidado; CompanyController: POST/PATCH agents, payout, settings, available-agents, assign; DeliveryFeeService y POST /api/buyer/delivery-fee/calculate; FCM para delivery; pantalla Agregar agente. FASE 4 (doble pago) cancelada.
- **19 Mar 2026:** Subida a dev: reorganización de seeders (movidos de `database/seeders/_archive/` a `database/seeders/`), nuevo `NotificationService.php` y listener `OrderNotificationSubscriber`, ajustes en Events (OrderStatusChanged, NotificationCreated), BroadcastingController, rutas y migraciones. `.gitignore`: añadidos `venv_scraper/` y `pendrive_badblocks_result.txt` (proyecto/archivo ajeno); eliminado del repo el archivo local `pendrive_badblocks_result.txt`. Tests 269 OK.
- **9 Mar 2026:** Módulo Exportar datos: ruta `GET /api/profile/export` (auth:sanctum, cualquier rol) para que commerce y otros roles puedan exportar; ExportController.getProfileDataForExport defensivo con `$profile` null (evita error en usuarios sin perfil buyer); frontend usa esa URL y descarga real (archivo JSON/TXT + Share.shareXFiles para guardar/compartir); formato TXT corregido (ciudad como nombre, activity_type en actividad).
- **6 Mar 2026:** Tests: MultiRoleSimulationTest corrige assert (API devuelve `data.status` → assertJsonPath); migración `add_context_and_entity_fks_to_phones_table` en `down()` evita dropForeign/dropIndex en SQLite para que `php artisan test` pase (MySQL sin cambios).
- **6 Mar 2026:** Norma Migraciones: documentada en `.cursorrules` y AGENTS.md. No crear migraciones add*\* ni change*\*; tablas existentes se actualizan editando la migración create correspondiente.
- **6 Mar 2026:** Módulo demo/seed: `operator_codes`: columna `code` como entero (migración create), `name` como string; OperatorCodeSeeder con 412, 414, 424, 416, 426. ZonixDemoSeeder: zonas Valencia/Carabobo (El Socorro, Los Chorritos, Mayorista, etc.), user 6 fijo (Wistremiro/commerce), direcciones y user_locations de users 1 y 6 en El Socorro; docblock con grafo de conexiones entre roles (buyer→orden→commerce→delivery_agent→delivery_company, reviews, disputes). Migraciones consolidadas (edición de creates, eliminación de add/change sobrantes).
- **6 Mar 2026:** Módulo Documents: solo tipos `ci` y `rif`; tabla depurada (migración elimina RECEIPT_N, sky, rif_url, commune_register, community_rif; enum type restringido a ci/rif). Campos útiles: number_ci, rif_number (formato Venezuela J-19217553-0), taxDomicile, front_image, approved, status. Estado aprobado: documento verificado o pendiente de verificación (campo `approved`). Tests: DocumentControllerTest.
- **6 Mar 2026:** Documentado en AGENTS.md: Profile como entidad principal; Users 1:1 con Profile; teléfonos/documentos/direcciones pertenecen al perfil (`profile_id`).
- **Fecha:** 18 Marzo 2026
- **Resumen:** Refactorización integral de Pusher completada usando Streams para permitir múltiples suscriptores simultáneos. Se solucionó el bug crítico de pérdida de eventos al navegar y se optimizó el backend para eliminar ruido en eventos públicos.
- **Áreas tocadas:** `OrderStatusChanged.php`, `pusher_service.dart`, `UserProvider.dart`, y 9 pantallas de órdenes/comercio.
- **Próximos pasos sugeridos:** Monitorear estabilidad de Pusher en redes inestables (edge cases). Verificar si Review/Dispute events necesitan migrar al mismo patrón de Streams.
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
php artisan test                   # Todos (269 tests)
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

## Modelo de datos: Profile como entidad principal

- **Profile** es la tabla/entidad principal para datos de persona (comprador, comercio, delivery): teléfonos, documentos, direcciones pertenecen al **perfil** (`profile_id` en `phones`, `documents`, `addresses`, etc.).
- **Users** tiene relación **1:1** con **Profile**: un usuario es la cuenta de login; el perfil es el dueño de los datos. Al autenticarse se obtiene el `user`; desde ahí se obtiene el `profile` para listar/crear recursos del perfil.
- Al diseñar APIs o flujos nuevos, considerar **profile_id** como identificador del “dueño” de los datos. Algunos endpoints legacy siguen usando **user_id** en URL o body (p. ej. `GET /api/phones/{user_id}` hace `Profile::where('user_id', $id)`); es por compatibilidad.

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
| `context-updater`       | Resumir sesión en docs/active_context  | [.agents/skills/context-updater/SKILL.md](.agents/skills/context-updater/SKILL.md)             |
| `documentar-avances`    | Proponer texto para Cambios recientes  | [.agents/skills/documentar-avances/SKILL.md](.agents/skills/documentar-avances/SKILL.md)       |

---

## Auto-invoke Skills

Al realizar estas acciones, SIEMPRE invocar la skill correspondiente PRIMERO:

| Acción                                | Skill                                                      |
| ------------------------------------- | ---------------------------------------------------------- |
| Crear/modificar controladores o rutas | `laravel-specialist`                                       |
| Crear/modificar modelos Eloquent      | `laravel-specialist`                                       |
| Diseñar nuevos endpoints API          | `api-design-principles`                                    |
| Crear migraciones de BD               | `mysql-best-practices` + **norma Migraciones** (ver abajo) |
| Optimizar queries o agregar índices   | `mysql-best-practices`                                     |
| Agregar autenticación o autorización  | `security`                                                 |
| Implementar validaciones de seguridad | `security-requirement-extraction`                          |
| Refactorizar código existente         | `architecture-patterns`                                    |
| Crear o modificar tests               | `test-driven-development`                                  |
| Debuggear un error                    | `systematic-debugging`                                     |
| Revisar código de un PR               | `code-review-excellence`                                   |
| Manejar errores y excepciones         | `error-handling-patterns`                                  |
| Implementar lógica de pagos           | `zonix-payments` (custom)                                  |
| Trabajar con estados/flujo de órdenes | `zonix-order-lifecycle` (custom)                           |
| Calcular distancias, rutas, o zonas   | `zonix-delivery-system` (custom)                           |
| Implementar eventos o broadcasting    | `zonix-realtime-events` (custom)                           |
| Crear endpoints o response format     | `zonix-api-patterns` (custom)                              |
| Optimizar queries SQL o usar EXPLAIN  | `sql-optimization-patterns`                                |
| Modificar views Blade o Bootstrap     | `frontend-design`                                          |
| Hacer git commit                      | `git-commit`                                               |
| Crear/modificar GitHub Actions CI/CD  | `github-actions-templates`                                 |
| Crear nuevas skills para el proyecto  | `skill-creator`                                            |
| Cerrar sesión con cambios relevantes  | `context-updater` (actualizar docs/active_context.md)      |
| Finalizar tarea y documentar avances  | `documentar-avances` (proponer Cambios recientes)          |

### Norma Migraciones (obligatoria)

- **NUNCA** crear migraciones tipo `add_*_to_*`, `change_*_table`, etc. para tablas que ya existen.
- **Tabla nueva** → una sola migración `create_*_table`.
- **Tabla existente que hay que actualizar** → **editar la migración create** de esa tabla (añadir o quitar columnas ahí). No crear una migración aparte "add" ni "change".
- Resumen: o se crea la tabla (create) o se actualiza su create; nada de add/change sueltos.

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

## Documentación detallada

Para no sobrecargar este archivo, el detalle por tema está en [docs/agents/](docs/agents/). Resumen:

- **Arquitectura:** MVC + Services; Controllers delgados, lógica en Services. Ver [docs/agents/architecture.md](docs/agents/architecture.md).
- **Code style:** snake_case/PascalCase, Controller/Service pattern, Form Requests, `with()`, `DB::transaction()`. Ver [docs/agents/code-style.md](docs/agents/code-style.md).
- **Testing:** `php artisan test`, patrón Feature + Sanctum. Ver [docs/agents/testing.md](docs/agents/testing.md).
- **API:** Response `success`/`data`/`message`, paginación obligatoria en listados. Ver [docs/agents/api-conventions.md](docs/agents/api-conventions.md).
- **Roles y auth:** 6 roles (users, commerce, delivery_company, delivery_agent, delivery, admin); Sanctum, middleware `role:`. Ver [docs/agents/roles-auth.md](docs/agents/roles-auth.md).
- **Tiempo real:** FCM + Pusher (NO WebSocket); eventos y canales privados. Ver [docs/agents/realtime.md](docs/agents/realtime.md).
- **Reglas de negocio:** Carrito uni-commerce, estados de orden, modelo de negocio, direcciones, penalizaciones. Ver [docs/agents/business-rules.md](docs/agents/business-rules.md).
- **Análisis exhaustivo:** Prompts y checklist v2.0. Ver [docs/agents/analysis.md](docs/agents/analysis.md).
- **Mejoras pendientes:** Paginación, índices BD, refactor God Classes. Ver [docs/agents/pending-improvements.md](docs/agents/pending-improvements.md).
- **Pagos por rol:** Quién configura métodos de pago, flujo del dinero, diagramas. Ver [docs/logica-pagos-por-rol.md](docs/logica-pagos-por-rol.md).
- **Plan módulo tarifa delivery:** Diseño futuro (config global base+km, CRUD zonas, cálculo en backend). Cuando se vaya a implementar, usar y refinar [docs/PLAN_MODULO_TARIFA_DELIVERY.md](docs/PLAN_MODULO_TARIFA_DELIVERY.md).
- **Teléfonos:** Tablas `phones` y `operator_codes`, dueño siempre `profile_id`, cómo cada rol obtiene el número. Ver [docs/LOGICA_MODULO_PHONE.md](docs/LOGICA_MODULO_PHONE.md).
- **Plan métodos de pago Venezuela:** Investigación completa de métodos de pago (pago móvil, Zelle, Binance Pay, TDC, C2P, PayPal, Stripe, etc.), regulación Sudeban/Sunacrip, comisiones, requisitos y fases de implementación. **Leer antes de tocar el enum `payment_methods` o agregar nuevos tipos.** Ver [docs/PLAN_METODOS_PAGO_VENEZUELA.md](docs/PLAN_METODOS_PAGO_VENEZUELA.md).
- **Requisitos para operar en Venezuela:** Todo lo legal, fiscal, laboral, sanitario, de datos personales, propiedad intelectual, seguros, infraestructura y checklist de lanzamiento para un marketplace de comida rápida en VE. Incluye caso Yummy/Sudeban, facturación digital SENIAT (obligatoria desde 19 mar 2026), Ley de Datos Personales 2025, modelo laboral delivery, y regulación derogada de Ipostel. Ver [docs/REQUISITOS_OPERAR_VENEZUELA.md](docs/REQUISITOS_OPERAR_VENEZUELA.md).

Índice completo: [docs/agents/README.md](docs/agents/README.md).

---

**Documentación completa de lógica de negocio:** Ver `README.md`
**Última actualización:** 31 Marzo 2026
