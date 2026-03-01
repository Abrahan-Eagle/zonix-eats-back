# 🛠️ Guía de Mantenimiento de Skills y Coherencia — Zonix Eats

Esta guía define las reglas para mantener la integridad y coherencia del sistema de documentación y lógica procedimental de Zonix Eats. Es de lectura obligatoria para cualquier IA o humano que desee modificar las **Custom Skills**.

---

## 1. El Sistema de Skills (Por qué existe)

Las skills (`.agents/skills/*.md`) no son simple documentación; son **guías procedimentales** para que la IA actúe como un experto especializado. Transforman a una IA genérica en un "Zonix Engineer" que conoce los estados de las órdenes, las reglas de pago y el sistema de diseño sin tener que redescubrirlos cada vez.

---

## 2. Terminología Estándar de Roles

Cualquier cambio en código o docs **DEBE** usar esta nomenclatura para evitar alucinaciones de la IA:

| Nivel | Código en BD | Nombre Estándar | Alias aceptados            |
| ----- | ------------ | --------------- | -------------------------- |
| 0     | `users`      | **Buyer**       | Comprador, Cliente         |
| 1     | `commerce`   | **Commerce**    | Comercio, Restaurante      |
| 2     | `delivery`   | **Delivery**    | Delivery Agent, Repartidor |
| 3     | `admin`      | **Admin**       | Administrador              |

---

## 3. Reglas de Oro para Actualizaciones

### 3.1. Auditoría Previa (Mandatorio para IAs)

Antes de proponer un cambio en una skill o en `README.md`, la IA debe:

1. Leer todas las skills custom (actualmente 7).
2. Identificar si el cambio afecta a otros dominios (ej: un cambio en estados de orden afecta a `realtime-events` y `payments`).
3. Generar un pequeño reporte de impacto (como el `coherence_audit.md` original).

### 3.2. Sincronización Cross-Project

Zonix Eats se divide en `zonix-eats-back` y `zonix-eats-front`.

- Las skills de lógica (ej: `order-lifecycle`, `realtime-events`) viven en ambos repositorios.
- **Regla:** Si actualizas la versión en el Backend, la copia en el Frontend **debe** actualizarse inmediatamente para que ambos agentes hablen el mismo idioma.

### 3.3. Cross-References

Toda skill debe referenciar a otras si hay solapamiento. Ejemplo:

- La skill de `payments` referencia a `order-lifecycle` para los estados.
- La skill de `onboarding` referencia a `api-patterns` para el formato de respuesta.

---

## 4. Infraestructura Crítica (Inamovible)

Existen reglas técnicas que no deben "alucinarse":

1. **NO WebSockets:** Usar exclusivamente Pusher Channels + FCM.
2. **Canales Privados:** Toda actualización de orden usa canales `private-`.
3. **Roles:** Existen 6 roles: admin, users, commerce, delivery_company, delivery_agent, delivery. delivery_company = empresa de delivery; delivery_agent = motorizado bajo empresa; delivery = motorizado autónomo. Los roles `transport` y `affiliate` están eliminados.
4. **Deprecaciones:** `profiles.phone` no debe usarse; los teléfonos están en la tabla `phones`.

---

## 5. Cómo Hacer Cambios (IA Flow)

1. **Analizar:** Leer `AGENTS.md` y `MAINTENANCE_SKILLS.md`.
2. **Proponer:** Crear un `implementation_plan.md` detallando las skills a modificar.
3. **Ejecutar:** Aplicar cambios, subir versión de la skill (v1.0 -> v2.0) y añadir fecha de actualización.
4. **Verificar:** Correr auditoría de coherencia.

---

**Última actualización:** 25 Febrero 2026
**Zonix Team**
