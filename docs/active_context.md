# Contexto activo de sesión — Zonix Eats Backend

> **Uso:** La IA debe leer este archivo al iniciar o retomar trabajo en el proyecto para recuperar el estado reciente sin depender de que el usuario lo pida.
> La skill **context-updater** indica cómo actualizar este archivo al cerrar una sesión relevante.

---

## Última actualización de contexto

*(La skill **context-updater** rellena esta sección al final de sesiones con cambios relevantes. Si está vacía, no hay resumen pendiente.)*

- **Fecha:** 6 Marzo 2026
- **Resumen:** Módulos cerrados y Jarvis actualizado para continuar con otro. Backend: operator_codes (create + seeder), ZonixDemoSeeder (zonas Valencia, user 6, conexiones entre roles), consolidación de migraciones (editar creates, eliminar add/change), norma Migraciones (no add/change; editar create), tests (MultiRoleSimulationTest + migración phones down() en SQLite). Frontend: onboarding dropdown código de operador (fallback, formato 0412).
- **Áreas tocadas:** `database/seeders/OperatorCodeSeeder.php`, `ZonixDemoSeeder.php`, migraciones (create_operator_codes_table, add_context_and_entity_fks_to_phones_table), `tests/Feature/MultiRoleSimulationTest.php`, `lib/features/screens/onboarding/client_onboarding_flow.dart`, `.cursorrules`, AGENTS.md (back y front), `docs/active_context.md`, README (back y front).
- **Próximos pasos sugeridos:** Continuar con otro módulo. Tests backend pasan (269). Commit/push cuando el usuario lo indique.

---

## Notas

- No borres este archivo; si no hay nada que resumir, deja las secciones con "—".
- Mantén una sola entrada "Última actualización" y reemplázala cada vez (no acumules infinitas entradas).
- Incluye solo lo que ayude a la siguiente sesión: decisiones de diseño, archivos clave modificados, tareas a medio hacer, bloqueos conocidos.
