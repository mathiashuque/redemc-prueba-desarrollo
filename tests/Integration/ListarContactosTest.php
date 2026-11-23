<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Config;
use App\Database;
use PDO;

/**
 * Prueba GET /api/listar.php contra la app levantada y la base real.
 * Cada test vacía la tabla `contactos` (es la base de desarrollo/test) y siembra sus datos.
 */
final class ListarContactosTest extends ApiTestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = Database::connect(Config::fromEnvironment());
        $this->pdo->exec('DELETE FROM contactos');
    }

    public function testRespuestaTieneLaEstructuraDelContrato(): void
    {
        $this->sembrar(1);

        [$status, $body] = $this->request('GET', '/api/listar.php');
        $json = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertSame(['data', 'page', 'perPage', 'total', 'totalPages'], array_keys($json));
        self::assertSame(1, $json['page']);
        self::assertSame(8, $json['perPage']);
        self::assertSame(1, $json['total']);
        self::assertSame(1, $json['totalPages']);

        $contacto = $json['data'][0];
        self::assertSame(['id', 'nombre', 'email', 'telefono', 'created_at'], array_keys($contacto));
        self::assertIsInt($contacto['id']);
        self::assertSame('Contacto 1', $contacto['nombre']);
        self::assertSame('contacto1@example.com', $contacto['email']);
        self::assertNull($contacto['telefono']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $contacto['created_at']);
    }

    public function testTelefonoSeDevuelveCuandoExiste(): void
    {
        $this->pdo->prepare(
            'INSERT INTO contactos (nombre, email, telefono, created_at)'
            . ' VALUES (:nombre, :email, :telefono, :created_at)',
        )->execute([
            'nombre' => 'Con Teléfono',
            'email' => 'con.telefono@example.com',
            'telefono' => '097680102',
            'created_at' => '2026-01-01 10:00:00',
        ]);

        [, $body] = $this->request('GET', '/api/listar.php');
        $json = json_decode($body, true);

        self::assertSame('097680102', $json['data'][0]['telefono']);
    }

    public function testMaximoOchoRegistrosPorPagina(): void
    {
        $this->sembrar(10);

        [$status, $body] = $this->request('GET', '/api/listar.php?page=1');
        $json = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertCount(8, $json['data']);
        self::assertSame(10, $json['total']);
        self::assertSame(2, $json['totalPages']);
    }

    public function testOrdenDelMasRecienteAlMasAntiguo(): void
    {
        $this->insertarConFecha('Antiguo', '2026-01-01 10:00:00');
        $this->insertarConFecha('Reciente', '2026-03-01 10:00:00');
        $this->insertarConFecha('Medio', '2026-02-01 10:00:00');

        [, $body] = $this->request('GET', '/api/listar.php');
        $nombres = array_column(json_decode($body, true)['data'], 'nombre');

        self::assertSame(['Reciente', 'Medio', 'Antiguo'], $nombres);
    }

    public function testEmpateDeFechaSeDesempataPorIdDescendente(): void
    {
        $primero = $this->insertarConFecha('Primero', '2026-01-01 10:00:00');
        $segundo = $this->insertarConFecha('Segundo', '2026-01-01 10:00:00');

        [, $body] = $this->request('GET', '/api/listar.php');
        $ids = array_column(json_decode($body, true)['data'], 'id');

        self::assertLessThan($segundo, $primero);
        self::assertSame([$segundo, $primero], $ids);
    }

    public function testTotalPagesEsCoherenteConTotal(): void
    {
        $this->sembrar(17);

        [, $body] = $this->request('GET', '/api/listar.php?page=3');
        $json = json_decode($body, true);

        self::assertSame(17, $json['total']);
        self::assertSame(3, $json['totalPages']);
        self::assertSame(3, $json['page']);
        self::assertCount(1, $json['data']);
    }

    public function testPaginaFueraDeRangoDevuelveLaUltima(): void
    {
        $this->sembrar(10);

        [, $body] = $this->request('GET', '/api/listar.php?page=99');
        $json = json_decode($body, true);

        self::assertSame(2, $json['page']);
        self::assertCount(2, $json['data']);
    }

    public function testPaginaInvalidaSeTrataComoLaPrimera(): void
    {
        $this->sembrar(10);

        [$status, $body] = $this->request('GET', '/api/listar.php?page=abc');
        $json = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertSame(1, $json['page']);
        self::assertCount(8, $json['data']);
    }

    public function testSinRegistrosDevuelveListaVaciaYUnaPagina(): void
    {
        [$status, $body] = $this->request('GET', '/api/listar.php');
        $json = json_decode($body, true);

        self::assertSame(200, $status);
        self::assertSame([], $json['data']);
        self::assertSame(0, $json['total']);
        self::assertSame(1, $json['page']);
        self::assertSame(1, $json['totalPages']);
    }

    public function testRechazaOtrosMetodos(): void
    {
        [$status] = $this->request('POST', '/api/listar.php');

        self::assertSame(405, $status);
    }

    private function sembrar(int $cantidad): void
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $this->pdo->prepare(
                'INSERT INTO contactos (nombre, email, telefono, created_at)'
                . ' VALUES (:nombre, :email, NULL, :created_at)',
            )->execute([
                'nombre' => 'Contacto ' . $i,
                'email' => 'contacto' . $i . '@example.com',
                'created_at' => sprintf('2026-01-01 10:%02d:00', $i),
            ]);
        }
    }

    private function insertarConFecha(string $nombre, string $createdAt): int
    {
        $sentencia = $this->pdo->prepare(
            'INSERT INTO contactos (nombre, email, telefono, created_at) VALUES (:nombre, :email, NULL, :created_at)',
        );
        $sentencia->execute([
            'nombre' => $nombre,
            'email' => strtolower($nombre) . '@example.com',
            'created_at' => $createdAt,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
