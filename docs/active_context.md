# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 18 Marzo 2026
- **Resumen:** Correcciones post-refactorización de Pusher. Se corrigieron 3 bugs: (1) canal incorrecto `private-users` → `private-user` en `orders_page.dart` que impedía recibir eventos al buyer; (2) `NotificationService()` instanciado con constructor en `commerce_orders_page.dart` causando listeners huérfanos (ahora usa Provider); (3) `markAllAsRead()` no actualizaba `_unreadCount` ni items en memoria (badge se quedaba con conteo viejo). Se hicieron casts seguros en 8 pantallas (`.toString()` en vez de `as String`), se optimizó suscripción Pusher al cambiar de rol (`UserProvider` ahora suscribe `private-commerce.$id` si el rol es commerce), y se limpiaron 12 lint warnings (0 issues en `flutter analyze`).
- **Áreas tocadas:** `orders_page.dart`, `commerce_orders_page.dart`, `notification_service.dart`, `user_provider.dart`, `order_detail_page.dart`, `current_order_detail_page.dart`, `buyer_order_chat_page.dart`, `commerce_dashboard_page.dart`, `commerce_order_detail_page.dart`, `commerce_chat_messages_page.dart`.
- **Próximos pasos sugeridos:** Probar flujo completo Buyer→Commerce con Pusher en dispositivo. Verificar que el badge de notificaciones se resetea correctamente al abrir la página. Monitorear si Review/Dispute events necesitan migrar al patrón de Streams.
- **Correcciones adicionales (misma sesión):** Backend: al cancelar orden (Buyer) ahora se emite `OrderStatusChanged` para que comercio/comprador reciban el evento en tiempo real. Logs de depuración en producción reducidos: BroadcastingController y channels.php solo hacen Log::debug cuando `config('app.debug')`; eliminado `Log::info('ORDERS EN DB')` de OrderController. Documentación: conteos de tests actualizados a 269 (backend) y 250 (frontend) en AGENTS.md y README de ambos repos.

---

## Notas

- No borres este archivo; si no hay nada que resumir, deja las secciones con "—".
- Mantén una sola entrada "Última actualización" y reemplázala cada vez (no acumules infinitas entradas).
- Incluye solo lo que ayude a la siguiente sesión: decisiones de diseño, archivos clave modificados, tareas a medio hacer, bloqueos conocidos.
