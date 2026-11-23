#!/usr/bin/env bash
# Hook UserPromptSubmit de Claude Code: agrega cada prompt del usuario a
# docs/ia/prompts.md (bitácora de prompts). Enmascara secretos obvios y nunca
# bloquea el prompt: ante cualquier error sale con código 0.
set -uo pipefail

root="${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}"
log="$root/docs/ia/prompts.md"

prompt="$(jq -r '.prompt // empty' 2>/dev/null)" || exit 0
[[ -z "$prompt" ]] && exit 0

redacted="$(printf '%s' "$prompt" | sed -E \
    -e 's/(sk|pk|rk)-[A-Za-z0-9_-]{16,}/[REDACTADO]/g' \
    -e 's/gh[pousr]_[A-Za-z0-9]{20,}/[REDACTADO]/g' \
    -e 's/(Bearer|bearer) [A-Za-z0-9._~+\/=-]+/\1 [REDACTADO]/g' \
    -e 's/((password|passwd|pwd|token|secret|api[_-]?key)[[:space:]]*[:=][[:space:]]*)[^[:space:]]+/\1[REDACTADO]/gI')"

branch="$(git -C "$root" branch --show-current 2>/dev/null || true)"

mkdir -p "$(dirname "$log")"
{
    printf '\n### %s' "$(date '+%Y-%m-%d %H:%M')"
    [[ -n "$branch" ]] && printf ' · `%s`' "$branch"
    printf '\n\n````text\n%s\n````\n' "$redacted"
} >> "$log" 2>/dev/null

exit 0
