<?php

declare(strict_types=1);

namespace Tests\Integration;

/**
 * Prueba la aplicación levantada (Docker o `php -S`) contra una base real.
 */
final class HealthTest extends ApiTestCase
{
    public function testHealthEndpointReportsDatabaseOk(): void
    {
        [$status, $body] = $this->request('GET', '/api/health.php');

        self::assertSame(200, $status);
        self::assertSame(['status' => 'ok', 'db' => true], json_decode($body, true));
    }

    public function testHealthEndpointRejectsOtherMethods(): void
    {
        [$status] = $this->request('POST', '/api/health.php');

        self::assertSame(405, $status);
    }
}
