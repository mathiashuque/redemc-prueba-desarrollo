---
name: implementar-feature
description: Implementa una feature de la prueba RedEMC con gitflow - crea feature/<slug> desde develop, implementa y testea, commitea sin atribución a IA, abre un pull request a develop y levanta la app en Docker para que el usuario la revise. Usar cuando el usuario pide implementar una funcionalidad.
---

# Implementar una feature (gitflow)

Invocar esta skill es la autorización del usuario para hacer push de la rama de la feature y abrir el PR a `develop`. Nunca hagas push a `develop` ni a `main`, y nunca mergees: el merge lo hace el usuario.

## 1. Preparar la rama

1. `git status`: si hay cambios sin commitear que no son tuyos, pará y preguntá.
2. `git fetch origin` y `git switch -c feature/<slug> origin/develop`, con `<slug>` corto en kebab-case (por ejemplo `feature/alta-contacto`).
3. Quitá el upstream que hereda la rama (`git branch --unset-upstream`): el primer push lo fija a `origin/feature/<slug>`.
4. Si existe `docs/plans/<slug>.md`, seguilo. Si la feature no es trivial y no hay plan, usá primero `planificar-feature`.

## 2. Implementar

Leé `AGENTS.md` y la sección de `task.md` que corresponde. Implementá el slice completo, en este orden:

1. **Base de datos**: cambios en `database/schema.sql` (idempotente).
2. **Backend**: lógica en `src/` (validación, repositorio con PDO preparado), endpoint fino en `public/api/` con `JsonResponse`, contrato JSON de `AGENTS.md`.
3. **Frontend**: maquetado con `<div>` en `public/index.php`, jQuery en `public/js/app.js` (AJAX, errores por campo, sin perder datos cargados, `.text()` para pintar datos), estilos responsive en `public/css/app.css`.
4. **Tests**: unitarios para la lógica (casos válidos e inválidos de cada regla) e integración contra el endpoint.

Reglas: mismas validaciones en cliente y servidor; mensajes en español; nada de `<table>`; nada de SQL concatenado; no tocar lo que no pide la feature.

## 3. Verificar

Seguí `verificar-cambio`. No avances con checks en rojo. Si cambia la UI, pedí al agente `revisor` una revisión antes del PR.

## 4. Commitear

- Commits pequeños y convencionales en español (`feat: alta de contactos por AJAX`).
- **Sin atribución a IA**: sin `Co-Authored-By`, sin "Generated with …". El hook `commit-msg` lo bloquea; si no está activo, corré `git config core.hooksPath .githooks`.
- Si una sugerencia de la IA se rechazó o corrigió, anotala en `docs/ia/bitacora.md` (skill `registrar-proceso-ia`).

## 5. Pull request a develop

```bash
git push -u origin feature/<slug>
gh pr create --base develop --title "feat: <resumen>" --body-file <archivo>
```

El cuerpo del PR, en español y **sin atribución a IA**, incluye:

- **Qué hace**: 2-4 líneas.
- **Cómo probar**: pasos concretos en la app local.
- **Checks**: comandos corridos y resultado.
- **task.md**: ítems del checklist §9 cubiertos.

## 6. Dejar la app lista para revisar

1. `make up` en la rama de la feature (si el puerto 8080 está ocupado, `APP_PORT=8081 make up`).
2. Confirmá que `curl -s localhost:<puerto>/api/health.php` responde `{"status":"ok","db":true}`.
3. Pasale al usuario la URL local y el link del PR, con un resumen de qué revisar.
