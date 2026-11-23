<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contacto\Paginacion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginacionTest extends TestCase
{
    /**
     * @return iterable<string, array{string|null}>
     */
    public static function paginasInvalidas(): iterable
    {
        yield 'nula' => [null];
        yield 'vacía' => [''];
        yield 'texto' => ['abc'];
        yield 'cero' => ['0'];
        yield 'negativa' => ['-3'];
        yield 'decimal' => ['2.5'];
        yield 'notación científica' => ['1e2'];
    }

    #[DataProvider('paginasInvalidas')]
    public function testPaginaInvalidaCuentaComoLaPrimera(?string $pagina): void
    {
        $paginacion = new Paginacion($pagina, 20);

        self::assertSame(1, $paginacion->page);
        self::assertSame(0, $paginacion->offset);
    }

    public function testPaginaMayorQueLaUltimaDevuelveLaUltima(): void
    {
        $paginacion = new Paginacion('99', 20);

        self::assertSame(3, $paginacion->page);
        self::assertSame(3, $paginacion->totalPages);
        self::assertSame(16, $paginacion->offset);
    }

    public function testPaginaValidaCalculaOffset(): void
    {
        $paginacion = new Paginacion('2', 20);

        self::assertSame(2, $paginacion->page);
        self::assertSame(8, $paginacion->offset);
    }

    public function testUltimaPaginaParcialCalculaOffset(): void
    {
        $paginacion = new Paginacion('4', 25);

        self::assertSame(4, $paginacion->page);
        self::assertSame(4, $paginacion->totalPages);
        self::assertSame(24, $paginacion->offset);
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function totalesYPaginas(): iterable
    {
        yield 'exactamente 8' => [8, 1];
        yield '9 registros dan 2 páginas' => [9, 2];
        yield '16 registros dan 2 páginas' => [16, 2];
        yield '17 registros dan 3 páginas' => [17, 3];
    }

    #[DataProvider('totalesYPaginas')]
    public function testTotalPaginasPorRegistros(int $total, int $paginasEsperadas): void
    {
        self::assertSame($paginasEsperadas, (new Paginacion('1', $total))->totalPages);
    }

    public function testSinRegistrosHayUnaSolaPagina(): void
    {
        $paginacion = new Paginacion('5', 0);

        self::assertSame(1, $paginacion->page);
        self::assertSame(1, $paginacion->totalPages);
        self::assertSame(0, $paginacion->offset);
        self::assertSame(0, $paginacion->total);
    }

    public function testExponeElTotal(): void
    {
        self::assertSame(9, (new Paginacion(null, 9))->total);
    }
}
