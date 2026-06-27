---
name: zonix-glasses-api-patterns
description: >
  Contratos REST Zonix Glasses — óptica B2B2C: partners, pacientes, fórmulas, catálogo, órdenes.
  Trigger: Crear o modificar endpoints Laravel o contratos con Flutter.
license: UNLICENSED
metadata:
  author: Zonix Glasses
  version: "1.0"
  scope: [domain]
  category: backend
  auto_invoke:
    - "Nuevo endpoint Zonix Glasses"
    - "Contrato API óptica"
  triggers: zonix-glasses, api, optical, prescription, order
  related-skills: [laravel-specialist, api-design-principles, security, zonix-glasses-prescriptions]
allowed-tools: [Read, Edit, Write, Glob, Grep, Bash]
---

# Zonix Glasses — API Patterns

## Convenciones

- Envelope: `{ success, data, message }`
- Auth: Sanctum + RBAC (`user`, `optical_partner`, `admin`)
- Multi-tenant: filtrar por `optical_partner_id` salvo admin
- Form Requests + Services; `DB::transaction()` en checkout
- Paginación obligatoria en listados

## Prefijos propuestos

| Rol | Prefijo |
|-----|---------|
| Paciente | `/api/patient/` |
| Partner | `/api/partner/` |
| Admin | `/api/admin/` |
| Catálogo público autenticado | `/api/catalog/` |

## Endpoints (MVP)

> **Código actual (feature 001 experimental):** solo prescripción bajo `/api/partner/prescriptions/*` y tenant partner.  
> **Catálogo, carrito y órdenes** — tabla siguiente es **spec 002+** (no implementado; congelado hasta HITL + seguridad).

| Método | Ruta | Descripción | Spec |
|--------|------|-------------|------|
| GET | `/api/partner/patients` | Lista pacientes del partner | 001 |
| POST | `/api/partner/patients` | Alta paciente bajo tenant | 001 |
| POST | `/api/partner/prescriptions` | Carga manual fórmula | 001 |
| POST | `/api/partner/prescriptions/ocr` | OCR stub → `pending_review` (throttle 10/min) | 001 |
| POST | `/api/partner/prescriptions/{id}/approve` | Capa (a) — requiere `pd` | 001 |
| POST | `/api/partner/prescriptions/{id}/reject` | Rechazo con `reason` | 001 |
| GET | `/api/patient/prescriptions` | Historial propio | 001 |
| POST | `/api/patient/prescriptions/{id}/confirm` | Capa (c) paciente → `patient_confirmed` | 001 |
| GET | `/api/catalog/lens-materials?prescription_id=` | Materiales + precios | **002+** |
| GET | `/api/catalog/frames` | Monturas paginadas | **002+** |
| POST | `/api/patient/face-captures` | Fotos try-on | **002+** |
| POST | `/api/patient/cart/items` | Añadir lente/montura | **002+** |
| POST | `/api/patient/orders` | Checkout | **002+** |
| GET | `/api/patient/orders/{id}` | Tracking | **002+** |

## Ejemplo respuesta Prescription

```json
{
  "success": true,
  "data": {
    "id": 12,
    "status": "patient_confirmed",
    "od": { "sphere": -2.5, "cylinder": -0.75, "axis": 90 },
    "oi": { "sphere": -2.25, "cylinder": null, "axis": null },
    "addition": 1.5,
    "pd": 62.0,
    "optical_partner_id": 3
  },
  "message": "Fórmula confirmada"
}
```

## Anti-patrones

- Exponer fórmulas sin policy tenant.
- Lógica pricing en controller (usar `PricingService`).
- Endpoints smart-glasses legacy (`/api/devices`).

## Canon

- `docs/DOMINIO_DATOS.md`
- `docs/MODELO_NEGOCIO/FLUJOS_OPERATIVOS.md`
