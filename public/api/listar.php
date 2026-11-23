<?php

declare(strict_types=1);

use App\Config;
use App\Contacto\ContactoListado;
use App\Contacto\Paginacion;
use App\Database;
use App\Http\JsonResponse;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

JsonResponse::allowMethods('GET');

try {
    $listado = new ContactoListado(Database::connect(Config::fromEnvironment()));

    $paginaCruda = is_string($_GET['page'] ?? null) ? $_GET['page'] : null;
    $paginacion = new Paginacion($paginaCruda, $listado->contar());
    $contactos = $listado->pagina(Paginacion::POR_PAGINA, $paginacion->offset);

    JsonResponse::send([
        'data' => $contactos,
        'page' => $paginacion->page,
        'perPage' => Paginacion::POR_PAGINA,
        'total' => $paginacion->total,
        'totalPages' => $paginacion->totalPages,
    ]);
} catch (Throwable $e) {
    // El detalle va al log del servidor, nunca al cliente.
    error_log('listar: ' . $e->getMessage());
    JsonResponse::send(['error' => 'Error interno.'], 500);
}
