# Dominio de datos (conceptual) — Zonix Glasses

> Nombres en inglés para código; labels UI en español.  
> **Fase actual:** diseño de negocio — entidades marcadas ✅ existen en código experimental (001); el resto es **conceptual** hasta HITL + realineación.

## Entidades prescripción / tenant (código experimental — congelado)

### OpticalPartner ✅ (experimental)

Óptica aliada o canal Zonix directo.

- `id`, `name`, `slug`, `tax_id` [PENDIENTE], `is_zonix_direct`, `commission_rate`, `status`

### OpticalPartnerUser ✅ (experimental)

Staff de óptica aliada.

- `user_id`, `optical_partner_id`

### PatientProfile ✅ (experimental)

- `profile_id`, `optical_partner_id`, `clinical_notes`, `created_by_user_id`

### Prescription ✅ (experimental)

Fórmula óptica — **solo pedidos con lentes** (`lens_only`, `lens_and_frame`).

- Campos clínicos: `od_*`, `oi_*`, `addition`, `pd`, `pd_near`
- `source`: `manual` | `patient_upload` | `ocr_ai`
- `status`: `draft` → … → `patient_confirmed` | `locked_for_order` | …
- **Nullable en `OpticalOrder` cuando `order_type = frame_only`**

## Entidades catálogo (conceptual)

### Manufacturer

Fabricante en la red (1..N).

- `id`, `name`, `code`, `country`
- `supplies_lenses` (bool), `supplies_frames` (bool)
- `contact_ops`, `status`
- Un fabricante puede ofrecer solo lentes, solo monturas, o ambos.

### LensMaterial

- `manufacturer_id`, `name`, `index`, `treatment`, `base_price`, `compatible_ranges` (JSON)

### Frame

- `manufacturer_id`, `sku`, `name`, `brand`, `price`, `images[]`, `dimensions`, `manufacturer_sku` (alias legacy: `supplier_sku`)
- `in_stock_ve` (bool) — montura en stock local VE (§12)
- `made_to_order` (bool) — contra pedido al fabricante
- `fulfillment_mode`: `stock_ve` | `manufacturer_mto` (derivado de flags)
- `stock_qty` [PENDIENTE ops] — cantidad disponible si `in_stock_ve`

### FaceCapture

- `patient_profile_id`, `images[]`, `analysis`, `retention_until`

## Entidades pedido y logística (conceptual)

### Cart / CartItem

- Ítems: `lens_line`, `frame_line` según `order_type`

### OpticalOrder

- `patient_profile_id`, `optical_partner_id`
- `order_type`: `lens_only` | `frame_only` | `lens_and_frame`
- `prescription_id` — **nullable** si `frame_only`
- `frame_id`, `lens_material_id` (según tipo)
- `status`, `total`, `payment_proof`
- `shipping_mode`: `home_delivery` | `partner_pickup`
- `shipping_address_id`

### SupplierOrder (1..N por OpticalOrder)

- `optical_order_id`, `manufacturer_id`
- `fulfillment_role`: `frame_shipment` | `lens_production` | `finished_goods`
- `spec_payload` (JSON), `status`, `paid_at`

### ShipmentLeg (1..N por SupplierOrder o por tramo global)

- `supplier_order_id` (nullable si tramo cross-order)
- `courier_id`, `from_party`, `to_party` (fabricante, lab, hub, paciente)
- `tracking_number`, `status`, `shipped_at`, `delivered_at`

### Courier

- `id`, `name`, `code`, `supports_international`, `supports_last_mile`

## Relaciones clave

```
Manufacturer 1─* LensMaterial
Manufacturer 1─* Frame
Manufacturer 1─* SupplierOrder

OpticalPartner 1─* PatientProfile
PatientProfile 1─* Prescription (opcional si solo montura en historial futuro)
PatientProfile 1─* OpticalOrder

OpticalOrder 1─* SupplierOrder
SupplierOrder 1─* ShipmentLeg
Courier 1─* ShipmentLeg

OpticalOrder *─0..1 Prescription  (nullable frame_only)
```

## Estados canónicos (nombres únicos — usar en docs y código futuro)

### Prescription.status

```
draft → pending_review → approved → patient_confirmed → locked_for_order
                              ↘ rejected / superseded
```

- **`patient_confirmed`:** mínimo para elegir material lente en catálogo.
- **`locked_for_order`:** se setea al **checkout** cuando la fórmula queda ligada a un `OpticalOrder` (aún no implementado en código 001).

### OpticalOrder.status (pago + fulfillment)

```
draft_cart
  → pending_payment_validation   ← nombre canónico (no usar pending_payment)
  → paid
  → awaiting_frame_shipment      (lens_and_frame, multi-fab)
  → awaiting_frame_at_lab
  → in_lens_production
  → shipped
  → in_customs
  → out_for_delivery
  → delivered
  ↘ cancelled / disputed
```

### SupplierOrder.status (conceptual)

`draft` → `sent_to_manufacturer` → `in_production` → `shipped` → `completed` / `cancelled`

### ShipmentLeg.status (conceptual)

`pending` → `picked_up` → `in_transit` → `delivered` / `exception`

## Reglas de negocio (dominio)

| order_type | prescription_id | SupplierOrders típicos |
|------------|-------------------|------------------------|
| `frame_only` | null | 1× frame_shipment → destino |
| `lens_only` | required | 1× lens_production → destino |
| `lens_and_frame`, mismo fab | required | 1× finished_goods |
| `lens_and_frame`, fab distintos | required | frame_shipment + lens_production (montura→lab) |

## Portabilidad historial

- El **paciente** (`PatientProfile`) pertenece a su **óptica aliada** actual (`optical_partner_id` = tenant de registro/atención).
- Las **fórmulas** (`Prescription`) pertenecen al paciente (FK `patient_profile_id`); el tenant del partner es contexto operativo, no propiedad que bloquee datos al paciente autenticado.
- **Cese de óptica aliada:** pacientes y fórmulas **se reasignan a Zonix** (canal directo / ops admin) — **no** `cascadeOnDelete` sobre datos clínicos.
- Recompra: paciente con cuenta puede usar fórmulas `patient_confirmed` / `locked_for_order` en cualquier canal autorizado.
- Detalle founder: [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §6.

## Scaffold reutilizable (futuro)

| Módulo actual | Uso Glasses |
|---------------|-------------|
| Auth + Profile | Paciente / partner users |
| Documents | Upload PDF/foto fórmula |
| Addresses | Entrega |
| PaymentMethod | Pago manual VE |
| Notifications | Estado pedido / tramos |

## Nota sobre código existente

Backend feature 001 (prescripciones) fue escrito **antes** de cerrar el modelo multi-fabricante. **Congelado** hasta HITL — ver `docs/HITL_APROBACION_DOCS.md` y `specs/001-prescription-intake/spec.md`.

**Última actualización:** 2026-06-27
