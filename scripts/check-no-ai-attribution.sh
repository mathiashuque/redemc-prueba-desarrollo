#!/usr/bin/env bash
# Falla si algún mensaje de commit tiene atribución a una herramienta de IA
# (trailers Co-Authored-By, "Generated with ...", correos de Anthropic).
#
# Uso:
#   scripts/check-no-ai-attribution.sh --file <archivo>   # hook commit-msg
#   scripts/check-no-ai-attribution.sh [rango]            # p. ej. develop..HEAD (por defecto: HEAD)
set -euo pipefail

PATTERN='co-authored-by:.*(claude|anthropic|copilot|chatgpt|openai|cursor|gemini)|generated (with|by) .*(claude|chatgpt|copilot|cursor|gemini)|noreply@anthropic\.com'

has_attribution() {
    grep -v '^#' | grep -Eiq "$PATTERN"
}

if [[ "${1:-}" == "--file" ]]; then
    if has_attribution < "$2"; then
        echo "error: el mensaje de commit contiene atribución a una herramienta de IA." >&2
        echo "Quitá las líneas Co-Authored-By / 'Generated with ...' y volvé a intentar." >&2
        exit 1
    fi
    exit 0
fi

range="${1:-HEAD}"
failed=0
while read -r sha; do
    if git log -1 --format=%B "$sha" | has_attribution; then
        echo "error: el commit $(git log -1 --format='%h %s' "$sha") tiene atribución a IA." >&2
        failed=1
    fi
done < <(git rev-list "$range")

if [[ "$failed" -ne 0 ]]; then
    exit 1
fi
echo "OK: ningún commit en '$range' tiene atribución a IA."
