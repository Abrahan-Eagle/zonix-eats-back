# Dominio de datos (conceptual) — Zonix Glasses

> Nombres en inglés para código; labels UI en español.  
> **Fase actual:** diseño de negocio — entidades marcadas ✅ existen en código experimental (001); el resto es **conceptual** hasta HITL + realineación.

## Entidades prescripción / tenant (código experimental — congelado)

### OpticalPartner ✅ (experimental)

Óptica aliada o canal Zonix directo.

- `id`, `name`, `slug`, `tax_id` [PENDIENTE], `is_zonix_direct`, `status`
- **Pricing v3:** mayor por SKU vía tabla `PartnerWholesalePrice` (conceptual) — **no** `commission_rate` como eje comercial
- `commission_rate` en código legacy — **deprecar**; seed demo no es decisión comercial

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

Fabricante en la red (1..N) — **login MVP** (v3).

- `id`, `name`, `code`, `country`
- `supplies_lenses` (bool), `supplies_frames` (bool)
- `zonix_agreement_active` (bool) — requerido para tramos inter-fab
- `wholesale_price` por SKU (negociado con Zonix; cambios con OK Zonix)
- `contact_ops`, `status`
- Un fabricante puede ofrecer solo lentes, solo monturas, o ambos.

### ManufacturerUser (conceptual)

Staff fabricante con login.

- `user_id`, `manufacturer_id`

### PartnerWholesalePrice (conceptual)

Precio mayor Zonix → óptica aliada por SKU.

- `optical_partner_id`, `frame_id` | `lens_material_id` | `bundle_id`, `wholesale_usd`

### DeliveryProvider (conceptual)

Empresa de delivery — China (fab) o VE (última milla).

- `id`, `name`, `code`, `country`, `owner_type`: `manufacturer` | `zonix` | `third_party`
- `manufacturer_id` (nullable), `supports_inter_fab`, `supports_handoff_courier`

### LensMaterial

- `manufacturer_id`, `name`, `index`, `treatment`, `base_price`, `compatible_ranges` (JSON)

### Frame

