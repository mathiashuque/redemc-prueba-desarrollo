# Análisis de seguridad

Revisión del 23/11/2026 sobre `develop` (después del PR #14). Para cada hallazgo se indica qué se corrigió en la rama `feature/seguridad` y qué riesgo se acepta.

## Alcance y metodología

| Área | Cómo se revisó |
|---|---|
| Código PHP (`src/`, `public/api/`) | Lectura completa: inyección SQL, validación, manejo de errores, métodos HTTP |
| Frontend (`public/index.php`, `public/js/app.js`) | XSS (cómo se pintan los datos), recursos externos |
| Dependencias PHP y npm | `composer audit` y `npm audit` |
| Imagen Docker de producción | Trivy 0.76.1 (`image --scanners vuln`, HIGH y CRITICAL) |
| Dockerfile y configuración | Trivy `config`, lectura de `Dockerfile`, `docker/vhost.conf` y `docker-compose.yml` |
| Cabeceras HTTP | `curl -I` contra el contenedor de producción |
| CI/CD | Lectura de `.github/workflows/*.yml`: permisos, inyección en scripts, versiones fijas |

## Resultado general

- **Inyección SQL: no se encontraron vectores.** Todas las consultas usan sentencias preparadas de PDO con `ATTR_EMULATE_PREPARES=false`, así que la base prepara las consultas de verdad. `LIMIT` y `OFFSET` van con `bindValue(..., PDO::PARAM_INT)`, la página se valida con `filter_var` y ninguna entrada del usuario se concatena en SQL (`src/Contacto/*.php`).
- **XSS en los datos: no hay.** Los datos del usuario se pintan solo con `.text()` (`public/js/app.js`). Además, el servidor valida con listas de caracteres permitidos (`ContactoValidator`).
- **Fuga de información: no hay.** Ante un error, el cliente recibe `{"error": "Error interno."}` y el detalle va a `error_log`.
- **Dependencias:** `composer audit` y `npm audit` no reportan vulnerabilidades. Las dependencias de Composer y npm son solo de desarrollo. En producción no hay ninguna librería PHP de terceros.

## Hallazgos

| # | Hallazgo | Severidad | Estado |
|---|---|---|---|
| 1 | Bootstrap y jQuery se cargaban de jsDelivr **sin SRI**: si el CDN se compromete, se ejecuta JS arbitrario en la página | Alta | Corregido |
| 2 | Sin **Content-Security-Policy** | Media | Corregido |
| 3 | **CSRF** en `POST /api/crear.php`: un sitio externo podía enviar un formulario simple y dar de alta contactos en nombre del visitante | Media | Corregido |
| 4 | La imagen de producción tenía **75 vulnerabilidades HIGH con parche disponible** en paquetes de Debian (krb5, linux-libc-dev) | Media | Corregido |
| 5 | Apache corría como **root** dentro del contenedor (Trivy DS-0002) | Media | Corregido |
| 6 | Imágenes con **tags flotantes** (`php:8.4-apache`, `composer:2`, `mysql:8.4`) y sin escaneo en el CI | Media | Corregido |
| 7 | Cadena de suministro del CI: actions de terceros referenciadas por tag, `@railway/cli` sin versión, sin auditoría de dependencias ni Dependabot | Media | Corregido |
| 8 | Apache mostraba su versión (`Server: Apache/2.4.x (Debian)`) y aceptaba `TRACE`. Faltaban HSTS, `Permissions-Policy` y COOP | Baja | Corregido |
| 9 | Sin límite de tamaño del cuerpo: PHP aceptaba hasta 8 MB por POST | Baja | Corregido |
| 10 | `docker-compose` publicaba MySQL (con contraseñas de desarrollo) y la app en `0.0.0.0`, accesibles desde la red local | Baja | Corregido |
| 11 | Sin **rate limiting** en el alta | Baja | Aceptado |
| 12 | 92 vulnerabilidades HIGH/CRITICAL de Debian **sin parche publicado** en la imagen base | Baja | Aceptado |

## Cambios

1. **SRI** (`public/index.php`): `integrity="sha384-…"` en los tres recursos de jsDelivr. Los hashes se calcularon sobre los archivos de jsDelivr, coinciden con los de code.jquery.com y con los publicados por Bootstrap. Se mantienen Bootstrap 5.3.3 y jQuery 3.7.1, que no tienen vulnerabilidades conocidas. Las versiones nuevas (5.3.9 y 3.8.0) salieron hace menos de dos semanas y se dejan para Dependabot.
2. **Cabeceras** (`docker/vhost.conf`):
   - CSP: solo scripts propios y de jsDelivr, estilos propios y de Google Fonts, y `connect-src 'self'`. También `object-src 'none'`, `base-uri 'self'`, `form-action 'self'` y `frame-ancestors 'none'`. No hace falta `'unsafe-inline'`.
   - `Permissions-Policy`, `Cross-Origin-Opener-Policy: same-origin`, y HSTS solo cuando el request llegó por HTTPS (según `X-Forwarded-Proto` del proxy de Railway).
   - `ServerTokens Prod`, `ServerSignature Off` y `TraceEnable Off`.
3. **CSRF** (`src/Http/MismoOrigen.php`): `crear.php` rechaza con `403` un request cuyo `Sec-Fetch-Site` no sea `same-origin` ni `none`. En navegadores sin `Sec-Fetch-Site`, compara el host de `Origin` con `Host`. Sin ninguno de los dos headers el request no viene de un navegador (curl, tests) y se acepta. No hace falta token, porque la app no tiene sesiones ni cookies. Tiene tests unitarios (`MismoOrigenTest`) y de integración (`CrearContactoTest`).
4. **Imagen de producción** (`Dockerfile`):
   - `apt-get upgrade` en la base.
   - `USER www-data`: Apache escucha en `$PORT`, que es mayor a 1024, y el código no se puede escribir.
   - Versiones fijas: `php:8.4.28-apache`, `composer:2.11.2`, `mysql:8.4.13`.
   - Límites de PHP: `post_max_size=16K`, `max_input_vars=20`, `file_uploads=Off`, `allow_url_fopen=Off`. Apache agrega `LimitRequestBody 16384`.
5. **docker-compose**: puertos publicados solo en `127.0.0.1`.
6. **CI** (`.github/workflows/ci.yml`): job `security` con `composer audit`, `npm audit --audit-level=high`, Trivy sobre la imagen (falla ante HIGH/CRITICAL con parche) y Trivy `config`. Trivy corre desde su imagen oficial con versión fija, sin actions de terceros. `shivammathur/setup-php` y `ramsey/composer-install` quedan fijadas por SHA, y `@railway/cli` por versión.
7. **Dependabot** (`.github/dependabot.yml`): PRs semanales a `develop` para Composer, npm, Docker, docker-compose y GitHub Actions.

## Escaneo de la imagen: antes y después

| | Antes | Después |
|---|---|---|
| HIGH/CRITICAL con parche disponible | 75 | **0** |
| HIGH/CRITICAL en total | 167 | 92 |
| Usuario del proceso | root | www-data |
| Trivy `config` (Dockerfile) | 1 HIGH (DS-0002) | 0 |

## Riesgos aceptados

- **Sin rate limiting:** alguien puede llenar la tabla con altas automatizadas. Limitar por IP requiere estado compartido (Redis o una tabla) o un WAF delante, y excede el alcance de la prueba. El límite de tamaño y la protección CSRF reducen el abuso desde navegadores. En un entorno real se pondría en el proxy o en Cloudflare.
- **Vulnerabilidades de Debian sin parche:** no tienen versión corregida publicada. La mayoría están en librerías que la app no usa en tiempo de ejecución (perl, ncurses, util-linux, linux-libc-dev). El CI falla apenas aparezca un parche, y Dependabot propone la imagen base nueva.
- **Usuario de MySQL en Railway:** usa las credenciales que crea el servicio de Railway, con privilegios amplios sobre su base. Un usuario con solo `SELECT` e `INSERT` sobre `contactos` reduciría el impacto de una inyección SQL hipotética, pero `bin/migrate.php` necesita `CREATE`.
- **Google Fonts sin SRI:** el CSS se genera según el navegador, así que no admite hash. Queda acotado por la CSP (`style-src` y `font-src`) y no ejecuta código.

## Cómo reproducir

```bash
make check && make test-integration
docker build -t redemc:scan .
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy:0.76.1 \
  image --scanners vuln --severity HIGH,CRITICAL --ignore-unfixed redemc:scan
docker run --rm -v "$PWD:/src:ro" aquasec/trivy:0.76.1 config --severity HIGH,CRITICAL /src
curl -sI http://localhost:8080/
curl -s -X POST -H 'Origin: https://otro.example' -d 'nombre=Ana&email=a@b.com' http://localhost:8080/api/crear.php
```
