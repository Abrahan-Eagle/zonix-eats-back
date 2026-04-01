# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 1 Abril 2026
- **Resumen:** Módulo Métodos de Pago cerrado con objetivo 10/10 verificable. Se completó hardening de endpoints legacy de pagos (`buyer/payments/*`) con control por rol/rate limit, señalización de deprecación (`X-API-Deprecated`, `X-API-Replacement`, `Sunset`) y gate por flag (`LEGACY_PAYMENT_PROCESSING_ENABLED`) para desactivar procesamiento legacy. Se reforzó el flujo canónico de comprobantes/validación con idempotencia y coherencia de contrato: bloqueo de reemplazo de comprobante ya validado (409), rechazo con motivo obligatorio, validación de comprobante existente y protección ante doble validación/rechazo. Se mantuvo compatibilidad transitoria con campos legacy en `orders` detrás de flag (`SYNC_LEGACY_ORDER_PAYMENT_FIELDS`). 
- **Áreas tocadas:** `routes/api/buyer.php`, `app/Http/Controllers/Buyer/PaymentController.php`, `app/Http/Controllers/Buyer/OrderController.php`, `app/Http/Controllers/Commerce/OrderController.php`, `tests/Feature/OrderPaymentTest.php`, `tests/Feature/MultiRoleSimulationTest.php`, `AGENTS.md`.
- **Próximos pasos sugeridos:** Conservar legacy processing deshabilitado por defecto en entornos objetivo, monitorear logs estructurados de eventos de pago y, al confirmar estabilidad operativa, ejecutar retiro definitivo de rutas legacy según ventana `Sunset` y runbook de rollback.

---

## Línea base reciente (no es backlog)

- Flujo post-pago con `all_payments_validated`, auto-asignación en `processing`, timeout ~60s, fallback empresa, QR pickup/delivery, chat de llegada, calificaciones con `order_id` y manejo independiente de errores.

---

## Backlog candidato (no implementado)

Inventario para decidir qué implementar después. **No** implica compromiso hasta aprobación explícita del líder del proyecto.

### Negocio / producto

| Área | Idea | Notas |
|------|------|--------|
| Tiempo | ETA de entrega / preparación | Mejora percepción y soporte al cliente. |
| Operación | Cancelaciones automáticas o reglas más claras | Alinear con políticas ya documentadas (ej. ventanas de comprobante). |
| Incentivos | Modelo claro para delivery company / agentes | Comisiones, prioridad, penalizaciones. |
| Cobertura | Zonas / módulo tarifa delivery | Base: `docs/PLAN_MODULO_TARIFA_DELIVERY.md`. |
| Monetización | Membresía fija (suscripcion a Commerce y Delivery Company, sin comision %) | Revisar `docs/logica-pagos-por-rol.md`. Modelo confirmado: solo membresia. |
| **Pagos VE** | **Zelle, Binance Pay, C2P, multi-moneda, limpieza enum** | **Plan completo en `docs/PLAN_METODOS_PAGO_VENEZUELA.md`. Incluye regulación Sudeban, fases, costos.** |
| Admin | Panel operativo (zonas, disputas, métricas) | Si el MVP lo requiere. |
| Propinas | Permitir o no | Decisión de negocio (MVP suele excluirlas). |

### Técnico / mantenibilidad

| Área | Idea | Archivos / notas |
|------|------|------------------|
| Rutas | Partir `routes/api.php` en archivos por dominio | Reduce carga cognitiva. |
| Entrada app | Reducir peso de `lib/main.dart` | Extraer providers / rutas. |
| Datos demo | Acotar `ZonixDemoSeeder` o documentar grafo | Valorar tamaño. |
| Tests | Ampliar cobertura en flujos críticos nuevos | Feature tests backend + tests servicios Flutter donde aplique. |
| Errores | Manejo centralizado en app | Mejora UX ante fallos de red. |

---

## Prioridad sugerida (siguiente iteración, no comprometida)

Sugerencia para la **próxima** ronda de trabajo (elegir 1–2 y planificar):

1. **Producto:** ETA visible (preparación / entrega aproximada) — API mínima + UI buyer/commerce si aplica.
2. **Técnico:** Refactor incremental de `routes/api.php` agrupando por dominio (manteniendo `php artisan test` verde).

Alternativa de negocio acotada: arrancar **módulo tarifa de delivery** según `docs/PLAN_MODULO_TARIFA_DELIVERY.md` si la prioridad es cobertura/zonas antes que ETA.

---

## Notas

- No borres este archivo; si no hay nada que resumir, deja las secciones con "—".
- Mantén una sola entrada "Última actualización" y reemplázala cada vez (no acumules infinitas entradas).
- Incluye solo lo que ayude a la siguiente sesión: decisiones de diseño, archivos clave modificados, tareas a medio hacer, bloqueos conocidos.
