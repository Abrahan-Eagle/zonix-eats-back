# Decisiones de negocio — Founder (Jun 2026)

> Registro de decisiones para alinear docs, spec 001 e implementación.  
> **Validación a+b+c:** confirmada por founder en conversación ("las 3 a b c").  
> **Modelo v3 (pagos, pricing mayorista, delivery/courier):** sesión founder Jun 2026 — ver §3–§4, §9–§11.

## 1. Validación de fórmula (confirmado)

**Cadena en 3 capas** antes de fabricar:

| Capa | Actor | Acción |
|------|-------|--------|
| **a** | Óptica aliada (optometrista habilitado, presencial) u ops Zonix (online) | Revisa y aprueba graduación — ver párrafos presencial/online abajo |
| **b** | IA (OCR / visión) | Solo precarga borrador; nunca auto-confirma |
| **c** | Paciente | Confirma explícitamente antes de checkout/orden |

Estados técnicos: `draft` → `pending_review` → `approved` → `patient_confirmed` → `locked_for_order`.

**Canal presencial / local (óptica aliada o consulta en sede):** capa (a) = **optometrista u oftalmólogo habilitado** mide y aprueba graduación.

**Canal 100% online (Zonix directo, sin visita):** **no** hay optometrista Zonix en el flujo. Capa (a) operativa = **revisión humana ops/partner remoto** sobre datos cargados (OCR + manual + comparación de las 3 vías) antes de `approved`; capa (c) paciente confirma igual. `[PENDIENTE abogado VE]` si esta revisión cumple requisito clínico para fabricar lentes en canal digital.

## 2. Distancia pupilar (DP) — propuesta MVP

| Prioridad | Método |
|-----------|--------|
| 1 | Óptica aliada o consulta **presencial** Zonix mide y registra `pd` / `pd_near` |
| 2 | Paciente ingresa si la conoce (validación mínima de rango) |
| 3 | IA estima desde foto — **fase 2**; no bloquea MVP |

**Regla:** no pasar a `approved` sin `pd` (monocular o binocular según proveedor).

**Canal B2C directo (confirmado founder 2026-06-27):** fase 1 **no exige** consulta presencial obligatoria. Flujo online con capa (a) ops + OCR. El paciente **puede** acudir presencialmente a sede Zonix Glasses si lo desea (opcional, no bloqueante).

## 3. Pago — confirmado founder v3

### 3.1 Validación de comprobante

Tras transferencia / pago móvil / Zelle, el cliente u óptica sube **captura o referencia**. **Ops Zonix** verifica que el dinero entró y que la referencia no se reutilizó → recién entonces se paga al fabricante y se dispara producción. Ver [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md).

### 3.2 Canal directo (Zonix → paciente)

| Modalidad | Al confirmar pedido | Al retirar / entregar |
|-----------|---------------------|------------------------|
| **4.1** | **100%** del **total checkout** | — |
| **4.2** | **30%** del **total checkout** | **70%** restante |

- **70%:** transferencia, pago móvil, presencial en hub u online antes de retirar.
- **Producción 4.2:** arranca tras validar el **30%**.
- **Retención si no paga 70%:** ver [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) — plazo 30 días en hub; multa tardía = `total × hub_late_pickup_fee_percent` (default **10%**, variable admin); `[PENDIENTE abogado VE]`.

### 3.3 Canal aliado (Zonix → óptica → paciente)

| Quién | Qué paga a Zonix | Cuándo |
|-------|------------------|--------|
| **Óptica aliada** | **100% del precio mayor** (por SKU) | **Antes de fabricar** |
| **Paciente** | Lo que la óptica decida (200 USD, 30/70, etc.) | Entre paciente y óptica |

Zonix **no impone** split al paciente del aliado. La óptica asume riesgo de cobro si ya pagó 100% mayor a Zonix.

### 3.4 FX (§13)

