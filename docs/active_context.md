# Contexto activo — Zonix Glasses Backend

> Leer al iniciar. Actualizar al cerrar sesiones relevantes.

## Estado actual (2026-06-27)

### Fase del proyecto

- **Diseño de negocio** — modelo multi-fabricante documentado; **código congelado** hasta HITL founder + seguridad 001.
- **No programar** nuevas migraciones/API/front hasta cerrar pendientes §11 + gate HITL + [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md).

### Pivot producto

- **Negocio:** óptica online B2B2C — orquestador de cadena óptica multi-proveedor (N fabricantes, N couriers).
- **Pedidos:** `lens_only` | `frame_only` | `lens_and_frame` — montura sola **sin fórmula**; `frame_only` **mixto** (stock top + contra pedido — §12).
- **Cadena:** montura (Fab A) → lab lentes (Fab B) → producto terminado → envío final.
- **No es:** smart glasses / BLE / OTA.

### Decisiones founder (documentadas)

- Validación fórmula **3 capas (a+b+c)** — solo si hay lentes.
- §6 portabilidad: paciente bajo aliado; al cesar aliado → **pasa a Zonix** (no cascade delete).
- §8–§11 composición, cadena, couriers — §11 logística **pendiente firma**.
- §12 `frame_only` mixto · §13 cambiaria Bs/Binance/USDT · §14 proteger aliado + mayorista futuro.
- Canon: [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md), [CADENA_SUMINISTRO.md](MODELO_NEGOCIO/CADENA_SUMINISTRO.md).

### Feature 001 — Prescription Intake ⏸ congelado

- **Experimental pre-pivot** — no es gate HITL.
- Código diverge del modelo ampliado; spec con banner de congelamiento.
- Tests: **~46 suite total / 6 prescripción** — no re-ejecutar como gate hasta realineación.
- Seguridad: [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) — **bloqueante** pre-descongelar.

### Verificación docs multi-proveedor

- Informe: [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md) — **PASS documental parcial**, bloqueado §11.
- **Auditoría forense v2:** [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) — 9 CRITICAL doc corregidos; P1 negocio (FX, canal aliado, unit economics, 6A-stock/MTO).
- Mejoras negocio: [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md).
- Checklist founder §11: [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) — **pendiente firma**.
- Realineación código (solo tras HITL): [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md).

### Docs negocio ampliados (forense v2)

- [POLITICA_CANAL_ALIADO.md](MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md) · [UNIT_ECONOMICS.md](MODELO_NEGOCIO/UNIT_ECONOMICS.md)
- [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) — Pagos VE + FX §13 · disputas stock
- [FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md) — ramas 6A-stock / 6A-MTO

### HITL

- Docs negocio multi-proveedor + corrección forense — ver [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md).
- Pendiente founder: §11 logística, marca visual, legal VE, revisión seguridad 001.

### JARVIS

- Manifest + sync scripts activos.
- Skills fulfillment/api-patterns alineados a multi-fab (sin China legacy).

### Git / infra

- Remote git aún `zonix-eats-back` — ver [CLONE_CHECKLIST.md](CLONE_CHECKLIST.md).

## Próximos pasos

1. Founder completa [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) y marca HITL
2. Revisar [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) antes de descongelar 001
3. OK explícito → ejecutar [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md)
4. Front fase 1.1 tras backend alineado

---

**Última actualización:** 2026-06-27 (cierre forense v2 — docs only)
