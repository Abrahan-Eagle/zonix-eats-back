# Realineación post-HITL — Feature 001 + dominio multi-proveedor

> **No ejecutar código** hasta cierre HITL **legal/marca + seguridad 001** + OK explícito founder.  
> **§11 logística:** cerrado founder 2026-06-27 — [CHECKLIST_FOUNDER_S11.md](../../docs/MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md).  
> Este documento define **qué cambiar** en backend/spec 001 para alinear con [DOMINIO_DATOS.md](../../docs/DOMINIO_DATOS.md).

## Objetivo

Mantener el núcleo de prescripción (a+b+c, tenant partner) y extender el dominio para `order_type`, fabricantes y logística — **sin borrar** el código experimental actual de golpe.

## Fase A — Spec 001 (prescripción, acotada)

| Cambio | Detalle |
|--------|---------|
| Alcance explícito | API prescripción solo cuando pedido futuro incluye lentes |
| Descongelar banner | Tras HITL; mantener out-of-scope fulfillment |
| Front 1.1 | Pantallas partner/paciente fórmulas |

**Sin cambio de contrato** salvo documentar que `POST /orders` con `frame_only` no exige `prescription_id`.

## Fase B — Dominio pedido (nueva spec 002 recomendada)

Entidades según [DOMINIO_DATOS.md](../../docs/DOMINIO_DATOS.md):

```
Manufacturer → LensMaterial, Frame
OpticalOrder (order_type, prescription_id nullable)
SupplierOrder (1..N, fulfillment_role)
ShipmentLeg + Courier
```

Migraciones **nuevas** (no editar las de 001 salvo política repo acordada).

## Fase C — Realineación código 001 existente

| Área | Estado actual | Target |
|------|---------------|--------|
| `prescriptions` table | Campos clínicos base | **Migración:** `prescribed_at`, `valid_until`; cifrado columnas sensibles |
| `locked_for_order` | Constante sin transición | Servicio checkout post OpticalOrder |
| Storage clínico | Document legacy público | Nuevo tipo blob + disco privado |
| Validación checkout | N/A en 001 | Policy: prescription required iff `order_type` in (`lens_only`, `lens_and_frame`) |
| Tests | ~46 suite total / **6** prescripción (`PrescriptionIntakeTest`) | Añadir casos `frame_only` sin prescription cuando exista OpticalOrder |
| FK tenant / portabilidad | `cascadeOnDelete` en patient/prescription ↔ partner | **Target:** job admin: partner `inactive` → reassign a `is_zonix_direct`; `nullOnDelete` — ver [DECISIONES_FOUNDER.md](../../docs/MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §6 |

## Fase D — Fulfillment (spec 003+)

- Generar SupplierOrders al `paid`
- Tramo montura→lab (6C en [FLUJOS_OPERATIVOS.md](../../docs/MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md))
- Tracking ShipmentLeg por courier

## Orden de implementación sugerido

1. HITL founder §11 cerrado
2. Spec 002 `order-type-catalog` (enum + OpticalOrder stub)
3. Descongelar 001 + front fórmulas
4. Spec 003 `manufacturer-supplier-orders`
5. Spec 004 `shipment-legs-couriers`

## Referencias

- [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](../../docs/VERIFICACION_MODELO_MULTI_PROVEEDOR.md)
- [HITL_APROBACION_DOCS.md](../../docs/HITL_APROBACION_DOCS.md)
- [CADENA_SUMINISTRO.md](../../docs/MODELO_NEGOCIO/CADENA_SUMINISTRO.md)
- [AUDIT_FORENSE_2026-06-27.md](../../docs/AUDIT_FORENSE_2026-06-27.md)

**Última actualización:** 2026-06-27
