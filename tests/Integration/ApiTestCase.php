<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Base de los tests de integración: hace requests HTTP contra la app levantada.
 * La URL base se toma de APP_URL.
 */
abstract class ApiTestCase extends TestCase
{
    protected const FORM = 'application/x-www-form-urlencoded';

    /**
     * @param array<string, string>|null $form Campos enviados como application/x-www-form-urlencoded.
     * @param string|null $contentType Content-Type del cuerpo; por defecto, el de formulario.
     * @param string|null $rawBody Cuerpo crudo (para probar tipos no soportados).
     * @return array{int, string, array<string, string>} Status, cuerpo y cabeceras (nombres en minúsculas).
     */
    protected function request(
        string $method,
        string $path,
        ?array $form = null,
        ?string $contentType = null,
        ?string $rawBody = null,
    ): array {
        $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
        $http = ['method' => $method, 'ignore_errors' => true, 'timeout' => 10];

        if ($rawBody !== null) {
            $http['header'] = 'Content-Type: ' . ($contentType ?? self::FORM);
            $http['content'] = $rawBody;
        } elseif ($form !== null) {
            $http['header'] = 'Content-Type: ' . ($contentType ?? self::FORM);
            $http['content'] = http_build_query($form);
        }
        $context = stream_context_create(['http' => $http]);

        $body = @file_get_contents($baseUrl . $path, false, $context);
        if ($body === false) {
            self::fail('No se pudo conectar con ' . $baseUrl . '. ¿Está levantada la aplicación?');
        }

        /** @var list<string> $http_response_header */
        preg_match('{HTTP/\S+ (\d{3})}', $http_response_header[0] ?? '', $matches);

        $headers = [];
        foreach (array_slice($http_response_header, 1) as $linea) {
            [$nombre, $valor] = array_pad(explode(':', $linea, 2), 2, '');
            $headers[strtolower(trim($nombre))] = trim($valor);
        }

        return [(int) ($matches[1] ?? 0), $body, $headers];
    }
}
