# Zonix Glasses Constitution

> Hub SDD: `zonix-glasses-back` (`specs/`, `.specify/`).  
> Front espejo: `../zonix-glasses-front` — rutas `front:lib/...` en tasks.

**Version**: 1.0.0 | **Ratified**: 2026-06-26 | **Last Amended**: 2026-06-26

---

## I. Collaboration (NON-NEGOTIABLE)

1. User leads — OK explícito antes de `/speckit-implement` o migraciones dominio.
2. No push/merge sin orden.
3. Leer `docs/active_context.md` y `docs/HITL_APROBACION_DOCS.md` al retomar.
4. Skills dominio `zonix-glasses-*` obligatorias en implementación.

---

## II. Dual Repository

| Repo | Rol |
|------|-----|
| zonix-glasses-back | API hub, specs, Laravel 10, MySQL, Sanctum |
| zonix-glasses-front | Flutter `zonix_glasses` |

Specs en `specs/00N-*/`. Contrato: `zonix-glasses-api-patterns`.

---

## III. Backend

- Services, Form Requests, envelope JSON, paginación, `DB::transaction()` en checkout.
- Multi-tenant: `optical_partner_id` en policies.
- Migraciones: editar `create_*` en local; append-only en prod.
- Datos sensibles: fórmulas y fotos faciales — storage privado.

### Roles MVP

`user` (paciente), `optical_partner`, `admin`.

---

## IV. Frontend

- `AppConfig.apiUrl`, `AuthHelper`, Provider.
- Brand: `docs/BRAND_ZONIX_GLASSES.md`
- Consentimiento try-on antes de captura facial.

---

## V. Quality Gates

| Repo | Comando |
|------|---------|
| Backend | `php artisan test` |
| Front | `flutter analyze && flutter test` |
| Skills | `./scripts/check-global-skills-sync.sh` |

---

## VI. Domain

Producto = **óptica online B2B2C contra pedido**, no smart glasses.

Canon: `docs/PRODUCT_VISION.md`, `docs/DOMINIO_DATOS.md`.
