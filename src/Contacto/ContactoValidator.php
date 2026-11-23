<?php

declare(strict_types=1);

namespace App\Contacto;

/**
 * Valida y normaliza los datos de un contacto (task.md §3.1).
 *
 * Es la autoridad final: el cliente valida lo mismo, pero el servidor no confía en él.
 */
final class ContactoValidator
{
    private const REGEX_NOMBRE = '/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+(?: [A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+)*$/u';
    private const REGEX_EMAIL = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/u';
    private const REGEX_TELEFONO = '/^09\d{7}$/u';

    private const MAX_NOMBRE = 100;
    private const MAX_EMAIL = 255;

    private const MSG_TELEFONO = 'El teléfono debe tener 9 dígitos y empezar con 09, sin espacios ni guiones.';

    /**
     * Devuelve los datos normalizados y los errores por campo.
     *
     * Los datos solo sirven si no hay errores. Acepta cualquier tipo de entrada
     * (por ejemplo, arrays enviados en $_POST) sin lanzar excepciones.
     *
     * @return array{datos: array{nombre: string, email: string, telefono: ?string}, errores: array<string, string>}
     */
    public function validar(mixed $nombre, mixed $email, mixed $telefono): array
    {
        $errores = [];

        $nombreLimpio = $this->recortar($nombre);
        if ($nombreLimpio === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombreLimpio) > self::MAX_NOMBRE) {
            $errores['nombre'] = 'El nombre no puede superar los 100 caracteres.';
        } elseif (preg_match(self::REGEX_NOMBRE, $nombreLimpio) !== 1) {
            $errores['nombre'] = 'El nombre solo puede contener letras y espacios.';
        }

        $emailLimpio = strtolower($this->recortar($email));
        if ($emailLimpio === '') {
            $errores['email'] = 'El email es obligatorio.';
        } elseif (strlen($emailLimpio) > self::MAX_EMAIL) {
            $errores['email'] = 'El email no puede superar los 255 caracteres.';
        } elseif (preg_match(self::REGEX_EMAIL, $emailLimpio) !== 1) {
            $errores['email'] = 'El email no tiene un formato válido.';
        }

        $telefonoLimpio = null;
        if ($telefono !== null && !is_string($telefono)) {
            $errores['telefono'] = self::MSG_TELEFONO;
        } elseif (is_string($telefono) && trim($telefono) !== '') {
            $telefonoLimpio = trim($telefono);
            if (preg_match(self::REGEX_TELEFONO, $telefonoLimpio) !== 1) {
                $errores['telefono'] = self::MSG_TELEFONO;
            }
        }

        return [
            'datos' => [
                'nombre' => $nombreLimpio,
                'email' => $emailLimpio,
                'telefono' => $telefonoLimpio,
            ],
            'errores' => $errores,
        ];
    }

    /**
     * Quita espacios de los bordes. Lo que no es texto cuenta como vacío.
     */
    private function recortar(mixed $valor): string
    {
        return is_string($valor) ? trim($valor) : '';
    }
}
