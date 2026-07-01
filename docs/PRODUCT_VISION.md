# PRODUCT_VISION — Zonix Glasses

> Óptica online B2B2C — lentes y/o monturas contra pedido, con IA para fórmulas y try-on virtual.  
> Zonix **orquesta** una red de fabricantes y couriers (no un solo proveedor monolítico).

## Problema

En Venezuela, obtener lentes a medida con buen precio implica fricción: visitas repetidas a óptica, catálogo limitado, tiempos largos y poca transparencia de materiales/precios. Las ópticas locales quieren digitalizar sin invertir en fabricación.

## Propuesta de valor

**Zonix Glasses** conecta pacientes y **ópticas aliadas** con una **red de fabricantes** (1..N) contra pedido, ofreciendo:

1. **Pedidos MVP:** montura (`frame_only`) o paquete lentes + montura (`lens_and_frame`); `lens_only` fase 2.
2. **Fórmula + IA** cuando hay lentes graduados (manual, OCR, validación 3 capas).
3. **Try-on virtual** para monturas (sin fórmula si es solo montura).
4. **Historial del paciente** vinculado a su óptica (multi-tenant B2B2C).
5. **Fulfillment orquestado:** montura→lab de lentes (si aplica), producción, multi-courier, entrega (~SLA 30 días desde inicio fab lentes en montura — [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) §11).

## Composición del pedido

| Tipo | Qué compra el paciente | MVP |
|------|------------------------|-----|
| `frame_only` | Montura (catálogo + try-on; **sin fórmula**) | Sí |
| `lens_and_frame` | Paquete completo; ensamblaje en lab de lentes | Sí |
| `lens_only` | Lentes graduados (requiere fórmula) | **No** (fase 2) |

## Segmentos

| Segmento | Descripción |
|----------|-------------|
| **B2C directo** | Cliente final compra vía Zonix (óptica propia o marca Zonix). |
| **B2B2C aliado** | Óptica registrada como partner; sus pacientes operan bajo su tenant. |
| **Fabricante (N)** | Lab de lentes y/o proveedor de monturas — **login MVP** (panel fase 1). |
| **Courier (N)** | Transporte por tramo (inter-fabricante, internacional, última milla). |

## Diferenciadores IA

- OCR / extracción estructurada de fórmula óptica desde imagen o PDF.
- Análisis facial para try-on y recomendación de monturas.
- [PENDIENTE founder] Proveedor IA concreto (Gemini / OpenAI / local).

## Qué NO es (pivot Jun 2026)

No es companion app de **smart glasses** (BLE, sync wearable, OTA). El nombre *Glasses* = **gafas/lentes ópticos**.

## Métricas norte (MVP)

- Tiempo selección → **total checkout** completado (por tipo de pedido) — [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) · [DOMINIO_DATOS.md](DOMINIO_DATOS.md) `OrderLineItem`.
- Tasa conversión try-on → carrito (monturas).
- SLA entrega extremo a extremo (por tramo visible al paciente).
- Ópticas aliadas activas y pacientes por partner.

## Documentos relacionados

- [MODELO_NEGOCIO/LEAN_CANVAS.md](MODELO_NEGOCIO/LEAN_CANVAS.md)
- [MODELO_NEGOCIO/DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md)
- [MODELO_NEGOCIO/POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md)
- [MODELO_NEGOCIO/CADENA_SUMINISTRO.md](MODELO_NEGOCIO/CADENA_SUMINISTRO.md)
- [MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md)
- [DOMINIO_DATOS.md](DOMINIO_DATOS.md)

**Última actualización:** 2026-06-27 (alineado v3.1)
