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
| **Endpoints**            | 233+ rutas REST                                    |
| **Controladores**        | 54                                                 |
| **Modelos**              | 35                                                 |
| **Migraciones**          | 51                                                 |
| **Tests**                | 206+ pasaron ✅, 0 fallaron                        |
| **Seguridad**            | Sanctum + RBAC + Rate Limiting + Upload validation |
| **Última actualización** | 9 Marzo 2026                                      |

### Cambios recientes (documentar aquí los avances)

- **9 Mar 2026:** Módulo Exportar datos: ruta `GET /api/profile/export` (auth:sanctum, cualquier rol) para que commerce y otros roles puedan exportar; ExportController.getProfileDataForExport defensivo con `$profile` null (evita error en usuarios sin perfil buyer); frontend usa esa URL y descarga real (archivo JSON/TXT + Share.shareXFiles para guardar/compartir); formato TXT corregido (ciudad como nombre, activity_type en actividad).
- **6 Mar 2026:** Tests: MultiRoleSimulationTest corrige assert (API devuelve `data.status` → assertJsonPath); migración `add_context_and_entity_fks_to_phones_table` en `down()` evita dropForeign/dropIndex en SQLite para que `php artisan test` pase (MySQL sin cambios).
- **6 Mar 2026:** Norma Migraciones: documentada en `.cursorrules` y AGENTS.md. No crear migraciones add_* ni change_*; tablas existentes se actualizan editando la migración create correspondiente.
- **6 Mar 2026:** Módulo demo/seed: `operator_codes`: columna `code` como entero (migración create), `name` como string; OperatorCodeSeeder con 412, 414, 424, 416, 426. ZonixDemoSeeder: zonas Valencia/Carabobo (El Socorro, Los Chorritos, Mayorista, etc.), user 6 fijo (Wistremiro/commerce), direcciones y user_locations de users 1 y 6 en El Socorro; docblock con grafo de conexiones entre roles (buyer→orden→commerce→delivery_agent→delivery_company, reviews, disputes). Migraciones consolidadas (edición de creates, eliminación de add/change sobrantes).
- **6 Mar 2026:** Módulo Documents: solo tipos `ci` y `rif`; tabla depurada (migración elimina RECEIPT_N, sky, rif_url, commune_register, community_rif; enum type restringido a ci/rif). Campos útiles: number_ci, rif_number (formato Venezuela J-19217553-0), taxDomicile, front_image, approved, status. Estado aprobado: documento verificado o pendiente de verificación (campo `approved`). Tests: DocumentControllerTest.
- **6 Mar 2026:** Documentado en AGENTS.md: Profile como entidad principal; Users 1:1 con Profile; teléfonos/documentos/direcciones pertenecen al perfil (`profile_id`).
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
php artisan test                   # Todos (206+ tests)
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
| `documentar-avances`   | Proponer texto para Cambios recientes | [.agents/skills/documentar-avances/SKILL.md](.agents/skills/documentar-avances/SKILL.md)     |

---

## Auto-invoke Skills

Al realizar estas acciones, SIEMPRE invocar la skill correspondiente PRIMERO:

| Acción                                | Skill                             |
| ------------------------------------- | --------------------------------- |
| Crear/modificar controladores o rutas | `laravel-specialist`              |
| Crear/modificar modelos Eloquent      | `laravel-specialist`              |
| Diseñar nuevos endpoints API          | `api-design-principles`           |
| Crear migraciones de BD               | `mysql-best-practices` + **norma Migraciones** (ver abajo) |
| Optimizar queries o agregar índices   | `mysql-best-practices`            |
| Agregar autenticación o autorización  | `security`                        |
| Implementar validaciones de seguridad | `security-requirement-extraction` |
| Refactorizar código existente         | `architecture-patterns`           |
| Crear o modificar tests               | `test-driven-development`         |
| Debuggear un error                    | `systematic-debugging`            |
| Revisar código de un PR               | `code-review-excellence`          |
| Manejar errores y excepciones         | `error-handling-patterns`         |
| Implementar lógica de pagos           | `zonix-payments` (custom)         |
| Trabajar con estados/flujo de órdenes | `zonix-order-lifecycle` (custom)  |
| Calcular distancias, rutas, o zonas   | `zonix-delivery-system` (custom)  |
| Implementar eventos o broadcasting    | `zonix-realtime-events` (custom)  |
| Crear endpoints o response format     | `zonix-api-patterns` (custom)     |
| Optimizar queries SQL o usar EXPLAIN  | `sql-optimization-patterns`       |
| Modificar views Blade o Bootstrap     | `frontend-design`                 |
| Hacer git commit                      | `git-commit`                      |
| Crear/modificar GitHub Actions CI/CD  | `github-actions-templates`        |
| Crear nuevas skills para el proyecto  | `skill-creator`                   |
| Cerrar sesión con cambios relevantes  | `context-updater` (actualizar docs/active_context.md) |
| Finalizar tarea y documentar avances | `documentar-avances` (proponer Cambios recientes)     |

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

Índice completo: [docs/agents/README.md](docs/agents/README.md).

---

**Documentación completa de lógica de negocio:** Ver `README.md`
**Última actualización:** 9 Marzo 2026
