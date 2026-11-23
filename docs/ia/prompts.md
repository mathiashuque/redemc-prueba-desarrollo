# Bitácora de prompts

Registro de los prompts enviados a herramientas de IA durante la prueba. Están reescritos con una redacción más formal que la original, sin cambiar lo que se pidió. En Claude Code se completa solo con el hook `scripts/log-prompt.sh`. Los prompts de otras herramientas se agregan a mano con la skill `registrar-prompts`. Los prompts clave están marcados con ⭐.

### 2026-11-23 · Claude Code (armado del harness) ⭐

```text
Planificá e implementá un harness (skills, AGENTS.md, etc.) para desarrollar features con IA en este proyecto. Implementá también la estructura básica de repositorio sugerida en task.md.

Ningún commit debe estar firmado por Claude ni incluir "Co-Authored-By: Claude".

Además, quiero un pipeline de CI/CD en GitHub: CI para validar el código y CD para desplegarlo en Railway.
```

> Resultado: el plan se aprobó tras los dos ajustes que siguen y el harness quedó implementado en `feature/harness-setup`.

### 2026-11-23 · Claude Code (ajuste del plan)

```text
Quiero una skill para registrar los prompts: que cada prompt que envío quede anotado en un archivo Markdown, a modo de bitácora.

Agregá además las skills básicas que consideres útiles para este proyecto.
```

### 2026-11-23 · Claude Code (ajuste del plan) ⭐

```text
Sumá una skill para implementar features con gitflow:
1. Se crea una rama a partir de develop.
2. En esa rama se implementa la feature.
3. Al terminar, se abre un pull request a develop.
4. Se levanta la aplicación en Docker para que yo revise el resultado.

El CI debe ejecutarse en todas las ramas y subramas. El CD debe ejecutarse automáticamente solo en main, y también debe poder dispararse a mano desde develop.
```

> Resultado: el harness, las skills y el CI/CD quedaron definidos y el plan se aprobó. Las entradas siguientes se agregaron a mano porque el hook automático no las registró.

### 2026-11-23 · Claude Code (quitar job del CI)

```text
Eliminá del CI el job "Sin atribución a IA en commits". No tiene que ser parte del pipeline: es una regla general del proyecto.
```

> Resultado: el job salió del CI. La regla sigue en `AGENTS.md` y en el hook local `commit-msg`.

### 2026-11-23 · Claude Code (probar CD sin tocar main)

```text
El PR ya está mergeado en develop. Quiero probar el CD sin abrir todavía un pull request a main.
```

> Resultado: el CD se disparó a mano desde `develop`, cambiando por un rato la rama por defecto. El deploy a Railway pasó el smoke test.

### 2026-11-23 · Claude Code (issues de features) ⭐

```text
A partir de task.md, armá la lista de features a implementar. Cada feature, con su checklist de aceptación, tiene que quedar redactada en un issue de GitHub asignado a mí, con el label "enhancement" y el tipo "Feature". Después cada feature se implementará con la skill implementar-feature. Planificalo y luego ejecutalo.
```

> Resultado: issues #4 a #8, uno por feature, con su checklist de aceptación y asignados al usuario.

### 2026-11-23 · Claude Code (coordinación senior y juniors) ⭐

```text
Vas a coordinar el trabajo como developer senior. Los developers junior van a ser agentes Haiku, que implementarán las features, en paralelo cuando se pueda. Vos revisás lo que entregan y después lo reviso yo. Planificá cómo organizarlo.
```

> Resultado: #4 y #5 se hicieron en paralelo, y después #6, #7 y #8 en secuencia. De ahí salieron los PR #9, #10, #11 y #12, que el usuario revisó y mergeó.
