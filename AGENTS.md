# AGENTS.md - Zonix Glasses Backend (Laravel API)

> Instrucciones para agentes de IA en el backend de Zonix Glasses.
> Mantenimiento de skills: [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md).

> **Memoria viva:** [`docs/active_context.md`](docs/active_context.md) — leer al iniciar.

## Cambios recientes

- **2026-06-26:** Pivot óptica B2B2C — docs canon (`PRODUCT_VISION`, `MODELO_NEGOCIO/`, `BRAND`, `PRIVACIDAD`, `DOMINIO_DATOS`); JARVIS Paso C (manifest + sync); skills dominio v1.0; Spec Kit `001-prescription-intake`; rebrand Scaffold → Zonix Glasses.
- **2026-06-18:** Bootstrap inicial; skills globales por referencia.

---

## Project Overview

| Métrica | Valor |
| -------- | ----- |
| **Producto** | Zonix Glasses |
| **Framework** | Laravel 10.x / PHP 8.1+ |
| **Base de datos** | MySQL |
| **Frontend hermano** | `../zonix-glasses-front/` (Flutter, paquete `zonix_glasses`) |
| **Estado** | Bootstrap inicial — listo para desarrollo |
| **Agentes IA** | Cursor + skills globales JARVIS |

---

## Contexto entre sesiones

1. `.cursorrules`
2. `AGENTS.md`
3. `docs/active_context.md`
4. `docs/CONTEXTO_IA.md`

---

## Arquitectura (convenciones)

- Lógica de negocio en **Services**, no en Controllers
- API REST con envelope `{ success, data, message }`
- **Sanctum** + roles/middleware según producto
- Validación con **Form Requests**
- Operaciones críticas: **`DB::transaction()`**
- Listados: paginación obligatoria
- Eager loading: evitar N+1

### Migraciones (desarrollo local)

- Tabla nueva → una migración `create_*_table`
- Cambio en tabla aún no desplegada → editar la `create_*` existente
- Staging/producción → migraciones append-only; no reescribir history

---

## Collaboration Rules

1. **Preguntar** antes de cambios amplios
2. **No push/merge** sin orden explícita
3. **Usuario prueba primero** (`php artisan test`, endpoints manuales)
4. Commits solo cuando el usuario lo pida
5. **Skills de dominio:** prefijo `zonix-glasses-*` en `.agents/skills/`

---

## Git Workflow

`dev` → pruebas → `main` → producción

---

## Setup Commands

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
php artisan test
```

Variables: [docs/ENV_VARIABLES.md](docs/ENV_VARIABLES.md).

---

## Skills — Capas (Paso C activo)

Globales sincronizadas vía `.agents/skills/.global-sync-manifest`:

```bash
export JARVIS_SKILLS_LIBRARY=/var/www/html/proyectos/AIPP/jarvis-skills-library
./scripts/sync-global-skills-from-library.sh
./scripts/check-global-skills-sync.sh
python3 .agents/skills/sync.sh
```

Ver [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md) y [docs/ZONIX_GLASSES_JARVIS_INTEGRATION.md](docs/ZONIX_GLASSES_JARVIS_INTEGRATION.md).

Dominio exclusivo (no en manifest): `zonix-glasses-*`.

---

## Available Skills

<!-- SKILLS-START -->
<!-- SKILLS-END -->

---

## Auto-invoke Skills

<!-- AUTO-INVOKE-START -->
<!-- AUTO-INVOKE-END -->

---

## Repo hermano

Frontend: **`../zonix-glasses-front/AGENTS.md`**

Biblioteca global: **`/var/www/html/proyectos/AIPP/jarvis-skills-library/AGENTS.md`**
