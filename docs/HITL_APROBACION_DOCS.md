# Gate HITL — Aprobación docs de negocio

> **Estado:** Fase **diseño de negocio v3.1** (Jun 2026) — checkout dinámico, multa config, envío gratis documentados.

## Verificación documental (JARVIS)

- [x] **Auditoría plan vs docs** — PASS v3.1 ([VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md))
- [x] Coherencia v3.1 ↔ FLUJOS ↔ DOMINIO ↔ CADENA ↔ POLITICA_COMERCIAL
- [x] Auditoría forense v2 + **ronda v3.1 doc-vs-doc** — [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) §5
- [x] Estados canónicos v3 — [DOMINIO_DATOS.md](DOMINIO_DATOS.md) + [FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md)
- [x] **§11 logística cerrado** — [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md)
- [x] **Repaso subagentes 2026-06-27** — [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) §6 (R1–R12)
- [x] **Auditoría integral docs/** — [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) §7 (dedupe + P0)
- [ ] Handoff implementación — [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) *(documentado; gate humano: legal/marca + AUDIT + OK founder)*

## Checklist founder — docs negocio v3

- [x] `DECISIONES_FOUNDER.md` — v3.1 §15 checkout, multa `hub_late_pickup_fee_percent`
- [x] `POLITICA_COMERCIAL.md` — desglose dinámico, IVA incluido, envío gratis, multa 10% config
- [x] `POLITICA_CANAL_ALIADO.md` — mayor por SKU; prepago Zonix; cese culmina pedidos
- [x] `CADENA_SUMINISTRO.md` — delivery fab vs courier Zonix vs delivery VE
- [x] `ROLES_Y_ACTORES.md` — fabricante login MVP; deliveries registrados
- [x] `FLUJOS_OPERATIVOS.md` — estados pago v3; hub; retención 4.2
- [x] `UNIT_ECONOMICS.md` — sin comisión %; caja 30% vs 100% mayor
- [x] `DOMINIO_DATOS.md` — `OrderLineItem`, `ShippingZoneRule`, `PlatformConfig`
- [x] `PRODUCT_VISION.md` — MVP order types; fab login MVP; §11 cerrado
- [x] `LEAN_CANVAS.md` — mayorista (no comisión %); lens_only post-MVP
- [x] `VERIFICACION_MODELO_MULTI_PROVEEDOR.md` — PASS v3.1
- [x] Skills `zonix-glasses-partners` / `zonix-glasses-fulfillment` — alineados v3.1
- [x] `BRAND_ZONIX_GLASSES.md` — **aprobado founder** 2026-06-27 (§16)
- [ ] `PRIVACIDAD_OPTICA.md` — **revisión abogado VE**
- [ ] **Retención hub + multa 10%** — redacción T&C `[PENDIENTE abogado VE]`
- [ ] **Seguridad feature 001** — [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) antes de descongelar
- [ ] ~~Feature 001 implementado~~ — **no es criterio HITL**

## Ítems `[PENDIENTE founder]` abiertos (post v3.1 + §16)

| Tema | Doc | Estado |
|------|-----|--------|
| Factura SENIAT (IVA 16% incluido) | DECISIONES §15 · POLITICA_COMERCIAL | `[PENDIENTE contador]` |
| Tabla pricing materiales (cifras) | UNIT_ECONOMICS | `[PENDIENTE founder]` |
| Proveedor IA OCR/try-on | PRODUCT_VISION | `[PENDIENTE founder]` |

**Cerrados founder 2026-06-27 (§16–§17):** B2C presencial opcional · IVA 16% · aduanas courier incluido · vigencia 12 meses · anti-fraude manual MVP · panel aliado · stock hub/aliado · try-on **detalle producto** (rostro+montura + galería catálogo + ficha técnica) · marca BRAND.

## Nota — código feature 001 (congelado)

- Experimental pre-pivot — **no gate HITL**.
- Realinear tras HITL legal/marca + seguridad 001.
- Deprecar `commission_rate` como eje comercial; introducir mayor por SKU.

## Matriz: decidido vs implementado (v3)

| Tema | Decidido en docs v3 | Implementado código 001 |
|------|---------------------|-------------------------|
| Pagos 4.1/4.2 directo | ✅ | ❌ solo prepago 100% |
| Aliado 100% mayor | ✅ | ❌ commission_rate |
| §11 logística | ✅ cerrado | N/A |
| Fabricante login | ✅ MVP doc | ❌ manual fase 1 |
| Portabilidad §6 | ✅ | ❌ cascadeOnDelete |
| Checkout dinámico + OrderLineItem | ✅ v3.1 | ❌ |
| Multa config 10% | ✅ v3.1 | ❌ |
| Envío gratis por zona | ✅ v3.1 | ❌ |
| Seguridad 001 | Documentado | ❌ 7 ítems abiertos |

## Tras aprobar legal/marca (orden sugerido)

1. Founder cierra pendientes v3 + abogado retención/T&C
2. Marcar checklist arriba + [active_context.md](active_context.md)
3. Realinear spec 001 + backend
4. Front: partner + paciente + fabricante MVP
5. Feature 002+: fulfillment hub

**Última actualización:** 2026-06-27 (§16 founder cerrado; pendiente legal/contador/pricing)
