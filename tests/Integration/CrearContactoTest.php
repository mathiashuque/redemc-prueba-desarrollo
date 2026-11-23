<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Prueba el endpoint de alta contra la aplicación levantada y su base real.
 * Los datos son únicos por corrida, así que los tests se pueden repetir.
 */
final class CrearContactoTest extends ApiTestCase
{
    public function testAltaCorrectaDevuelve201ConElContacto(): void
    {
        $email = $this->emailUnico();
        $telefono = $this->telefonoUnico();

        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'María José',
            'email' => $email,
            'telefono' => $telefono,
        ]);

        self::assertSame(201, $status);
        $data = json_decode($body, true)['data'];
        self::assertIsInt($data['id']);
        self::assertSame('María José', $data['nombre']);
        self::assertSame($email, $data['email']);
        self::assertSame($telefono, $data['telefono']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['created_at']);
    }

    public function testAltaSinTelefonoDevuelveNull(): void
    {
        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'Ana',
            'email' => $this->emailUnico(),
            'telefono' => '',
        ]);

        self::assertSame(201, $status);
        self::assertNull(json_decode($body, true)['data']['telefono']);
    }

    public function testDatosInvalidosDevuelven422ConUnErrorPorCampo(): void
    {
        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => '   ',
            'email' => 'no-es-email',
            'telefono' => '+59897680102',
        ]);

        self::assertSame(422, $status);
        $errores = json_decode($body, true)['errors'];
        self::assertSame(['nombre', 'email', 'telefono'], array_keys($errores));
    }

    public function testEmailDuplicadoConOtrasMayusculasDevuelve409(): void
    {
        $email = $this->emailUnico();
        $this->request('POST', '/api/crear.php', [
            'nombre' => 'Original',
            'email' => $email,
        ]);

        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'Duplicado',
            'email' => strtoupper($email),
        ]);

        self::assertSame(409, $status);
        self::assertSame(
            ['errors' => ['email' => 'Ya existe un contacto con ese email.']],
            json_decode($body, true),
        );
    }

    public function testTelefonoDuplicadoDevuelve409(): void
    {
        $telefono = $this->telefonoUnico();
        $this->request('POST', '/api/crear.php', [
            'nombre' => 'Original',
            'email' => $this->emailUnico(),
            'telefono' => $telefono,
        ]);

        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'Duplicado',
            'email' => $this->emailUnico(),
            'telefono' => $telefono,
        ]);

        self::assertSame(409, $status);
        self::assertArrayHasKey('telefono', json_decode($body, true)['errors']);
    }

    public function testMetodoGetDevuelve405ConCabeceraAllow(): void
    {
        [$status, , $headers] = $this->request('GET', '/api/crear.php');

        self::assertSame(405, $status);
        self::assertSame('POST', $headers['allow'] ?? null);
    }

    public function testContentTypeJsonDevuelve415(): void
    {
        [$status, $body] = $this->request('POST', '/api/crear.php', null, 'application/json', '{"nombre":"Ana"}');

        self::assertSame(415, $status);
        self::assertArrayHasKey('error', json_decode($body, true));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function cabecerasDeOtroSitio(): iterable
    {
        yield 'Sec-Fetch-Site cross-site' => ['Sec-Fetch-Site: cross-site'];
        yield 'Origin de otro host' => ['Origin: https://evil.example'];
    }

    #[DataProvider('cabecerasDeOtroSitio')]
    public function testAltaDesdeOtroSitioDevuelve403(string $cabecera): void
    {
        $email = $this->emailUnico();

        [$status, $body] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'Ana',
            'email' => $email,
        ], headers: [$cabecera]);

        self::assertSame(403, $status);
        self::assertSame(['error' => 'Origen no permitido.'], json_decode($body, true));

        // No se guardó: el mismo email se puede dar de alta desde el mismo origen.
        [$status] = $this->request('POST', '/api/crear.php', [
            'nombre' => 'Ana',
            'email' => $email,
        ], headers: ['Sec-Fetch-Site: same-origin']);
        self::assertSame(201, $status);
    }

    private function emailUnico(): string
    {
        return 'contacto' . bin2hex(random_bytes(6)) . '@ejemplo.test';
    }

    private function telefonoUnico(): string
    {
        return '09' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
    }
}
