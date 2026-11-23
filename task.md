# Prueba técnica RedEMC: Registro y listado de contactos

## 1. Alcance

Aplicación web de una sola pantalla con dos componentes:

1. **Formulario de alta** con validación y envío por AJAX.
2. **Listado de registros** persistidos en MySQL, con paginado o scroll dinámico.

**Stack obligatorio:** HTML5, CSS3, Bootstrap, JavaScript/jQuery, PHP, MySQL.
**Uso de IA:** permitido (Claude, ChatGPT, Copilot, Cursor, etc.) y debe documentarse (ver sección 8).

**Deadline de entrega:** 30/11/2026.

---

## 2. Modelo de datos

Tabla única (nombre sugerido: `contactos`).

| Campo | Tipo sugerido | Restricciones |
|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT` | PK |
| `nombre` | `VARCHAR(100)` | `NOT NULL` |
| `email` | `VARCHAR(255)` | `NOT NULL`, `UNIQUE`, comparación sin distinguir mayúsculas |
| `telefono` | `VARCHAR(9)` | `NULL`, `UNIQUE` (MySQL permite múltiples `NULL` en un índice único) |
| `created_at` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` (fecha de inserción) |

- Charset `utf8mb4`. Para unicidad de email sin distinguir mayúsculas: usar collation `_ci` (por ejemplo `utf8mb4_unicode_ci`) o guardar el email normalizado en minúsculas.
- El teléfono vacío debe persistirse como `NULL`, no como cadena vacía, para no violar el índice único.
- La unicidad debe garantizarla la **base de datos** (índice `UNIQUE`), además de la verificación previa en la aplicación. Esto evita condiciones de carrera.

---

## 3. Formulario de alta

Campos: **Nombre**, **Email**, **Teléfono (opcional)**.

### 3.1 Reglas de validación

| Campo | Requisito | Regla |
|---|---|---|
| Nombre | Obligatorio | Se aplica `trim()`; una cadena solo de espacios se considera vacía. Debe coincidir con una expresión regular que admita únicamente letras y espacios, incluyendo tildes y `ñ`. |
| Email | Obligatorio | Debe coincidir con una expresión regular de formato de email estricta. **Único** en la base, sin distinguir mayúsculas. |
| Teléfono | Opcional | Si se completa: exactamente 9 dígitos, empieza con `09`, sin espacios, guiones ni prefijo `+598` (ejemplo válido: `097680102`). Validación por expresión regular. **Único** entre los registros existentes. |

**Expresiones regulares de partida** (a revisar y probar):

```text
Nombre:   ^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+(?: [A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+)*$
Teléfono: ^09\d{7}$
Email:    ^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$
```

### 3.2 Manejo de errores

- Ante cualquier validación fallida (campo obligatorio vacío, formato inválido o dato duplicado) se muestra un **mensaje claro y específico por campo**.
- **No se pierden los datos ya cargados** en los demás campos.
- La validación debe ejecutarse en **cliente** (retroalimentación inmediata) y en **servidor** (autoridad final). Nunca depender solo del cliente.

### 3.3 Envío

1. El formulario se envía por **AJAX** (jQuery) a un endpoint PHP, sin recargar la página.
2. El servidor valida, verifica duplicados e inserta el registro con la fecha de inserción.
3. El endpoint responde en JSON, con un estado de éxito o con errores por campo y un código HTTP acorde (por ejemplo `422` para validación y `409` para duplicados).
4. Si el alta es exitosa, el nuevo registro aparece **sin refrescar la página** y en la **primera posición** del listado. El formulario se limpia.

---

## 4. Listado de datos

- Muestra los registros guardados en MySQL, ordenados del más reciente al más antiguo (`ORDER BY created_at DESC, id DESC`).
- **Máximo 8 registros visibles.** Si hay más, se implementa una de estas dos opciones:
  - **Paginado dinámico** (8 por página), con cambio de página por AJAX, sin refrescar. La consulta debe usar `LIMIT`/`OFFSET`.
  - **Scroll** con altura limitada.
- Al agregar un registro nuevo, este se inserta al inicio de la vista actual. Si se superan los 8 visibles, el paginado o el scroll deben ajustarse de forma coherente (por ejemplo, el registro más antiguo pasa a la página siguiente).
- Datos mostrados: nombre, email, teléfono (o vacío) y fecha de alta.

---

## 5. Requisitos no funcionales

