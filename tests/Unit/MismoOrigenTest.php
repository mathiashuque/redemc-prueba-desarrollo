<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\MismoOrigen;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MismoOrigenTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, string>, bool}>
     */
    public static function casos(): iterable
    {
        yield 'sin headers (curl, tests)' => [[], true];
        yield 'mismo origen' => [['HTTP_SEC_FETCH_SITE' => 'same-origin'], true];
        yield 'navegación directa' => [['HTTP_SEC_FETCH_SITE' => 'none'], true];
        yield 'otro sitio' => [['HTTP_SEC_FETCH_SITE' => 'cross-site'], false];
        yield 'subdominio hermano' => [['HTTP_SEC_FETCH_SITE' => 'same-site'], false];
        yield 'Sec-Fetch-Site manda sobre Origin' => [
            ['HTTP_SEC_FETCH_SITE' => 'cross-site', 'HTTP_ORIGIN' => 'http://app.test', 'HTTP_HOST' => 'app.test'],
            false,
        ];
        yield 'Origin igual a Host' => [['HTTP_ORIGIN' => 'https://app.test', 'HTTP_HOST' => 'app.test'], true];
        yield 'Origin con puerto' => [
            ['HTTP_ORIGIN' => 'http://localhost:8081', 'HTTP_HOST' => 'localhost:8081'],
            true,
        ];
        yield 'Origin con otro puerto' => [
            ['HTTP_ORIGIN' => 'http://localhost:9999', 'HTTP_HOST' => 'localhost:8081'],
            false,
        ];
        yield 'Origin de otro host' => [['HTTP_ORIGIN' => 'https://evil.example', 'HTTP_HOST' => 'app.test'], false];
        yield 'Origin null' => [['HTTP_ORIGIN' => 'null', 'HTTP_HOST' => 'app.test'], false];
        yield 'Origin sin Host' => [['HTTP_ORIGIN' => 'https://app.test'], false];
    }

    /**
     * @param array<string, string> $server
     */
    #[DataProvider('casos')]
    public function testPermitido(array $server, bool $esperado): void
    {
        self::assertSame($esperado, MismoOrigen::permitido($server));
    }
}
