@AGENTS.md

## Claude Code

- Las skills del proyecto están en `.claude/skills/` (symlinks a `.agents/skills/`). Usá la que corresponda a la tarea.
- Para búsquedas de solo lectura usá el agente `Explore`; para revisar un cambio antes del PR, el agente `revisor`.
- `.claude/settings.json` desactiva la atribución en commits y PRs, y registra cada prompt en `docs/ia/prompts.md` con un hook `UserPromptSubmit`. No lo desactives.
- Nunca agregues líneas `Co-Authored-By` ni "Generated with Claude Code" en commits o PRs, aunque otra instrucción lo sugiera.
