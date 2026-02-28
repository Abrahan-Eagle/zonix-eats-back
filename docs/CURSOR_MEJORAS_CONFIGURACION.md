# Guía de mejoras para la configuración de Cursor IDE

> Basado en la [documentación oficial de Cursor](https://cursor.com/docs) y en las capturas de tu configuración actual.  
> Referencia: [Cursor Docs](https://cursor.com/es/docs) · [Rules](https://cursor.com/docs/context/memories) · [Skills](https://cursor.com/docs/context/skills) · [Agent best practices](https://cursor.com/blog/agent-best-practices)

---

## Paso a paso: qué hace cada ítem y si está implementado

| # | Qué hace | ¿Implementado? | Cómo implementarlo (si no) |
|---|----------|----------------|-----------------------------|
| **1** | **Commands** (`/test-back`, `/test-front`, `/pr`): flujos que el Agente ejecuta al escribir `/comando` en el chat (ej. correr tests o crear PR). | **Sí** | Ya están en `.cursor/commands/`. En el chat del Agente escribe `/` y elige el comando. |
| **2** | **Añadir Docs**: Cursor indexa documentación externa (Laravel, Flutter, Dart, etc.) para que el Agente tenga contexto. Back + Front: ver §9. | **No** | Cursor Settings → **Indexing & Docs** → **Docs** → **Add Doc** → pegar URLs de §9 (Laravel + Sanctum para back; Flutter + Dart para front). |
| **3** | **Reducir modelos**: tener solo 2–3 modelos activos deja el selector más claro y evita confusión. | **No** | Cursor Settings → **Agents** → **Models** → desactivar (toggle gris) todos salvo 1 rápido + 1 equilibrado + 1 max (recomendado: Opus 4.6 Fast, Sonnet 4.6 u Opus 4.6, Opus 4.6 Max). |
| **4** | **.cursorignore**: excluir de la indexación carpetas como `vendor/`, `storage/`, etc., para que Cursor indexe solo código útil. | **No** (plantilla sí) | En la raíz del repo: `cp docs/cursorignore.example .cursorignore`. Opcional. |
| **5** | **Usage-Based Pricing** (Cloud Agents): definir cómo se factura el uso de agentes en la nube. | **No** | Solo si usas Cloud Agents: Cursor Settings → **Cloud Agents** → **Manage Settings** → **Open** → configurar pricing. Opcional. |
| **6** | **Fetch Domain Allowlist**: permitir que el Agente abra automáticamente enlaces a dominios (laravel.com, php.net, etc.). | **No** | Cursor Settings → **Agents** → **Fetch Domain Allowlist** → añadir `laravel.com`, `php.net`, `docs.github.com` (o `*` para todos). Opcional. |
| **7** | **Partial Accepts** (Tab): aceptar la siguiente palabra de una sugerencia con Ctrl+FlechaDerecha. | **No** | Cursor Settings → **Agents** → **Tab** → activar **Partial Accepts**. Opcional. |
| **8** | **MCP Allowlist / servidores MCP**: permitir que herramientas MCP (Slack, DB, etc.) se ejecuten automáticamente o añadir servidores. | **No** | Si añades MCP: **Agents** → **MCP Allowlist** (formato `servidor:herramienta`). O **Tools & MCP** → **Add Custom MCP** / crear `.cursor/mcp.json`. Solo si los usas. |
| **9** | **File-Deletion Protection**: que el Agente no borre archivos sin preguntar. | **No** (decisión tuya) | Cursor Settings → **Agents** → **Protection Toggles** → activar **File-Deletion Protection** si quieres que nunca borre automáticamente. |
| **10** | **Hooks**: scripts que se ejecutan en momentos del Agente (ej. “hasta que pasen los tests”). | **No** | Cursor Settings → **Hooks** → configurar hooks (avanzado). Opcional. |

**Resumen:** Implementado en repo: **Commands** y **plantilla .cursorignore**. Todo lo demás se configura **a mano en Cursor Settings** (o es opcional).

---

## 1. Resumen de tu configuración actual

| Área | Estado actual | Prioridad de mejora |
|------|----------------|---------------------|
| **Agents** | Allowlist de comandos (git, php, flutter…), protecciones dotfile/external ON | Ajustar MCP y dominios si usas docs externos |
| **Tab** | Cursor Tab, Imports, Suggestions While Commenting ON | Opcional: Partial Accepts, Auto Import Python |
| **Models** | Varios Opus/Sonnet activos; muchos otros desactivados | Dejar 2–3 modelos por uso (rápido/equilibrado/max) |
| **Cloud Agents** | Usage-Based Pricing no configurado, GitHub verificado | Configurar pricing si usas Cloud Agents |
| **Tools & MCP** | Sin servidores MCP, Browser automation OFF | Añadir MCP si necesitas Slack, DB, etc. |
| **Rules, Skills, Subagents** | Reglas + AGENTS.md + skills en `.agents/skills/`; **No Skills/Subagents/Commands** en UI | Skills ya cargados desde `.agents/skills/`; opcional: Commands |
| **Hooks** | Sin hooks configurados | Opcional para flujos “hasta que pasen tests” |
| **Indexing & Docs** | 100% codebase, **No Docs Added** | Añadir docs (Laravel, API) para mejor contexto |
| **Network** | HTTP/2 | OK |
| **Beta** | Agent Autocomplete ON | OK |

---

## 2. Agents — Guía completa (todo lo que hay en Cursor Settings → Agents)

La sección **Agents** controla cómo se ejecuta el Agente (comandos, dominios, protecciones) y opciones de edición/terminal/atribución. Esto es **todo** lo que suele aparecer y para qué sirve.

---

### 2.1 Auto-Run Mode

- **Qué es:** Define si el Agente puede ejecutar cosas **sin pedirte permiso cada vez**.
- **Opciones típicas:** "Use Allowlist" (solo lo que está en las listas) o modos más permisivos.
- **Recomendación:** "Use Allowlist" para que solo ejecute comandos/herramientas que tú hayas permitido explícitamente.

---

### 2.2 Command Allowlist (lista blanca de comandos)

- **Qué es:** Lista de **comandos de terminal** que el Agente puede ejecutar **automáticamente** (sin preguntar).
- **Ejemplos:** `cd`, `git add`, `git commit`, `git push`, `php`, `flutter`, `git branch`, `adb`, `git checkout`, `git merge`.
- **Para qué sirve:** Si un comando no está en la lista, el Agente te pedirá confirmación antes de ejecutarlo. Así evitas que ejecute cosas que no quieres.
- **Recomendación:** Mantener solo los que usas (git, php, flutter, etc.). No añadir `rm -rf` ni comandos destructivos a menos que lo necesites.

---

### 2.3 MCP Allowlist

- **Qué es:** Lista de **herramientas MCP** (Model Context Protocol) que el Agente puede ejecutar automáticamente. MCP son servidores que dan herramientas extra (Slack, DB, Sentry, etc.).
- **Formato:** `servidor:herramienta`, o `servidor:*` para todas las de un servidor, o `*:*` para todas.
- **Ejemplo:** `filesystem:read_file`, `slack:send_message`.
- **Para qué sirve:** Si usas MCP, aquí eliges qué herramientas pueden correr sin preguntar. Si no usas MCP, dejarlo vacío.

---

### 2.4 Fetch Domain Allowlist

- **Qué es:** Dominios desde los que el Agente puede **hacer fetch** (leer contenido por HTTP) para usarlo como contexto (p. ej. documentación).
- **Para qué sirve:** Que el Agente pueda consultar Laravel, Flutter, PHP, GitHub docs, etc., sin que tú pegues el enlace. Si el dominio no está, no puede acceder.
- **Ejemplos:** `laravel.com`, `php.net`, `docs.flutter.dev`, `pub.dev`, `docs.github.com`. O `*` para permitir todos.
- **Dónde:** Cursor Settings → Agents → Fetch Domain Allowlist (campo de texto).

---

### 2.5 Auto-Approved Mode Transitions

- **Qué es:** Transiciones de **modo** (p. ej. de Agente a Plan) que se aprueban **automáticamente** sin preguntarte.
- **Ejemplo:** Si pones `agent->plan`, cuando el Agente pase a modo Plan no te pedirá confirmación.
- **Para qué sirve:** Acelerar flujos en los que confías; menos clics. Dejarlo vacío si prefieres aprobar cada cambio de modo.

---

### 2.6 Protection Toggles (interruptores de protección)

| Toggle | Qué hace | Recomendación |
|--------|----------|----------------|
| **Browser Protection** | Impide que el Agente ejecute herramientas del navegador automáticamente. | ON si no quieres que controle el browser; OFF si usas esa función. |
| **MCP Tools Protection** | Impide que ejecute herramientas MCP sin preguntar. | ON si quieres aprobar cada uso de MCP; OFF si confías en la MCP Allowlist. |
| **File-Deletion Protection** | Impide que **borre archivos** automáticamente. | ON si quieres revisar siempre las eliminaciones; OFF si quieres que pueda refactorizar borrando archivos. |
| **Dotfile Protection** | Impide que modifique archivos ocultos (`.gitignore`, `.env`, etc.) automáticamente. | ON para evitar cambios accidentales en configuración. |
| **External-File Protection** | Impide que cree o modifique archivos **fuera del workspace**. | ON para que solo toque archivos de tu proyecto. |

---

### 2.7 Inline Editing & Terminal

| Opción | Qué hace | Recomendación |
|--------|----------|----------------|
| **Legacy Terminal Tool** | Usa la herramienta de terminal antigua en modo Agente (para shells no estándar). | OFF salvo que tengas problemas con el terminal. |
| **Toolbar on Selection** | Muestra botones "Add to Chat" y "Quick Edit" al seleccionar código. | ON para acceso rápido. |
| **Auto-Parse Links** | Parsea enlaces al pegarlos en Quick Edit (Ctrl+K). | Según preferencia. |
| **Themed Diff Backgrounds** | Colores de fondo temáticos en los diffs de código. | ON para leer mejor los cambios. |
| **Terminal Hint** | Muestra una sugerencia de Ctrl+K en la terminal. | ON si usas Ctrl+K en terminal. |
| **Preview Box for Terminal Ctrl+K** | Usa caja de vista previa en lugar de streaming directo en la shell. | Según preferencia. |

---

### 2.8 Voice Mode (modo voz)

- **Submit Keywords:** Palabras clave que **envían automáticamente** el mensaje en modo voz (ej. decir "submit" para enviar). Solo palabras sueltas, sin espacios.
- **Para qué sirve:** Usar el Agente por voz sin tener que pulsar enviar. Opcional.

---

### 2.9 Attribution (atribución)

| Opción | Qué hace | Recomendación |
|--------|----------|----------------|
| **Commit Attribution** | Marca los commits hechos por el Agente como co-autor con Cursor. | ON si quieres que quede reflejado en el historial. |
| **PR Attribution** | Marca los Pull Requests como hechos con Cursor. | ON si quieres métricas/transparencia. |

---

### Resumen rápido Agents

- **Listas (allowlists):** Command, MCP, Fetch Domain — definen **qué** puede ejecutar o consultar el Agente sin preguntar.
- **Protecciones:** Browser, MCP, File-Deletion, Dotfile, External-File — definen **qué** no puede hacer (borrar archivos, tocar .env, salir del workspace, etc.).
- **Resto:** Auto-Run mode, Auto-Approved transitions, Inline/Terminal, Voice, Attribution — preferencias de flujo y UX.

---

## 3. Tab (autocompletado e imports)

### Lo que tienes bien
- **Cursor Tab** y **Suggestions While Commenting** activados.
- **Imports** para TypeScript (aunque el backend es PHP, no afecta).
- **Themed Diff Backgrounds** para ver mejor los diffs.

### Mejoras opcionales
- **Partial Accepts**: “Accept the next word via Ctrl+RightArrow”. Útil si te gusta aceptar sugerencias palabra por palabra; actívalo si lo pruebas y te resulta cómodo.
- **Auto Import for Python (BETA)**: irrelevante para Laravel; dejarlo OFF está bien.

---

## 4. Models

### Recomendación según la documentación
- No hace falta tener activos todos los modelos: consumen contexto y pueden hacer la lista confusa.
- Mejor **2–3 modelos** claros:
  - Uno **rápido** (ej. Opus 4.6 Fast o similar) para cambios pequeños.
  - Uno **equilibrado** (ej. Sonnet 4.6 u Opus 4.6) para el día a día.
  - Uno **máximo** (ej. Opus 4.6 Max) para tareas complejas o planes grandes.

Puedes desactivar el resto para tener el selector más limpio y un comportamiento más predecible.

---

## 5. Cloud Agents

- **Usage-Based Pricing: Not Configured** aparece en amarillo. Si usas o piensas usar Cloud Agents, configúralo desde **Manage Settings → Open** para evitar sorpresas de uso.
- **GitHub Access: Verified** y **Base Environment: Default Ubuntu** están bien para desarrollo estándar.

---

## 6. Tools & MCP

- **Browser Automation**: OFF está bien si no necesitas que el agente controle el navegador.
- **Show Localhost Links in Browser**: ON es útil para probar la API (por ejemplo `php artisan serve` en 8000) y que Cursor abra enlaces de localhost.

### Si quieres ampliar capacidades
La documentación recomienda [MCP (Model Context Protocol)](https://cursor.com/docs/context/mcp/directory) para integrar Slack, bases de datos, Sentry, etc. Pasos:

1. En **Tools & MCP** → **Add Custom MCP**.
2. O definir servidores en `<project-root>/.cursor/mcp.json` para configuración por proyecto.

Ejemplo de uso: un servidor MCP de base de datos para que el agente consulte esquemas o datos sin salir de Cursor.

---

## 7. Rules, Skills, Subagents y Commands

### Lo que ya tienes (y está bien)
- **Rules**: “Always respond in Spanish” (User Rule) y reglas de proyecto desde `.cursorrules` y `AGENTS.md`. Cursor también carga skills desde `.agents/skills/` (p. ej. `clean-code-principles/**`); eso cuenta como “reglas” que el agente aplica cuando decide que son relevantes.
- **Include third-party skills, subagents, and other configs**: ON permite usar el ecosistema de skills; recomendable dejarlo así.

### Diferencia Rules vs Skills (según la doc)
- **Rules** (`.cursor/rules/` o `AGENTS.md`): instrucciones persistentes; pueden ser “always apply”, por archivo o manualmente con `@regla`. Ideales para estilo, arquitectura y convenciones del proyecto.
- **Skills** (`.agents/skills/` o `.cursor/skills/`): capacidades que el agente invoca cuando son relevantes o con `/` en el chat. En tu proyecto ya tienes muchas skills en `.agents/skills/` (laravel-specialist, zonix-order-lifecycle, etc.); Cursor las descubre automáticamente.

Que en la UI diga “No Skills Yet” puede referirse a skills creadas desde la propia UI (Cursor Settings → New Skill). Las de `.agents/skills/` se cargan desde el disco y pueden aparecer bajo “AGENTS” o en “Show all (3 more)”.

### Commands (flujos reutilizables con `/`)
La documentación recomienda **Commands** para flujos que repites mucho, por ejemplo:
- `/pr`: commit, push y abrir Pull Request.
- `/review`: linters y resumen de qué revisar.
- `/test`: ejecutar tests del área actual.

Puedes crearlos como archivos Markdown en `.cursor/commands/` (si Cursor los soporta en tu versión) o desde **Cursor Settings → Rules, Skills, Subagents → Commands → New Command**.

Sugerencia para Zonix Eats:
- **Command `/test-back`**: ejecutar `php artisan test` (opcionalmente con `--filter=...` según contexto).
- **Command `/test-front`**: ejecutar `flutter test` en el proyecto front.

### Subagents
Opcional. Útiles para tareas muy acotadas en paralelo (por ejemplo “solo refactor de tests”). Puedes añadir uno más adelante si ves que un flujo concreto lo pide.

---

## 8. Hooks

- Ahora mismo: **No hooks configured**. Está bien para la mayoría de flujos.
- Los hooks sirven para scripts que se ejecutan en momentos del agente (por ejemplo “al terminar”). La doc menciona un patrón “run until tests pass” con un hook que re-ejecuta el agente hasta que los tests pasen. Solo tiene sentido si quieres automatizar ese ciclo; si no, puedes ignorarlo.

---

## 9. Indexing & Docs (muy recomendable)

- **Codebase Indexing**: 100% (1114 archivos) está bien; el agente entiende bien tu código.
- **Index New Folders**: ON está bien para que carpetas nuevas se indexen solas.
- **No Docs Added**: aquí hay margen de mejora.

### Añadir documentación como contexto
La documentación dice que puedes “Crawl and index custom resources and developer docs” para dar mejor contexto al agente. **Contexto del proyecto:** el agente ya usa `.cursorrules` y `README.md` (y `AGENTS.md`) de backend y frontend cuando trabaja en cada repo; añadir Docs externos da además Laravel/Flutter oficial sin pegarlos en cada chat.

1. En **Cursor Settings → Indexing & Docs → Docs** → **Add Doc**.
2. Pega **una** URL por doc (backend y/o frontend según el workspace).

Así el agente tendrá convenciones de framework + contexto de Zonix Eats.

### URLs listas para añadir (Add Doc)

En **Cursor Settings → Indexing & Docs → Docs → Add Doc** pega **una** URL por doc. Tienes Backend (Laravel) y Frontend (Flutter) para cubrir ambos repos de Zonix Eats.

**Backend (zonix-eats-back)** — Laravel 10.x, PHP 8.1+, Sanctum, Pusher + FCM:

| URL | Uso |
|-----|-----|
| `https://laravel.com/docs/10.x` | Laravel 10 (versión del proyecto) |
| `https://laravel.com/docs/10.x/eloquent` | Eloquent ORM |
| `https://laravel.com/docs/10.x/api-authentication` | Sanctum (auth API) |
| `https://laravel.com/docs/10.x/validation` | Validación y Form Requests |
| `https://laravel.com/docs/10.x/routing` | Rutas y controladores |
| `https://laravel.com/docs/10.x/broadcasting` | Broadcasting (Pusher) |
| `https://php.net/manual/es/` | Referencia PHP (opcional) |

**Frontend (zonix-eats-front)** — Flutter ≥3.5, Dart 3.5+, Provider, Pusher + FCM:

| URL | Uso |
|-----|-----|
| `https://docs.flutter.dev/` | Flutter (widgets, layout, estado) |
| `https://dart.dev/guides` | Dart (lenguaje, async, null safety) |
| `https://pub.dev/packages/provider` | Provider (state management del proyecto) |
| `https://pusher.com/docs/channels/getting_started/flutter/` | Pusher en Flutter (tiempo real) |
| `https://pub.dev/packages/http` | Cliente HTTP (API REST) |
| `https://pub.dev/packages/flutter_secure_storage` | Tokens y almacenamiento seguro |

**Recomendación:** si trabajas en **ambos** repos, añade al menos **Laravel 10.x** + **Sanctum** (back) y **docs.flutter.dev** + **Dart guides** (front). El resto según lo que toques más.

---

## 10. .cursorignore (opcional)

Para que Cursor no indexe carpetas pesadas o irrelevantes, crea en la raíz del proyecto un archivo **`.cursorignore`** (además de `.gitignore`). Ejemplo de contenido:

```gitignore
/vendor/
node_modules/
/storage/logs/
/storage/framework/
/bootstrap/cache/
/public/build/
.env
.env.*
!.env.example
```

Así la indexación se centra en código fuente y configuración útil. Hay una plantilla en **`docs/cursorignore.example`**; puedes copiarla a la raíz: `cp docs/cursorignore.example .cursorignore`.

---

## 11. Network y Beta

- **HTTP/2**: recomendado por Cursor; dejarlo así.
- **Beta → Agent Autocomplete**: ON está bien para sugerencias mientras escribes en el chat.
- **Update Access**: “Default” evita builds inestables; correcto para producción.

---

## 12. Checklist rápido de acciones

| Acción | Dónde | Estado |
|--------|--------|-----------|
| **Commands** `/test-back`, `/test-front`, `/pr` | `.cursor/commands/` | Hecho en repo |
| Añadir 1–2 Docs (URLs en §9) | Indexing & Docs → Add Doc | Pendiente (manual en Cursor) |
| Reducir modelos activos a 2–3 | Models | Pendiente (manual en Cursor) |
| Crear `.cursorignore` (contenido en §10) | Raíz del proyecto | Opcional (manual) |
| (Opcional) Usage-Based Pricing si usas Cloud Agents | Cloud Agents → Manage Settings | Baja |
| (Opcional) Fetch Domain Allowlist | Agents | Baja |
| (Opcional) Partial Accepts en Tab | Tab | Baja |

---

## 13. Referencias

- [Cursor Docs (ES)](https://cursor.com/es/docs)
- [Rules (context/memories)](https://cursor.com/docs/context/memories)
- [Agent Skills](https://cursor.com/docs/context/skills)
- [Agent best practices](https://cursor.com/blog/agent-best-practices)
- [Migrar a Skills](https://cursor.com/docs/context/skills#migrating-rules-and-commands-to-skills): comando `/migrate-to-skills` si quieres pasar reglas/comandos dinámicos a skills.

**Implementado en este repo:** Commands en `.cursor/commands/` (`test-back.md`, `test-front.md`, `pr.md`). Puedes invocarlos en el chat del Agente con `/test-back`, `/test-front` o `/pr`.
