# AGENTS.md - Zonix Glasses Backend (Laravel API)

> Instrucciones para agentes de IA en el backend de Zonix Glasses.
> Mantenimiento de skills: [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md).

> **Memoria viva:** [`docs/active_context.md`](docs/active_context.md) — leer al iniciar.

## Cambios recientes

- **2026-06-27:** Modelo negocio **v3.1** — checkout dinámico, pagos 4.1/4.2, mayorista aliado, §11 cerrado; **forense doc-vs-doc PASS**; **código 001 congelado** hasta HITL legal/marca + `AUDIT_RIESGOS_SEGURIDAD.md`.
- **2026-06-26:** Pivot óptica B2B2C — docs canon (`PRODUCT_VISION`, `MODELO_NEGOCIO/`, `BRAND`, `PRIVACIDAD`, `DOMINIO_DATOS`); JARVIS Paso C (manifest + sync); skills dominio v1.0; Spec Kit `001-prescription-intake`; rebrand Scaffold → Zonix Glasses.
- **2026-06-18:** Bootstrap inicial; skills globales por referencia.

---

## Project Overview

| Métrica | Valor |
| -------- | ----- |
| **Producto** | Zonix Glasses |
| **Framework** | Laravel 10.x / PHP 8.1+ |
| **Base de datos** | MySQL |
| **Frontend hermano** | `../zonix-glasses-front/` (Flutter, paquete `zonix_glasses`) |
| **Estado** | Diseño negocio v3.1 — **doc-vs-doc PASS**; código 001 **congelado** hasta HITL legal/marca + seguridad |
| **Agentes IA** | Cursor + skills globales JARVIS |

---

## Contexto entre sesiones

1. `.cursorrules`
2. `AGENTS.md`
3. `docs/active_context.md`
4. `docs/CONTEXTO_IA.md`

---

## Arquitectura (convenciones)

- Lógica de negocio en **Services**, no en Controllers
- API REST con envelope `{ success, data, message }`
- **Sanctum** + roles/middleware según producto
- Validación con **Form Requests**
- Operaciones críticas: **`DB::transaction()`**
- Listados: paginación obligatoria
- Eager loading: evitar N+1

### Migraciones (desarrollo local)

- Tabla nueva → una migración `create_*_table`
- Cambio en tabla aún no desplegada → editar la `create_*` existente
- Staging/producción → migraciones append-only; no reescribir history

---

## Collaboration Rules

1. **Preguntar** antes de cambios amplios
2. **No push/merge** sin orden explícita
3. **Usuario prueba primero** (`php artisan test`, endpoints manuales)
4. Commits solo cuando el usuario lo pida
5. **Skills de dominio:** prefijo `zonix-glasses-*` en `.agents/skills/`

---

## Git Workflow

`dev` → pruebas → `main` → producción

---