Tasa **Binance del día en que se paga** cada tramo (30%, 70% o 100%). Tras validación: conversión operativa a **USDT** (tesorería Zonix).

## 4. Pricing — confirmado founder v3 (sin comisión %)

**Descartado:** Zonix fija PVP único y aliado gana comisión %.

**Modelo vigente — tres capas:**

| Capa | Ejemplo | Visible a |
|------|---------|-----------|
| Costo mayor fab → Zonix | ~3 USD (montura + lab + envíos fab) | Zonix + fabricante |
| Mayor Zonix → óptica | **Por SKU** (ej. 50 USD) — Zonix fija | Óptica aliada |
| PVP | Óptica libre (ej. 200 USD) o Zonix directo (ej. 180 USD) | Público |

- **Stock VE vs MTO importado:** **mismo precio mayor** al aliado; diferencia = **tiempo**, no tarifa.
- **Checkout canal directo:** desglose dinámico en líneas (montura, lentes/lab, courier intl, delivery VE) → total final; ver §15.

## 5. Entrega al paciente — confirmado v3

- **Default:** paciente elige domicilio o retiro en óptica aliada / hub Zonix.
- **Online:** entrega a domicilio cuando hub lo habilita; depende de decisión operativa del hub.
- Logística: delivery China (fab) → courier internacional (Zonix) → hub VE → delivery última milla (Zonix propio o MRW/Domesa/etc.).

## 6. Portabilidad del historial — confirmado founder (Jun 2026)

- El **paciente** pertenece a su **óptica aliada** (`optical_partner_id`).
- Las **fórmulas** pertenecen al paciente; no se eliminan al cambiar de canal.
- **Si la óptica aliada cesa:** pacientes nuevos pasan a Zonix; **pedidos ya vendidos al paciente deben culminarse** por la óptica (o handoff acordado) antes de cierre operativo.
- Recompra: paciente puede usar fórmulas `patient_confirmed` / `locked_for_order`.

**Código 001:** migraciones con `cascadeOnDelete` — cambiar a `nullOnDelete` / job reasignación antes de descongelar.

## 7. Entrada de fórmula (confirmado implícito)

Tres vías: receta paciente, medición profesional, IA OCR → capas a+b+c.

**Alcance MVP:** `lens_and_frame` con fórmula (a+b+c); `frame_only` **sin** fórmula. **`lens_only` no en MVP** — no promocionar solo cristales.

## 8. Composición del pedido (confirmado v3)

| Tipo | Código | MVP | Fórmula | Flujo principal |
|------|--------|-----|---------|-----------------|
| Solo montura | `frame_only` | Sí | No | Captura facial + try-on → carrito; stock VE o MTO |
| Lentes + montura | `lens_and_frame` | Sí | Obligatoria (a+b+c) | Try-on → fórmula → cadena §9 |
| Solo lentes | `lens_only` | **No** | Obligatoria | Fase 2 |

## 9. Cadena multi-fabricante (confirmado v3)

- **1..N fabricantes** con **login MVP**; solo lentes, solo monturas, o ambos.
- **Inter-fábrica:** solo entre fabricantes con **convenio activo Zonix Glasses**.
- Montura (Fab A) → **delivery del fabricante** (China, paga fab) → lab (Fab B) → producto terminado → **courier internacional Zonix** → hub VE.
- Fabricante integrado (lentes + montura): mismo flujo delivery fab → courier Zonix.
- **Cross-fab:** **sin homologación de catálogo** — el lab fabrica la lente con la forma de la montura recibida.

Ejemplo costos referencia interna: montura ~1 USD (fab 1) + lab ~5 USD montaje (fab 2) + envíos fab + courier Zonix.

## 10. Delivery vs courier (confirmado v3)

