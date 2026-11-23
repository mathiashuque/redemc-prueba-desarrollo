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

> Resultado: harness, skills y CI/CD acordados; el plan quedó aprobado tras estos ajustes. Las entradas siguientes se agregaron a mano: el hook automático no las registró.

### 2026-11-23 · Claude Code (quitar job del CI)

```text
borra del CI " [Sin atribución a IA en commits](...) succeeded 1 minute ago in 5s " no deberia ser parte del CI, simplemente una regla general
```

> Resultado: se quitó el job del CI. La regla queda en `AGENTS.md` y en el hook local `commit-msg`.

### 2026-11-23 · Claude Code (probar CD sin tocar main)

```text
el pr ya esta mergeado a develop, la idea es no tocar main por ahora, quiero testear el cd sin pr a main todavia
```

> Resultado: se disparó el CD a mano desde `develop`, cambiando temporalmente la rama por defecto. El deploy a Railway pasó el smoke test.

### 2026-11-23 · Claude Code (issues de features) ⭐

```text
Bien, ahora desde task.md, quiero que hagas una lista features a realizar, la idea es cada uno de los features y su checklist de aceptacion sean redactados en un issue en github, con assignees a mi, labels Enhancement, Type Feature. la idea es que cada feature sea luego implementado utilizando la skill de implementar features. planea esto y luego ejecutalo (redacta los features).
```

> Resultado: issues #4 a #8, uno por feature, con checklist de aceptación y asignados al usuario.

### 2026-11-23 · Claude Code (coordinación senior y juniors) ⭐

```text
bien, tu coordinaras como un developer senior, tus developers junior seran agentes haikuu, haz que implementen los features (en lo posible en simultaneo). tu revisaras sus outputs, y luego lo revisare yo. planea como hacer esto
```

> Resultado: #4 y #5 en paralelo, luego #6, #7 y #8 en secuencia. Generaron los PR #9, #10, #11 y #12, que el usuario revisó y mergeó.
