# Roles y actores — Zonix Glasses

## Roster confirmado (5 roles — founder Jun 2026)

| # | Rol | Descripción |
|---|-----|-------------|
| 1 | **Paciente / cliente** | Compra lentes, montura, o ambos bajo su óptica aliada o canal Zonix. |
| 2 | **Zonix Glasses (core / admin)** | Orquestación, catálogo, fabricantes, couriers, disputas, reasignación al cesar aliado. |
| 3 | **Óptica aliada** | Partner B2B2C; registra pacientes, valida fórmulas (capa a) cuando hay lentes. |
| 4 | **Fabricante** (lentes y/o monturas) | Contra pedido; puede ser 1..N por pedido. Sin login fase 1. |
| 5 | **Courier aliado** | Transporte por tramo; 1..N por pedido. Sin login fase 1. |

## Actores de negocio (detalle)

| Actor | Descripción | Login sistema |
|-------|-------------|---------------|
| **Paciente / cliente** | Compra lentes, montura, o ambos. | Sí (`users`) |
| **Óptica aliada** | Partner B2B; registra pacientes, valida fórmulas cuando hay lentes. | Sí (`optical_partner`) |
| **Zonix ops / admin** | Catálogo, fabricantes, couriers, disputas, orquestación tramos. | Sí (`admin`) |
| **Fabricante (N)** | Lab de lentes y/o proveedor de monturas; contra pedido. | No (fase 1 manual/email) |
| **Courier (N)** | Transporte por tramo (inter-fab, internacional, última milla). | No (fase 1 manual/integración) |
| **Optometrista aliado** | Profesional en sede partner (puede ser mismo user partner). | Bajo rol partner |

## Matriz rol técnico → API (futuro)

| Rol BD | Prefijo API (propuesto) | Permisos clave |
|--------|-------------------------|----------------|
| `user` | `/api/patient/*` | Perfil, fórmulas propias (si aplica), catálogo, carrito, órdenes |
| `optical_partner` | `/api/partner/*` | Pacientes tenant, fórmulas, pedidos de su red |
| `admin` | `/api/admin/*` | Fabricantes, couriers, catálogo, auditoría, disputas |

**Código:** feature 001 experimental **congelado** — no extender hasta HITL del modelo ampliado.

## Multi-tenant

- Cada **paciente** pertenece a exactamente una **óptica aliada** (`optical_partner_id`), incluyendo canal Zonix directo.
- Si la óptica aliada **cesa**, pacientes y fórmulas **pasan a Zonix** — ver [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §6.
- Queries y policies filtran por tenant salvo `admin`.

## Validación fórmula (3 capas — solo pedidos con lentes)

Aplica a `lens_only` y `lens_and_frame` — **no** a `frame_only`.

Ver [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §1 y §8.

## Responsabilidades operativas

| Actor | Responsabilidad |
|-------|-----------------|
| Fabricante montura | Despachar montura correcta; tramo 1 hacia lab si multi-fab |
| Fabricante lentes | Recibir montura, producir lentes montados, tramo final |
| Zonix ops | Orquestar SupplierOrders, couriers, excepciones, comunicación paciente |
| Courier | Ejecutar tramo asignado; tracking por ShipmentLeg |

## Responsabilidades fórmula [PENDIENTE legal VE]

| Tema | Borrador operativo |
|------|-------------------|
| Emisión fórmula | Óptica aliada o Zonix con optometrista habilitado |
| Validación antes de producción lentes | Capa (a) + (c); no aplica a solo montura |
| Error graduación post-entrega | Según capa fallida `[PENDIENTE abogado]` |
| Montura dañada en tramo inter-fab | Ver [POLITICA_COMERCIAL.md](POLITICA_COMERCIAL.md) |

## Documentos relacionados

- [FLUJOS_OPERATIVOS.md](FLUJOS_OPERATIVOS.md)
- [CADENA_SUMINISTRO.md](CADENA_SUMINISTRO.md)
- [../PRIVACIDAD_OPTICA.md](../PRIVACIDAD_OPTICA.md)

**Última actualización:** 2026-06-27