| Tipo | Quién contrata | Quién paga | Alcance |
|------|----------------|------------|---------|
| **Delivery fabricante** | Fabricante | Fabricante | China: inter-fab (convenio Zonix) + entrega al punto/courier que Zonix indica |
| **Courier internacional** | **Zonix** | Zonix | Avión/barco a Venezuela; couriers registrados en sistema; dirección fijada por Zonix |
| **Delivery VE** | Zonix (propio o tercero) | Según política envío | Hub → óptica aliada o domicilio paciente (MRW, Domesa, flota propia, etc.) |

Tracking **por tramo** (`ShipmentLeg`).

## 11. Logística y catálogo — cerrado founder v3

| # | Tema | Decisión |
|---|------|----------|
| 1 | **Flete montura→lab** | **Paga fabricante montura** vía su delivery (incluido en costo mayor negociado con Zonix) |
| 2 | **Courier por tramo** | **Mixto:** delivery fab (China) + courier internacional Zonix + delivery VE (Zonix/tercero) |
| 3 | **SLA ~30 días** | Desde **inicio fabricación de lentes / montaje en montura** (no desde pago ni llegada montura al lab) |
| 4 | **Catálogo cross-fab** | **Permitido sin homologación** — lab usa forma de montura recibida |
| 5 | **Hub VE** | **Sí** — consolidación obligatoria |

Checklist firmado: [CHECKLIST_FOUNDER_S11.md](CHECKLIST_FOUNDER_S11.md).

## 12. `frame_only` — inventario mixto (confirmado founder)

- **Default operativo:** pedido al **proveedor** (MTO) — canal directo y aliado.
- **Stock hub Zonix VE:** capacidad de cargar monturas en almacén Zonix (`in_stock_ve`); al lanzar **no importa** si hay 0 unidades — la opción debe existir en sistema.
- **Stock óptica aliada:** inventario en almacén de la óptica = **activo de la óptica**; Zonix no opera ese almacén ni emite `SupplierOrder` por esas unidades (venta local del aliado).
- Cuando el pedido pasa por hub/fab Zonix: mismo precio mayor aliado en stock hub y MTO (§4); solo cambia plazo.

## 13. Política cambiaria (confirmado founder v3)

- Cobro en **Bs** a tasa **Binance del día de cada pago** (30%, 70% o 100%).
- Alternativa: cobro directo **USD**.
- Anti-fraude comprobante: [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md).

## 14. Canal y ópticas aliadas (confirmado founder v3)

- **Mayorista activo MVP:** Zonix fija precio mayor **por SKU** a cada óptica aliada; PVP libre al paciente.
- **Proteger aliado:** no captura pacientes del aliado sin reglas — [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md).
- Contrato B2B: `[PENDIENTE abogado VE]`.

## 15. Checkout y fiscal — confirmado founder v3.1

| Regla | Detalle |
|-------|---------|
| Desglose | Checkout muestra líneas que **van sumando** (montura, lentes/lab, courier, delivery VE) hasta **total final** |
| IVA | **Incluido en PVP**; línea informativa **16%** en checkout — no se suma IVA extra. Factura SENIAT `[PENDIENTE contador]` |
| Envío gratis | **Configurable** por zona/campaña (`ShippingZoneRule`); línea envío = 0 cuando aplica |
| Multa retiro tardío | `hub_late_pickup_fee_percent` default **0.10** (10% sobre total pedido); editable admin |
| Base pagos | Total checkout = base para 4.1 (100%) y 4.2 (30%/70%) |

Detalle operativo: [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) · entidades: [DOMINIO_DATOS.md](../DOMINIO_DATOS.md).

## 16. Cierre founder — sesión 2026-06-27 (parte 2)

