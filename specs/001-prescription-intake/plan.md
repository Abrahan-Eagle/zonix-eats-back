# Plan — 001 Prescription Intake

## Technical Approach

### Backend

1. Migración `optical_partners`, `patient_profiles` (or extend profiles), `prescriptions`
2. Modelos + `PrescriptionService`, `PrescriptionOcrService` (interface + stub)
3. Controllers `Partner/PatientController`, `Partner/PrescriptionController`
4. Policies `PrescriptionPolicy`, middleware `EnsureOpticalPartner`
5. Rutas bajo `/api/partner/*`
6. Tests: `PrescriptionIntakeTest.php`

### Frontend (fase paralela opcional)

- `front:lib/features/optical/` — partner list patients, form fórmula, upload OCR
- Servicios alineados a contrato API

## Dependencies

- Scaffold auth/perfiles existente
- Firebase no bloqueante para 001

## Risks

- OCR provider TBD → stub con revisión manual obligatoria
- Regulación fórmula VE → copy legal pendiente

## Verification

```bash
php artisan test --filter=Prescription
```

## Implement Gate

**NO ejecutar implementación hasta OK founder en `docs/HITL_APROBACION_DOCS.md`.**
