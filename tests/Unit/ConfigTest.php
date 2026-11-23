<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConfigTest extends TestCase
{
    public function testReadsOwnVariables(): void
    {
        $config = Config::fromEnvironment([
            'DB_HOST' => 'db',
            'DB_PORT' => '3307',
            'DB_NAME' => 'redemc',
            'DB_USER' => 'app',
            'DB_PASSWORD' => 'secret',
        ]);

        self::assertSame('db', $config->dbHost);
        self::assertSame(3307, $config->dbPort);
        self::assertSame('redemc', $config->dbName);
        self::assertSame('app', $config->dbUser);
        self::assertSame('secret', $config->dbPassword);
    }

    public function testFallsBackToRailwayVariables(): void
    {
        $config = Config::fromEnvironment([
            'MYSQLHOST' => 'mysql.railway.internal',
            'MYSQLPORT' => '3306',
            'MYSQLDATABASE' => 'railway',
            'MYSQLUSER' => 'root',
            'MYSQLPASSWORD' => 'pw',
        ]);

        self::assertSame('mysql.railway.internal', $config->dbHost);
        self::assertSame('railway', $config->dbName);
        self::assertSame('pw', $config->dbPassword);
    }

    public function testOwnVariablesTakePrecedenceAndEmptyValuesAreIgnored(): void
    {
        $config = Config::fromEnvironment([
            'DB_HOST' => '',
            'MYSQLHOST' => 'fallback',
            'DB_NAME' => 'own',
            'MYSQLDATABASE' => 'railway',
            'DB_USER' => 'app',
        ]);

        self::assertSame('fallback', $config->dbHost);
        self::assertSame('own', $config->dbName);
        self::assertSame(3306, $config->dbPort);
        self::assertSame('', $config->dbPassword);
    }

    public function testFailsWhenRequiredVariablesAreMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_HOST, DB_NAME');

        Config::fromEnvironment(['DB_USER' => 'app']);
    }
}
