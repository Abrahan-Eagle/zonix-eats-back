# Roles y actores — Zonix Glasses

## Roster confirmado (v3 — founder Jun 2026)

| # | Rol | Descripción |
|---|-----|-------------|
| 1 | **Paciente / cliente** | Compra montura y/o paquete completo; paga según canal. |
| 2 | **Zonix Glasses (core / admin)** | Orquestación, pricing mayor, couriers, hub, disputas, validación comprobantes. |
| 3 | **Óptica aliada** | Mayorista B2B2C; PVP libre; paga **100% mayor** a Zonix antes de fabricar. |
| 4 | **Fabricante** | Login MVP; catálogo + precio mayor a Zonix; solo lentes / monturas / ambos. |
| 5 | **Delivery / courier** | Registrados en sistema; delivery fab (China), courier intl (Zonix), delivery VE. |

## Actores de negocio (detalle)

| Actor | Descripción | Login sistema |
|-------|-------------|---------------|
| **Paciente / cliente** | Compra `frame_only` o `lens_and_frame` (MVP). | Sí (`users`) |
| **Óptica aliada** | Partner B2B2C; valida fórmulas capa (a). | Sí (`optical_partner`) |
| **Zonix ops / admin** | Hub, fabricantes, couriers, deliveries, disputas, comprobantes. | Sí (`admin`) |
| **Fabricante** | Lab y/o monturas; convenio Zonix obligatorio para inter-fab. | **Sí (MVP)** — confirmado founder v3 ([DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §9) |
| **Delivery fabricante** | Empresas de envío China del fabricante (inter-fab + handoff courier). | Registro bajo fabricante |
| **Courier internacional** | Avión/barco a VE — contrato Zonix. | Registro admin |
| **Delivery VE** | MRW, Domesa, flota Zonix, etc. — hub → destino. | Registro admin |
| **Optometrista aliado** | Profesional en sede partner. | Bajo rol partner |

## Matriz rol técnico → API (futuro)

| Rol BD | Prefijo API (propuesto) | Permisos clave |
|--------|-------------------------|----------------|
| `user` | `/api/patient/*` | Perfil, fórmulas, catálogo, carrito, órdenes, comprobantes |
| `optical_partner` | `/api/partner/*` | Pacientes tenant, fórmulas, pedidos, pago mayor a Zonix |
| `manufacturer` | `/api/manufacturer/*` | Catálogo propio, precios mayor, SupplierOrders, deliveries |
| `admin` | `/api/admin/*` | Fabricantes, couriers, deliveries, catálogo mayor aliado, hub |

**Código:** feature 001 experimental **congelado** — rol `manufacturer` post-realineación.

## Multi-tenant

- Paciente bajo **óptica aliada** (`optical_partner_id`) o Zonix directo.
- **Cese aliado:** culminar pedidos abiertos; reasignación pacientes — [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §6.

## Validación fórmula (3 capas — solo `lens_and_frame` MVP)

Ver [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §1. **`frame_only`:** sin fórmula.

## Responsabilidades operativas

| Actor | Responsabilidad |
|-------|-----------------|
| Fabricante montura | Despachar montura; **delivery propio** a lab (convenio Zonix) o handoff courier |
| Fabricante lentes | Recibir montura, producir y montar; delivery a courier Zonix |
| Zonix ops | Validar comprobantes; pagar fabs; contratar courier intl; operar hub |
| Courier Zonix | Tramo internacional → hub VE |
| Delivery VE | Última milla hub → óptica/paciente |

## Responsabilidades fórmula `[PENDIENTE legal VE]`

| Tema | Borrador operativo |
|------|-------------------|
| Emisión / validación capa (a) | **Local:** optometrista en óptica aliada o sede con consulta |
| Partner 100% remoto (sin visita) | Revisión ops/partner remoto sobre OCR+manual — `[PENDIENTE legal VE]` (misma lógica operativa que canal online Zonix) |
| Canal online Zonix directo | **Sin optometrista Zonix** — revisión ops sobre OCR+manual; `[PENDIENTE legal VE]` |
| Error graduación | Costo quien aprobó capa (a) si error probado — [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) |

## Documentos relacionados

- [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md)
- [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md)
- [POLITICA_CANAL_ALIADO.md](POLITICA_CANAL_ALIADO.md)
- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [../PRIVACIDAD_OPTICA.md](../PRIVACIDAD_OPTICA.md)

**Última actualización:** 2026-06-27 (v3 founder)
