<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Prueba la aplicación levantada (Docker o `php -S`) contra una base real.
 * La URL base se toma de APP_URL.
 */
final class HealthTest extends TestCase
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

    /**
     * @return array{int, string}
     */
    private function request(string $method, string $path): array
    {
        $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
        $context = stream_context_create([
            'http' => ['method' => $method, 'ignore_errors' => true, 'timeout' => 10],
        ]);

        $body = @file_get_contents($baseUrl . $path, false, $context);
        if ($body === false) {
            self::fail('No se pudo conectar con ' . $baseUrl . '. ¿Está levantada la aplicación?');
        }

        /** @var list<string> $http_response_header */
        preg_match('{HTTP/\S+ (\d{3})}', $http_response_header[0] ?? '', $matches);

        return [(int) ($matches[1] ?? 0), $body];
    }
}
