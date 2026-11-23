---
name: desplegar-railway
description: Configura y verifica el deploy de la prueba RedEMC en Railway (servicio de la app + MySQL en el mismo proyecto, variables, token y secretos de GitHub para el CD) y prueba la URL pública de punta a punta. Usar para el primer deploy, cuando falla el CD o antes de la entrega.
---

# Desplegar en Railway

La app se construye con el `Dockerfile` (target `prod`). Apache escucha en `$PORT`, que inyecta Railway, y el contenedor aplica `database/schema.sql` al arrancar. El CD (`.github/workflows/cd.yml`) despliega con `railway up` cuando hay push a `main`, o a mano desde `main` o `develop`.

Las acciones en Railway y en GitHub (crear servicios, tokens, secretos) las hace el usuario en su cuenta. Guialo paso a paso; nunca le pidas que te pegue tokens ni contraseñas.

## Configuración inicial (una vez)

1. **Proyecto**: en railway.com, *New Project → Empty Project*.
2. **MySQL**: *Create → Database → MySQL*. Railway crea las variables `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD` y `MYSQLDATABASE`.
3. **Servicio de la app**: *Create → Empty Service* (sin conectar el repo, para que el único deploy sea el del CD). Nombre sugerido: `app`.
4. **Variables del servicio `app`** (*Variables → Raw Editor*), por referencia al MySQL:

   ```text
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   ```

5. **Dominio público**: *Settings → Networking → Generate Domain* en el servicio `app`.
6. **Token**: *Project Settings → Tokens → Create* (token de proyecto para el entorno `production`).
7. **GitHub** (*Settings → Secrets and variables → Actions* del repo):
   - Secret `RAILWAY_TOKEN`: el token del paso 6.
   - Variable `RAILWAY_SERVICE`: `app`.
   - Variable `APP_URL`: `https://<dominio-del-paso-5>` (sin barra final).
8. Opcional: en *Settings → Environments* de GitHub, el entorno `production` se crea solo en el primer deploy; ahí se puede exigir aprobación manual.

## Desplegar

- Automático: merge a `main`.
- Manual: *Actions → CD → Run workflow*, eligiendo `main` o `develop`.
- Desde la máquina (si el usuario tiene la CLI): `railway up --service app`.

## Verificar de punta a punta

La entrega exige que la URL pública funcione completa. Comprobá:

1. `curl -s "$APP_URL/api/health.php"` devuelve `{"status":"ok","db":true}`.
2. La página carga sin errores en la consola.
3. Cuando existan las features: dar de alta un contacto desde la URL pública, ver que aparece primero en el listado, recargar y ver que sigue (quedó en la base).

## Si algo falla

- Logs: en Railway, *Deployments → View logs* del servicio `app`; o el log del job `deploy` en GitHub Actions.
- `health` responde 503: revisar las variables `DB_*` y que MySQL esté en el mismo proyecto y entorno.
- El job falla en "Verificar configuración": faltan `RAILWAY_TOKEN`, `RAILWAY_SERVICE` o `APP_URL` en GitHub.

Al terminar el primer deploy, actualizá la sección de Railway del `README.md` con lo que realmente se hizo (es un entregable).
