# Privacidad — datos óptica y biometría facial

> No sustituye asesoría legal. Marcar `[PENDIENTE abogado]` donde aplique.

## Estado actual vs aspiracional (Jun 2026)

| Área | **Hoy (001 congelado / diseño)** | **Target post-HITL** |
|------|----------------------------------|----------------------|
| Fórmulas | API tenant + policies en spec; storage clínico pendiente | Disco privado + vigencia receta — [REALIGNMENT_POST_HITL.md](../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) |
| Try-on facial | No implementado en front | **Varias fotos reales** → análisis rostro → preview montura; borrado raw post sesión salvo consentimiento — §17 [DECISIONES_FOUNDER.md](MODELO_NEGOCIO/DECISIONES_FOUNDER.md) |
| Export / delete | Scaffold Eats heredado | Extender a `PatientProfile` + `Prescription` |
| Compartir con fabricante | Solo spec técnica de graduación (sin PII innecesaria) | Por `SupplierOrder`; país del fab según contrato — no asumir región |

## Datos sensibles

| Dato | Clasificación | Retención propuesta |
|------|---------------|---------------------|
| Fórmula óptica (dioptrías, PD) | Salud / sensible | Historial mientras cuenta activa + [PENDIENTE] plazo legal |
| Fotos faciales (try-on) | Biometría / imagen | **Set de varias fotos reales** por sesión; procesamiento para superponer monturas; borrar raw post try-on salvo consentimiento explícito |
| Documentos PDF fórmula | Salud | Disco privado; acceso auth + policy tenant |
| Pedidos y pagos | PII comercial | Según política retención fiscal VE |

## Principios técnicos (implementación futura)

1. **Minimización:** no pedir fotos hasta flujo try-on; cuando se pide, **varias tomas guiadas** (no una sola).
2. **Aislamiento tenant:** partner A no ve pacientes de partner B.
3. **Storage privado:** blobs en disco `local`/S3 privado; URLs vía endpoint autenticado.
4. **OCR IA:** opt-in; log sin contenido clínico en texto plano.
5. **Export / delete:** reutilizar scaffold `data_export` y `account_deletion` extendidos a fórmulas.

## Consentimientos UX (copy borrador)

- Uso de **varias fotos reales** del rostro para simulación visual de monturas (no diagnóstico ni medicion clinica).
- Almacenamiento de fórmula para historial y producción.
- Compartir spec técnica de graduación con **fabricante de lentes** contratado para el pedido (sin PII innecesaria; país/región según `Supplier` — `[PENDIENTE abogado]` cláusulas transferencia).

## AppSec checklist

- Sanctum + policies por `optical_partner_id`.
- Rate limit OCR y upload fotos.
- Auditar accesos admin a fórmulas.

## Referencias

- [MODELO_NEGOCIO/ROLES_Y_ACTORES.md](MODELO_NEGOCIO/ROLES_Y_ACTORES.md)
- Skill `security` + `zonix-glasses-prescriptions`
