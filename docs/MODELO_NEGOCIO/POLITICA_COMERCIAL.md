# Política comercial — Zonix Glasses

> Pagos, comisiones aliados y disputas para producto **a medida / contra pedido**.  
> Basado en [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md). No sustituye asesoría legal.

## Pricing por composición

| order_type | Líneas facturables |
|------------|-------------------|
| `frame_only` | Montura (+ envío directo fabricante) |
| `lens_only` | Material lente + lab (+ envío) |
| `lens_and_frame` | Montura + material + ensamblaje en lab (+ tramos logísticos) |

Desglose transparente en checkout cuando intervienen **2 fabricantes** (montura + lab).

## Pagos al paciente

| Regla | Detalle |
|-------|---------|
| Modelo MVP | **Prepago 100%** antes de disparar órdenes a fabricantes |
| Métodos | Transferencia, pago móvil, Zelle (patrón scaffold) |
| Validación | Admin/ops valida comprobante → `pending_payment_validation` → `paid` |
| Multi-fabricante | `[PENDIENTE founder]` ¿mismo prepago cubre flete montura→lab? |

**Riesgo de caja:** Zonix paga fabricantes tras `paid`; posible pago escalonado por tramo en fase 2.

## Pagos VE + tipo de cambio (§13)

| Regla | Detalle |
|-------|---------|
| Cobro paciente | Bs a **tasa Binance del momento** mostrada en checkout, o USD directo |
| Ancla FX | Tasa congelada al **inicio checkout**; recálculo si validación comprobante >24h `[PENDIENTE founder]` |
| Tras `paid` | Conversión operativa a **USDT** (tesorería Zonix) |
| Pago fabricantes | USD/USDT según contrato fab |
| Anti-fraude | Verificar comprobante único; reglas rechazo `[PENDIENTE founder]` — ver [MEJORAS_MODELO_NEGOCIO.md](../MEJORAS_MODELO_NEGOCIO.md) |

## Comisión óptica aliada (B2B2C)

- % sobre pedido atribuido al tenant — ver §4 en [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md).
- **Seed demo:** migración default `commission_rate` 0.10; seeder **Zonix Direct** usa `0` — **ninguno es decisión comercial** hasta founder fije % en HITL.

## Disputas y garantías (a medida + multi-tramo)

| Caso | Tratamiento propuesto |
|------|----------------------|
| Error graduación (lentes) | Capa fallida a+b+c; refabricación `[PENDIENTE legal]` |
| Defecto fabricación lentes | Reclamo lab de lentes |
| Montura dañada en tramo 1 (→ lab) | Reclamo courier / fab montura; reenvío |
| Error montaje en lab | Reclamo lab; posible nueva montura si irreparable |
| "No me gustó" estética | No aplica devolución estándar |
| Retraso por montura tardía al lab | SLA pausado; comunicación proactiva `[PENDIENTE founder]` |
| Agotado stock VE post-checkout (§12) | Ofrecer MTO equivalente o reembolso; SLA distinto MTO |
| Error pick/pack stock VE | Reenvío desde almacén; responsable Zonix ops |

## Aduanas e INCOTERM

Por tramo internacional — `[PENDIENTE founder]` DDP/DAP, agente aduanal, quién paga aranceles en lab→VE.

## Referencias

- [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [../DOMINIO_DATOS.md](../DOMINIO_DATOS.md)

**Última actualización:** 2026-06-27
