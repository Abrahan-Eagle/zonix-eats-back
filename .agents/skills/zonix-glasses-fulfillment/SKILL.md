---
name: zonix-glasses-fulfillment
description: >
  Orquestación multi-fabricante y multi-courier post-pago: SupplierOrder, ShipmentLeg, estados canónicos.
  Trigger: supplier, fulfillment, tracking, manufacturer, courier, multi-fab.
license: UNLICENSED
metadata:
  author: Zonix Glasses
  version: "2.0"
  scope: [domain]
  category: backend
  related-skills: [zonix-glasses-api-patterns, error-handling-patterns]
allowed-tools: [Read, Edit, Write, Glob, Grep, Bash]
---

# Zonix Glasses — Fulfillment (multi-fabricante)

> **Modelo canónico:** [CADENA_SUMINISTRO.md](../../../docs/MODELO_NEGOCIO/CADENA_SUMINISTRO.md) y [FLUJOS_OPERATIVOS.md](../../../docs/MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md).  
> **No usar** el flujo legacy "un proveedor China / sent_to_supplier único".

## Flujo post-pago

1. `OpticalOrder` → `paid` (desde `pending_payment_validation`).
2. Según `order_type`, generar **1..N** `SupplierOrder` con `fulfillment_role`:
   - `frame_shipment` — montura sola o tramo montura→lab
   - `lens_production` — lab de lentes
   - `finished_goods` — mismo fabricante lentes+montura
3. Cada tramo logístico → `ShipmentLeg` + `Courier` (tracking por tramo).
4. Fase 1: export email/PDF manual a fabricantes; fase 2: API proveedor.

## Estados canónicos

**OpticalOrder (pre-pago + fulfillment):** `draft_cart` → `pending_payment_validation` → `paid` → `awaiting_frame_shipment` → … → `delivered`

**SupplierOrder:** `draft` → `sent_to_manufacturer` → `in_production` → `shipped` → `completed`

Ver enum completo en [DOMINIO_DATOS.md](../../../docs/DOMINIO_DATOS.md).

## spec_payload (ejemplo — lens_and_frame multi-fab)

```json
{
  "order_type": "lens_and_frame",
  "prescription_id": 12,
  "prescription_status_required": "locked_for_order",
  "lens_material_code": "POLY_AR",
  "frame_sku": "ZG-FR-001",
  "frame_manufacturer_id": 1,
  "lens_manufacturer_id": 2,
  "shipping_mode": "home_delivery",
  "destination": { "country": "VE", "city": "Valencia" }
}
```

## SLA

Meta entrega ~30 días — **origen del reloj:** `sla_started_at` = inicio fabricación lentes en montura (`in_lens_production` / equivalente) — [CHECKLIST_FOUNDER_S11.md](../../../docs/MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) §11. Monitorear por fabricante/courier en admin.

## Logística

INCOTERM, aduanas y quién contrata cada courier: ver [CADENA_SUMINISTRO.md](../../../docs/MODELO_NEGOCIO/CADENA_SUMINISTRO.md) y [CHECKLIST_FOUNDER_S11.md](../../../docs/MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) §11 (cerrado founder 2026-06-27).
