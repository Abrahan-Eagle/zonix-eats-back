#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
LIBRARY="${JARVIS_SKILLS_LIBRARY:-/var/www/html/proyectos/AIPP/jarvis-skills-library}"

for repo in zonix-glasses-back zonix-glasses-front; do
  echo "== $repo =="
  JARVIS_SKILLS_LIBRARY="$LIBRARY" "$ROOT/$repo/scripts/sync-global-skills-from-library.sh"
  JARVIS_SKILLS_LIBRARY="$LIBRARY" "$ROOT/$repo/scripts/check-global-skills-sync.sh"
  (cd "$ROOT/$repo" && python3 .agents/skills/sync.sh)
done
echo "Done: Zonix Glasses skills synced"