Montura en catálogo marketplace web + PDP (§17). **Checklist campos obligatorios al alta:** [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §17 → *Checklist alta montura*.

- `manufacturer_id`, `sku`, `name`, `brand`, `price`, `manufacturer_sku` (alias legacy: `supplier_sku`)
- `catalog_images[]` — **fotos normales de producto** (sin rostro); visibles en marketplace y galería del detalle
- `images[]` — alias legacy → migrar a `catalog_images[]` en implementación
- `dimensions` — medidas del armazón (ancho, puente, patilla, altura lente, etc.)
- `material`, `color`, `shape`, `gender` [opcional catálogo]
- `size_label` — talla comercial (ej. 52-18-140)
- `measurement_guide` — texto/HTML «cómo medir» para el paciente
- `specs` (JSON) — resto de atributos para ficha técnica en detalle producto
- Try-on en detalle: render **rostro paciente + montura** (desde `FaceCapture` + asset montura); no reemplaza `catalog_images[]`
- `in_stock_ve` (bool) — montura en **stock hub Zonix VE** (§12); al lanzar puede ser 0 unidades — capacidad de carga obligatoria
- Inventario en almacén **óptica aliada** = activo del aliado; no modelado como hub Zonix — ver [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §12
- `made_to_order` (bool) — contra pedido al fabricante
- `fulfillment_mode`: `stock_ve` | `manufacturer_mto` (derivado de flags)
- `stock_qty` [PENDIENTE ops] — cantidad disponible si `in_stock_ve`

### FaceCapture

Captura facial para **try-on IA** (§17 [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md)) — **varias fotos reales** del paciente, no una sola imagen.

- `patient_profile_id`
- `images[]` — set de fotos reales (frente, ángulos guiados); mínimo operativo `[PENDIENTE UX]` (ej. 3–5 tomas)
- `analysis` — resultado IA: landmarks / modelo facial para superponer monturas
- `capture_status`: `in_progress` | `complete` | `insufficient_quality` | `consent_declined`
- `retention_until` — borrado raw post sesión salvo consentimiento extendido — [PRIVACIDAD_OPTICA.md](PRIVACIDAD_OPTICA.md)
- Relación: 1 paciente puede tener varios `FaceCapture` (re-captura); try-on activo usa el último `complete`

## Entidades pedido y logística (conceptual)

### Cart / CartItem

- Ítems: `lens_line`, `frame_line` según `order_type`
- Preview de `OrderLineItem` antes de confirmar orden

### OrderLineItem (conceptual — checkout desglose v3.1)

Líneas visibles al paciente en checkout y congeladas al crear `OpticalOrder`.

- `optical_order_id` (nullable en carrito preview)
- `line_type`: `frame` | `lens_lab` | `intl_courier` | `last_mile` | `iva_info` | `discount`
- `label`, `amount_usd`, `sort_order`
- `metadata` (JSON): SKU, zona envío, promo aplicada

### ShippingZoneRule (conceptual)

Reglas envío gratis o tarifa fija por zona.

- `zone_code`, `city`, `state`
- `free_shipping` (bool), `flat_rate_usd` (nullable si gratis)
- `valid_from`, `valid_to`, `campaign_label`

### PlatformConfig (conceptual — clave/valor admin)

- `hub_late_pickup_fee_percent` — default **0.10** (multa retiro tardío 4.2)
- `iva_rate_display` — tasa mostrada en línea informativa `[PENDIENTE legal]`
- Otras claves pricing/logística sin redeploy

### OpticalOrder

- `patient_profile_id`, `optical_partner_id`
- `order_type`: `lens_only` | `frame_only` | `lens_and_frame` — **`lens_only` no MVP**
- `prescription_id` — **nullable** si `frame_only`
- `frame_id`, `lens_material_id` (según tipo)
- `sales_channel`: `zonix_direct` | `partner_wholesale`
- `payment_mode`: `full_upfront` (4.1) | `deposit_30` (4.2) | `partner_wholesale_full`
- **Canal aliado:** checkout en panel partner (100% mayor por SKU vía `PartnerWholesalePrice`); **no** usa carrito paciente con `OrderLineItem` PVP — agregados `subtotal`/`total` reflejan mayor cobrado a la óptica.
- `status`, `subtotal`, `shipping_amount`, `iva_included_amount`, `total`
- `deposit_amount`, `balance_due`, `late_pickup_fee_amount` (calculado al reclamar tarde)
- `payment_proof` (JSON múltiples comprobantes)
- `fx_rate_at_deposit`, `fx_rate_at_balance` — Binance día de cada pago
- `shipping_mode`: `home_delivery` | `partner_pickup` | `hub_pickup`
- `free_shipping_applied` (bool) — reemplaza lógica binaria `shipping_included_in_total`
- `shipping_address_id`, `hub_id`, `shipping_zone_code`
- `sla_started_at` — inicio fab lentes en montura
- `hub_arrived_at`, `pickup_deadline_at` — retención 30 días (4.2)

**Agregados vs líneas (v3.1):** fuente de verdad del desglose checkout = **`OrderLineItem[]`**. Al confirmar orden, los agregados son **cache denormalizado**:

| Campo | Fórmula (suma de líneas) |
|-------|---------------------------|
| `subtotal` | `frame` + `lens_lab` |
| `shipping_amount` | `intl_courier` + `last_mile` |
| `total` | `subtotal` + `shipping_amount` + `discount` (línea negativa o 0) |
| `iva_included_amount` | `amount_usd` de la línea `iva_info` (display-only; **no suma al `total`**) — tasa vía `PlatformConfig.iva_rate_display` |

- Línea `iva_info`: informativa; **no entra** en `total`.
- Envío gratis: modelar como `last_mile` = 0 **o** línea `discount`; no ambos por el mismo concepto.
- `deposit_amount` / `balance_due` / `late_pickup_fee_amount` derivan de `total` (4.1/4.2).

### SupplierOrder (1..N por OpticalOrder)

- `optical_order_id`, `manufacturer_id`
- `fulfillment_role`: `frame_shipment` | `lens_production` | `finished_goods`
- `spec_payload` (JSON), `status`, `paid_at`

### ShipmentLeg (1..N por SupplierOrder o por tramo global)

- `supplier_order_id` (nullable si tramo cross-order)
- `leg_type`: `manufacturer_delivery` | `international_courier` | `last_mile_ve`
- `courier_id` | `delivery_provider_id`
- `from_party`, `to_party` (fabricante, lab, hub, paciente, courier_handoff)
- `tracking_number`, `status`, `shipped_at`, `delivered_at`

### Courier

Courier **internacional** contratado por Zonix.

- `id`, `name`, `code`, `supports_international`
- `handoff_address` — dirección que Zonix comunica al fabricante

## Relaciones clave

```
Manufacturer 1─* LensMaterial
Manufacturer 1─* Frame
Manufacturer 1─* SupplierOrder

OpticalPartner 1─* PatientProfile
PatientProfile 1─* Prescription (opcional si solo montura en historial futuro)
PatientProfile 1─* OpticalOrder

OpticalOrder 0..* SupplierOrder   (stock VE frame_only: 0 SupplierOrder)
OpticalOrder 1─* OrderLineItem
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

### OpticalOrder.status (pago + fulfillment — v3)

```
draft_cart
  → pending_payment_validation   ← nombre canónico (no usar pending_payment)
  → deposit_validated            (4.2 directo — 30% OK; dispara fab)
  → paid_full                    (4.1 100% | 4.2 saldo 70% | aliado 100% mayor)
  → awaiting_frame_shipment      (lens_and_frame, multi-fab)
  → awaiting_frame_at_lab
  → in_lens_production           ← sla_started_at; inicio SLA ~30d
  → shipped
  → in_customs
  → at_hub
  → ready_for_pickup             (4.2 — balance_due > 0)
  → out_for_delivery
  → delivered
  ↘ cancelled / disputed / forfeited_deposit
```

**Alias legacy:** `paid` = `paid_full` en docs antiguos — usar `paid_full` en código nuevo.

### SupplierOrder.status (conceptual)

`draft` → `sent_to_manufacturer` → `in_production` → `shipped` → `completed` / `cancelled`

### ShipmentLeg.status (conceptual)

`pending` → `picked_up` → `in_transit` → `delivered` / `exception`

## Reglas de negocio (dominio)

| order_type | MVP | prescription_id | SupplierOrders típicos |
|------------|-----|-----------------|------------------------|
| `frame_only` stock VE | Sí | null | **0** — pick/pack hub + `ShipmentLeg` last_mile |
| `frame_only` MTO | Sí | null | 1× `frame_shipment` → hub → destino |
| `lens_only` | No | required | 1× lens_production — fase 2 |
| `lens_and_frame`, mismo fab | Sí | required | 1× finished_goods → hub |
| `lens_and_frame`, fab distintos | Sí | required | frame_shipment + lens_production (montura→lab, convenio Zonix) |

## Portabilidad historial

- El **paciente** (`PatientProfile`) pertenece a su **óptica aliada** actual (`optical_partner_id` = tenant de registro/atención).
- Las **fórmulas** (`Prescription`) pertenecen al paciente (FK `patient_profile_id`); el tenant del partner es contexto operativo, no propiedad que bloquee datos al paciente autenticado.
- **Cese de óptica aliada:** **pedidos ya vendidos al paciente deben culminarse** (por la óptica o handoff acordado) antes del cierre operativo; luego pacientes y fórmulas **se reasignan a Zonix** (canal directo / ops admin) — **no** `cascadeOnDelete` sobre datos clínicos.
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

**Última actualización:** 2026-06-27 (v3.1 — OrderLineItem, ShippingZoneRule, checkout)
