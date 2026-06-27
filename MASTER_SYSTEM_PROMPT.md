# MASTER_SYSTEM_PROMPT — Zonix Glasses

Ecosistema IA para **Zonix Glasses** (Laravel + Flutter) — **óptica online B2B2C**.

## Producto

- **Backend:** `zonix-glasses-back` — API REST, Sanctum, multi-tenant ópticas
- **Frontend:** `zonix-glasses-front` — app paciente/partner, paquete `zonix_glasses`
- **Skills globales:** `jarvis-skills-library` → sync Paso C en `.agents/skills/`
- **Skills dominio:** `zonix-glasses-*`
- **Canon negocio:** `docs/PRODUCT_VISION.md`, `docs/MODELO_NEGOCIO/`

## Identificadores

| Capa | Valor |
|------|-------|
| BD | `zonix_glasses` |
| Android/iOS | `com.zonix.glasses` |
| Dart | `zonix_glasses` |

## Reglas

1. Globales vía manifest; no duplicar fuera de sync.
2. Lógica óptica exclusiva → prefijo `zonix-glasses-*`.
3. No implementar dominio smart-glasses (BLE/OTA) — pivot Jun 2026.
4. Spec Kit implement solo con OK founder (`docs/HITL_APROBACION_DOCS.md`).

**Última actualización:** Junio 2026
