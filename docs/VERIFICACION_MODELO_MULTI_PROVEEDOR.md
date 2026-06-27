# Verificación — Modelo multi-proveedor (solo docs)

> Auditoría de coherencia entre el plan *Modelo multi-proveedor* (Jun 2026) y la documentación en repo.  
> **Fecha verificación:** 2026-06-27  
> **Resultado:** **PASS documental parcial** — entregables presentes; **bloqueado por §11** (HITL founder) y pendientes marca/legal.

## Checklist plan vs archivos

| # | Entregable | Archivo | Criterio | Resultado |
|---|------------|---------|----------|-----------|
| 1 | §8–§10 decisiones + tabla §11 | [MODELO_NEGOCIO/DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) | `order_type`, cadena frame→lab, couriers; §11 = pendientes | **PARCIAL** (§11 sin firmar) |
| 2 | Visión multi-fab | [PRODUCT_VISION.md](PRODUCT_VISION.md) | Sin “un proveedor China”; pedidos parciales | OK |
| 3 | Flujos ramificados | [MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md) | Flujo 0, 6A–6D, estados conceptuales | OK |
| 4 | Dominio ampliado | [DOMINIO_DATOS.md](DOMINIO_DATOS.md) | Manufacturer, SupplierOrder, ShipmentLeg, Courier; Prescription nullable | OK |
| 5 | Cadena suministro | [MODELO_NEGOCIO/CADENA_SUMINISTRO.md](MODELO_NEGOCIO/CADENA_SUMINISTRO.md) | Matriz, diagramas, checklist ops | OK |
| 6 | Roles | [MODELO_NEGOCIO/ROLES_Y_ACTORES.md](MODELO_NEGOCIO/ROLES_Y_ACTORES.md) | Fabricante (N), Courier (N), ops | OK |
| 7 | Política comercial | [MODELO_NEGOCIO/POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) | Pricing por composición; disputas multi-tramo | OK |
| 8 | Lean Canvas | [MODELO_NEGOCIO/LEAN_CANVAS.md](MODELO_NEGOCIO/LEAN_CANVAS.md) | Costos multi-fab + multi-courier | OK |
| 9 | Gate HITL | [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md) | Multi-fab en checklist; código 001 no es gate | OK |
| 10 | Memoria sesión | [active_context.md](active_context.md) + front espejo | Fase diseño; código congelado | OK |
| 11 | Spec 001 banner | [specs/001-prescription-intake/spec.md](../specs/001-prescription-intake/spec.md) | CONGELADO; fórmula solo con lentes | OK |

## Coherencia cruzada (muestreo)

| Concepto | DECISIONES | FLUJOS | DOMINIO | CADENA |
|----------|------------|--------|---------|--------|
| `frame_only` sin fórmula | §8 | Flujo 0, 4, 6A | prescription nullable | Matriz fila frame_only |
| Montura → lab | §9 | Flujo 6C | SupplierOrder roles | Diagrama bothMulti |
| Tracking por tramo | §10 | Flujo 6 | ShipmentLeg | Tramos logísticos |

## Divergencias conocidas (esperadas)

| Área | Docs | Código feature 001 | Acción |
|------|------|-------------------|--------|
| Prescription en pedido | Nullable en `frame_only` | Siempre en flujo prescripción | Realinear post-HITL — ver [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) |
| Fulfillment | SupplierOrder N, ShipmentLeg | No implementado | Feature 002+ post-HITL |
| order_type | Enum en OpticalOrder | No existe | Migración post-HITL |

## Pendiente humano (bloquea implementación)

- Founder: cerrar §11 — [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md)
- Marca: `BRAND_ZONIX_GLASSES.md` *(borrador puede existir; falta aprobación founder)*
- Legal: `PRIVACIDAD_OPTICA.md` *(borrador puede existir; falta revisión abogado)*

## Auditoría forense posterior (2026-06-27)

- Ronda v1: [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md), [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md)
- Ronda v2 (juez orquestador): [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md)

## Alcance excluido (correcto)

- Sin migraciones, controllers ni Flutter en esta fase
- Sin revertir código 001
- Sin editar `.cursor/plans/`

**Verificado por:** JARVIS (auditoría documental)  
**Próximo gate:** HITL founder §11
