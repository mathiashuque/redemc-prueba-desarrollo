# Proceso con IA

Cómo usé IA en esta prueba. El detalle completo está en [`docs/ia/prompts.md`](docs/ia/prompts.md) (prompts) y [`docs/ia/bitacora.md`](docs/ia/bitacora.md) (decisiones).

## 1. Herramientas y para qué

Usé **Claude Code** a lo largo del proyecto. Primero pasé la consigna a una especificación técnica (`task.md`) y armé un harness para trabajar con agentes: `AGENTS.md`, skills para planificar, implementar con gitflow y verificar, más un agente revisor. También armé la estructura base con Docker y el pipeline de CI/CD hacia Railway.

Para las features, Claude Code (Opus) coordinó y revisó, y la implementación quedó a cargo de agentes **Claude Haiku**. Cada agente trabajó en su propio git worktree y abrió un PR a `develop`. Yo revisé cada PR y la app levantada en local antes de mergear, y decidí qué sugerencias se aceptaban.

## 2. Prompts clave

- **Harness**: le pedí planificar e implementar un harness (skills, `AGENTS.md`) para desarrollar features con IA y un pipeline de CI/CD a Railway, con la condición de que ningún commit llevara atribución a IA. Ajusté el plan dos veces: sumé una skill para registrar los prompts y un flujo gitflow para las features.
- **Features como issues**: le pedí convertir cada feature de `task.md` en un issue de GitHub con su checklist de aceptación, para implementar cada uno con la skill de features.
- **Coordinación**: le pedí que actuara como developer senior, que coordinara agentes junior trabajando en paralelo cuando se pudiera y que revisara lo que entregaban antes de que yo lo viera.

## 3. Sugerencia corregida: `maxlength` en el teléfono

Un agente le puso `maxlength="9"` al campo teléfono. El navegador recortaba un número de 10 dígitos a 9 y lo aceptaba como válido sin avisar, así que el error de longitud que pide la consigna nunca aparecía. Los tests automáticos no lo detectaron. Salió en la revisión en el navegador y se quitó el atributo.

## 4. Lo que aprendí

No fue algo demasiado nuevo para mí, pero me sirvió para repasar PHP: hacía tiempo que no lo usaba y volví a sus versiones modernas, con tipos estrictos, clases `readonly` y PDO con consultas preparadas. También tuve que recordar Bootstrap. Hoy trabajo más con Tailwind, y volver a pensar con componentes y la grilla de Bootstrap en lugar de utilidades me costó un poco al principio.
