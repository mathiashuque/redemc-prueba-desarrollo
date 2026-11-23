<?php

declare(strict_types=1);

use App\Config;
use App\Database;
use App\Http\JsonResponse;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

JsonResponse::allowMethods('GET');

try {
    Database::connect(Config::fromEnvironment())->query('SELECT 1');
} catch (Throwable $e) {
    // El detalle va al log del servidor, nunca al cliente.
    error_log('health: ' . $e->getMessage());
    JsonResponse::send(['status' => 'error', 'db' => false], 503);
}

JsonResponse::send(['status' => 'ok', 'db' => true]);
