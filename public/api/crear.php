<?php

declare(strict_types=1);

use App\Config;
use App\Contacto\ContactoRepository;
use App\Contacto\ContactoValidator;
use App\Database;
use App\Http\JsonResponse;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

JsonResponse::allowMethods('POST');

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (!str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
    JsonResponse::send(['error' => 'Tipo de contenido no soportado.'], 415);
}

$resultado = (new ContactoValidator())->validar(
    $_POST['nombre'] ?? null,
    $_POST['email'] ?? null,
    $_POST['telefono'] ?? null,
);
if ($resultado['errores'] !== []) {
    JsonResponse::send(['errors' => $resultado['errores']], 422);
}

$datos = $resultado['datos'];

try {
    $repositorio = new ContactoRepository(Database::connect(Config::fromEnvironment()));

    $errores = [];
    if ($repositorio->existeEmail($datos['email'])) {
        $errores['email'] = 'Ya existe un contacto con ese email.';
    }
    if ($datos['telefono'] !== null && $repositorio->existeTelefono($datos['telefono'])) {
        $errores['telefono'] = 'Ya existe un contacto con ese teléfono.';
    }
    if ($errores !== []) {
        JsonResponse::send(['errors' => $errores], 409);
    }

    $contacto = $repositorio->crear($datos['nombre'], $datos['email'], $datos['telefono']);
} catch (PDOException $e) {
    // SQLSTATE 23000: el UNIQUE de la base rechazó un alta concurrente con el mismo dato.
    if ($e->getCode() === '23000') {
        $campo = str_contains($e->getMessage(), 'uq_contactos_telefono') ? 'telefono' : 'email';
        $mensaje = $campo === 'telefono'
            ? 'Ya existe un contacto con ese teléfono.'
            : 'Ya existe un contacto con ese email.';
        JsonResponse::send(['errors' => [$campo => $mensaje]], 409);
    }

    error_log('crear contacto: ' . $e->getMessage());
    JsonResponse::send(['error' => 'Error interno.'], 500);
} catch (Throwable $e) {
    error_log('crear contacto: ' . $e->getMessage());
    JsonResponse::send(['error' => 'Error interno.'], 500);
}

JsonResponse::send(['data' => $contacto], 201);
