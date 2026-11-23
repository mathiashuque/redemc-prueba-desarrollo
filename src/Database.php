<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Crea conexiones PDO a MySQL con opciones seguras por defecto.
 */
final class Database
{
    public static function connect(Config $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config->dbHost,
            $config->dbPort,
            $config->dbName,
        );

        return new PDO($dsn, $config->dbUser, $config->dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Consultas preparadas reales en el servidor, no emuladas por PDO.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