| # | Tema | Decisión |
|---|------|----------|
| 1 | **Canal aliado — UX** | Óptica **logueada** en panel; paciente es **usuario de esa óptica** (`optical_partner_id`). **Captura facial** (varias fotos reales) → IA analiza rostro → **try-on virtual** superpone monturas y muestra preview al paciente → carrito → fórmula (si aplica) → **T&C** → pago → notificación **ops Zonix** → aprobación → fábrica. Ver §17 y [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md). |
| 2 | **Aduanas lab→VE** | **Courier internacional** gestiona impuestos/aduana; costo **incluido** en línea `intl_courier` / PVP. Cliente y óptica aliada **no ven** trámites internos Zonix. |
| 3 | **Vigencia fórmula / recompra** | Reconfirmación si antigüedad **> 12 meses** desde última `patient_confirmed` / `locked_for_order`. |
| 4 | **Anti-fraude pago MVP** | **Conciliación manual:** comprobante + verificar ingreso en banco antes de `paid_full` / `deposit_validated`. Integración banco como intermediario = **fase futura**. |
| 5 | **Marca** | `BRAND_ZONIX_GLASSES.md` **aprobado founder** para app/web (2026-06-27). |

## 17. Try-on IA — captura facial (confirmado founder 2026-06-27)

| Regla | Detalle |
|-------|---------|
| **Prerrequisito** | Antes de explorar monturas con try-on, el paciente completa **captura facial con varias fotos reales** (no una sola selfie). |
| **Captura** | Múltiples tomas: rostro descubierto, buena luz, ángulos guiados (frente y laterales según UX). Entidad `FaceCapture.images[]`. |
| **Procesamiento** | IA **analiza el rostro** (proporciones, posición) a partir del set; **no** sustituye medición clínica ni PD. |
| **Salida try-on** | En **detalle de producto**, IA superpone el armazón sobre el rostro del paciente y muestra preview («cómo te queda»). |
| **Catálogo marketplace** | Listado/grid con **fotos normales de montura** cargadas en sistema (`Frame.catalog_images[]`) — **sin** rostro del paciente. |
| **Detalle producto** | Al **tocar** una montura: (1) preview **rostro del paciente con montura puesta**; (2) **mismas fotos de catálogo** del producto (sin cara); (3) **abajo**, ficha técnica (tamaño, materiales, cómo medir, medidas del armazón, datos para análisis/compra). |
| **Plataforma** | **Web** (navegador) — storefront B2C y panel óptica aliada. Referencia UX: páginas tipo AliExpress/Alibaba (PDP desktop 2 columnas). **No** asumir app mobile nativa como canal principal del marketplace monturas. Responsive web sí; diseño de referencia = desktop. |
| **Siguiente paso** | Si le gusta → **añadir al carrito** desde detalle → checkout (directo o aliado). |
| **Calidad** | Si el set es insuficiente, pedir **repetir captura** antes de habilitar try-on. |
| **Privacidad** | Consentimiento explícito antes de captura; ver [PRIVACIDAD_OPTICA.md](../PRIVACIDAD_OPTICA.md). |
| **Distinto de** | **OCR receta** (foto/PDF de fórmula) · **PD por IA** (fase 2, §2) — no confundir flujos. |

Aplica a **canal directo** y **canal aliado** (misma secuencia; contexto tenant del aliado en panel partner).

### UX web — página detalle montura (PDP)

> Patrón de referencia visual: marketplace e-commerce eyewear (AliExpress / Alibaba) — **scroll vertical web**, no pantalla mobile app.

**Listado (PLP):** grid de tarjetas con thumbnail `catalog_images[0]`, nombre, PVP desde, badge stock/MTO.

**Detalle (PDP) — bloque superior (above the fold, 2 columnas desktop):**

| Columna | Contenido Zonix |
|---------|-----------------|
| **Izquierda — galería** | Imagen principal con **conmutador de modo:** (A) **«Ver en ti»** — try-on: rostro del paciente + montura (default si `FaceCapture` completo); (B) **Fotos producto** — `catalog_images[]` sin rostro. Miniaturas verticales u horizontales para cambiar foto/modo (como thumbnails AliExpress). |
| **Derecha — caja compra** | Título SKU · PVP / preview precio · selector **color/variante** (thumbnails) · disponibilidad stock VE / MTO · plazo estimado · CTA **«Añadir al carrito»** · CTA secundario comprar (según canal). En aliado: copy que indica mayorista a Zonix antes de fab. |

