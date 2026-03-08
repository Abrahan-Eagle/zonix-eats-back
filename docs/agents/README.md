# Índice — Documentación detallada del backend

El **AGENTS.md** en la raíz contiene: contexto de sesión, overview, cambios recientes, setup, skills, auto-invoke y reglas de colaboración.  
La documentación detallada por tema está en este directorio. La IA debe usar este índice cuando necesite profundizar en un área sin perder el hilo del proyecto.

| Tema | Archivo | Contenido |
|------|---------|-----------|
| Arquitectura | [architecture.md](architecture.md) | Estructura del proyecto, MVC + servicios, patrones |
| Estilo de código | [code-style.md](code-style.md) | Naming, Controller/Service pattern, reglas clave |
| Testing | [testing.md](testing.md) | Comandos, patrón de tests |
| API | [api-conventions.md](api-conventions.md) | Response format, códigos HTTP, paginación |
| Roles y auth | [roles-auth.md](roles-auth.md) | 6 roles, Sanctum, middleware |
| Tiempo real | [realtime.md](realtime.md) | FCM + Pusher, eventos, canales |
| Reglas de negocio | [business-rules.md](business-rules.md) | MVP: carrito, órdenes, pagos, delivery, direcciones |
| Análisis exhaustivo | [analysis.md](analysis.md) | Prompts y checklist de análisis v2.0 |
| Mejoras pendientes | [pending-improvements.md](pending-improvements.md) | ALTO / MEDIO |
| Pagos por rol      | [../logica-pagos-por-rol.md](../logica-pagos-por-rol.md) | Quién configura métodos, flujo dinero, diagramas |
| Teléfonos          | [../LOGICA_MODULO_PHONE.md](../LOGICA_MODULO_PHONE.md)   | Tablas phones/operator_codes, dueño profile_id, roles |

**Cómo usar:** Leer AGENTS.md (raíz) para el estado actual y las reglas; abrir el archivo de este directorio que corresponda al tema en el que se está trabajando.
