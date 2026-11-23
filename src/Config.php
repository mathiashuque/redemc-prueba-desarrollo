<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

/**
 * Configuración de la aplicación leída de variables de entorno.
 *
 * Acepta las variables propias (DB_*) y, como alternativa, las que expone el
 * servicio MySQL de Railway (MYSQLHOST, MYSQLPORT, ...).
 */
final class Config
{
    private const DEFAULT_DB_PORT = 3306;

    public function __construct(
        public readonly string $dbHost,
        public readonly int $dbPort,
        public readonly string $dbName,
        public readonly string $dbUser,
        public readonly string $dbPassword,
    ) {
    }

    /**
     * @param array<string, string>|null $env Variables a usar; por defecto, las del proceso.
     */
    public static function fromEnvironment(?array $env = null): self
    {
        $env ??= getenv();

        $host = self::firstValue($env, 'DB_HOST', 'MYSQLHOST');
        $name = self::firstValue($env, 'DB_NAME', 'MYSQLDATABASE');
        $user = self::firstValue($env, 'DB_USER', 'MYSQLUSER');

        $missing = array_keys(array_filter(
            ['DB_HOST' => $host, 'DB_NAME' => $name, 'DB_USER' => $user],
            static fn (?string $value): bool => $value === null,
        ));
        if ($missing !== []) {
            throw new RuntimeException('Faltan variables de entorno: ' . implode(', ', $missing));
        }

        $port = self::firstValue($env, 'DB_PORT', 'MYSQLPORT');

        return new self(
            dbHost: (string) $host,
            dbPort: $port === null ? self::DEFAULT_DB_PORT : (int) $port,
            dbName: (string) $name,
            dbUser: (string) $user,
            dbPassword: self::firstValue($env, 'DB_PASSWORD', 'MYSQLPASSWORD') ?? '',
        );
    }

    /**
     * @param array<string, string> $env
     */
    private static function firstValue(array $env, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($env[$key]) && $env[$key] !== '') {
                return $env[$key];
            }
        }

        return null;
    }
}
