# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 9 Marzo 2026
- **Resumen:** Módulo Exportar datos cerrado. Backend: ruta GET /api/profile/export (auth:sanctum, cualquier rol), ExportController.getProfileDataForExport defensivo con profile null. Frontend: exportPersonalData() → /api/profile/export; descarga real (archivo + Share); formato TXT con ciudad y activity_type corregidos.
- **Áreas tocadas:** `routes/api.php`, `app/Http/Controllers/Buyer/ExportController.php`, `lib/features/DomainProfiles/Profiles/api/profile_service.dart`, `lib/features/DomainProfiles/Profiles/screens/data_export_page.dart`, AGENTS.md, README, .cursorrules, docs/active_context.md (back y front).
- **Próximos pasos sugeridos:** Elegir siguiente módulo (notificaciones, órdenes commerce, promociones, paginación backend, i18n, etc.). Commit/push cuando el usuario lo indique.

---

## Notas

- No borres este archivo; si no hay nada que resumir, deja las secciones con "—".
- Mantén una sola entrada "Última actualización" y reemplázala cada vez (no acumules infinitas entradas).
- Incluye solo lo que ayude a la siguiente sesión: decisiones de diseño, archivos clave modificados, tareas a medio hacer, bloqueos conocidos.