**Debajo del fold (scroll, mismo estilo catálogo B2B/B2C):**

1. **Tabla especificaciones** — material, tipo de montura, peso, modelo, etc. (`Frame.specs`).
2. **Diagrama de medidas (mm)** — ancho total, ancho puente, ancho/altura lente, longitud patilla (`Frame.dimensions` + `measurement_guide`). **Crítico para IA:** escala try-on y orientación al paciente.
3. **Galería ampliada** — ángulos adicionales, detalle bisagras/material, variantes de color (fotos catálogo).
4. **Contenido marketing opcional** — lifestyle / empaque (si ops carga assets).
5. **Relacionados (opcional MVP)** — grid «Más monturas» (PLP embed).

**Diferencia vs AliExpress:** el slot de imagen principal incluye **try-on personalizado** además de fotos de producto; la ficha técnica incluye datos ópticos de armazón, no solo moda.

### Checklist alta montura — ops/admin (MVP)

> Campos mínimos para publicar en PLP/PDP web + try-on. Entidad: [DOMINIO_DATOS.md](../DOMINIO_DATOS.md) `Frame` · mayor aliado vía `PartnerWholesalePrice`.

**Identidad y precio (obligatorio)**

| Campo | Notas |
|-------|--------|
| `sku`, `name`, `brand`, `manufacturer_id`, `manufacturer_sku` | Identificación y `SupplierOrder` |
| `price` | PVP canal directo Zonix |
| `PartnerWholesalePrice.wholesale_usd` | Por óptica aliada / SKU (canal B2B2C) |

**Variantes (obligatorio si hay más de un color)**

| Campo | Notas |
|-------|--------|
| `color` (+ fila o variante por color) | Selector thumbnails en PDP |

**Fotos catálogo `catalog_images[]` (mínimo 4)**

| Toma | Obligatoria |
|------|-------------|
| Frente (producto solo, fondo limpio) | Sí |
| Lateral / 3/4 | Sí |
| Otro ángulo (45° o perfil patilla) | Sí |
| Detalle bisagra / puente | Recomendada |

**Medidas (obligatorio — crítico try-on IA)**

| Campo | Unidad |
|-------|--------|
| Ancho total, puente, ancho lente, alto lente, longitud patilla | mm |
| `size_label` | ej. `52-18-140` |
| `measurement_guide` | Texto o imagen «cómo medir» |

**Ficha técnica (obligatorio)**

| Campo | Ejemplo |
|-------|---------|
| `material` | Titanio, acetato, etc. |
| Peso en `specs` | gramos |
| Tipo montura en `specs` | Full rim, semi-rimless |
| `shape`, `gender` | Opcional catálogo/filtros |

**Logística (obligatorio)**

| Campo | Valores |
|-------|---------|
| `made_to_order` | true / false |
| `in_stock_ve` + `stock_qty` | Si hay stock hub Zonix |

**Opcional MVP:** lifestyle, empaque, marketing scroll, tags, relacionados.

**Verificación antes de publicar**

- [ ] PLP: thumbnail + nombre + PVP
- [ ] PDP: galería fotos producto + miniaturas
- [ ] PDP: tabla specs + diagrama medidas mm visible
- [ ] Try-on: `dimensions` completas para escalar montura
- [ ] Mayor aliado cargado si aplica canal B2B2C
- [ ] PVP coherente con línea `frame` en checkout

## Referencias

- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [UNIT_ECONOMICS.md](UNIT_ECONOMICS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [../DOMINIO_DATOS.md](../DOMINIO_DATOS.md)

**Última actualización:** 2026-06-27 (§17 checklist alta montura ops)
