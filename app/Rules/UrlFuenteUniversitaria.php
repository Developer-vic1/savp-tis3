<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UrlFuenteUniversitaria implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/[\x00-\x20\x7f]|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)/i', $value)
            || str_contains($value, '\\')) {
            $fail('Use una URL HTTPS sin espacios, controles ni barras invertidas.');

            return;
        }

        $parts = parse_url($value);
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass'])
            || ! in_array($parts['port'] ?? 443, [443], true)
            || filter_var($host, FILTER_VALIDATE_IP)
            || ! str_contains($host, '.')
            || ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            $fail('Use el dominio público de la universidad, con HTTPS, sin IP, credenciales ni puertos personalizados.');
        }
    }
}
