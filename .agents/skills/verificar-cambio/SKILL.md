---
name: verificar-cambio
description: Verifica un cambio de la prueba RedEMC antes de commitear o abrir un PR - corre los checks automáticos en Docker, revisa el checklist de task.md y reporta evidencia exacta. Usar antes de cada commit relevante, PR o entrega.
---

# Verificar un cambio

## Checks automáticos

```bash
make check             # parallel-lint, PHPCS, PHPStan, PHPUnit Unit, ESLint, Stylelint
make test-integration  # levanta app + MySQL y corre tests/Integration
scripts/check-no-ai-attribution.sh origin/develop..HEAD
```

Si el puerto 8080 está ocupado, anteponé `APP_PORT=8081`. Si cambió el `Dockerfile` o `docker/`, construí también la imagen de producción:

```bash
docker build -t redemc:local .
docker run --rm -d -p 9000:9000 -e PORT=9000 -e RUN_MIGRATIONS=0 --name redemc-check redemc:local
curl -s -o /dev/null -w '%{http_code}\n' localhost:9000/   # 200
docker stop redemc-check
```

Si un check falla, arreglá la causa; no desactives reglas para que pase. Para estilo PHP, `make fix`.

## Revisión contra task.md

Recorré el checklist de `task.md` §9 y marcá qué ítems toca el cambio. Para cada uno, decí cómo lo comprobaste (test automático o prueba manual).

Si cambió la UI, pedile al usuario (o hacé con el navegador si hay uno disponible):

- Probar en móvil (375 px), tablet y escritorio.
- Probar en al menos dos navegadores (Chrome y Firefox; Safari si es posible).
- Revisar la consola del navegador sin errores.

## Reporte

Respondé con:

1. Comandos corridos y resultado exacto (pasó / falló, con la línea de error decisiva).
2. Ítems de §9 cubiertos y cómo se comprobaron.
3. Lo que **no** se pudo verificar y por qué.
