# Cadena de suministro — Zonix Glasses

> Orquestación multi-fabricante: **delivery fabricante** (China) + **courier Zonix** (internacional) + **delivery VE** (última milla).  
> Decisiones founder v3: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §9–§11.

## Principio rector

Zonix **coordina** fabricantes con **login MVP** y couriers/deliveries registrados. **No fabrica.**

**Regla inter-fábrica:** tramos entre fábricas solo si ambos tienen **convenio Zonix Glasses**.

## Delivery vs courier vs delivery VE

| Actor | Contrata | Paga | Tramo |
|-------|----------|------|-------|
| **Delivery fabricante** | Fabricante | Fabricante | China: fab montura → fab lentes; fab → punto entrega courier Zonix |
| **Courier internacional** | **Zonix** | Zonix | Avión/barco → **hub Venezuela** (dirección fijada en sistema) |
| **Delivery VE** | Zonix (propio o tercero) | Según política envío | Hub → óptica aliada o domicilio paciente (MRW, Domesa, flota Zonix, etc.) |

Cada tramo = `ShipmentLeg` + carrier registrado (`DeliveryProvider` o `Courier` según tipo).

## Matriz: composición × escenario fabricante

|  | Un solo fabricante | Fab montura + fab lentes | Solo un tipo |
|--|-------------------|--------------------------|--------------|
| **`frame_only`** | 1 orden → delivery/courier → **hub VE** → destino | N/A | Stock VE (pick/pack hub) o MTO vía hub — [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md) 6A |
| **`lens_and_frame`** | 1 orden integrada → hub → destino | **Montura → lab** (delivery fab) → producto → courier Zonix → hub | Ver fila |
| **`lens_only`** | — | — | **No MVP** |

## Flujo detallado: montura → lab → hub (confirmado v3)

**Ejemplo costos referencia:** montura ~1 USD (fab 1) + lab ~5 USD montaje (fab 2).

1. Zonix valida pago según canal (§3 [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md)).
2. **SupplierOrder** fab montura → delivery fab envía montura a fab lentes (**solo si convenio Zonix**).
3. Fab lentes confirma recepción → `awaiting_frame_at_lab` → `in_lens_production`.
4. Fab lentes monta producto terminado → delivery fab entrega al **courier internacional Zonix** (dirección sistema).
5. Courier Zonix → **hub VE** (consolidación obligatoria).
6. Delivery VE → óptica aliada o domicilio paciente.

**Fabricante integrado (lentes + montura):** pasos 2–4 colapsan en un fab; delivery fab → courier Zonix.

**Cross-fab:** **sin homologación de catálogo** — lab fabrica lente con forma de montura recibida.

## SLA ~30 días

Cuenta desde **inicio fabricación de lentes / montaje en montura** — no desde pago ni desde llegada de montura al lab.

## Checklist operativo (excepciones)

| Situación | Acción ops Zonix |
|-----------|------------------|
| Montura no llega al lab en X días | Escalar fab montura; pausar SLA; notificar paciente |
| Montura dañada tramo delivery fab | Reclamo delivery/fab montura; reenvío |
| Lab rechaza montura | Escalar ops; disputa según [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) |
| Retraso courier internacional | Comunicación proactiva; SLA pausado si causa externa `[PENDIENTE]` |
| Hub retención 70% no pagado | Ver retención 30 días — [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) |

## Inventario monturas (`frame_only` — §12)

| Modo | Fulfillment |
|------|-------------|
| **Stock VE** | Pick/pack hub → delivery VE |
| **MTO** | SupplierOrder fab → courier Zonix → hub → delivery VE |

Mismo precio mayor aliado en ambos modos.

## §11 — cerrado founder v3

> Tabla canónica: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §11. Operaciones: matriz excepciones arriba + hub obligatorio.

## Referencias

- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [DOMINIO_DATOS.md](../DOMINIO_DATOS.md)
- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)

**Última actualización:** 2026-06-27 (v3 founder)
