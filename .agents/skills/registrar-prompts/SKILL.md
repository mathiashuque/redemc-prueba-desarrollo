---
name: registrar-prompts
description: Mantiene la bitácora de prompts del usuario en docs/ia/prompts.md - verifica el registro automático, marca prompts clave, anota el resultado de un prompt y suma prompts hechos en otras herramientas (ChatGPT, Copilot, Cursor). Usar cuando el usuario pide registrar, marcar o revisar prompts.
---

# Registrar prompts

`docs/ia/prompts.md` es la bitácora de **todos** los prompts que manda el usuario. Es material para `PROCESO_IA.md` (que pide 2 o 3 prompts clave), así que se versiona en el repo.

## Registro automático

En Claude Code, el hook `UserPromptSubmit` de `.claude/settings.json` ejecuta `scripts/log-prompt.sh` con cada prompt. El script agrega una entrada con fecha, hora y rama, y enmascara secretos obvios (tokens, `password=...`, `Bearer ...`). No hace falta que el modelo haga nada.

Si el registro no aparece:

1. Confirmá que `jq` está instalado y que el script es ejecutable (`chmod +x scripts/log-prompt.sh`).
2. Probalo a mano: `echo '{"prompt":"prueba"}' | CLAUDE_PROJECT_DIR="$PWD" scripts/log-prompt.sh && tail docs/ia/prompts.md`.
3. Los hooks del proyecto se cargan al abrir la sesión: si se acaba de crear el hook, hay que reiniciar Claude Code.

## Formato de una entrada

````markdown
### 2026-11-24 10:15 · `feature/alta-contacto`

```text
<texto del prompt>
```
````

Las entradas solo se agregan al final. No reescribas ni borres prompts anteriores.

## Acciones a pedido

- **Marcar un prompt clave**: agregá ` ⭐` al final del título de la entrada (`### 2026-11-24 10:15 · \`feature/x\` ⭐`).
- **Anotar el resultado**: debajo del bloque del prompt, una línea `> Resultado: ...` con qué produjo y si se aceptó, corrigió o descartó. Si se corrigió o descartó, anotá el detalle en `docs/ia/bitacora.md` (skill `registrar-proceso-ia`).
- **Prompt de otra herramienta**: agregá una entrada con el formato de arriba y la herramienta en el título (`### 2026-11-24 10:15 · ChatGPT`). Hora aproximada si no se sabe.
- **Listar prompts clave**: `grep -n '⭐' docs/ia/prompts.md`.

## Privacidad

Antes de commitear la bitácora, revisá que no quedó ningún secreto, dato personal real ni contenido confidencial. Si aparece uno, reemplazalo por `[REDACTADO]`.
