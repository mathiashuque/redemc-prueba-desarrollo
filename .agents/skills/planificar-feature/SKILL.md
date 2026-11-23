---
name: planificar-feature
description: Convierte un pedido de funcionalidad de la prueba RedEMC en un plan de implementación autocontenido en docs/plans/<slug>.md, trazado contra task.md. Usar antes de implementar una feature no trivial (formulario, validaciones, alta por AJAX, listado, paginado, diseño).
---

# Planificar una feature

Objetivo: un plan corto que otra sesión pueda ejecutar sin releer toda la conversación.

## Pasos

1. Leé `AGENTS.md`, la sección de `task.md` que corresponde y el código actual que toca (`public/`, `src/`, `database/schema.sql`, `tests/`). Mirá `docs/plans/` por planes previos.
2. Definí el **slice**: qué ve el usuario al terminar. Si el pedido es grande, partilo en features que se puedan mergear por separado (por ejemplo: alta con validación → listado → paginado → diseño).
3. Si hay una decisión que es del usuario (scroll o paginado, diseño visual, texto de mensajes), preguntá antes de escribir el plan.
4. Escribí `docs/plans/<slug>.md` con esta estructura:

```markdown
# <Título>

## Objetivo
Qué cambia para el usuario, en 2-3 líneas.

## Requisitos (task.md)
- §3.1 Nombre: ... (cita breve de cada requisito cubierto)

## Diseño
- Base de datos: cambios en schema.sql (o "sin cambios").
- Backend: clases en src/ y endpoint en public/api/, contrato JSON (request, respuesta, códigos HTTP).
- Frontend: cambios en index.php, js/app.js y css/app.css.

## Archivos
Lista de archivos a crear o modificar.

## Tests
- Unit: casos (válidos e inválidos) para la lógica nueva.
- Integration: llamadas al endpoint contra la base real.
- Manual: navegadores y tamaños de pantalla a revisar.

## Checklist cubierto
Ítems de task.md §9 que quedan resueltos.

## Fuera de alcance
Lo que no se hace en esta feature.
```

5. No implementes nada en esta skill. Al terminar, ofrecé seguir con `implementar-feature`.
