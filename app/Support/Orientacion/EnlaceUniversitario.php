<?php

namespace App\Support\Orientacion;

final class EnlaceUniversitario
{
    private const ACTUALIZADOS = [
        'https://emi.edu.bo/index.php/universidad/licenciatura/ingenieria-de-sistemas' => 'https://www.emi.edu.bo/universidad/licenciatura/ingenieria-de-sistemas',
        'https://emi.edu.bo/index.php/universidad/admision' => 'https://www.emi.edu.bo/universidad/admision',
    ];

    public static function vigente(mixed $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = rtrim(trim($url), '/');
        $url = self::ACTUALIZADOS[$url] ?? $url;

        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
            && parse_url($url, PHP_URL_USER) === null
            && parse_url($url, PHP_URL_PASS) === null ? $url : null;
    }
}
