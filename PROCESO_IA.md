# Proceso con IA

Este archivo resume cómo usé herramientas de IA en la prueba. El detalle completo está en `docs/ia/prompts.md` (todos los prompts) y `docs/ia/bitacora.md` (decisiones).

## 1. Herramientas y para qué

- **Claude Code (Opus)**: coordinador y revisor. Planificó el harness (AGENTS.md, skills, agente `revisor`), la estructura base con Docker, el CI/CD y el reparto de features. Revisó el trabajo de los agentes antes de que yo lo viera.
- **Agentes Claude Haiku**: implementadores de las features #4 a #8. Cada uno trabajó en su propio git worktree y rama `feature/*`, con un PR a `develop`.
- **Yo (desarrollador)**: definí los requisitos en `task.md`, revisé cada PR en el navegador y mergeé a `develop`. Decidí qué sugerencias se aceptaban.

## 2. Prompts clave

- **Harness y estructura base**: pedí "planea e implementa un harness (skills, agents.md etc) para desarrollar features con IA", con la regla de que ningún commit lleve atribución a IA. El plan se ajustó dos veces: agregué la skill de registro de prompts y el flujo gitflow para las features.
- **Issues de features**: pedí redactar cada feature de `task.md` como issue de GitHub con su checklist de aceptación, asignado a mí, para luego implementarlo con la skill de features.
- **Coordinación senior y juniors**: pedí que el coordinador trabajara "como un developer senior", con agentes junior en paralelo cuando fuera posible, y que yo revisara sus salidas al final. Así salieron #4 y #5 en paralelo, y después #6, #7 y #8 en secuencia.

## 3. Sugerencia corregida: `maxlength` en el teléfono

Un agente junior (feature del formulario) puso `maxlength="9"` en el campo teléfono. El navegador truncaba un número de 10 dígitos a 9 y lo aceptaba como válido en silencio, así que el error de longitud nunca llegaba al usuario. Se encontró en la revisión en navegador, no en los tests automáticos. Se quitó el atributo, y el teléfono ahora muestra el error específico que pide la spec.

## 4. Lo que aprendí

- Con agentes en paralelo, el trabajo del coordinador empieza antes de programar: hay que definir contratos y límites de archivos. Por ejemplo, separar el endpoint único en `crear.php` y `listar.php` evitó conflictos entre dos juniors.
- Los tests automáticos no alcanzan. Un problema de UI como el `maxlength` solo apareció al probar en el navegador.
- Un agente puede agregar reglas que la spec no pide (por ejemplo, topes de caracteres). Hay que revisarlas, porque a veces tienen buen motivo, como evitar un error 500 de la base.
