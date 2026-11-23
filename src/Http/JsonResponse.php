<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Respuestas JSON uniformes para los endpoints de public/api/.
 */
final class JsonResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public static function send(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Corta la ejecución con 405 si el método HTTP no es uno de los permitidos.
     */
    public static function allowMethods(string ...$methods): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, $methods, true)) {
            return;
        }

        header('Allow: ' . implode(', ', $methods));
        self::send(['error' => 'Método no permitido.'], 405);
    }
}
