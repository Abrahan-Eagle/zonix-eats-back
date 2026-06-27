# Decisiones de negocio — Founder (Jun 2026)

> Registro de decisiones para alinear docs, spec 001 e implementación.  
> **Validación a+b+c:** confirmada por founder en conversación ("las 3 a b c").  
> Resto: **propuesta MVP** — revisar en HITL antes de producción.

## 1. Validación de fórmula (confirmado)

**Cadena en 3 capas** antes de fabricar:

| Capa | Actor | Acción |
|------|-------|--------|
| **a** | Óptica aliada o profesional Zonix | Revisa y aprueba graduación clínica |
| **b** | IA (OCR) | Solo precarga borrador; nunca auto-confirma |
| **c** | Paciente | Confirma explícitamente antes de checkout/orden |

Estados técnicos: `draft` → `pending_review` → `approved` → `patient_confirmed` → `locked_for_order`.

## 2. Distancia pupilar (DP) — propuesta MVP

| Prioridad | Método |
|-----------|--------|
| 1 | Óptica aliada o Zonix mide en consulta y registra `pd` / `pd_near` |
| 2 | Paciente ingresa si la conoce (validación mínima de rango) |
| 3 | IA estima desde foto — **fase 2**; no bloquea MVP |

**Regla:** no pasar a `approved` sin `pd` (monocular o binocular según proveedor).

`[PENDIENTE founder]` Confirmar si fase 1 exige medición presencial para canal B2C directo.

## 3. Pago — propuesta MVP

- **Prepago 100%** antes de disparar `SupplierOrder`(s) a fabricantes (Zonix no financia producción en MVP).
- Flujo: checkout → comprobante → `pending_payment_validation` → `paid`.
- Patrón pagos manuales VE del scaffold (transferencia / pago móvil).

`[PENDIENTE founder]` Anticipo + saldo contra entrega en fase 2.

## 4. Comisión óptica aliada — propuesta MVP

- Zonix fija **precio al paciente** (PVP).
- Óptica aliada recibe **comisión %** sobre venta neta del pedido atribuido a su tenant.
- Campo `commission_rate` en `OpticalPartner`; valor seed demo **10%** — **no es decisión comercial** hasta founder fije % en HITL `[PENDIENTE founder %]`.

Alternativa descartada en MVP: mayorista + markup libre (complejidad pricing).

## 5. Entrega al paciente — propuesta MVP

- **Default:** paciente elige en checkout — **domicilio** o **retiro en óptica aliada** (última milla / tramo final).
- Logística internacional y multi-tramo: ver §9–§10 y [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md).
- Si canal Zonix directo sin aliado físico: solo domicilio.
- Ajuste fino de montura en óptica aliada: opcional post-entrega `[PENDIENTE founder]`.

## 6. Portabilidad del historial — confirmado founder (Jun 2026)

- El **paciente pertenece a su óptica aliada** (`optical_partner_id` en `PatientProfile` = tenant de registro/atención actual).
- Las **fórmulas** pertenecen al paciente (profile); no se eliminan al cambiar de canal.
- **Si la óptica aliada cesa** (contrato terminado, baja operativa): pacientes y fórmulas **pasan a Zonix** (reasignación a tenant Zonix directo) — **no** borrado en cascada.
- Recompra: paciente puede usar fórmulas `patient_confirmed` / `locked_for_order` con su cuenta.
- Cambio voluntario de óptica: reasignación con auditoría en log.

**Código 001 (pendiente realineación):** migraciones actuales usan `cascadeOnDelete` en FKs de tenant — **cambiar** a `nullOnDelete` / job de reasignación antes de descongelar. Ver [REALIGNMENT_POST_HITL.md](../../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md).

`[PENDIENTE founder]` Contrato B2B si algún aliado exige exclusividad de datos (tensiona con portabilidad al paciente).

## 7. Entrada de fórmula (confirmado implícito)

Tres vías equivalentes al inicio del flujo:

1. Paciente trae receta (foto/upload o datos manuales).
2. Óptica/Zonix miden y cargan manualmente.
3. IA OCR desde foto/PDF → borrador → capas a+b+c.

**Alcance:** solo pedidos con **lentes graduados** (`lens_only` o `lens_and_frame`). Ver §8.

## 8. Composición del pedido (confirmado)

