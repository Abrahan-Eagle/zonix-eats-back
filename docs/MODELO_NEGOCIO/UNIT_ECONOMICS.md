# Unit economics — Zonix Glasses

> Plantilla por `order_type` y canal. **Cifras `[PENDIENTE founder]`** — no usar en pitch inversor hasta completar.

## Supuestos globales (v3)

- Moneda reporting: **USD** (tesorería post-§13).
- **Canal aliado:** margen = mayor Zonix→óptica vs COGS fab — **no comisión %**.
- **Canal directo 4.2:** caja parcial al 30%; riesgo saldo 70% y retención hub.
- SLA: **~30 días desde inicio fab lentes en montura** — [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §11.
- **IVA:** incluido en PVP; checkout solo desglosa informativo — no suma al total.
- **Envío gratis:** promoción por zona reduce `last_mile` a 0 → margen ↓ en esas zonas.

## Checkout — líneas PVP vs COGS interno

El paciente ve líneas PVP (`OrderLineItem`); COGS fab/courier alimenta margen interno — no se expone en checkout.

## Tres capas de precio (ejemplo founder)

Tabla canónica: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §4. Ejemplo numérico ilustrativo en §9 del mismo doc (~1 montura + ~5 lab + envíos).

## Plantilla por tipo de pedido

| Línea | `frame_only` stock | `frame_only` MTO | `lens_and_frame` multi-fab |
|-------|---------------------|------------------|----------------------------|
| PVP directo (USD) | `[ ]` | `[ ]` | `[ ]` |
| Mayor aliado (USD/SKU) | `[ ]` | `[ ]` | `[ ]` |
| COGS montura | stock VE | fab | fab |
| COGS lentes / lab | — | — | lab + ensamblaje |
| Delivery fab China | — | — | incl. COGS fab ref. |
| Courier intl Zonix | `[ ]` | `[ ]` | `[ ]` |
| Delivery VE (PVP línea checkout) | `[ ]` | `[ ]` | `[ ]` |
| Promo envío gratis (margen ↓) | opcional | opcional | opcional |
| IVA incluido en PVP | sí | sí | sí |
| **Margen bruto Zonix** | `[ ]` | `[ ]` | `[ ]` |

**Stock vs MTO:** mismo mayor aliado; stock mejora días caja y SLA percibido.

## Caja por canal de pago

| Canal | Entrada caja Zonix | Riesgo |
|-------|-------------------|--------|
| Directo 4.1 | 100% upfront | Bajo |
| Directo 4.2 | 30% del total checkout → fab/courier; 70% al hub | Medio (retención 30d + multa config) |
| Aliado | 100% mayor upfront | Bajo en Zonix; óptica asume cobro paciente |

## Capital de trabajo §12

| Concepto | Nota |
|----------|------|
| Stock top SKUs hub VE | `[PENDIENTE]` N SKUs × ticket |
| Pedidos 4.2 en tránsito | Financiamiento fab/courier con solo 30% |
| Obsolescencia stock | `[PENDIENTE founder]` |

## Referencias

- [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §3–§4, §12–§13
- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)

**Última actualización:** 2026-06-27 (v3.1 founder)
