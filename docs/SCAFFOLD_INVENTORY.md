# Inventario — Zonix Glasses Backend

Módulos incluidos en la API base (scaffold) y dominio óptica pendiente.

## Scaffold incluido

| Módulo | Ubicación | Descripción |
|--------|-----------|-------------|
| Auth Sanctum + Google | `routes/api/auth.php` | Login, registro, token |
| Perfiles | `Profiles/ProfileController` | CRUD perfil 1:1 con user |
| Teléfonos / Direcciones / Documentos | `Profiles/*Controller` | Datos de usuario — **legacy CI/RIF en disco `public`**; **NO** usar para fórmula clínica (ver [AUDIT_RIESGOS_SEGURIDAD.md](AUDIT_RIESGOS_SEGURIDAD.md) §9) |
| Pagos VE | `PaymentMethodController` | Métodos de pago, bancos |
| Notificaciones | `NotificationController` | In-app + FCM |
| Admin | `Admin/UserController` | Gestión usuarios |
| Panel web | `routes/web.php` | Dashboard CoreUI |

## Dominio óptica — por implementar

| Módulo | Prioridad | Skill |
|--------|-----------|-------|
| Checkout + `OrderLineItem` + pagos 4.1/4.2 | P0 | [POLITICA_COMERCIAL.md](MODELO_NEGOCIO/POLITICA_COMERCIAL.md) · [DOMINIO_DATOS.md](DOMINIO_DATOS.md) |
| `PlatformConfig` (multa hub, IVA display) | P0 | DOMINIO § PlatformConfig |
| Partners (ópticas aliadas) | P0 | `zonix-glasses-partners` |
| Prescripciones / OCR fórmula | P0 | `zonix-glasses-prescriptions` |
| Catálogo materiales + monturas | P1 | `zonix-glasses-api-patterns` |
| Try-on IA (servicio externo) | P1 | `zonix-glasses-virtual-tryon` *(front `.agents/skills/`)* |
| Carrito + órdenes óptica | P1 | `zonix-glasses-api-patterns` |
| Fulfillment multi-fab + hub | P2 | `zonix-glasses-fulfillment` |

**Deprecado:** narrativa smart-glasses / BLE / OTA (pivot Jun 2026).

Canon: `docs/PRODUCT_VISION.md`, `docs/DOMINIO_DATOS.md`.

Frontend: `../zonix-glasses-front` — paquete `zonix_glasses`.
