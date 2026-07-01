# Feature 001 — Prescription Intake

> **⏸ CONGELADO (Jun 2026)** — Modelo de negocio **v3.1 documentado** (doc-vs-doc PASS). No implementar más hasta HITL **legal/marca + [AUDIT_RIESGOS_SEGURIDAD.md](../../docs/AUDIT_RIESGOS_SEGURIDAD.md)** + OK explícito founder.  
> **Alcance fórmula:** solo pedidos con lentes (`lens_only`, `lens_and_frame`); **`frame_only` no requiere Prescription**.  
> Código backend existente requiere **realineación post-HITL** — ver [REALIGNMENT_POST_HITL.md](REALIGNMENT_POST_HITL.md), `docs/HITL_APROBACION_DOCS.md` y `docs/active_context.md`.

**Status:** Código **experimental pre-pivot** (sesión previa); **no es gate HITL**. Congelado hasta realineación + remediación en [AUDIT_RIESGOS_SEGURIDAD.md](../../docs/AUDIT_RIESGOS_SEGURIDAD.md).

## Summary

Registro de paciente bajo óptica aliada + captura y **cadena de validación 3 capas** de fórmula óptica (manual, upload paciente, OCR borrador).

## User Stories

1. **Partner** registra paciente vinculado a su tenant.
2. **Partner** carga fórmula manualmente con validación de campos incl. **PD obligatorio** para aprobar.
3. **Partner** sube foto/PDF → OCR stub → `pending_review` → aprueba → `approved`.
4. **Paciente** ve historial de fórmulas propias.
5. **Paciente** confirma fórmula `approved` → `patient_confirmed`.
6. **Admin** audita fórmulas cross-tenant *(futuro — policy existe; endpoint admin no implementado)*.

## Out of Scope (001)

- Catálogo monturas, try-on, carrito, fulfillment.
- Pedidos `frame_only` (sin fórmula).
- Manufacturer, SupplierOrder, ShipmentLeg, multi-courier (dominio post-001).

## Acceptance Criteria

> **Estado congelado:** marcar `[x]` solo lo verificado **sin descongelar** producción. Seguridad: [AUDIT_RIESGOS_SEGURIDAD.md](../../docs/AUDIT_RIESGOS_SEGURIDAD.md).

- [x] Rol `optical_partner` en RBAC + middleware tenant *(código experimental)*
- [x] CRUD pacientes scoped a partner *(código experimental)*
- [x] Prescription estados: `draft` → `pending_review` → `approved` → `patient_confirmed` *(API 001)*
- [x] OCR endpoint opt-in (stub local; revisión humana obligatoria)
- [x] PD requerido para transición a `approved`
- [x] Tests Feature tenant isolation (6 escenarios prescripción; ~46 suite total)
- [ ] `locked_for_order` al checkout *(post OpticalOrder — spec 002+)*
- [ ] Remediación [AUDIT_RIESGOS_SEGURIDAD.md](../../docs/AUDIT_RIESGOS_SEGURIDAD.md) (7 ítems)
- [ ] Admin API cross-tenant audit (policy only; sin endpoint)
- [ ] Front: pantallas partner mínimas (fase 1.1)

## References

- `docs/MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md` (flujos 1–2)
- `docs/MODELO_NEGOCIO/DECISIONES_FOUNDER.md`
- `docs/DOMINIO_DATOS.md`
- [REALIGNMENT_POST_HITL.md](REALIGNMENT_POST_HITL.md)
- Skill `zonix-glasses-prescriptions`
