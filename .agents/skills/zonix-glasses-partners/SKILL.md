---
name: zonix-glasses-partners
description: >
  Ópticas aliadas B2B: onboarding, multi-tenant, pacientes bajo partner.
  Trigger: optical_partner, aliado, tenant, panel óptica.
license: UNLICENSED
metadata:
  author: Zonix Glasses
  version: "1.0"
  scope: [domain]
  category: backend
  related-skills: [zonix-glasses-api-patterns, zonix-glasses-prescriptions, security]
allowed-tools: [Read, Edit, Write, Glob, Grep, Bash]
---

# Zonix Glasses — Partners (B2B)

## Rol `optical_partner`

- Registro/aprobación admin.
- Invitación pacientes (código, link, QR).
- Dashboard: pacientes, fórmulas pendientes, pedidos del tenant.

## Multi-tenant

- Columna `optical_partner_id` en pacientes, prescripciones, órdenes.
- Middleware `EnsureOpticalPartner` + header opcional `X-Partner-Id` para multi-sede futuro.

## Pricing aliado (v3 — mayorista, no comisión %)

- Tabla **`PartnerWholesalePrice`** (conceptual): mayor por SKU por óptica.
- Óptica paga **100% del mayor** a Zonix **antes de fabricar** — ver [POLITICA_CANAL_ALIADO.md](../../../docs/MODELO_NEGOCIO/POLITICA_CANAL_ALIADO.md).
- **`commission_rate`** en código legacy — **deprecar**; seed demo 10% no es decisión comercial.

## Seed

Partner `Zonix Direct` (`is_zonix_direct=true`) para canal B2C.
