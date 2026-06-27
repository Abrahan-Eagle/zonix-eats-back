# Lean Canvas — Zonix Glasses

> Basado en modelo descrito por founder (Jun 2026). Cifras marcadas `[PENDIENTE founder]`.

## 1. Problema

- Lentes a medida caros o lentos en canal tradicional.
- Ópticas sin plataforma digital ni acceso a fabricación offshore eficiente.
- Pacientes sin try-on ni comparación clara de materiales/precios.
- Logística multi-proveedor opaca (montura en un sitio, lentes en otro).

## 2. Segmentos de clientes

- Pacientes finales (con o sin prescripción según producto).
- Ópticas aliadas (B2B) que quieren vender online bajo su marca/sede.
- Zonix operación directa (canal propio).

## 3. Propuesta de valor única

Óptica online **contra pedido** + IA (fórmula + try-on) + **orquestación multi-fabricante y multi-courier** (~SLA 30 días `[PENDIENTE §11]`).

## 4. Solución

App Flutter + API Laravel: multi-tenant, fórmulas (si lentes), catálogo flexible (solo montura / solo lentes / paquete), pago VE, orquestación SupplierOrders y tramos.

## 5. Canales

- App móvil (`zonix_glasses`).
- Panel web óptica aliada (fase 2).
- WhatsApp / soporte `[PENDIENTE founder]`.

## 6. Fuentes de ingreso

- Margen sobre lente, montura y paquete (Zonix directo).
- Comisión % vía óptica aliada `[PENDIENTE founder %]` (seed demo 10% ≠ decisión).
- Posible suscripción panel aliado `[PENDIENTE founder]`.
- Futuro B2B mayorista a ópticas — [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md) · §14 [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md).

## 7. Estructura de costos

- COGS fabricantes (N): lentes, monturas, ensamblaje.
- **Stock monturas top** (`frame_only` mixto — §12): capital de trabajo VE.
- **Flete inter-fabricante** (montura→lab) + **multi-courier**.
- IA (OCR, try-on).
- Ops Zonix (validación, orquestación tramos, aduanas).
- Infra (hosting, Firebase, Pusher).

## 8. Métricas clave

- Pedidos/mes por `order_type`, AOV, margen bruto — plantilla [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md).
- Conversión try-on → carrito (monturas).
- SLA por tramo y extremo a extremo — §11 [CHECKLIST_FOUNDER_S11.md](CHECKLIST_FOUNDER_S11.md).
- Aliados activos / pacientes por partner — [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md).
- KPIs ampliados: [MEJORAS_MODELO_NEGOCIO.md](../MEJORAS_MODELO_NEGOCIO.md).

## 9. Ventaja injusta

- Red curada de fabricantes + reglas de compatibilidad montura/lab.
- Pipeline IA fórmula + try-on integrado al pedido.
- Orquestación visible (tracking por tramo) vs importación opaca.
- Red de ópticas aliadas locales.

## Hipótesis a validar

1. Pacientes compran **solo montura** online sin fórmula (try-on suficiente).
2. Cadena montura→lab es viable en SLA y costo.
3. Multi-courier no confunde al paciente si UX de tracking es clara.
4. Ópticas aliadas adoptan panel sin fricción regulatoria.
5. Pagos manuales VE bastan para MVP (FX §13 — [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)).

## Referencias

- [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) · [AUDIT_FORENSE_2026-06-27.md](../AUDIT_FORENSE_2026-06-27.md)

**Última actualización:** 2026-06-27
