# Política comercial — Zonix Glasses

> Pagos, pricing mayorista, retención hub y disputas.  
> Basado en [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) v3. No sustituye asesoría legal.

## Pricing por composición

| order_type | MVP | Líneas facturables |
|------------|-----|-------------------|
| `frame_only` | Sí | Montura (+ envío según checkout) |
| `lens_and_frame` | Sí | Montura + material + ensamblaje lab (+ tramos logísticos) |
| `lens_only` | **No MVP** | Material lente + lab — fase 2 |

Matriz MVP: [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §8.

Desglose transparente en checkout cuando intervienen **2 fabricantes** (montura + lab).

## Tres capas de precio (sin comisión %)

Ver tabla canónica [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §4. Resumen operativo: mayor fab → Zonix (negociado) · mayor Zonix → óptica **por SKU** · PVP libre (óptica o directo Zonix).

**Stock VE vs MTO:** mismo mayor al aliado; solo cambia plazo de entrega.

## Checkout — desglose dinámico (confirmado v3.1)

El checkout **calcula en tiempo real** y muestra **líneas que van sumando** hasta el **total final**. El cliente ve **PVP Zonix** por línea (no costo mayor fab interno).

| line_type | Cuándo aparece | Fuente de tarifa |
|-----------|----------------|------------------|
| `frame` | Montura elegida | PVP catálogo SKU |
| `lens_lab` | `lens_and_frame` + material | PVP catálogo material + ensamblaje |
| `intl_courier` | Pedido importado / MTO | Tabla courier Zonix por ruta |
| `last_mile` | Domicilio o envío desde hub | Tabla delivery VE (MRW, Domesa, propio) |
| `discount` | Promo envío gratis u otra | Reglas `ShippingZoneRule` / campaña admin |

### Por `order_type`

| Tipo | Líneas típicas en checkout |
|------|----------------------------|
| `frame_only` stock VE | Montura + última milla (si domicilio) |
| `frame_only` MTO | Montura + courier intl + última milla |
| `lens_and_frame` | Montura + lentes/lab + courier intl + última milla |

Al cambiar montura, material, dirección o zona, el **total se recalcula** antes de pagar.

### Envío gratis (configurable)

- Admin define reglas por **zona/ciudad**, **campaña** o **umbral** (`ShippingZoneRule.free_shipping`).
- Si aplica: línea `last_mile` = **0 USD** (etiqueta «Envío gratis»).
- Si no aplica: tarifa según delivery VE / courier.
- El hub puede limitar domicilio operativo del día; el **precio ya pagado** no se cobra dos veces.

### IVA

- **PVP publicado ya incluye IVA** — el checkout **no suma** IVA extra al final.
- Línea informativa `iva_info`: «IVA incluido (**16%**)» — tasa confirmada founder; factura SENIAT `[PENDIENTE contador]`.

### Base para pagos 4.1 / 4.2

El **total del checkout** (suma de líneas tras promos) es la base para:

- **4.1:** 100% de ese total.
- **4.2:** 30% anticipo + 70% saldo al hub/entrega.

Ejemplo ilustrativo `lens_and_frame` (165 USD total): anticipo 30% = 49.50 USD; saldo 70% = 115.50 USD.

## Pagos — canal directo (Zonix → paciente)

| Modalidad | Al pedido | Al retirar / entregar |
|-----------|-----------|------------------------|
| **4.1 — 100%** | 100% **total checkout** | — |
| **4.2 — 30/70** | 30% **total checkout** | 70% restante |

| Regla | Detalle |
|-------|---------|
| Métodos | Transferencia, pago móvil, Zelle (patrón scaffold) |
| Validación comprobante | Cliente sube captura/referencia → ops confirma ingreso → estado pago OK |
| Disparo fabricación 4.1 | Tras validar **100%** |
| Disparo fabricación 4.2 | Tras validar **30%** |
| Saldo 70% | Transferencia, pago móvil, presencial u online antes de retirar |

## Pagos — canal aliado (Zonix → óptica)

| Regla | Detalle |
|-------|---------|
| Obligación óptica | **100% del precio mayor** (por SKU) a Zonix |
| Cuándo | **Antes de fabricar** — prepago total a Zonix |
| Paciente | Paga a la óptica como ella decida; Zonix no impone split |
| Riesgo | Óptica asume cobro al paciente si ya pagó 100% mayor a Zonix |

## Validación de comprobante (anti-fraude)

**MVP (confirmado founder §16):**

1. Cliente u óptica transfiere y sube comprobante en app/panel.
2. **Ops Zonix concilia manualmente:** verifica ingreso en banco + referencia no reutilizada.
3. Pedido pasa a pago confirmado (`paid_full` o `deposit_validated` según modalidad).
4. Zonix paga fabricante(s) y emite `SupplierOrder`(s).

**Fase futura:** alianza con banco como intermediario de confirmación automática (sin sustituir revisión ops en MVP).

Ver estados en [DOMINIO_DATOS.md](../DOMINIO_DATOS.md).

## Retención hub — modalidad 4.2 (solo canal directo)

> **Resumen:** si el cliente pagó 30% y no paga el 70% a tiempo, Zonix guarda las gafas un plazo, luego puede pasarlas a stock y el cliente pierde el anticipo si no reclama.

| Paso | Regla |
|------|-------|
| 1 | Producto llega al hub → `ready_for_pickup` |
| 2 | Avisos al paciente: debe pagar **70%** para retirar |
| 3 | Plazo: **30 días calendario** desde llegada al hub |
| 4 | Sin pago: producto pasa a **stock físico** (ops) |
| 5 | Reclamo tardío (si disponible): paga **70% del total** + **multa**, donde multa = `total × hub_late_pickup_fee_percent` (default **10%**, configurable admin) |
| 6 | Si vendido a otro: pedido original cerrado; **anticipo 30% no reembolsable** |

**Forfeiture (pérdida del 30%):** el anticipo queda para Zonix como compensación por fabricación y envío ya incurridos; el cliente no recupera ese monto si no pagó el saldo a tiempo y el producto ya no está reservado para él.

**Legal VE — `[PENDIENTE abogado]`:** retención, forfeiture y multa deben constar en T&C/contrato con plazos y notificaciones documentadas (Ley Protección al Consumidor). Alternativa conservadora si abogado objeta multa: solo retención 30 días + anticipo no reembolsable.

## Pagos VE + tipo de cambio (§13)

| Regla | Detalle |
|-------|---------|
| Cobro | Bs a **tasa Binance del día de cada pago** (30%, 70% o 100%) |
| USD directo | Aceptado sin paso por Bs |
| Tras validación | Conversión operativa a **USDT** (tesorería Zonix) |
| Pago fabricantes | USD/USDT según contrato fab |
| Anti-fraude | Comprobante único; conciliación manual MVP — ver § Validación comprobante |

## Disputas y garantías

| Caso | Tratamiento (recomendación v3) |
|------|--------------------------------|
| **Graduación incorrecta** | Rehacer lentes; costo quien **aprobó capa (a)** si error probado; trazabilidad obligatoria |
| **Retraso > SLA** | Crédito **10%** próximo pedido (alternativa: reembolso parcial envío) |
| **Defecto fabricación** | Zonix responde al paciente; reclama al fabricante del tramo |
| **Montura dañada tramo inter-fab** | Reclamo delivery fab / fab montura; reenvío |
| **Error montaje lab** | Reclamo lab; posible nueva montura si irreparable |
| **«No me gustó» estética** | **Sin devolución** (a medida) — T&C claro |
| **Canal aliado** | Primera línea: **óptica**; escalamiento Zonix en 48h |
| **Agotado stock VE post-checkout** | MTO equivalente o reembolso |
| **Error pick/pack stock VE** | Reenvío; responsable Zonix ops |

Opciones alternativas documentadas en sesión founder — elegir redacción final en T&C `[PENDIENTE abogado]`.

## Aduanas e INCOTERM

**Confirmado founder §16 (2026-06-27):**

| Aspecto | Regla |
|---------|-------|
| Gestión | **Courier internacional** contratado por Zonix gestiona aduana e impuestos en tramo lab/fab → Venezuela |
| Costo al cliente | **Incluido** en línea `intl_courier` / PVP — sin cargo aduanal sorpresa al paciente u óptica |
| Visibilidad | Cliente y aliado **no ven** trámites internos de tránsito Zonix |
| Detalle legal | Redacción INCOTERM en contrato courier `[PENDIENTE abogado]` si aplica |

## Referencias

- [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [../DOMINIO_DATOS.md](../DOMINIO_DATOS.md)

**Última actualización:** 2026-06-27 (v3.1 + §16 IVA 16%, aduanas, anti-fraude MVP)
