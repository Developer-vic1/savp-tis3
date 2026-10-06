<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FechaPublicacionAcademica implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/\A\d{4}(?:-\d{2}(?:-\d{2})?)?\z/', $value)) {
            $fail('Use un año, año-mes o fecha completa: 2026, 2026-03 o 2026-03-15.');

            return;
        }

        $parts = array_map('intval', explode('-', $value));
        if (! checkdate($parts[1] ?? 1, $parts[2] ?? 1, $parts[0])) {
            $fail('La fecha de publicación no existe en el calendario.');
        }
    }
}
