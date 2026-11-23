# Bitácora de uso de IA

Decisiones sobre el uso de IA durante la prueba: herramientas, sugerencias aceptadas, corregidas o rechazadas, y aprendizajes. Es la base de `PROCESO_IA.md` (skill `registrar-proceso-ia`).

## 2026-11-23 · Armado del harness y la estructura base

- **Herramienta**: Claude Code, para planificar e implementar el harness de IA (AGENTS.md, skills, agente revisor, hook de registro de prompts), la estructura base PHP con Docker y los workflows de CI/CD.
- **Corregido**: el primer plan no tenía una skill para registrar cada prompt ni un flujo gitflow para las features; pedí agregar ambas. También ajusté el disparo del CI (todas las ramas) y del CD (automático solo en `main`, manual desde `develop`).
- **Decisión**: ningún commit ni PR con atribución a IA. Se configuró en `.claude/settings.json` y se refuerza con un hook local `commit-msg`. La IA también agregó un job en el CI para verificarlo; lo saqué porque es una regla general del proyecto, no algo que el pipeline deba validar.

## 2026-11-23 · Features con agentes junior y revisión senior

- **Herramientas**: Claude Code (Opus) como coordinador y revisor. Agentes Claude Haiku como implementadores, cada uno en su propio git worktree y rama `feature/*`. El usuario revisó y mergeó cada PR.
- **Decisión**: para implementar #4 y #5 en paralelo sin conflictos, el coordinador separó el endpoint único propuesto en el issue en `public/api/crear.php` y `public/api/listar.php`, como sugiere `task.md` §6. Asignó archivos y puertos distintos a cada junior.
- **Aceptado**: el junior de #4 agregó topes de 100 caracteres al nombre y de 255 al email. La spec no los pedía, pero evitan un error 500 de la base por datos demasiado largos. Se aceptó.
- **Corregido en revisión**: el junior de #5 maquetó el paginado con `<nav>`. Se cambió a `<div role="navigation">` por la regla de maquetar solo con `<div>`.
- **Corregido en revisión**: el junior de #6 puso `maxlength="9"` al teléfono. El navegador truncaba un número de 10 dígitos a 9 y lo aceptaba como válido en silencio, ocultando el error de longitud. Se quitó el atributo.
- **Corregido antes del primer deploy**: al configurar Railway, `APP_URL` quedó cargada sin esquema y el smoke test del CD habría fallado, porque Railway responde 301 a http. Se detectó al preparar la prueba del CD y el workflow ahora normaliza la URL.
- **Aprendizaje**: con agentes en paralelo, el trabajo del coordinador es definir contratos y límites de archivos antes de empezar. La revisión en navegador encontró problemas que los tests no cubrían, como el truncado del `maxlength`.

## 2026-11-23 · Análisis de seguridad

- **Herramientas**: Claude Code (Opus) para revisar el código y la configuración. También corrió `composer audit`, `npm audit` y Trivy sobre la imagen de producción. El resultado está en [`docs/SEGURIDAD.md`](../SEGURIDAD.md).
- **Aceptado**:
  - La protección CSRF se hace por `Sec-Fetch-Site`/`Origin` y no con tokens, porque la app no tiene sesiones.
  - No se suben Bootstrap 5.3.9 ni jQuery 3.8.0 por tener menos de dos semanas de publicadas. Se mantienen las versiones actuales con SRI.
  - Trivy corre desde su imagen oficial con versión fija, en lugar de una action de terceros.
- **Corregido en la verificación**: el primer hash SRI de jQuery estaba mal. Se calculó sobre la salida de `$(curl …)`, que quita el salto de línea final del archivo. El navegador bloqueó jQuery y el listado no cargó. Los tests no lo detectaron, pero sí la revisión en el navegador. El hash se recalculó directo del pipe y se contrastó con code.jquery.com.
- **Aprendizaje**: un SRI incorrecto rompe la página sin dar señales del lado del servidor. Hay que verificarlo siempre en el navegador, o comparándolo con el hash que publica el proveedor.