| Tipo | Código | Fórmula | Flujo principal |
|------|--------|---------|-----------------|
| Solo lentes | `lens_only` | Obligatoria (a+b+c) | Material + lab de lentes |
| Solo montura | `frame_only` | **No requiere** | Catálogo + try-on (+ talla si aplica) |
| Lentes + montura | `lens_and_frame` | Obligatoria (a+b+c) | Material + montura + cadena §9 |

## 9. Cadena multi-fabricante (confirmado)

- Puede haber **1..N fabricantes**; cada uno puede proveer solo lentes, solo monturas, o ambos.
- Si montura y lentes son de **fabricantes distintos**: el **fabricante de montura envía la montura al fabricante de lentes** para fabricar y montar los lentes en esa montura.
- El **fabricante de lentes** entrega el **producto terminado** (gafa montada) hacia Zonix / paciente / óptica.
- Si un solo fabricante provee ambos SKUs, puede omitirse el tramo inter-fabricante.

`[PENDIENTE founder]` Ver §11 — flete montura→lab, plazos si la montura llega tarde al lab, SLA desde pago vs desde recepción en lab.

## 10. Couriers (confirmado modelo)

- Puede haber **1..N couriers**; cada **tramo logístico** puede usar carrier distinto.
- Tracking **por tramo** (`ShipmentLeg`), no un único número opaco.
- Tramos típicos: montura→lab | lab→hub/VE | última milla→paciente u óptica.

`[PENDIENTE founder]` Ver §11 — quién contrata cada courier (Zonix vs fabricante).

## 11. Pendientes logística y catálogo (cerrar antes de programar)

> **Tabla canónica** — misma numeración que [CHECKLIST_FOUNDER_S11.md](CHECKLIST_FOUNDER_S11.md).  
> *Montura sola sin lab:* confirmado en §8 (`frame_only` envío directo, sin fórmula) — no es ítem de decisión §11.

| # | Tema | Opciones / nota |
|---|------|-----------------|
| 1 | **Flete montura→lab** | A) Incluido en PVP · B) Paga Zonix · C) Paga fab montura |
| 2 | **Courier por tramo** | A) Zonix contrata todos · B) Cada fabricante el suyo · C) Mixto |
| 3 | **SLA total ~30 días `[PENDIENTE §11]`** | A) Desde `paid` · B) Desde montura en lab · C) Distinto por `order_type` |
| 4 | **Catálogo cross-fab** | A) Reglas compatibilidad · B) Solo bundles homologados · C) Mismo fab obligatorio |
| 5 | **Hub VE** | A) Consolidación obligatoria · B) Lab→paciente directo · C) Hub solo si aduana |

Detalle operativo: [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md).  
Checklist cierre: [CHECKLIST_FOUNDER_S11.md](CHECKLIST_FOUNDER_S11.md).

## 12. `frame_only` — inventario mixto (confirmado founder)

- **Modelo mixto:** monturas **más vendidas** en stock local VE (capital de trabajo Zonix); resto del catálogo **contra pedido** al fabricante.
- Implica SKU con flag `in_stock_ve` vs `made_to_order` en catálogo futuro.
- Try-on y checkout igual; fulfillment 6A directo desde stock o desde fab según SKU.

## 13. Política cambiaria (confirmado founder)

- Cobro al paciente en **Bs** a **tasa Binance del momento** (referencia explícita en checkout).
- Tras validar comprobante (`paid`): conversión operativa a **USDT inmediato** (tesorería Zonix).
- Alternativa aceptada: cobro directo en **USD** (Binance / efectivo) sin paso por Bs.
- Anti-fraude comprobante y responsable de tipo de cambio: `[PENDIENTE founder]` — proceso ops en [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) (sección Pagos VE + FX) y [MEJORAS_MODELO_NEGOCIO.md](../MEJORAS_MODELO_NEGOCIO.md).

## 14. Canal y ópticas aliadas (confirmado founder — preliminar)

- **Principio:** proteger al aliado (Zonix no captura clientes del aliado para venderles directo sin reglas).
- **Futuro:** canal **B2B mayorista** (reposición monturas/lentes a ópticas) además del B2B2C paciente-under-partner.
- Variante aceptable a corto plazo: **solo proteger aliado** sin mayorista aún — marcar contrato B2B `[PENDIENTE abogado]`.
- Política operativa: [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md). Unit economics por canal: [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md) `[PENDIENTE cifras founder]`.

## Referencias

- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [../DOMINIO_DATOS.md](../DOMINIO_DATOS.md)

**Última actualización:** 2026-06-27
