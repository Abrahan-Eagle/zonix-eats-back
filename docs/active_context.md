# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 1 Abril 2026
- **Resumen:** Hardening global transversal de cierre aplicado en backend: auth pública endurecida (registro sin rol `admin` y verificación real de token Google fuera de testing), corrección de canal realtime en `PaymentValidated` para buyer vía `profile.user_id`, y paginación operativa en listados delivery críticos con límites seguros. Se cerró regresión completa con suite backend íntegra en verde.
- **Áreas tocadas:** `app/Http/Controllers/Authenticator/AuthController.php`, `app/Events/PaymentValidated.php`, `app/Http/Controllers/Delivery/DeliveryController.php`, `tests/Feature/RoleAuthenticationTest.php`, `AGENTS.md`.
- **Próximos pasos sugeridos:** Mantener monitoreo de uso de rutas legacy y, en una fase controlada, retirar alias/paths legacy restantes tras confirmar cero consumo en métricas.

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
