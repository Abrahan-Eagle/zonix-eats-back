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

**Estado post-v2:** coherencia doc-vs-doc mejorada; **§11 logística sigue abierto** (founder); **código 001 sigue congelado** hasta HITL + `AUDIT_RIESGOS_SEGURIDAD.md`.

---

## 1. Incoherencias (validadas)

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
3. Comisión % vs mayorista futuro
4. Proteger aliado vs reasignación §6 a Zonix
5. SLA ~30 días sin ancla (`[PENDIENTE §11]`)
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
| 13 | Cuotas / anticipo 50% VE | Medio–alto |
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
| §11 logística | **Sigue abierto** — gate founder |
| Código 001 | **Congelado** — 7 críticos AUDIT pendientes implementación |

---

## Referencias

- [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md)
- [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md)
- [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md)
- [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](VERIFICACION_MODELO_MULTI_PROVEEDOR.md)

**Última actualización:** 2026-06-27