## Setup Commands

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
php artisan test
```

Variables: [docs/ENV_VARIABLES.md](docs/ENV_VARIABLES.md).

---

## Skills — Capas (Paso C activo)

Globales sincronizadas vía `.agents/skills/.global-sync-manifest`:

```bash
export JARVIS_SKILLS_LIBRARY=/var/www/html/proyectos/AIPP/jarvis-skills-library
./scripts/sync-global-skills-from-library.sh
./scripts/check-global-skills-sync.sh
python3 .agents/skills/sync.sh
```

Ver [MAINTENANCE_SKILLS.md](MAINTENANCE_SKILLS.md) y [docs/ZONIX_GLASSES_JARVIS_INTEGRATION.md](docs/ZONIX_GLASSES_JARVIS_INTEGRATION.md).

Dominio exclusivo (no en manifest): `zonix-glasses-*`.

---

## Available Skills

<!-- SKILLS-START -->
| Skill | Descripción | Ruta |
|-------|-------------|------|
| `agent-loop-engineering` | Diseño de loops de agente concisos, reducidos y controlados: anatomía estímulo→iteración→stop, cuándo loop vs prompt, tipos de loop y mapeo a skills JARVIS. | [.agents/skills/agent-loop-engineering/SKILL.md](.agents/skills/agent-loop-engineering/SKILL.md) |
| `api-design-principles` | Master REST and GraphQL API design principles to build intuitive, scalable, and maintainable APIs that delight developers. Use when designing new APIs, reviewing API specifications, or establishing API design standards. | [.agents/skills/api-design-principles/SKILL.md](.agents/skills/api-design-principles/SKILL.md) |
| `architecture-patterns` | Implement proven backend architecture patterns including Clean Architecture, Hexagonal Architecture, and Domain-Driven Design. Use when architecting complex backend systems or refactoring existing applications for better maintainability. | [.agents/skills/architecture-patterns/SKILL.md](.agents/skills/architecture-patterns/SKILL.md) |
| `backlog-triage-ops` | Triage de backlog GitHub: auditar issues/PRs abiertos, clasificar disposición (merge, request-changes, close, needs-design), priorizar y generar reporte accionable. | [.agents/skills/backlog-triage-ops/SKILL.md](.agents/skills/backlog-triage-ops/SKILL.md) |
| `brainstorming-ops` | OBLIGATORIO antes de tareas complejas en proyecto activo: pantallas, providers, navegación, flujos KYC/onboarding. Propone alternativas y obtiene aprobación antes de codificar. | [.agents/skills/brainstorming-ops/SKILL.md](.agents/skills/brainstorming-ops/SKILL.md) |
| `branch-pr-ops` | Workflow branch + PR: naming conventional, checklist pre-PR, issue linking, presupuesto review, gh integration. Adaptable al AGENTS.md del repo. | [.agents/skills/branch-pr-ops/SKILL.md](.agents/skills/branch-pr-ops/SKILL.md) |
| `chained-pr-ops` | Divide PRs grandes en cadenas reviewables (stacked o feature-branch chain): regla 400 líneas, diagrama de dependencias, integración gh. | [.agents/skills/chained-pr-ops/SKILL.md](.agents/skills/chained-pr-ops/SKILL.md) |
| `clean-code-principles` | SOLID principles, design patterns, DRY, KISS, and clean code fundamentals. Use when reviewing architecture, checking code quality, refactoring, or discussing design decisions. Triggers on "review architecture", "check code quality", "SOLID principles", "design patterns", or "clean code". | [.agents/skills/clean-code-principles/SKILL.md](.agents/skills/clean-code-principles/SKILL.md) |
| `code-review-excellence` | DEPRECATED — usar code-review-playbook. Stub de compatibilidad para manifests legacy. | [.agents/skills/code-review-excellence/SKILL.md](.agents/skills/code-review-excellence/SKILL.md) |
| `code-review-playbook` | Use this skill when conducting or improving code reviews. Provides structured review processes, conventional comments patterns, language-specific checklists, and feedback templates. | [.agents/skills/code-review-playbook/SKILL.md](.agents/skills/code-review-playbook/SKILL.md) |
| `cognitive-doc-design-ops` | Diseñar docs con baja carga cognitiva: lead with answer, progressive disclosure, checklists para review. | [.agents/skills/cognitive-doc-design-ops/SKILL.md](.agents/skills/cognitive-doc-design-ops/SKILL.md) |
| `comment-writer-ops` | Redactar comentarios de colaboración cálidos y directos: PR, issues, reviews, Slack. | [.agents/skills/comment-writer-ops/SKILL.md](.agents/skills/comment-writer-ops/SKILL.md) |
| `context-packs-ops` | Modos de sesión ligeros research / produce / review (concepto ECC contexts/, sin inyección runtime). Define qué skills primar y qué evitar por modo. | [.agents/skills/context-packs-ops/SKILL.md](.agents/skills/context-packs-ops/SKILL.md) |
| `context-updater` | Actualizar el contexto de sesión para que la IA "recuerde" entre sesiones. Resumir cambios relevantes en docs/active_context.md al cerrar o finalizar una sesión de trabajo significativa. | [.agents/skills/context-updater/SKILL.md](.agents/skills/context-updater/SKILL.md) |
| `deep-interview-ops` | Entrevista socrática antes de tareas ambiguas en proyecto activo. Gate claridad mínima 3.5/5. | [.agents/skills/deep-interview-ops/SKILL.md](.agents/skills/deep-interview-ops/SKILL.md) |
| `docs-alignment-ops` | Alinear documentación con código: docs describen comportamiento actual, mismo PR que el cambio, ejemplos verificables. | [.agents/skills/docs-alignment-ops/SKILL.md](.agents/skills/docs-alignment-ops/SKILL.md) |
| `doubt-driven-development` | Revisión adversarial in-flight de decisiones no triviales: CLAIM → EXTRACT → DOUBT → RECONCILE → STOP. | [.agents/skills/doubt-driven-development/SKILL.md](.agents/skills/doubt-driven-development/SKILL.md) |
| `engram-memory-protocol` | Disciplina de memoria persistente con Engram MCP: mem_save, mem_search, mem_context, cierre de sesión y recuperación post-compactación. | [.agents/skills/engram-memory-protocol/SKILL.md](.agents/skills/engram-memory-protocol/SKILL.md) |
| `engram-router` | Orquesta memoria persistente Engram (MCP) vs context-updater/handoff/active_context JARVIS. | [.agents/skills/engram-router/SKILL.md](.agents/skills/engram-router/SKILL.md) |
| `error-handling-patterns` | Master error handling patterns across languages including exceptions, Result types, error propagation, and graceful degradation to build resilient applications. Use when implementing error handling, designing APIs, or improving application reliability. | [.agents/skills/error-handling-patterns/SKILL.md](.agents/skills/error-handling-patterns/SKILL.md) |
| `executing-plans` | Ejecutar plan Flutter paso a paso. | [.agents/skills/executing-plans/SKILL.md](.agents/skills/executing-plans/SKILL.md) |
| `fan-out-synthesize-ops` | Orquestación por defecto JARVIS: Map-Reduce agentico / Fan-out-and-synthesize — N subagentes en paralelo recaudan contexto → sesión principal (orquestador) sintetiza → writer único aplica → verify. | [.agents/skills/fan-out-synthesize-ops/SKILL.md](.agents/skills/fan-out-synthesize-ops/SKILL.md) |
| `finishing-a-development-branch` | Cerrar feature Flutter: analyze + test, opciones merge/PR. | [.agents/skills/finishing-a-development-branch/SKILL.md](.agents/skills/finishing-a-development-branch/SKILL.md) |
| `frontend-design` | Create distinctive, production-grade frontend interfaces with high design quality. Use this skill when the user asks to build web components, pages, artifacts, posters, or applications (examples include websites, landing pages, dashboards, React components, HTML/CSS layouts, or when styling/beautifying any web UI). Generates creative, polished code and UI design that avoids generic AI aesthetics. | [.agents/skills/frontend-design/SKILL.md](.agents/skills/frontend-design/SKILL.md) |
| `git-commit` | Execute git commit with conventional commit message analysis, intelligent staging, and message generation. Use when user asks to commit changes, create a git commit, or mentions "/commit". Supports: (1) Auto-detecting type and scope from changes, (2) Generating conventional commit messages from diff, (3) Interactive commit with optional type/scope/description overrides, (4) Intelligent file staging for logical grouping | [.agents/skills/git-commit/SKILL.md](.agents/skills/git-commit/SKILL.md) |
| `git-guardrails-ops` | Protección git: bloquea push a main, advierte en dev, exige confirmación antes de comandos destructivos. | [.agents/skills/git-guardrails-ops/SKILL.md](.agents/skills/git-guardrails-ops/SKILL.md) |
| `github-code-review` | DEPRECATED — usar code-review-playbook. Stub de compatibilidad para manifests legacy. | [.agents/skills/github-code-review/SKILL.md](.agents/skills/github-code-review/SKILL.md) |
| `handoff` | Compactar la sesion actual en un documento de traspaso para continuar en otro agente o chat. Complementa session-learner-ops (cierre de modulo) y active_context.md. | [.agents/skills/handoff/SKILL.md](.agents/skills/handoff/SKILL.md) |
| `human-in-the-loop-ops` | Gobernanza humana en bucles agénticos: HITL/HOTL/automation-bounded, umbrales de confianza, condiciones de terminación y escalamiento. | [.agents/skills/human-in-the-loop-ops/SKILL.md](.agents/skills/human-in-the-loop-ops/SKILL.md) |
| **`jarvis-core`** | **Protocolo base del sistema JARVIS para cualquier proyecto. Define honestidad, foco de negocio y flujo de trabajo modular.** | [.agents/skills/jarvis-core/SKILL.md](.agents/skills/jarvis-core/SKILL.md) |
| `jarvis-experts` | Panel de Expertos JARVIS (agencia de desarrollo virtual). Define roster de roles, criterios de activación, combinaciones recomendadas y plantilla de declaración. | [.agents/skills/jarvis-experts/SKILL.md](.agents/skills/jarvis-experts/SKILL.md) |
| `laravel-specialist` | Use when building Laravel 10+ applications requiring Eloquent ORM, API resources, or queue systems. Invoke for Laravel models, Livewire components, Sanctum authentication, Horizon queues. | [.agents/skills/laravel-specialist/SKILL.md](.agents/skills/laravel-specialist/SKILL.md) |
| `mysql-best-practices` | MySQL development best practices for schema design, query optimization, and database administration | [.agents/skills/mysql-best-practices/SKILL.md](.agents/skills/mysql-best-practices/SKILL.md) |
| `notebooklm-router` | Orquesta consulta RAG a Google NotebookLM (corpus grande/duradero con citas) vía MCP `notebooklm-mcp` vs subida directa al contexto y vs Engram (memoria cross-session). | [.agents/skills/notebooklm-router/SKILL.md](.agents/skills/notebooklm-router/SKILL.md) |
| `parallel-judge-ops` | Patrón "día del juicio": 2+ jueces adversariales en paralelo e independientes → orquestador valida real vs ruido → subagente aplica fixes → itera hasta sin hallazgos o max iterations. | [.agents/skills/parallel-judge-ops/SKILL.md](.agents/skills/parallel-judge-ops/SKILL.md) |
| `playwright-skill` | Complete browser automation with Playwright. Auto-detects dev servers, writes clean test scripts to /tmp. Test pages, fill forms, take screenshots, check responsive design, validate UX, test login flows, check links, automate any browser task. Use when user wants to test websites, automate browser interactions, validate web functionality, or perform any browser-based testing. | [.agents/skills/playwright-skill/SKILL.md](.agents/skills/playwright-skill/SKILL.md) |
| `qa-testing-playwright` | E2E web testing with Playwright. Use when writing tests, debugging flakes, or setting up CI with selectors, sharding, and network mocking. | [.agents/skills/qa-testing-playwright/SKILL.md](.agents/skills/qa-testing-playwright/SKILL.md) |
| `receiving-code-review` | Recibir feedback de review con verificación. Delega estándares a code-review-playbook. | [.agents/skills/receiving-code-review/SKILL.md](.agents/skills/receiving-code-review/SKILL.md) |
| `requesting-code-review` | Pedir code review antes de merge. Delega checklist a code-review-playbook. | [.agents/skills/requesting-code-review/SKILL.md](.agents/skills/requesting-code-review/SKILL.md) |
| `security` | OWASP security patterns, secrets management, security testing | [.agents/skills/security/SKILL.md](.agents/skills/security/SKILL.md) |
| `session-learner-ops` | Tras cerrar módulo UI: patrones en docs/active_context.md y walkthrough. | [.agents/skills/session-learner-ops/SKILL.md](.agents/skills/session-learner-ops/SKILL.md) |
| `session-startup-ops` | Protocolo de arranque de sesión (concepto ECC session-start, sin hooks). Checklist: active_context, Engram si activo, Roles/Skills, plan/handoff pendiente. | [.agents/skills/session-startup-ops/SKILL.md](.agents/skills/session-startup-ops/SKILL.md) |
| `skill-creator` | Guide for creating effective skills. This skill should be used when users want to create a new skill (or update an existing skill) that extends Claude's capabilities with specialized knowledge, workflows, or tool integrations. | [.agents/skills/skill-creator/SKILL.md](.agents/skills/skill-creator/SKILL.md) |
| `software-architecture` | Guide for quality focused software architecture. This skill should be used when users want to write code, design architecture, analyze code, in any case that relates to software development. | [.agents/skills/software-architecture/SKILL.md](.agents/skills/software-architecture/SKILL.md) |
| `sql-optimization-patterns` | Master SQL query optimization, indexing strategies, and EXPLAIN analysis to dramatically improve database performance and eliminate slow queries. Use when debugging slow queries, designing database schemas, or optimizing application performance. | [.agents/skills/sql-optimization-patterns/SKILL.md](.agents/skills/sql-optimization-patterns/SKILL.md) |
| `strategic-compact-ops` | Compactación estratégica (concepto ECC strategic-compact, sin hooks). Sugiere compactar en hitos lógicos; preserva decisiones, verificación y TODOs vía handoff + Engram. | [.agents/skills/strategic-compact-ops/SKILL.md](.agents/skills/strategic-compact-ops/SKILL.md) |
| `stripe-integration` | Implement Stripe payment processing for robust, PCI-compliant payment flows including checkout, subscriptions, and webhooks. Use when integrating Stripe payments, building subscription systems, or ... | [.agents/skills/stripe-integration/SKILL.md](.agents/skills/stripe-integration/SKILL.md) |
| `structured-commits-ops` | Commits con trailers de decisión en proyecto activo. Complementa git-commit. | [.agents/skills/structured-commits-ops/SKILL.md](.agents/skills/structured-commits-ops/SKILL.md) |
| `systematic-debugging` | Use when encountering any bug, test failure, or unexpected behavior, before proposing fixes | [.agents/skills/systematic-debugging/SKILL.md](.agents/skills/systematic-debugging/SKILL.md) |
| `task-pipeline-ops` | Pipeline multi-paso proyecto activo: Plan → Spec → Exec → Verify → Fix (máx. 3). | [.agents/skills/task-pipeline-ops/SKILL.md](.agents/skills/task-pipeline-ops/SKILL.md) |
| `test-driven-development` | Use when implementing any feature or bugfix, before writing implementation code | [.agents/skills/test-driven-development/SKILL.md](.agents/skills/test-driven-development/SKILL.md) |
| `using-git-worktrees` | Worktree aislado para features Flutter proyecto. Base dev. | [.agents/skills/using-git-worktrees/SKILL.md](.agents/skills/using-git-worktrees/SKILL.md) |
| `verification-before-completion` | OBLIGATORIO antes de declarar cualquier tarea completada en cualquier proyecto. Ejecuta verificación fresca del stack y solo entonces afirma éxito. | [.agents/skills/verification-before-completion/SKILL.md](.agents/skills/verification-before-completion/SKILL.md) |
| `webapp-testing` | Toolkit for interacting with and testing local web applications using Playwright. Supports verifying frontend functionality, debugging UI behavior, capturing browser screenshots, and viewing browser logs. | [.agents/skills/webapp-testing/SKILL.md](.agents/skills/webapp-testing/SKILL.md) |
| `work-unit-commits-ops` | Commits por unidad de trabajo reviewable: un propósito, tests/docs con el código, historia clara. Puente a chained PRs. | [.agents/skills/work-unit-commits-ops/SKILL.md](.agents/skills/work-unit-commits-ops/SKILL.md) |
| `writing-plans` | Plan bite-sized Flutter antes de codificar. .agents/plans/implementation_plan.md | [.agents/skills/writing-plans/SKILL.md](.agents/skills/writing-plans/SKILL.md) |
| **`zonix-glasses-api-patterns`** | **Contratos REST Zonix Glasses — óptica B2B2C: partners, pacientes, fórmulas, catálogo, órdenes.** | [.agents/skills/zonix-glasses-api-patterns/SKILL.md](.agents/skills/zonix-glasses-api-patterns/SKILL.md) |
| **`zonix-glasses-fulfillment`** | **Orquestación multi-fabricante y multi-courier post-pago: SupplierOrder, ShipmentLeg, estados canónicos.** | [.agents/skills/zonix-glasses-fulfillment/SKILL.md](.agents/skills/zonix-glasses-fulfillment/SKILL.md) |
| **`zonix-glasses-partners`** | **Ópticas aliadas B2B: onboarding, multi-tenant, pacientes bajo partner.** | [.agents/skills/zonix-glasses-partners/SKILL.md](.agents/skills/zonix-glasses-partners/SKILL.md) |
| **`zonix-glasses-prescriptions`** | **Fórmulas ópticas: captura manual, OCR IA, historial paciente, vínculo partner.** | [.agents/skills/zonix-glasses-prescriptions/SKILL.md](.agents/skills/zonix-glasses-prescriptions/SKILL.md) |
| `zoom-out` | Explicar código o un cambio en el contexto del sistema completo del proyecto activo (módulos, capas, flujos). Uso bajo demanda. | [.agents/skills/zoom-out/SKILL.md](.agents/skills/zoom-out/SKILL.md) |
<!-- SKILLS-END -->

---

## Auto-invoke Skills

<!-- AUTO-INVOKE-START -->
| Acción | Skill |
|--------|-------|
| Abrir PR con gh | `branch-pr-ops` |
| Actualizar docs tras cambio de código | `docs-alignment-ops` |
| Address review feedback | `receiving-code-review` |
| Agent loop engineering / no prompts haz loops | `agent-loop-engineering` |
| Alta stakes verificar antes de commit | `doubt-driven-development` |
| Auditar open issues como maintainer | `backlog-triage-ops` |
| Auditoría módulo | `fan-out-synthesize-ops` |
| Buscar contexto previo mem_search mem_context | `engram-memory-protocol` |
| Cambio API CLI setup que afecta documentación | `docs-alignment-ops` |
| Cierre sesión con mem_session_summary | `engram-memory-protocol` |
| Clasificar PRs merge request-changes close | `backlog-triage-ops` |
| Code review | `code-review-playbook` |
| Code review GitHub | `github-code-review` |
| Code review antes de merge | `requesting-code-review` |
| Code review excellence | `code-review-excellence` |
| Comando git destructivo | `git-guardrails-ops` |
| Compactar contexto | `strategic-compact-ops` |
| Compactar o traspasar sesion | `handoff` |
| Condiciones de terminación bucle autónomo | `human-in-the-loop-ops` |
| Configurar NotebookLM MCP en Cursor | `notebooklm-router` |
| Configurar engram en Cursor | `engram-router` |
| Consultar NotebookLM / notebook con citas | `notebooklm-router` |
| Contrato API óptica | `zonix-glasses-api-patterns` |
| Corpus grande de documentos para RAG | `notebooklm-router` |
| Crear commit | `git-commit` |
| Crear commit | `structured-commits-ops` |
| Crear commit | `verification-before-completion` |
| Crear o preparar pull request | `branch-pr-ops` |
| Cualquier tarea no trivial | `fan-out-synthesize-ops` |
| Cualquier tarea no trivial | `jarvis-experts` |
| Decidir loop vs prompt simple | `agent-loop-engineering` |
| Decisión cross-rol | `jarvis-experts` |
| Decisión no trivial seguridad producción | `doubt-driven-development` |
| Definir alcance de un módulo | `jarvis-experts` |
| Diseñar loop de agente | `agent-loop-engineering` |
| Dividir diff grande en slices reviewables | `chained-pr-ops` |
| Dividir implementación en commits reviewables | `work-unit-commits-ops` |
| Doc largo, denso o difícil de escanear | `cognitive-doc-design-ops` |
| Día del juicio / jueces paralelos | `parallel-judge-ops` |
| Encontrar bug o test fallido | `systematic-debugging` |
| Escribir descripción de PR o notas para review | `cognitive-doc-design-ops` |
| Escribir feedback de code review para humano | `comment-writer-ops` |
| Estandarizar prácticas de review | `code-review-playbook` |
| Evitar PR monolítico desde SDD tasks | `work-unit-commits-ops` |
| Explorar codebase | `fan-out-synthesize-ops` |
| Fórmula óptica Zonix Glasses | `zonix-glasses-prescriptions` |
| Gates humanos antes de acción irreversible | `human-in-the-loop-ops` |
| Guardar decisión o bugfix en Engram | `engram-memory-protocol` |
| HITL HOTL umbrales de confianza | `human-in-the-loop-ops` |
| Hacer git push o merge | `git-guardrails-ops` |
| Human-in-the-loop diseño de loop | `human-in-the-loop-ops` |
| Implementar feature multi-archivo | `fan-out-synthesize-ops` |
| Implementar feature o bugfix | `test-driven-development` |
| Iniciar módulo | `brainstorming-ops` |
| Iniciar módulo | `jarvis-core` |
| Iniciar módulo | `task-pipeline-ops` |
| Iniciar sesión | `session-startup-ops` |
| Investigar bug | `fan-out-synthesize-ops` |
| Iterar hasta lograr un objetivo medible | `agent-loop-engineering` |
| Memoria persistente Engram MCP | `engram-router` |
| Modo produce | `context-packs-ops` |
| Modo research | `context-packs-ops` |
| Modo review | `context-packs-ops` |
| Naming de branch y checklist pre-PR | `branch-pr-ops` |
| Nuevo endpoint Zonix Glasses | `zonix-glasses-api-patterns` |
| OCR prescription | `zonix-glasses-prescriptions` |
| PR supera 400 líneas o presupuesto de review | `chained-pr-ops` |
| Pedir code review | `requesting-code-review` |
| Planificar desarrollo | `brainstorming-ops` |
| Planificar desarrollo | `jarvis-core` |
| Planificar desarrollo | `writing-plans` |
| Preparar commits antes de abrir PR | `work-unit-commits-ops` |
| Recibir code review | `receiving-code-review` |
| Redactar comentario de PR o issue | `comment-writer-ops` |
| Redactar o mejorar README, RFC, onboarding o guía | `cognitive-doc-design-ops` |
| Requisitos ambiguos | `deep-interview-ops` |
| Respuesta de maintainer o mensaje async al equipo | `comment-writer-ops` |
| Retomar proyecto | `session-startup-ops` |
| Revisar pull request | `code-review-playbook` |
| Sesión larga sugerir compactación | `strategic-compact-ops` |
| Stacked PRs o chained PRs | `chained-pr-ops` |
| Terminar módulo | `finishing-a-development-branch` |
| Terminar módulo | `jarvis-core` |
| Terminar módulo | `session-learner-ops` |
| Terminar módulo | `verification-before-completion` |
| Triage backlog issues y PRs | `backlog-triage-ops` |
| Validar diff/PR con 2+ revisores independientes | `parallel-judge-ops` |
| Verificación adversarial paralela de un artefacto | `parallel-judge-ops` |
| Verificar que docs igualan comportamiento actual | `docs-alignment-ops` |
| doubt-driven revisión adversarial | `doubt-driven-development` |
| mem_save mem_search contexto entre sesiones | `engram-router` |
| nlm login nlm setup add cursor | `notebooklm-router` |
<!-- AUTO-INVOKE-END -->

---

## Repo hermano

Frontend: **`../zonix-glasses-front/AGENTS.md`**

Biblioteca global: **`/var/www/html/proyectos/AIPP/jarvis-skills-library/AGENTS.md`**
