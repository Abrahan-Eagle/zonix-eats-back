# 🚀 Prompt Maestro: Sistema de Coherencia y Custom Skills

Copia y pega este contenido en un nuevo proyecto para que la IA configure un ecosistema de documentación y "skills" idéntico al de Zonix Eats.

---

## Prompt para la IA: Configuración de Gobernanza de Agentes

"Actúa como un Arquitecto de Sistemas y experto en IA Agentic. Tu objetivo es implementar un sistema de **Custom Skills** y **Gobernanza de Documentación** en este proyecto para asegurar coherencia total entre múltiples IAs y humanos.

### PASO 1: Estructura de Skills

Crea un directorio `.agents/skills/` y define las siguientes skills base (puedes adaptarlas al dominio del proyecto):

1. **[DOMAIN]-logic:** Reglas de negocio core, estados y transiciones.
2. **[DOMAIN]-api-patterns:** Formato de respuesta, middleware y convenciones de rutas.
3. **[DOMAIN]-realtime:** Sistema de notificaciones, eventos y canales (ej: Pusher/FCM).
4. **[DOMAIN]-ui-design:** Paleta de colores, componentes reutilizables y layouts.

### PASO 2: Terminología Estándar (Roles)

Define una tabla de terminología obligatoria en el `.cursorrules` y `README.md`.
_Ejemplo para Marketplace:_

- **Buyer:** Nivel 0 (users)
- **Seller:** Nivel 1 (merchants)
- **Logistics:** Nivel 2 (couriers)
- **Admin:** Nivel 3 (backoffice)

### PASO 3: Infraestructura de Control

Crea los siguientes archivos en la raíz:

1. **AGENTS.md:** Tu punto de entrada. Incluye comandos de setup, lista de skills disponibles y reglas de colaboración.
2. **MAINTENANCE_SKILLS.md:** Guía de mantenimiento. Establece que ANTES de cualquier cambio, debes realizar una 'Auditoría de Coherencia' leyendo todas las skills existentes.

### PASO 4: Reglas de Oro para la IA

Inserta estas reglas en el `.cursorrules`:

- **Contexto Primero:** Lee `AGENTS.md` antes de empezar cualquier tarea compleja.
- **Auditoría de Impacto:** Si cambias lógica central, actualiza todas las skills afectadas y sube su versión (v1.0 -> v2.0).
- **Cross-References:** Cada skill debe enlazar a otras si hay solapamiento (ej: la skill de pagos referencia a la de estados de orden).
- **Sync Cross-Project:** Si el proyecto tiene Back y Front, mantén las skills lógicas sincronizadas en ambos repositorios.

---

### Misión Inicial del Agente:

Una vez configurado esto, realiza una **Auditoría de Coherencia Inicial** de los archivos actuales del proyecto y reporta las inconsistencias encontradas vs. las nuevas reglas establecidas."

---

## ¿Por qué funciona este sistema?

1. **Reduce Alucinaciones:** Al tener una tabla de roles fija, la IA no inventa nombres de variables o rutas.
2. **Memoria de Largo Plazo:** Las skills actúan como "memoria procedimental" que sobrevive a sesiones de chat cerradas.
3. **Escalabilidad:** Permite que 10 IAs diferentes trabajen en el mismo proyecto sin romper los estándares definidos."
