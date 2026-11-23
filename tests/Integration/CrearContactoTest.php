<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Prueba el endpoint de alta contra la aplicación levantada y su base real.
 * Los datos son únicos por corrida, así que los tests se pueden repetir.
 */
final class CrearContactoTest extends TestCase
{
    private const FORM = 'application/x-www-form-urlencoded';

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

    private function emailUnico(): string
    {
        return 'contacto' . bin2hex(random_bytes(6)) . '@ejemplo.test';
    }

    private function telefonoUnico(): string
    {
        return '09' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string, string>|null $form Campos enviados como application/x-www-form-urlencoded.
     * @return array{int, string, array<string, string>}
     */
    private function request(
        string $method,
        string $path,
        ?array $form = null,
        string $contentType = self::FORM,
        ?string $rawBody = null,
    ): array {
        $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
        $http = ['method' => $method, 'ignore_errors' => true, 'timeout' => 10];
        if ($rawBody !== null) {
            $http['header'] = 'Content-Type: ' . $contentType;
            $http['content'] = $rawBody;
        } elseif ($form !== null) {
            $http['header'] = 'Content-Type: ' . $contentType;
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
