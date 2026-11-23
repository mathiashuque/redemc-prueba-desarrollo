# AGENTS.md

Guía para agentes de IA (Claude Code, Codex, Cursor, Copilot) que trabajan en este repositorio.

## El proyecto

Prueba técnica de RedEMC: una pantalla con **formulario de alta** (nombre, email, teléfono opcional) validado y enviado por AJAX, y **listado** de los registros guardados en MySQL, con paginado o scroll a partir de 8 registros. Se despliega en Railway. **Deadline: 30/11/2026.**

`task.md` es la especificación técnica y la fuente de verdad de los requisitos. Leé la sección que toca tu tarea antes de empezar. Ante una duda de alcance, gana `task.md`.

**Stack obligatorio** (no agregar frameworks): HTML5, CSS3, Bootstrap 5, JavaScript con jQuery, PHP 8.4 sin framework, MySQL 8. Bootstrap y jQuery se cargan por CDN; no hay paso de build de frontend.

## Estructura

```text
public/            docroot de Apache: lo único que se sirve
  index.php        la pantalla (maquetado solo con <div>)
  api/*.php        endpoints finos: validan método, delegan en src/, responden con JsonResponse
  css/  js/        estilos y jQuery
src/               lógica (namespace App\, PSR-4): Config, Database, Http\JsonResponse, ...
database/schema.sql  estructura de la tabla (entregable); idempotente
bin/migrate.php    aplica schema.sql al arrancar el contenedor
tests/Unit/        PHPUnit sin base de datos
tests/Integration/ PHPUnit contra la app levantada (APP_URL)
docs/plans/        planes de features
docs/ia/           bitácora de prompts y de uso de IA (material para PROCESO_IA.md)
.agents/skills/    skills canónicas (expuestas en .claude/skills/ con symlinks)
```

## Comandos

PHP, Composer y MySQL corren en Docker; no hace falta instalarlos localmente.

```bash
make setup             # composer install, npm ci y hooks de git (una vez)
make up                # app + MySQL en http://localhost:8080 (APP_PORT=8081 make up si 8080 está ocupado)
make check             # parallel-lint, PHPCS (PSR-12), PHPStan nivel 6, PHPUnit Unit, ESLint, Stylelint
make test-integration  # levanta el stack y corre tests/Integration
make fix               # phpcbf
make down
```

## Convenciones

- PHP: `declare(strict_types=1)`, PSR-12, clases `final`, tipos en todas las firmas. Lógica en `src/`, nunca en los endpoints.
- **Base de datos**: siempre consultas preparadas con PDO (`Database::connect`). Nunca concatenar entrada del usuario en SQL. La unicidad de email y teléfono la garantiza la base (índices `UNIQUE`); la aplicación además la verifica para dar un mensaje claro.
- **API**: JSON con `App\Http\JsonResponse`. Éxito con `200`/`201`; errores de validación con `422` y un objeto `errors` por campo (`{"errors": {"email": "..."}}`); duplicados con `409`; método incorrecto con `405`. Nunca exponer detalles internos (excepciones, SQL); van a `error_log`.
- **Validación en cliente y en servidor**, con las mismas reglas de `task.md` §3.1. El servidor es la autoridad. Ante un error no se borran los demás campos del formulario.
- Mensajes al usuario en **español**, claros y específicos por campo.
- **Frontend**: jQuery, sin `<table>` (todo con `<div>`), responsive con la grilla de Bootstrap. Para pintar datos usá `.text()` o escapá el HTML: nada de concatenar datos del usuario en `.html()`.
- JS compatible con navegadores actuales sin transpilar (ES2020, sin módulos).

## Seguridad

- Credenciales solo en variables de entorno. Nunca commitear `.env` ni valores reales.
- No imprimir secretos en logs, tests, docs ni en la bitácora de prompts.

## Git (gitflow)

- `main` = producción (cada push despliega a Railway). `develop` = integración.
- Cada feature va en `feature/<slug>` creada desde `origin/develop`, y vuelve a `develop` por pull request. No hacer push directo a `develop` ni a `main`.
- Commits con prefijo convencional (`feat:`, `fix:`, `chore:`, `docs:`, `test:`, `ci:`), en español.
- **Ningún commit ni PR lleva atribución a IA**: nada de `Co-Authored-By: Claude …`, "Generated with Claude Code" ni similares. Es una regla general del proyecto; el hook local `commit-msg` ayuda a no olvidarla.
- CI corre en todas las ramas; CD corre solo en `main` y se puede disparar a mano desde `develop`.

## Skills del proyecto

| Skill | Cuándo |
|---|---|
| `planificar-feature` | Antes de una feature no trivial: plan en `docs/plans/`. |
| `implementar-feature` | Implementar una feature con gitflow: rama, código, checks, PR a `develop` y app levantada para revisión. |
| `verificar-cambio` | Antes de commitear o abrir un PR. |
| `desplegar-railway` | Configurar o verificar el deploy en Railway. |
| `registrar-prompts` | Bitácora de prompts (`docs/ia/prompts.md`): marcar prompts clave, anotar resultados, sumar prompts de otras herramientas. |
| `registrar-proceso-ia` | Anotar decisiones sobre la IA en `docs/ia/bitacora.md` y generar `PROCESO_IA.md`. |
| `preparar-entrega` | Auditoría final antes de enviar la prueba. |

## Definición de hecho

1. El cambio cumple la sección de `task.md` que corresponde y los ítems del checklist (§9) que toca.
2. `make check` y `make test-integration` en verde. Tests nuevos para la lógica nueva.
3. Revisado en móvil y escritorio, y en al menos dos navegadores si cambia la UI.
4. `database/schema.sql`, README y `.env.example` actualizados si cambia la estructura o la configuración.
5. Si hubo una decisión relevante sobre una sugerencia de la IA (aceptada, corregida o rechazada), queda anotada en `docs/ia/bitacora.md`.
