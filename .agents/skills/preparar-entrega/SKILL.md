---
name: preparar-entrega
description: Auditoría final de la prueba técnica RedEMC antes de enviarla por LinkedIn (deadline 30/11/2026) - funcionalidad contra task.md, entregables del repo, deploy en Railway probado de punta a punta y mensaje de envío. Usar cuando el usuario se prepara para entregar.
---

# Preparar la entrega

Recorré cada bloque y reportá un estado por ítem: ✅ cumplido (con evidencia), ❌ falta, ⚠️ dudoso. No marques nada como cumplido sin comprobarlo.

## 1. Funcionalidad

- Checklist completo de `task.md` §9 (formulario, guardado y listado, calidad).
- `verificar-cambio` en verde sobre `main`.

## 2. Repositorio

- Público en GitHub (`gh repo view --json visibility`).
- `main` contiene todo lo entregable (lo de `develop` ya mergeado).
- `database/schema.sql` presente y coincide con la base usada.
- `README.md` explica cómo se desplegó en Railway (lo que se hizo de verdad, no genérico).
- `PROCESO_IA.md` en la raíz, una página, con las 4 secciones pedidas (skill `registrar-proceso-ia`).
- Sin secretos: `git log -p | grep -iE "password|token|secret"` sin valores reales; `.env` no versionado.
- `scripts/check-no-ai-attribution.sh` pasa sobre todo el historial.
- Último CI de `main` en verde (`gh run list --branch main --limit 3`).

## 3. Deploy

- URL pública de Railway con MySQL en el mismo proyecto (skill `desplegar-railway`).
- Prueba de punta a punta **desde la URL pública**: alta de un contacto, aparece primero, persiste tras recargar, los errores de validación funcionan.
- Probado desde un móvil o con la vista responsive del navegador.

## 4. Envío

Se envía por LinkedIn a Elías Margolis, antes del 30/11/2026. Redactá un borrador corto para que el usuario lo copie (no lo envíes vos):

```text
Hola Elías, te comparto la prueba técnica:
- Repositorio: <link GitHub>
- Proyecto publicado: <link Railway>
¡Saludos!
```
