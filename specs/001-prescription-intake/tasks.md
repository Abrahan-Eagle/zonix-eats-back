# Tasks — 001 Prescription Intake

## Backend

- [x] B1 Migraciones: `optical_partners`, `patient_profiles`, `prescriptions`, `optical_partner_users`
- [x] B2 Seed: partner `Zonix Direct`, rol `optical_partner`
- [x] B3 Models + factories
- [x] B4 PrescriptionService (manual + approve + patient confirm)
- [x] B5 PrescriptionOcrService stub + config flag
- [x] B6 Form Requests validación dioptrías + PD
- [x] B7 Partner + Patient controllers + routes
- [x] B8 Policies + middleware tenant
- [x] B9 Feature tests isolation
- [x] B10 Skill prescriptions alineada

## Frontend (optional slice — fase 1.1)

- [ ] F1 `front:lib/features/optical/models/prescription.dart`
- [ ] F2 `front:lib/features/optical/services/prescription_service.dart`
- [ ] F3 Partner screen: lista pacientes + form fórmula
- [ ] F4 Widget upload OCR con estado pending_review

## Docs

- [x] D1 `DECISIONES_FOUNDER.md`, `POLITICA_COMERCIAL.md`, flujos/dominio actualizados
- [x] D2 HITL preparado — founder revisa ítems `[PENDIENTE]`
- [x] D3 `docs/active_context.md` post-implement *(actualizado ronda forense v2 — Jun 2026; no implica feature descongelada)*

**Implement:** backend **experimental pre-pivot** (Jun 2026) — **no cuenta como gate HITL**; congelado hasta realineación + [AUDIT_RIESGOS_SEGURIDAD.md](../../docs/AUDIT_RIESGOS_SEGURIDAD.md) cerrado. Informe doc v2: [AUDIT_FORENSE_2026-06-27.md](../../docs/AUDIT_FORENSE_2026-06-27.md).