| Área | Requisito |
|---|---|
| **Maquetado** | 100% con `<div>`; **sin `<table>`**. |
| **Responsive** | Formulario y listado adaptados a distintos tamaños de pantalla (grilla de Bootstrap y media queries). |
| **Cross-browser** | Funcionamiento verificado en varios navegadores (Chrome, Firefox, Edge, Safari si es posible). |
| **Diseño** | Estilo propio para los inputs, sin imágenes. Suma puntos. Efectos permitidos, sin saturar. |
| **Calidad de código** | Prolijidad, escalabilidad, eficiencia y seguridad (criterios de evaluación). |

### Seguridad (mínimo esperado)

- **Consultas preparadas (PDO o `mysqli`)** en todo acceso a la base: nada de concatenar entrada del usuario en SQL.
- **Escapado de salida** al renderizar datos en el DOM (evitar XSS; usar `.text()` en vez de `.html()` o escapar al construir el HTML).
- Validación y normalización en servidor.
- Credenciales de la base en **variables de entorno**, nunca en el repositorio.
- Manejo de errores sin exponer detalles internos (stack traces, consultas SQL).
- Opcional: token CSRF, `Content-Type` y método HTTP verificados en el endpoint.

### Escalabilidad y mantenibilidad

- Separar responsabilidades: conexión a la base, validación, endpoints, frontend (estructura de carpetas clara).
- Código reutilizable para validaciones.
- Índices en columnas consultadas (`email`, `telefono` y `created_at`).

---

## 6. Estructura sugerida del repositorio

```text
/
├── public/            # index.php, assets (css, js)
│   ├── css/
│   └── js/
├── api/               # endpoints PHP (crear, listar)
├── src/               # conexión, validación, repositorio
├── database/
│   └── schema.sql     # estructura de la tabla
├── README.md          # deploy en Railway
├── PROCESO_IA.md      # uso de IA
└── .gitignore
```

La estructura es orientativa y se puede adaptar mientras se mantenga la separación de responsabilidades.

---

## 7. Entrega

Se envía **por LinkedIn**, antes del **30/11/2026**, con estos elementos:

1. **Repositorio público en GitHub** con el código del proyecto.
2. **URL pública del proyecto desplegado** en Railway (o servicio similar), con la **base MySQL creada en el mismo proyecto**. Debe funcionar de punta a punta: ingresar un dato, que se guarde y que aparezca en el listado. Si el formulario carga pero no guarda, la entrega se considera **incompleta**. Probar desde la URL pública antes de enviar.
3. **Dentro del repositorio:**
   - Archivo `.sql` con la estructura de la tabla.
   - `README.md` que explique cómo se desplegó en Railway.
   - `PROCESO_IA.md` (ver sección 8).

---

## 8. PROCESO_IA.md

Una página como máximo. Debe incluir:

1. Herramientas de IA usadas y para qué partes.
2. Dos o tres prompts clave.
3. Una sugerencia de la IA que se **rechazó o corrigió**, con el motivo.
4. Algo aprendido durante la prueba.

Si no se usó IA, debe indicarse igualmente en este archivo.

---

## 9. Checklist de aceptación

**Formulario**
- [ ] Nombre vacío o solo espacios muestra error.
- [ ] Nombre con números o símbolos muestra error; con tildes y `ñ` es válido.
- [ ] Email vacío o con formato inválido muestra error.
- [ ] Email duplicado (incluso con otras mayúsculas) muestra error.
- [ ] Teléfono vacío es válido.
- [ ] Teléfono con formato incorrecto (`+598...`, espacios, guiones, otra longitud, no empieza con `09`) muestra error.
- [ ] Teléfono duplicado muestra error.
- [ ] Un error en un campo no borra los otros campos.
- [ ] Los errores se muestran por campo, con mensaje específico.

**Guardado y listado**
- [ ] El alta se hace por AJAX, sin recargar.
- [ ] Se guarda la fecha de inserción.
- [ ] El nuevo registro aparece primero, sin refrescar.
- [ ] Máximo 8 registros visibles; paginado dinámico o scroll funcionan.
- [ ] El listado se carga desde MySQL al abrir la página.

**Calidad**
- [ ] Sin `<table>`.
- [ ] Responsive en móvil, tablet y escritorio.
- [ ] Probado en al menos dos navegadores.
- [ ] Consultas preparadas, escapado de salida y validación en servidor.
- [ ] Sin credenciales en el repositorio.

**Entrega**
- [ ] Repositorio público con `.sql`, `README.md` y `PROCESO_IA.md`.
- [ ] URL de Railway probada de punta a punta desde fuera.
- [ ] Todo enviado por LinkedIn antes del 30/11/2026.
