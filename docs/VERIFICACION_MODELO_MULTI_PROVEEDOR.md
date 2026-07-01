# Verificación — Modelo multi-proveedor (solo docs)

> Auditoría de coherencia entre el plan *Modelo multi-proveedor* (Jun 2026) y la documentación en repo.  
> **Fecha verificación:** 2026-06-27 (refresh v3.1)  
> **Resultado:** **PASS v3.1** — docs negocio alineados; pendientes legal/marca/seguridad 001 (no bloquean coherencia doc-vs-doc).

## Checklist plan vs archivos

| # | Entregable | Archivo | Criterio | Resultado |
|---|------------|---------|----------|-----------|
| 1 | §8–§10 decisiones + §11 | [MODELO_NEGOCIO/DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) | `order_type`, cadena frame→lab, couriers; §11 cerrado | **OK** |
| 2 | Visión multi-fab | [PRODUCT_VISION.md](PRODUCT_VISION.md) | Sin “un proveedor China”; MVP order types | **OK** (post-remediación v3.1) |
| 3 | Flujos ramificados | [MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md) | Flujo 0, 6A–6C, estados conceptuales | **OK** |
| 4 | Dominio ampliado | [DOMINIO_DATOS.md](DOMINIO_DATOS.md) | Manufacturer, SupplierOrder, ShipmentLeg, OrderLineItem | **OK** |
| 5 | Cadena suministro | [MODELO_NEGOCIO/CADENA_SUMINISTRO.md](MODELO_NEGOCIO/CADENA_SUMINISTRO.md) | Matriz, diagramas, checklist ops | **OK** |
| 6 | Roles | [MODELO_NEGOCIO/ROLES_Y_ACTORES.md](MODELO_NEGOCIO/ROLES_Y_ACTORES.md) | Fabricante (N), Courier (N), ops | **OK** |
| 7 | Política comercial | [MODELO_NEGOCIO/POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) | Checkout v3.1, pagos 4.1/4.2, disputas | **OK** |
| 8 | Lean Canvas | [MODELO_NEGOCIO/LEAN_CANVAS.md](MODELO_NEGOCIO/LEAN_CANVAS.md) | Mayorista B2B2C (no comisión %) | **OK** (post-remediación v3.1) |
| 9 | Gate HITL | [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md) | Multi-fab + v3.1; código 001 no es gate | **OK** |
| 10 | Memoria sesión | [active_context.md](active_context.md) + front espejo | Fase diseño v3.1; código congelado | **OK** |
| 11 | Spec 001 banner | [specs/001-prescription-intake/spec.md](../specs/001-prescription-intake/spec.md) | CONGELADO; fórmula solo con lentes | **OK** |

## Coherencia cruzada (muestreo)

| Concepto | DECISIONES | FLUJOS | DOMINIO | CADENA |
|----------|------------|--------|---------|--------|
| `frame_only` sin fórmula | §8 | Flujo 0, 4, 6A | prescription nullable | Matriz fila frame_only |
| Montura → lab | §9 | Flujo 6C | SupplierOrder roles | Diagrama bothMulti |
| Tracking por tramo | §10 | Flujo 6 | ShipmentLeg | Tramos logísticos |
| Checkout OrderLineItem | §15 | Flujo 5 | OrderLineItem | — |
| `frame_only` stock VE | §12 | 6A-stock | 0 SupplierOrder | Stock pick/pack hub |
| Canal aliado checkout | §4 | Flujo 5 pago aliado | partner_wholesale (mayor SKU) | — |
| `iva_included_amount` | §15 | Flujo 5 | tabla agregados L133-140 | — |
| §11 SLA | §11 | Flujo 6 | sla_started_at | CHECKLIST §11 |

## Divergencias conocidas (esperadas)

| Área | Docs | Código feature 001 | Acción |
|------|------|-------------------|--------|
| Prescription en pedido | Nullable en `frame_only` | Siempre en flujo prescripción | Realinear post-HITL — ver [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) |
| Fulfillment | SupplierOrder N, ShipmentLeg | No implementado | Feature 002+ post-HITL |
| order_type / checkout | OrderLineItem, 4.2 | No existe | Migración post-HITL |
| Pagos | 4.1/4.2, mayor aliado | prepago 100%, commission_rate | Realinear post-HITL |

## Pendiente humano (bloquea implementación, no docs negocio)

- Legal: IVA SENIAT, retención hub T&C, `PRIVACIDAD_OPTICA.md`
- Marca: `BRAND_ZONIX_GLASSES.md` — aprobación founder
- Seguridad: [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) — descongelar 001

## §11 logística

**Cerrado founder 2026-06-27** — [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) §11. SLA ~30 días desde inicio fabricación lentes en montura; hub VE obligatorio.

## Auditoría forense

- Ronda v2: [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md)
- Ronda v3.1 (doc-vs-doc): misma fecha — §5–§7
- Auditoría integral dedupe: §7 mismo doc

## Alcance excluido (correcto)

- Sin migraciones, controllers ni Flutter en esta fase
- Sin revertir código 001
- Sin editar `.cursor/plans/`

**Verificado por:** JARVIS (auditoría documental v3.1)  
**Próximo gate:** HITL legal/marca + seguridad 001
