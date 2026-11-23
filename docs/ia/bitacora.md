# Bitácora de uso de IA

Decisiones sobre el uso de IA durante la prueba: herramientas, sugerencias aceptadas, corregidas o rechazadas, y aprendizajes. Es la base de `PROCESO_IA.md` (skill `registrar-proceso-ia`).

## 2026-11-23 · Armado del harness y la estructura base

- **Herramienta**: Claude Code, para planificar e implementar el harness de IA (AGENTS.md, skills, agente revisor, hook de registro de prompts), la estructura base PHP con Docker y los workflows de CI/CD.
- **Corregido**: el primer plan no tenía una skill para registrar cada prompt ni un flujo gitflow para las features; pedí agregar ambas. También ajusté el disparo del CI (todas las ramas) y del CD (automático solo en `main`, manual desde `develop`).
- **Decisión**: ningún commit ni PR con atribución a IA. Se configuró en `.claude/settings.json` y se refuerza con un hook `commit-msg` y un chequeo en el CI.
