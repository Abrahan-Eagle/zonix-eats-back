# Auditoría forense v2 — Zonix Glasses (Jun 2026)

> **Metodología:** `parallel-judge-ops` — 4 subagentes readonly + juez orquestador.  
> **Alcance:** documentación hub back + skills + spec 001 vs código congelado (sin editar código).  
> **Corrección aplicada:** ronda v2 documentada en commits locales post-informe.

## Resumen ejecutivo

| Eje | Hallazgos | Acción |
|-----|-----------|--------|
| Incoherencias | 9 CRITICAL + 13 WARNING | Corregidos en docs (v2) |
| Mejoras doc/proceso | 6 P0–P2 | HITL, §11 unificado, AUDIT ampliado |
| Negocio | 10 agujeros lógicos | POLITICA FX, canal aliado, unit economics, flujos 6A |
| Ideas nuevas | 14 propuestas | Volcadas en `MEJORAS_MODELO_NEGOCIO.md` |

**Estado post-v2:** coherencia doc-vs-doc mejorada en ronda v2; **§11 cerrado** y **checkout v3.1** en rondas posteriores (§5–§7); **código 001 sigue congelado** hasta HITL legal/marca + `AUDIT_RIESGOS_SEGURIDAD.md`.

> **§1–§4 = histórico pre-v3.1** (hallazgos ya corregidos). Estado actual: §5–§7.

---

## 1. Incoherencias (validadas) — histórico v2

### CRITICAL (corregidas v2)

| # | Hallazgo | Resolución doc |
|---|----------|----------------|
| 1 | Tabla §11 distinta DECISIONES vs CHECKLIST | DECISIONES §11 alineada a 5 filas CHECKLIST + nota montura sola |
| 2 | HITL [x] §8–§11 con §11 abierto | Desmarcado; matriz decidido vs implementado |
| 3 | VERIFICACION fila OK §11 vs PASS parcial | Fila §11 → PARCIAL |
| 4 | CHECKLIST §6 "documentado ✅" vs cascade código | "Decidido / código pendiente" |
| 5 | Enlace roto REALIGNMENT desde DECISIONES §6 | `../../specs/...` |
| 6 | Skill `patient_id` vs `patient_profile_id` | Skills corregidas |
| 7 | spec 001 AC [x] con feature congelada + riesgos | AC acotados a lo verificable pre-descongelar |
| 8 | SeoHelper smart glasses | Excepción en FORENSIC_VERIFICATION (código fuera alcance docs) |
| 9 | POLITICA salta `pending_payment_validation` | Alineado a estados canónicos |

### WARNING (corregidas o documentadas v2)

- Frame flags §12 en `DOMINIO_DATOS`
- CHECKLIST §13 anti-fraude pendiente
- MEJORAS referencia §6 ROLES corregida
- Seed comisión 10% vs Zonix Direct 0 aclarado
- Skill fulfillment enum pre-pago
- spec `locked_for_order` en AC futuro
- PRIVACIDAD proveedor CN → genérico + aspiracional
- deep-interview overlay China → nota Glasses
- ui-router front Pharma → puntero Glasses
- MEJORAS skill analytics → futuro doc KPIs
- FLUJOS 6A-stock / 6A-MTO
- REALIGNMENT vigencia receta
- tasks.md D3 acotado a fase diseño

---

## 2. Mejoras (documentación y proceso)

| Prioridad | Mejora | Artefacto |
|-----------|--------|-----------|
| P0 | Gate HITL honesto | `HITL_APROBACION_DOCS.md` |
| P0 | §11 canónico | `DECISIONES_FOUNDER.md`, `CHECKLIST_FOUNDER_S11.md` |
| P0 | Decidido vs implementado | CHECKLIST §6, REALIGNMENT |
| P1 | AUDIT ampliado (ítems 8–9, MVP vs target) | `AUDIT_RIESGOS_SEGURIDAD.md` |
| P1 | Skills alineados | prescriptions, fulfillment, overlays |
| P2 | Este informe | `AUDIT_FORENSE_2026-06-27.md` |

