# Checklist founder — §11 logística + cierre HITL

> **§11 cerrado** founder v3 (2026-06-27). Actualizar [HITL_APROBACION_DOCS.md](../HITL_APROBACION_DOCS.md) al marcar ítems restantes.

**Verificación documental:** PASS documental v3 negocio — §11 cerrado; pendientes legales/marca.

**Auditoría forense v2:** [AUDIT_FORENSE_2026-06-27.md](../AUDIT_FORENSE_2026-06-27.md) — re-auditar tras v3 opcional.

---

## Decisiones founder v3 (Jun 2026)

| § | Tema | Decidido docs | Código / ops |
|---|------|---------------|--------------|
| 3 | Pagos directo 4.1/4.2; aliado 100% mayor | ✅ | ❌ post-realineación |
| 4 | Pricing mayorista (no comisión %) | ✅ | ❌ deprecar commission_rate |
| 6 | Portabilidad; cese aliado culmina pedidos | ✅ | ❌ cascade delete 001 |
| 8 | `lens_only` no MVP | ✅ | N/A |
| 9–10 | Delivery fab / courier Zonix / delivery VE | ✅ | ❌ post-002 |
| 12 | `frame_only` mixo; mismo mayor stock/MTO | ✅ | ❌ post-002 |
| 13 | FX Binance día de cada pago | ✅ | ❌ conciliación manual MVP doc ✅ |
| 14 | Mayorista activo + proteger aliado | ✅ | ❌ contrato `[PENDIENTE abogado]` |
| 15 | Checkout dinámico + IVA incluido + envío gratis + multa config | ✅ v3.1 | ❌ post-realineación |

---

## v3.1 — Checkout y fiscal (cerrado founder)

| Tema | Decisión | Fecha |
|------|----------|-------|
| Desglose checkout | Líneas sumando: montura, lentes/lab, courier, delivery VE → total | 2026-06-27 |
| IVA | Incluido en PVP; desglose informativo **16%** | 2026-06-27 |
| Envío gratis | Configurable por zona/campaña (`ShippingZoneRule`) | 2026-06-27 |
| Multa retiro tardío | `hub_late_pickup_fee_percent` default **10%** sobre total pedido | 2026-06-27 |

## §11 — Logística (cerrado v3)

> Canon completo: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §11. Resumen HITL: flete montura→lab (fab montura) · courier mixto · SLA desde inicio fab lentes · cross-fab sin homologación · hub VE obligatorio — cerrado 2026-06-27.

---

## Pendientes v3 (no bloquean doc negocio)

| Ítem | Responsable | Estado |
|------|-------------|--------|
| Multa tardía — 10% default, variable admin | Founder | ✅ v3.1 |
| Capa (a) operativa — local optometrista; online ops sin optometrista Zonix | Founder | ✅ v3.1 |
| DP presencial obligatoria B2C directo (fase 1) | Founder | ✅ online; presencial Zonix **opcional** — §2 |
| Checkout dinámico + envío gratis configurable | Founder | ✅ v3.1 |
| IVA 16% informativo | Founder | ✅ §16; factura SENIAT `[PENDIENTE contador]` |
| Retención 30d + forfeiture 30% + multa en T&C | Abogado VE | `[PENDIENTE abogado]` |
| Anti-fraude comprobante | Founder/ops | ✅ conciliación manual MVP — §16 |
| INCOTERM / aduanas | Founder | ✅ courier incluido — §16 |
| Panel aliado + try-on detalle producto | Founder | ✅ §17 |
| Vigencia fórmula 12 meses | Founder | ✅ §16 |
| Marca BRAND | Founder | ✅ 2026-06-27 |

---

## HITL — Marca y legal

| Ítem | Responsable | Decisión | Fecha |
|------|-------------|----------|-------|
| [x] `BRAND_ZONIX_GLASSES.md` | Founder visual | Aprobado app/web | 2026-06-27 |
| [ ] `PRIVACIDAD_OPTICA.md` | Abogado | _____________ | ____/____/____ |
| [x] Modelo v3.1 checkout + multa config + envío gratis | Founder | Docs actualizados Jun 2026 | 2026-06-27 |

---

## Tras firmar legal/marca

1. Marcar checklist en [HITL_APROBACION_DOCS.md](../HITL_APROBACION_DOCS.md).
2. Actualizar [active_context.md](../active_context.md).
3. Ejecutar [REALIGNMENT_POST_HITL.md](../../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) — solo con OK explícito de programación.

**Última actualización:** 2026-06-27 (§16 founder)
