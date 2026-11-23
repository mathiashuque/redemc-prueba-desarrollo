# Bitácora de prompts

Registro de los prompts enviados a herramientas de IA durante la prueba. En Claude Code se completa solo con el hook `scripts/log-prompt.sh`; los prompts de otras herramientas se agregan a mano (skill `registrar-prompts`). Los prompts clave se marcan con ⭐.

### 2026-11-23 · Claude Code (armado del harness) ⭐

```text
planea e implementa un harnes (skills, agents.md etc) para desarrollar features con IA en este proyecto, tambien implementa la estructura basica sugerida del repositorio indicado en el task.md.

recuerda que la idea es que no hayan commits ni firmados ni coauthored by claude.

Tambien me gustaria que haya un pipeline de github de CI/CD, CI para validar codigo, CD para luego subirlo a railway.
```

> Resultado: plan aprobado tras dos ajustes del usuario (abajo) y harness implementado en `feature/harness-setup`.

### 2026-11-23 · Claude Code (ajuste del plan)

```text
una de las skills que me gustaria tener es una de registrar prompts, que registre cada prompt que mando en un markdown o algo asi, como una bitacora.

despues skills basicas que tu encuentres util para este proyecto.
```

### 2026-11-23 · Claude Code (ajuste del plan) ⭐

```text
otra skill que seria implementar feature, la idea es que se implemente el feature realizando gitflow, es decir se crea una rama a partir de develop, en dicha rama se implementa el feature, y luego de finalizado se crea un pull request a develop y se levanta en docker para que yo revise lo realizado.

sobre el CI, este se ejecuta en todas las ramas y subramas, el cd solamente en main, pero debe poder ser ejecutable manualmente desde la rama develop.
```
