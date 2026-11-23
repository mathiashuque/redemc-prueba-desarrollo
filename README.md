# redemc-prueba-desarrollo

Prueba técnica para el puesto de desarrollador web en RedEMC: formulario de alta de contactos (nombre, email y teléfono opcional) con validación y guardado por AJAX en MySQL, y listado dinámico de los registros. La especificación técnica está en [`task.md`](task.md).

**Stack**: HTML5, CSS3, Bootstrap 5, JavaScript con jQuery, PHP 8.4 (sin framework) y MySQL 8.4. Desplegado en Railway.

> Estado: estructura base, CI/CD y harness de IA listos. El formulario y el listado se implementan como features.

## Estructura

```text
public/              docroot (lo único que sirve Apache)
  index.php          pantalla principal
  api/health.php     estado de la app y de la base
  css/  js/
src/                 lógica PHP (namespace App\)
database/schema.sql  estructura de la tabla
bin/migrate.php      aplica el esquema al arrancar
tests/               PHPUnit: Unit e Integration
docker/              configuración de Apache y entrypoint
docs/                planes de features y bitácora de uso de IA
.github/workflows/   CI y CD
```

Los endpoints están en `public/api/` (y no en `api/` como sugiere la estructura orientativa) para que el docroot sea `public/` y el código de `src/`, la base y los tests nunca queden expuestos.

## Desarrollo local

Requisitos: Docker (con Compose), Node.js 20 o superior y `make`. PHP, Composer y MySQL corren en contenedores.

```bash
make setup   # dependencias de Composer y npm, y hooks de git
make up      # app en http://localhost:8080 y MySQL en localhost:3307
```

Si el puerto 8080 está ocupado: `APP_PORT=8081 make up`. Para cambiar otros valores locales, copiá `.env.example` a `.env`.

| Comando | Qué hace |
|---|---|
| `make check` | parallel-lint, PHPCS (PSR-12), PHPStan (nivel 6), PHPUnit Unit, ESLint y Stylelint |
| `make test-integration` | levanta el stack y corre los tests de integración contra la app |
| `make fix` | corrige el estilo PHP con phpcbf |
| `make logs` / `make down` | logs de la app / detener contenedores |

## Flujo de trabajo (gitflow)

- `main`: producción. Cada push despliega en Railway.
- `develop`: integración.
- `feature/<nombre>`: una rama por feature, creada desde `develop` y mergeada a `develop` por pull request.

Los commits no llevan atribución a herramientas de IA. El hook `commit-msg` (activado por `make setup`) y el CI lo verifican.

## CI/CD

- **CI** (`.github/workflows/ci.yml`) corre en todas las ramas y en los pull requests:
  - Chequeo de que ningún commit tenga atribución a IA.
  - PHP: `composer validate`, lint de sintaxis, PHPCS, PHPStan y tests unitarios.
  - Frontend: ESLint y Stylelint.
  - Integración: MySQL en un contenedor de servicio, esquema aplicado y tests contra la app con `php -S`.
  - Construcción de la imagen Docker de producción y prueba de que respeta `$PORT`.
- **CD** (`.github/workflows/cd.yml`) corre el CI completo y, si pasa, despliega en Railway con `railway up`, y después prueba `/api/health.php` en la URL pública. Se ejecuta automáticamente en cada push a `main`, y a mano desde `main` o `develop` en *Actions → CD → Run workflow*. Ambos despliegan al mismo servicio de Railway.

## Deploy en Railway

La app se construye con el `Dockerfile` (target `prod`, PHP 8.4 + Apache). Railway inyecta la variable `PORT` y el contenedor aplica `database/schema.sql` al arrancar, así que la tabla se crea sola en la primera ejecución.

Pasos de configuración (una vez):

1. Crear un proyecto en [railway.com](https://railway.com) (*New Project → Empty Project*).
2. Agregar una base MySQL al proyecto (*Create → Database → MySQL*).
3. Agregar un servicio vacío para la app (*Create → Empty Service*), llamado `app`. No se conecta al repositorio, para que el único deploy sea el del CD.
4. En el servicio `app`, cargar estas variables por referencia al MySQL:

   ```text
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   ```

5. Generar un dominio público para `app` (*Settings → Networking → Generate Domain*).
6. Crear un token de proyecto (*Project Settings → Tokens*).
7. En GitHub (*Settings → Secrets and variables → Actions*):
   - Secret `RAILWAY_TOKEN`: el token del paso 6.
   - Variable `RAILWAY_SERVICE`: `app`.
   - Variable `APP_URL`: la URL del paso 5, sin barra final.
8. Hacer push a `main` (o correr el CD a mano) y comprobar que `https://<dominio>/api/health.php` responde `{"status":"ok","db":true}`.

> Esta sección se completará con los datos reales del deploy (URL pública y observaciones) cuando se haga el primer despliegue.

## Desarrollo con IA

El repositorio incluye un harness para trabajar con agentes de IA:

- [`AGENTS.md`](AGENTS.md): guía del proyecto para cualquier agente (convenciones, seguridad, gitflow, definición de hecho). [`CLAUDE.md`](CLAUDE.md) la importa para Claude Code.
- Skills en `.agents/skills/` (expuestas en `.claude/skills/`): `planificar-feature`, `implementar-feature`, `verificar-cambio`, `desplegar-railway`, `registrar-prompts`, `registrar-proceso-ia` y `preparar-entrega`.
- Agente `revisor` (`.claude/agents/revisor.md`) para revisar cambios antes de un PR.
- Registro automático de cada prompt en [`docs/ia/prompts.md`](docs/ia/prompts.md) mediante un hook de Claude Code, y bitácora de decisiones en [`docs/ia/bitacora.md`](docs/ia/bitacora.md). Ambos alimentan `PROCESO_IA.md`.
