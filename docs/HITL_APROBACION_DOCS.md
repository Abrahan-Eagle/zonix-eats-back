# Gate HITL — Aprobación docs de negocio

> **Estado:** Fase **diseño de negocio** (Jun 2026) — modelo multi-fabricante documentado. **No programar** hasta OK founder en checklist + pendientes §11.

## Verificación documental (JARVIS)

- [x] **Auditoría plan vs docs** — PASS documental parcial, bloqueado §11 ([VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md), 2026-06-27)
- [x] Coherencia §8–§10 ↔ FLUJOS ↔ DOMINIO ↔ CADENA_SUMINISTRO *(§11 logística **abierto** — ver checklist abajo)*
- [x] Auditoría forense v2 — [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md), [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md), [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md)
- [x] Estados canónicos — [DOMINIO_DATOS.md](DOMINIO_DATOS.md) + alineación [FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md)
- [x] Handoff implementación post-HITL — [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md)

## Checklist founder

- [x] `PRODUCT_VISION.md` — red de fabricantes, pedidos parciales, orquestación
- [x] `LEAN_CANVAS.md` — costos multi-fab + multi-courier
- [x] `ROLES_Y_ACTORES.md` — fabricante (N), courier (N), ops orquestación
- [x] `FLUJOS_OPERATIVOS.md` — ramificación `lens_only` / `frame_only` / `lens_and_frame`; fulfillment multi-fabricante
- [x] `DECISIONES_FOUNDER.md` — §6 portabilidad; §8–§10 composición/cadena; §12–§14 frame_only/cambiaria/canal *(§11 pendiente firma)*
- [x] `POLITICA_COMERCIAL.md` — pricing por composición; disputas multi-tramo
- [x] `CADENA_SUMINISTRO.md` — matriz escenarios + tramos
- [x] `DOMINIO_DATOS.md` — entidades conceptuales Manufacturer, SupplierOrder, ShipmentLeg, Courier; Prescription nullable en `frame_only`
- [ ] `BRAND_ZONIX_GLASSES.md` — **aprobación founder** *(borrador puede existir; ≠ aprobado)*
- [ ] `PRIVACIDAD_OPTICA.md` — **revisión abogado VE** *(borrador puede existir; ≠ aprobado)*
- [ ] **Modelo multi-fabricante + couriers + pedidos parciales** — founder confirma §11 cerrado o acepta `[PENDIENTE]`
- [ ] **Seguridad feature 001** — revisar [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) antes de descongelar
- [ ] ~~Feature 001 backend implementado~~ — **no es criterio de cierre HITL** (ver nota abajo)

## Ítems `[PENDIENTE founder]` abiertos

### Negocio / logística (§11 — bloquean implementación)

Usar plantilla: [MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md)

1. Flete montura→lab (Zonix / paciente / fab montura)
2. Courier por tramo (Zonix unificado vs por fabricante)
3. SLA ~30 días (desde pago vs desde montura en lab)
4. Catálogo cross-fabricante (montura Fab A + lab Fab B)
5. Hub VE obligatorio o envío directo lab→paciente

### Otros (heredados)

- % comisión aliado definitivo (seed demo **10% ≠ decisión comercial**)
- Política cambiaria operativa (§13 documentada; anti-fraude `[PENDIENTE]`)
- DP obligatoria en B2C directo sin consulta presencial
- INCOTERM / aduanas por tramo
- Tabla pricing materiales
- Proveedor IA OCR/try-on

## Nota — código feature 001 (congelado)

Backend feature 001 (prescripciones) fue escrito **antes** del modelo ampliado multi-fabricante. Estado:

- **Experimental pre-pivot** — existe en repo pero **no cuenta como gate HITL** ni como “listo para producción”.
- **Congelado** — no extender migraciones/API hasta HITL + realineación + [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md).
- **No revertir** automáticamente; documentar divergencia y realinear en fase implementación post-HITL.
- `Prescription` en código asume siempre flujo con lentes; docs exigen **nullable** en `frame_only`.

Spec: banner en `specs/001-prescription-intake/spec.md`. Realineación: `specs/001-prescription-intake/REALIGNMENT_POST_HITL.md`.

## Tras aprobar (orden sugerido)

1. Founder cierra o acepta pendientes §11 + marca/legal
2. Marcar checklist arriba y fecha en `docs/active_context.md`
3. **Entonces** realinear spec 001 + backend con dominio ampliado
4. Front fase 1.1: pantallas partner/paciente (solo lentes)
5. Feature 002+: catálogo, `order_type`, fulfillment

## Matriz: decidido vs implementado

| Tema | Decidido en docs | Implementado en código 001 |
|------|------------------|----------------------------|
| Portabilidad §6 | ✅ | ❌ (`cascadeOnDelete` — ver AUDIT §4) |
| §11 logística | ❌ pendiente founder | N/A |
| §12–§14 negocio | ✅ / preliminar | N/A (post-002) |
| Seguridad 001 | Documentado AUDIT | ❌ 7 ítems abiertos |

## Nota IA

Gate HITL = **solo documentación de negocio**. La existencia de código experimental no sustituye aprobación founder del modelo multi-proveedor.

**Última actualización:** 2026-06-27
