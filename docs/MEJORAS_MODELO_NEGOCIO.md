# Mejoras de modelo de negocio — Zonix Glasses

> Ideas y agujeros detectados en auditoría forense (Jun 2026). **Solo documentación** — no implica implementación inmediata.

## Tres agujeros antes de programar (prioridad founder)

| # | Agujero | Por qué importa | Acción doc |
|---|---------|-----------------|------------|
| 1 | **P&L en USD por `order_type`** | Margen distinto en `frame_only` (stock VE) vs `lens_and_frame` (multi-fab + flete) | Modelo unit economics por tipo; no mezclar en un solo AOV |
| 2 | **Responsable legal nombrado de la fórmula** | Capa (a) sin credencial = riesgo sanitario y reputacional | [ROLES_Y_ACTORES.md](MODELO_NEGOCIO/ROLES_Y_ACTORES.md) + [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) §6 |
| 3 | **Política cambiaria + anti-fraude comprobante** | Bs→USDT al validar pago (§13) exige reglas de tipo de cambio y doble gasto | [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) (Pagos VE + FX) · §13 [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) |

## Ideas v2 forense (Jun 2026 — prioridad founder)

> Síntesis [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md) eje 4. **Solo documentación.**

| # | Idea | Valor | Esfuerzo | KPI sugerido |
|---|------|-------|----------|--------------|
| 1 | **Home Try-On físico** (Warby Parker) | Reduce riesgo estético `frame_only` | Alto | Tasa devolución post-entrega ↓ |
| 2 | **Comparador materiales lente** interactivo | Transparencia → conversión | Medio | CTR checkout con lentes ↑ |
| 3 | **Frame Passport** — `lens_only` con montura propia | LTV recompra frecuente | Medio | % recompras `lens_only` / cliente |
| 4 | **Bundle accesorios** (clip solar, estuche) | Upsell margen | Bajo–medio | AOV accesorios / pedido |
| 5 | **Suscripción Lens Care** | LTV + alertas vigencia receta | Medio | Churn suscripción · días a recompra |
| 12 | **Spread FX + congelación tasa checkout** | Cierra agujero §13 | Bajo | Disputas por tipo de cambio ↓ |
| 13 | **Financiamiento cuotas / anticipo 50% VE** | Conversión alto ticket | Medio–alto | Conversión checkout >$X |
| 14 | **Trazabilidad importación por lote óptico** | Compliance VE | Medio | Tiempo aduana por lote |

**Backlog estratégico (P2):** co-brand storefront aliado (§14), scorecard fabricante + routing, escrow por hito logístico, seguro transporte/aduana por `ShipmentLeg`, red aliados solo medición PD, referidos QR, B2B2E corporativo, lentes de contacto, pediatría, WhatsApp transaccional.

## Ideas de producto / negocio (no contempladas en MVP)

| Idea | Valor | Esfuerzo |
|------|-------|----------|
| **Marca propia de montura** | Margen + control SKU top sellers | Medio — sourcing + stock VE |
| **B2B reposición a ópticas** | Canal mayorista (§14) sin competir paciente del aliado | Alto — contrato + catálogo wholesale |
| **Garantía / adaptación post-entrega** | Confianza en a medida; visita óptica aliada | Medio — política § disputas |
| **Escrow al fabricante** | Reduce riesgo Zonix paga antes de calidad | Alto — tesorería + legal |
| **Recompra / suscripción lentes** | LTV; alertas vigencia receta | Medio — notificaciones + reglas |
| **Try-on white-label** | SaaS ligero para ópticas sin full marketplace | Alto — producto aparte |

## Métricas que faltan en docs actuales

| KPI | Definición | Doc destino |
|-----|------------|-------------|
| Margen bruto USD | Por `order_type` y fabricante | [UNIT_ECONOMICS.md](MODELO_NEGOCIO/UNIT_ECONOMICS.md) |
| Refabricación | % pedidos con falla capa a / lab / courier | Dashboard admin futuro |
| Churn aliados | Ópticas dadas de baja / motivo | [POLITICA_CANAL_ALIADO.md](MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md) |
| SLA por tramo | Fab montura · lab · courier · E2E | [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md) §11 |
| Fraude comprobante | Rechazos / doble gasto VE | [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) §13 |
| Stock VE obsolescencia | Rotación SKU `in_stock_ve` | [FLUJOS_OPERATIVOS.md](MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md) 6A-stock |
| Atribución canal | B2B2C aliado vs mayorista futuro | [POLITICA_CANAL_ALIADO.md](MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md) |

Dashboard admin: skill **`zonix-glasses-analytics`** — **no existe aún**; usar tabla anterior hasta crear skill o módulo analytics.

## Pendientes legales / fiscales / sanitarios VE

Marcar siempre `[abogado/contador VE]` — no inventar norma:

| Tema | Nota |
|------|------|
| Importador de registro | Multi-fab internacional → aduanas |
| Registro sanitario de lentes | Clasificación dispositivo médico / material óptico |
| IVA / IGTF / factura SENIAT | Emisión al paciente y comisión aliado |
| Vigencia legal de receta | Plazo máximo uso fórmula para fabricar |
| Ejercicio de optometría | Quién puede emitir/validar graduación en canal digital |

## Relación con otros documentos

- Informe forense v2: [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md)
- Decisiones founder §12–§14: [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md)
- Seguridad pre-descongelar: [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md)
- Checklist logística: [CHECKLIST_FOUNDER_S11.md](MODELO_NEGOCIO/CHECKLIST_FOUNDER_S11.md)
- Unit economics plantilla: [UNIT_ECONOMICS.md](MODELO_NEGOCIO/UNIT_ECONOMICS.md)

**Última actualización:** 2026-06-27
