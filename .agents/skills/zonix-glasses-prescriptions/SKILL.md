---
name: zonix-glasses-prescriptions
description: >
  Fórmulas ópticas: captura manual, OCR IA, historial paciente, vínculo partner.
  Trigger: Prescription, OCR, fórmula, graduación, PD.
license: UNLICENSED
metadata:
  author: Zonix Glasses
  version: "1.1"
  scope: [domain]
  category: backend
  auto_invoke:
    - "Fórmula óptica Zonix Glasses"
    - "OCR prescription"
  triggers: prescription, formula, ocr, optometry
  related-skills: [zonix-glasses-api-patterns, zonix-glasses-partners, security]
allowed-tools: [Read, Edit, Write, Glob, Grep, Bash]
---

# Zonix Glasses — Prescriptions

## Flujo

1. Partner o paciente sube documento o ingresa campos manualmente.
2. OCR (opt-in config) produce borrador `pending_review`.
3. Profesional (capa a) aprueba → `approved` (requiere `pd`).
4. Paciente (capa c) confirma → `patient_confirmed`.
5. Checkout futuro → `locked_for_order` (post OpticalOrder).
6. Historial append-only (`superseded` al reemplazar).

## Modelo (ver `docs/DOMINIO_DATOS.md`)

- `Prescription`: estados `draft`, `pending_review`, `approved`, `patient_confirmed`, `locked_for_order`, `rejected`, `superseded`
- FK: `patient_profile_id`, `optical_partner_id`, optional `document_id` *(target: FK + policy — ver AUDIT_RIESGOS)*

## Seguridad

- Policy: partner solo tenant; paciente solo propias; admin audit.
- Rate limit OCR: `throttle:10,1`
- No loguear valores clínicos en texto plano.
- Prerrequisitos pre-descongelar: [AUDIT_RIESGOS_SEGURIDAD.md](../../../docs/AUDIT_RIESGOS_SEGURIDAD.md)

## Servicios

- `PrescriptionService` — CRUD + confirm
- `PrescriptionOcrStubService` — stub local MVP; proveedor externo fase 2

## Tests Feature (spec 001)

Alineado a `PrescriptionIntakeTest` — **6 escenarios** prescripción (~46 suite total):

- Partner confirma fórmula de su paciente
- Partner no accede fórmula de otro tenant
- OCR deshabilitado → 503; habilitado → pending_review
- PD obligatoria para approve
- Flujo manual draft → approved → patient_confirmed

## Feature Spec Kit

`specs/001-prescription-intake/` — **CONGELADO** hasta HITL + AUDIT
