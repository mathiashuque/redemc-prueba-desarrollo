-- Estructura de la base para la prueba técnica de RedEMC.
-- Idempotente: se aplica al arrancar el contenedor (bin/migrate.php).

CREATE TABLE IF NOT EXISTS contactos (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100) NOT NULL,
    -- Collation _ci: la unicidad del email no distingue mayúsculas.
    email       VARCHAR(255) NOT NULL,
    -- Opcional: se guarda NULL (no cadena vacía) para no chocar con el índice único.
    telefono    CHAR(9)      NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_contactos_email (email),
    UNIQUE KEY uq_contactos_telefono (telefono),
    KEY idx_contactos_created_at (created_at, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
