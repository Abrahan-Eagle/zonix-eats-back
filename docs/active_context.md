# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 19 Marzo 2026
- **Resumen:** Subida a dev completada (backend y frontend). Backend: reorganización de seeders (de `_archive/` a `database/seeders/`), `NotificationService.php`, listener `OrderNotificationSubscriber`, ajustes en Events/BroadcastingController/rutas/migraciones. `.gitignore` actualizado con `venv_scraper/` y `pendrive_badblocks_result.txt`; eliminado del repo el archivo local `pendrive_badblocks_result.txt`. Frontend: ya en dev (comprobante Commerce, Pusher Streams, notificaciones, auth, Android). Documentación: AGENTS.md y active_context actualizados en ambos repos.
- **Áreas tocadas:** Backend: seeders, app/Listeners, app/Services/NotificationService, Events, .gitignore, docs. Frontend: ya documentado en sesión anterior (comprobante, Pusher).
- **Próximos pasos sugeridos:** Probar flujo completo en dev (Buyer→Commerce, Pusher, notificaciones). Valorar merge a main cuando esté estable. Revisar si .env debe permanecer fuera del historial (deshacer commit si se subió con datos sensibles).

---

## Notas

- No borres este archivo; si no hay nada que resumir, deja las secciones con "—".
- Mantén una sola entrada "Última actualización" y reemplázala cada vez (no acumules infinitas entradas).
- Incluye solo lo que ayude a la siguiente sesión: decisiones de diseño, archivos clave modificados, tareas a medio hacer, bloqueos conocidos.
