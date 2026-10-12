<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UrlFuenteUniversitaria implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mensaje = app(\App\Support\Conocimiento\FuenteUniversitariaInteligente::class)->errorUrl($value, validarDominio: false);
        if ($mensaje !== null) $fail($mensaje);
    }
}
