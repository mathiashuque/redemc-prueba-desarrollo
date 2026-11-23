---
name: registrar-proceso-ia
description: Anota en docs/ia/bitacora.md cómo se usó la IA (herramientas, sugerencias aceptadas, corregidas o rechazadas, aprendizajes) y genera PROCESO_IA.md, el entregable de una página que pide la prueba RedEMC. Usar al cerrar una feature con decisiones relevantes sobre la IA y antes de la entrega.
---

# Registrar el proceso con IA

## Bitácora (`docs/ia/bitacora.md`)

Agregá una entrada al final cuando pase algo de esto:

- Se usó una herramienta de IA para una parte concreta (qué herramienta, para qué).
- La IA propuso algo que se **rechazó o corrigió** (qué propuso, qué se hizo en su lugar, por qué).
- Se aprendió algo (técnica, herramienta, error evitado).

Formato:

```markdown
## AAAA-MM-DD · <feature o tema>

- **Herramienta**: Claude Code (implementación), ...
- **Rechazado/corregido**: la IA propuso X; se cambió por Y porque Z.
- **Aprendizaje**: ...
```

Escribí hechos concretos y verificables, en primera persona del usuario. No inventes decisiones que no ocurrieron: si dudás, preguntá.

## Generar `PROCESO_IA.md`

Al final del proyecto (o cuando el usuario lo pida):

1. Leé `docs/ia/bitacora.md` y `docs/ia/prompts.md` (los marcados con ⭐ primero).
2. Escribí `PROCESO_IA.md` en la raíz, **una página como máximo**, en español, con estas secciones (las pide la entrega):
   1. **Herramientas utilizadas y para qué partes.**
   2. **Prompts clave** (2 o 3, textuales o resumidos fielmente, con una línea de contexto cada uno).
   3. **Algo que la IA propuso y rechacé o corregí, y por qué.**
   4. **Algo que aprendí realizando la prueba.**
3. Mostrale el borrador al usuario antes de darlo por terminado: es un texto en su nombre.
