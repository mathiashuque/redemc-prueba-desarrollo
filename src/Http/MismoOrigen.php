<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Protección CSRF para los endpoints que escriben: solo acepta requests del mismo sitio.
 *
 * Los navegadores actuales mandan `Sec-Fetch-Site`, que una página no puede falsificar.
 * Si falta (navegadores viejos), se compara el host de `Origin` con `Host`. Sin ninguno
 * de los dos headers el request no viene de un formulario de otro sitio en un navegador
 * (por ejemplo, curl o los tests), así que se acepta: la validación sigue aplicando.
 */
final class MismoOrigen
{
    private const SITIOS_PERMITIDOS = ['same-origin', 'none'];

    /**
     * @param array<string, mixed> $server Normalmente `$_SERVER`.
     */
    public static function permitido(array $server): bool
    {
        $sitio = $server['HTTP_SEC_FETCH_SITE'] ?? null;
        if (is_string($sitio) && $sitio !== '') {
            return in_array(strtolower($sitio), self::SITIOS_PERMITIDOS, true);
        }

        $origen = $server['HTTP_ORIGIN'] ?? null;
        if (!is_string($origen) || $origen === '') {
            return true;
        }

        $host = $server['HTTP_HOST'] ?? null;
        if (!is_string($host) || $host === '') {
            return false;
        }

        return strtolower(self::hostConPuerto($origen) ?? '') === strtolower($host);
    }

    /**
     * Corta con 403 si el request viene de otro sitio.
     */
    public static function exigir(): void
    {
        if (!self::permitido($_SERVER)) {
            JsonResponse::send(['error' => 'Origen no permitido.'], 403);
        }
    }

    /**
     * "https://ejemplo.com:8443" -> "ejemplo.com:8443"; null si no es una URL con host.
     */
    private static function hostConPuerto(string $origen): ?string
    {
        $partes = parse_url($origen);
        if (!is_array($partes) || !isset($partes['host'])) {
            return null;
        }

        return isset($partes['port']) ? $partes['host'] . ':' . $partes['port'] : $partes['host'];
    }
}
