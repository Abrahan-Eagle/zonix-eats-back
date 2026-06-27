# Auditoría de riesgos de seguridad — Feature 001 (código congelado)

> **Alcance:** prerrequisitos documentados **antes de descongelar** el backend experimental `001-prescription-intake`.  
> **No se corrige código en esta fase** — solo registro de hallazgos y gates.

**Origen:** auditoría forense docs + código (Jun 2026).  
**Relacionado:** [HITL_APROBACION_DOCS.md](HITL_APROBACION_DOCS.md), [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md).

## Resumen ejecutivo

| Severidad | Count | Gate descongelar |
|-----------|-------|------------------|
| Crítica | 7 | **Bloqueante** — resolver o mitigar con diseño aprobado |
| Alta | 4 | **Bloqueante** pre-producción — documentado v2 forense |

Feature 001 es **experimental pre-pivot** — no sustituye HITL de negocio ni §11 logística; además requiere remediación de seguridad antes de cualquier deploy.

## Hallazgos (prerrequisitos)

### 1. IDOR en `document_id` — CRÍTICO

- **Riesgo:** referencia a documento de otro tenant/paciente sin FK + scoping estricto en policy.
- **Target:** FK obligatoria `prescriptions.document_id` → `documents.id`; policy valida `optical_partner_id` del paciente.
- **Verificación:** test adversarial cross-tenant debe fallar con 403/404.

### 2. Documentos en disco `public` — CRÍTICO

- **Riesgo:** URLs públicas de recetas/fotos clínicas sin auth.
- **Target:** disco `local` (privado); descarga vía endpoint autenticado + policy (patrón CorralX documents).
- **Verificación:** no existe URL pública directa a blob clínico.

### 3. Datos clínicos sin cifrar — CRÍTICO

- **Riesgo:** `clinical_notes`, dioptrías, PD en texto plano en BD.
- **Target:** cifrado at-rest (Laravel encrypted casts o columna cifrada) + minimización en logs.
- **Verificación:** dump BD no expone valores legibles; logs sin PII clínica.

### 4. `cascadeOnDelete` sobre datos de salud — CRÍTICO

- **Riesgo:** baja de óptica aliada borra pacientes/fórmulas — contradice portabilidad §6 founder.
- **Target:** reasignación a Zonix; `nullOnDelete` / job admin — ver [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) §6.
- **Verificación:** test de cese aliado preserva registros clínicos.

### 5. OCR externo sin consentimiento / minimización — CRÍTICO

- **Riesgo:** envío de imagen de receta a proveedor IA sin base legal ni opt-in.
- **Target:** consentimiento explícito, DPA/proveedor documentado, retención mínima, posibilidad de flujo manual sin OCR.
- **Verificación:** feature flag + registro de consentimiento en audit log.

### 6. Aprobación capa (a) sin credencial profesional — CRÍTICO

- **Riesgo:** cualquier user partner puede aprobar graduación sin verificar optometrista habilitado.
- **Target:** rol/credencial en profile partner (nº colegiado MPPS u otro `[PENDIENTE abogado VE]`) obligatorio para `approve`.
- **Verificación:** approve sin credencial → 422/403.

### 7. Sin vigencia de receta — CRÍTICO

- **Riesgo:** fórmulas obsoletas usadas para producción sin `prescribed_at` / `valid_until`.
- **Target:** campos de vigencia + bloqueo checkout si expirada `[PENDIENTE abogado VE]` plazo legal.
- **Verificación:** test rechaza `patient_confirmed` vencida en checkout futuro.

### 8. Cascade `profile_id` — ALTA

- **Riesgo:** `patient_profiles.profile_id` → `cascadeOnDelete` en `profiles` borra historial clínico al eliminar cuenta usuario.
- **Target:** soft-delete cuenta o preservar `PatientProfile` anonimizado según retención legal.
- **Verificación:** delete account no elimina fórmulas sin política explícita.

### 9. Módulo `Document` legacy (CI/RIF) — ALTA

- **Riesgo:** scaffold `documents.type` solo `ci`/`rif` + disco `public` + URLs en accessor — incompatible con receta clínica.
- **Target:** entidad `clinical_document` o extensión tipada + storage privado; no reutilizar FK a CI/RIF para fórmula.
- **Verificación:** upload fórmula no pasa por `DocumentController` legacy sin rediseño.

## Matriz MVP vs target (OCR y storage)

| Ítem | MVP actual (código 001) | Target pre-producción |
|------|-------------------------|------------------------|
| OCR | Stub local `PrescriptionOcrStubService`; flag `OPTICAL_OCR_ENABLED` | Consentimiento + proveedor + DPA si externo |
| Storage fórmula | `Document` público CI/RIF | Blob clínico privado + endpoint auth |
| `document_id` | Entero sin FK | FK + policy tenant |

## Promesas spec 001 no implementadas (documentar, no confundir con listo)

- Upload binario foto/PDF en `POST /prescriptions/ocr` (solo campos JSON hoy)
- Source `patient_upload` sin endpoint dedicado
- Transición `locked_for_order` (sin OpticalOrder)
- Admin API cross-tenant (solo policy)
- `superseded` nunca seteado en servicio

## Checklist pre-descongelar

- [ ] IDOR document_id cerrado + tests
- [ ] Storage privado + endpoint auth
- [ ] Cifrado campos clínicos
- [ ] Portabilidad / no cascade delete
- [ ] OCR: consentimiento + minimización
- [ ] Capa (a): credencial profesional
- [ ] Vigencia receta modelada
- [ ] Cascade profile_id / delete account revisado
- [ ] Storage clínico rediseñado (no Document CI/RIF público)
- [ ] Tests adversariales: IDOR document_id, cese partner, approve sin credencial
- [ ] Founder OK explícito tras revisar este doc

## Referencias

- [MEJORAS_MODELO_NEGOCIO.md](MEJORAS_MODELO_NEGOCIO.md) — agujeros negocio relacionados (anti-fraude comprobante, responsable legal fórmula)
- [PRIVACIDAD_OPTICA.md](PRIVACIDAD_OPTICA.md)
- [AUDIT_FORENSE_2026-06-27.md](AUDIT_FORENSE_2026-06-27.md)

**Última actualización:** 2026-06-27
