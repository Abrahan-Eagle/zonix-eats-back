#!/usr/bin/env bash
# sync-context-for-ia.sh — Comprueba y opcionalmente actualiza archivos de contexto para IA.
# Uso: desde la raíz del repo (zonix-eats-back), ejecutar: ./scripts/sync-context-for-ia.sh
# Opción: SYNC_DATE=1 ./scripts/sync-context-for-ia.sh — actualiza "Última actualización" en .cursorrules, AGENTS.md, README.md con la fecha de hoy.

set -e
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

echo "=== Sync contexto IA — Zonix Eats Backend ==="
echo "Raíz: $REPO_ROOT"
echo ""

# Comprobar que existan los archivos clave
for f in .cursorrules AGENTS.md docs/active_context.md; do
  if [[ -f "$f" ]]; then
    echo "  OK $f"
  else
    echo "  FALTA $f"
  fi
done

# Opcional: actualizar fecha "Última actualización" en varios archivos
if [[ "${SYNC_DATE:-0}" == "1" ]]; then
  TODAY=$(date +"%d %B %Y" 2>/dev/null || date +"%d %b %Y")
  for file in .cursorrules AGENTS.md README.md; do
    if [[ -f "$file" ]]; then
      if grep -q "Última actualización" "$file" 2>/dev/null; then
        sed -i.bak "s/**Última actualización:**.*/**Última actualización:** $TODAY/" "$file" 2>/dev/null || true
        [[ -f "${file}.bak" ]] && rm -f "${file}.bak"
        echo "  Actualizada fecha en $file"
      fi
    fi
  done
else
  echo ""
  echo "Para actualizar la fecha en .cursorrules, AGENTS.md y README.md, ejecutá:"
  echo "  SYNC_DATE=1 ./scripts/sync-context-for-ia.sh"
fi

echo ""
echo "Listo. Para más detalles: docs/CONTEXTO_IA.md"
