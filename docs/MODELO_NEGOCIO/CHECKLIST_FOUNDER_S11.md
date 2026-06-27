# Checklist founder — §11 logística + cierre HITL

> Completar **antes de programar**. Marcar decisión y fecha; actualizar [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §11 y [HITL_APROBACION_DOCS.md](../HITL_APROBACION_DOCS.md).

**Verificación documental:** PASS documental parcial — bloqueado §11 — ver [VERIFICACION_MODELO_MULTI_PROVEEDOR.md](../VERIFICACION_MODELO_MULTI_PROVEEDOR.md) (2026-06-27).

**Auditoría forense v2:** [AUDIT_FORENSE_2026-06-27.md](../AUDIT_FORENSE_2026-06-27.md)

---

## Decisiones founder ya tomadas (fuera de §11)

| § | Tema | Decidido docs | Código / ops |
|---|------|---------------|--------------|
| 6 | Portabilidad — paciente bajo aliado; al cesar pasa a Zonix | ✅ | ❌ cascade delete en 001 |
| 12 | `frame_only` mixto (stock top + contra pedido) | ✅ | ❌ post-002 |
| 13 | Cambiaria Bs→Binance→USDT o USD directo | ✅ marco | ❌ anti-fraude `[PENDIENTE]` |
| 14 | Proteger aliado + B2B mayorista futuro | ✅ preliminar | ❌ contrato `[PENDIENTE abogado]` |

---

## §11 — Logística (bloquean implementación)

| # | Tema | Opciones | Decisión founder | Fecha |
|---|------|----------|------------------|-------|
| 1 | **Flete montura→lab** | A) Incluido en PVP paciente · B) Paga Zonix · C) Paga fab montura | `[ ]` _____________ | ____/____/____ |
| 2 | **Courier por tramo** | A) Zonix contrata todos · B) Cada fabricante el suyo · C) Mixto (Zonix última milla) | `[ ]` _____________ | ____/____/____ |
| 3 | **SLA ~30 días** | A) Desde `paid` · B) Desde montura recibida en lab · C) Distinto por `order_type` | `[ ]` _____________ | ____/____/____ |
| 4 | **Catálogo cross-fab** | A) Permitido con reglas compatibilidad · B) Solo bundles homologados · C) Mismo fab obligatorio en paquete | `[ ]` _____________ | ____/____/____ |
| 5 | **Hub VE** | A) Consolidación obligatoria · B) Lab→paciente directo · C) Hub solo si aduana | `[ ]` _____________ | ____/____/____ |

### Notas founder (opcional)

```
(montura sola: envío directo sin lab — confirmado sin fórmula en §8)
```

---

## HITL — Marca y legal

| Ítem | Responsable | Decisión | Fecha |
|------|-------------|----------|-------|
| [ ] `BRAND_ZONIX_GLASSES.md` — paleta/tokens | Founder visual | _____________ | ____/____/____ |
| [ ] `PRIVACIDAD_OPTICA.md` — datos clínicos VE | Abogado | _____________ | ____/____/____ |
| [ ] Modelo multi-fabricante + couriers + pedidos parciales | Founder | Acepto docs actuales / Ajustes: _________ | ____/____/____ |

---

## Tras firmar

1. Copiar decisiones §11 a tabla en [DECISIONES_FOUNDER.md](DECISIONES_FOUNDER.md) §11 (sustituir `[PENDIENTE founder]` donde aplique).
2. Marcar checklist en [HITL_APROBACION_DOCS.md](../HITL_APROBACION_DOCS.md).
3. Actualizar [active_context.md](../active_context.md) con fecha HITL cerrado.
4. Ejecutar [REALIGNMENT_POST_HITL.md](../../specs/001-prescription-intake/REALIGNMENT_POST_HITL.md) — solo con OK explícito de programación.

**Última actualización:** 2026-06-27
