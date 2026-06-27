# Guía de mantenimiento de skills — Zonix Glasses Backend

## Paso C — sync en repo (activo)

| Artefacto | Ubicación |
|-----------|-----------|
| Manifest | `.agents/skills/.global-sync-manifest` |
| Sync | `scripts/sync-global-skills-from-library.sh` |
| Check | `scripts/check-global-skills-sync.sh` |
| Tablas AGENTS | `python3 .agents/skills/sync.sh` |

Tras `git pull` en **jarvis-skills-library**:

```bash
export JARVIS_SKILLS_LIBRARY=/var/www/html/proyectos/AIPP/jarvis-skills-library
./scripts/sync-global-skills-from-library.sh
./scripts/check-global-skills-sync.sh
python3 .agents/skills/sync.sh
```

Workspace (back + front): `../scripts/sync-all-zonix-glasses-skills.sh`

## Capa 0 — máquina

```bash
cd /var/www/html/proyectos/AIPP/jarvis-skills-library
bash scripts/install.sh --all
```

## Dominio (no en manifest)

Skills `zonix-glasses-*` — editar solo en `.agents/skills/` de este repo o del front.

## Repos hermanos

- **zonix-glasses-back** (hub docs + API)
- **zonix-glasses-front** (Flutter)

**Última actualización:** Junio 2026