---

## 3. Mejoras propuesta de negocio

### Agujeros lógicos (top 10)

1. 6A vs stock VE — checkout único, fulfillment dual
2. Prepago Bs + pago fab USD — spread FX sin regla
3. ~~Comisión % vs mayorista futuro~~ — **Resuelto v3:** mayor por SKU; comisión % descartada
4. Proteger aliado vs reasignación §6 a Zonix
5. ~~SLA ~30 días sin ancla~~ — **Resuelto §11 cerrado:** `sla_started_at` inicio fab lentes en montura
6. Cross-fab sin homologación pre-checkout
7. AOV/margen único mezcla `order_type`
8. Capa (a) sin credencial profesional
9. `lens_only` fuera foco comercial MVP
10. Capital stock §12 sin monto piloto

### Docs nuevos v2

- [`MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md`](MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md)
- [`MODELO_NEGOCIO/UNIT_ECONOMICS.md`](MODELO_NEGOCIO/UNIT_ECONOMICS.md)
- Sección Pagos VE + FX en `POLITICA_COMERCIAL.md`
- Flujos 6A-stock / 6A-MTO en `FLUJOS_OPERATIVOS.md`

---

## 4. Nuevas ideas (priorizadas)

| # | Idea | Esfuerzo |
|---|------|----------|
| 1 | Home Try-On físico | Alto |
| 2 | Comparador materiales lente | Medio |
| 3 | Frame Passport (`lens_only` montura propia) | Medio |
| 4 | Bundle accesorios | Bajo–medio |
| 5 | Suscripción Lens Care | Medio |
| 6 | Red aliados solo PD | Medio |
| 7 | Co-brand storefront | Alto |
| 8 | Referidos QR | Bajo–medio |
| 9 | Scorecard fabricante | Medio |
| 10 | Escrow por hito logístico | Alto |
| 11 | Seguro por ShipmentLeg | Medio |
| 12 | Spread FX + congelación checkout | Bajo |
| 13 | **Cuotas / anticipo 50% VE** | Medio–alto | **Descartada post-v3** — vigente 30/70 (4.2) |
| 14 | Trazabilidad importación lote | Medio |

Detalle en [`MEJORAS_MODELO_NEGOCIO.md`](MEJORAS_MODELO_NEGOCIO.md) § Ideas v2 forense.

---

## Verificación post-corrección (2026-06-27)

Comandos en [`FORENSIC_VERIFICATION.md`](FORENSIC_VERIFICATION.md):

| Check | Resultado |
|-------|-----------|
| Anti-Eats (rg producto) | OK salvo doc auditoría + excepción `linux/CMakeLists.txt` documentada |
| Anti-China/smart-glasses (skills dominio) | OK — coincidencias solo en comentarios **DEPRECATED** explícitos |
| CRITICAL doc-vs-doc (9) | Corregidos en ronda v2 |
| §11 logística | **Cerrado founder 2026-06-27** — ver CHECKLIST §11 |
| Código 001 | **Congelado** — 7 críticos AUDIT pendientes implementación |

---

## 5. Ronda forense v3.1 — doc-vs-doc (2026-06-27)

> **Metodología:** `parallel-judge-ops` + `docs-alignment-ops` — remediación docs satélite vs canon v3.1.

| # | Hallazgo | Resolución |
|---|----------|------------|
| C1 | Capa (a) «profesional Zonix» vs online sin optometrista | `DECISIONES_FOUNDER.md` §1 tabla + §2 DP acotado |
| C2 | §7 «frame_only con fórmula» vs §8 | §7 corregido *(histórico v2)* |
| C3 | `PRODUCT_VISION` lens_only MVP, §11 pendiente, fab sin login | Refresh v3.1 |
| C4 | `LEAN_CANVAS` comisión % | Mayorista B2B2C; comisión % descartada |
| C5 | `VERIFICACION` bloqueada por §11 | PASS v3.1; §11 cerrado |
| M1 | «PVP Zonix» vs total checkout | Unificado en POLITICA + DECISIONES §3.2 |
| M2 | Skills partners/fulfillment legacy | `PartnerWholesalePrice`; SLA `sla_started_at` |
| M3 | Agregados order vs OrderLineItem | Nota cache en `DOMINIO_DATOS.md` |
| M4 | Informe v2 «§11 abierto» | Esta sección + estado actualizado abajo |

