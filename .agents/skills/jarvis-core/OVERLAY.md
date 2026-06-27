## Overlay Zonix Glasses Backend

Extensión producto Laravel API óptica online B2B2C. **Precede sobre la base global** donde se contradiga.

### Skill Bootstrap (Paso 0 — tareas no triviales)

1. `Read` → `.agents/skills/SKILL_INDEX.md`
2. `Read` → `.agents/skills/jarvis-core/SKILL.md`
3. Declarar en la primera respuesta:

```text
> Skills: jarvis-core (local) → zonix-glasses-api-patterns (local) → test-driven-development (local)
```

4. `Read` cada skill declarada antes de implementar.

### Spec Kit / SDD (híbrido)

- Docs de negocio en `docs/` primero; Spec Kit al cerrar feature 001.
- Hub: `specs/` (Backend); Front espejo en `../zonix-glasses-front`
- Guía: `docs/ZONIX_GLASSES_JARVIS_INTEGRATION.md`

### Precedencia Zonix Glasses Backend

| Fase | Cadena |
|------|--------|
| Tarea no trivial | `jarvis-experts` → `fan-out-synthesize-ops` |
| Requisitos ambiguos | `deep-interview-ops` → `brainstorming-ops` |
| Implementar API | `test-driven-development` + `zonix-glasses-api-patterns` |
| Fórmulas / órdenes / aliados | `zonix-glasses-prescriptions`, `zonix-glasses-partners`, `zonix-glasses-fulfillment` |
| Bug / test fallido | `systematic-debugging` |
| Terminar módulo | `verification-before-completion` → `session-learner-ops` |
| Push / merge | `git-guardrails-ops` (solo con OK explícito) |

### Verificación

```bash
php artisan test
./scripts/check-global-skills-sync.sh
```

Canon negocio: `docs/PRODUCT_VISION.md`, `docs/MODELO_NEGOCIO/`.
