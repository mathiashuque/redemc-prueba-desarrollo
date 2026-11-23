<?php

/**
 * Aplica database/schema.sql. Es idempotente (CREATE TABLE IF NOT EXISTS) y
 * reintenta mientras la base termina de arrancar.
 */

declare(strict_types=1);

use App\Config;
use App\Database;

require dirname(__DIR__) . '/vendor/autoload.php';

const MAX_ATTEMPTS = 15;
const RETRY_SECONDS = 2;

$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "migrate: no se pudo leer database/schema.sql\n");
    exit(1);
}

$config = Config::fromEnvironment();

for ($attempt = 1; $attempt <= MAX_ATTEMPTS; $attempt++) {
    try {
        Database::connect($config)->exec($schema);
        fwrite(STDOUT, "migrate: esquema aplicado\n");
        exit(0);
    } catch (PDOException $e) {
        fwrite(STDERR, sprintf("migrate: intento %d/%d falló: %s\n", $attempt, MAX_ATTEMPTS, $e->getMessage()));
        sleep(RETRY_SECONDS);
    }
}

exit(1);
