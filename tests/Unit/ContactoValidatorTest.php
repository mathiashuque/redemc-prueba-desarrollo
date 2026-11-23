<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contacto\ContactoValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContactoValidatorTest extends TestCase
{
    private const EMAIL_VALIDO = 'ana@ejemplo.com';

    /**
     * @return array<string, array{mixed}>
     */
    public static function nombresValidos(): array
    {
        return [
            'simple' => ['Ana'],
            'con tilde' => ['José'],
            'con ñ' => ['Ñandú'],
            'dos palabras' => ['María José'],
            'mayúsculas' => ['ÁNGEL'],
            'con espacios en los bordes' => ['  Ana  '],
        ];
    }

    #[DataProvider('nombresValidos')]
    public function testNombreValidoSeAceptaYSeRecorta(string $nombre): void
    {
        $resultado = (new ContactoValidator())->validar($nombre, self::EMAIL_VALIDO, null);

        self::assertArrayNotHasKey('nombre', $resultado['errores']);
        self::assertSame(trim($nombre), $resultado['datos']['nombre']);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function nombresInvalidos(): array
    {
        $obligatorio = 'El nombre es obligatorio.';
        $soloLetras = 'El nombre solo puede contener letras y espacios.';

        return [
            'vacío' => ['', $obligatorio],
            'solo espacios' => ['   ', $obligatorio],
            'null' => [null, $obligatorio],
            'array' => [['Ana'], $obligatorio],
            'números' => ['Ana1', $soloLetras],
            'solo números' => ['1234', $soloLetras],
            'símbolos' => ['Ana!', $soloLetras],
            'guion' => ['Ana-María', $soloLetras],
            'guion bajo' => ['Ana_', $soloLetras],
            'espacio doble' => ['Ana  María', $soloLetras],
            'más de 100 caracteres' => [str_repeat('a', 101), 'El nombre no puede superar los 100 caracteres.'],
        ];
    }

    #[DataProvider('nombresInvalidos')]
    public function testNombreInvalidoDaErrorEspecifico(mixed $nombre, string $mensaje): void
    {
        $resultado = (new ContactoValidator())->validar($nombre, self::EMAIL_VALIDO, null);

        self::assertSame(['nombre' => $mensaje], $resultado['errores']);
    }

    public function testNombreDeCienCaracteresEsValido(): void
    {
        $resultado = (new ContactoValidator())->validar(str_repeat('a', 100), self::EMAIL_VALIDO, null);

        self::assertSame([], $resultado['errores']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function emailsValidos(): array
    {
        return [
            'simple' => ['ana@ejemplo.com', 'ana@ejemplo.com'],
            'mayúsculas se normalizan' => ['ANA@Ejemplo.COM', 'ana@ejemplo.com'],
            'con espacios' => ['  ana@ejemplo.com  ', 'ana@ejemplo.com'],
            'con punto y más' => ['ana.perez+prueba@ejemplo.com', 'ana.perez+prueba@ejemplo.com'],
            'subdominio' => ['ana@mail.ejemplo.com.uy', 'ana@mail.ejemplo.com.uy'],
            'guion en dominio' => ['ana@mi-ejemplo.com', 'ana@mi-ejemplo.com'],
        ];
    }

    #[DataProvider('emailsValidos')]
    public function testEmailValidoSeNormalizaAMinusculas(string $entrada, string $esperado): void
    {
        $resultado = (new ContactoValidator())->validar('Ana', $entrada, null);

        self::assertSame([], $resultado['errores']);
        self::assertSame($esperado, $resultado['datos']['email']);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function emailsInvalidos(): array
    {
        $obligatorio = 'El email es obligatorio.';
        $formato = 'El email no tiene un formato válido.';

        return [
            'vacío' => ['', $obligatorio],
            'solo espacios' => ['   ', $obligatorio],
            'null' => [null, $obligatorio],
            'array' => [['ana@ejemplo.com'], $obligatorio],
            'sin arroba' => ['ana.ejemplo.com', $formato],
            'sin usuario' => ['@ejemplo.com', $formato],
            'sin dominio' => ['ana@', $formato],
            'dos arrobas' => ['ana@@ejemplo.com', $formato],
            'sin TLD' => ['ana@ejemplo', $formato],
            'TLD de una letra' => ['ana@ejemplo.c', $formato],
            'dos puntos seguidos' => ['ana@ejemplo..com', $formato],
            'con espacio interno' => ['ana @ejemplo.com', $formato],
            'con tilde' => ['josé@ejemplo.com', $formato],
        ];
    }

    #[DataProvider('emailsInvalidos')]
    public function testEmailInvalidoDaErrorEspecifico(mixed $email, string $mensaje): void
    {
        $resultado = (new ContactoValidator())->validar('Ana', $email, null);

        self::assertSame(['email' => $mensaje], $resultado['errores']);
    }

    public function testEmailDemasiadoLargoDaError(): void
    {
        $email = str_repeat('a', 245) . '@ejemplo.com';

        $resultado = (new ContactoValidator())->validar('Ana', $email, null);

        self::assertSame(['email' => 'El email no puede superar los 255 caracteres.'], $resultado['errores']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function telefonosValidos(): array
    {
        return [
            'nueve dígitos' => ['097680102'],
            'con espacios en los bordes' => [' 097680102 '],
            'empieza con 09 otro dígito' => ['099123456'],
        ];
    }

    #[DataProvider('telefonosValidos')]
    public function testTelefonoValidoSeGuarda(string $telefono): void
    {
        $resultado = (new ContactoValidator())->validar('Ana', self::EMAIL_VALIDO, $telefono);

        self::assertSame([], $resultado['errores']);
        self::assertSame(trim($telefono), $resultado['datos']['telefono']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function telefonosVacios(): array
    {
        return [
            'null' => [null],
            'vacío' => [''],
            'solo espacios' => ['   '],
        ];
    }

    #[DataProvider('telefonosVacios')]
    public function testTelefonoVacioEsValidoYSeNormalizaANull(mixed $telefono): void
    {
        $resultado = (new ContactoValidator())->validar('Ana', self::EMAIL_VALIDO, $telefono);

        self::assertSame([], $resultado['errores']);
        self::assertNull($resultado['datos']['telefono']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function telefonosInvalidos(): array
    {
        return [
            'prefijo internacional' => ['+59897680102'],
            'con espacios' => ['097 680 102'],
            'con guiones' => ['097-680-102'],
            'ocho dígitos' => ['09768010'],
            'diez dígitos' => ['0976801020'],
            'empieza con 08' => ['087680102'],
            'no empieza con 09' => ['197680102'],
            'con letras' => ['09768010a'],
            'solo números sin 09' => ['123456789'],
            'array' => [['097680102']],
            'entero' => [97680102],
        ];
    }

    #[DataProvider('telefonosInvalidos')]
    public function testTelefonoInvalidoDaError(mixed $telefono): void
    {
        $resultado = (new ContactoValidator())->validar('Ana', self::EMAIL_VALIDO, $telefono);

        self::assertSame(
            ['telefono' => 'El teléfono debe tener 9 dígitos y empezar con 09, sin espacios ni guiones.'],
            $resultado['errores'],
        );
    }

    public function testVariosCamposInvalidosDevuelvenUnErrorPorCampo(): void
    {
        $resultado = (new ContactoValidator())->validar('', 'malo', '+59897680102');

        self::assertSame(
            ['nombre', 'email', 'telefono'],
            array_keys($resultado['errores']),
        );
    }

    public function testDatosValidosSeNormalizanCompletos(): void
    {
        $resultado = (new ContactoValidator())->validar(' María José ', ' MARIA@Ejemplo.com ', ' 097680102 ');

        self::assertSame([], $resultado['errores']);
        self::assertSame(
            ['nombre' => 'María José', 'email' => 'maria@ejemplo.com', 'telefono' => '097680102'],
            $resultado['datos'],
        );
    }
}