**Estado post-v3.1:** coherencia **doc-vs-doc PASS** en cluster negocio v3.1; pendientes **legal/marca/seguridad 001** (no incoherencia documental). Código 001 sigue congelado.

---

## 6. Repaso subagentes — precisión checkout y gates (2026-06-27)

> **Metodología:** 3 subagentes `explore` (pagos/checkout, MVP/logística, satélites/HITL/skills).

| # | Hallazgo | Resolución |
|---|----------|------------|
| R1 | Multa L112 «70% + multa = percent» ambiguo | `POLITICA_COMERCIAL.md` — 70% del total + multa aparte |
| R2 | Fórmula `subtotal`/`shipping_amount`/`total`/`iva_info` | Tabla canónica en `DOMINIO_DATOS.md` |
| R3 | FLUJOS «subtotal = total» vs agregados | «Total checkout» + puntero DOMINIO |
| R4 | REALIGNMENT gate §11 obsoleto | Gate = legal/marca + AUDIT + OK founder |
| R5 | AGENTS, CONTEXTO_IA, JARVIS integration desfasados | Fase v3.1 PASS; código congelado |
| R6 | spec 001 banner ambiguo | Negocio v3.1 cerrado; bloquea legal/seguridad |
| R7 | LEAN compatibilidad vs homologación | Aclarado cross-fab sin homologación catálogo |
| R8 | DOMINIO cese aliado incompleto | Pedidos en curso culminan antes de reasignación |
| R9 | CADENA `frame_only` omitía hub | Hub VE en matriz |
| R10 | Partner remoto capa (a) | Fila en `ROLES_Y_ACTORES.md` |
| R11–R12 | Skills prescriptions + ui-patterns | Capa (a) bifurcada; checkout v3.1 en UI skill |

**Estado post-repaso:** **PASS completo** doc-vs-doc en los 6 ejes checkout + satélites indexados. Próximo gate humano: HITL legal/marca + `AUDIT_RIESGOS_SEGURIDAD.md`.

---

## 7. Auditoría integral `docs/` — dedupe e incoherencias (2026-06-27)

> **Metodología:** 4 subagentes `explore` (MODELO_NEGOCIO, dominio/producto, gates/memoria, legal/scaffold) + orquestador dedupe.

| # | Tipo | Fix aplicado |
|---|------|--------------|
| I1 | contradiction | `frame_only` stock VE: 0 SupplierOrder en `DOMINIO_DATOS`; checkout FLUJOS stock vs MTO |
| I2 | contradiction | CHECKLIST capa (a) vs DP presencial §2 — filas separadas |
| I3 | stale | ROLES fabricante `[propuesto]` → MVP confirmado §9 |
| I4 | duplicate | Tablas §11 / tres capas / order_type / 4.1-4.2 / cese aliado → punteros a DECISIONES/POLITICA |
| I5 | duplicate | `active_context`, MEJORAS, HITL pendientes — SSOT HITL + mapa `CONTEXTO_IA` |
| I6 | navigation | BRAND banner NO CANON; ENV PlatformConfig; SCAFFOLD documents riesgo; CLONE forensic rg |
| I7 | stale | AUDIT §1–§4 marcado histórico v2 |

**Veredicto:** **PASS v3.1** mantenido; duplicación reducida a patrón canon + enlace. Sin regresión R1–R12.

---

## Referencias

- [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md)
- [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md)
- [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md)
- [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md)

**Última actualización:** 2026-06-27 (auditoría integral docs/ §7)
