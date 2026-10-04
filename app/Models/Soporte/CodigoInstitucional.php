<?php

declare(strict_types=1);

namespace App\Models\Soporte;

/** Códigos alfanuméricos nuevos de 20 caracteres; no modifica códigos históricos. */
trait CodigoInstitucional
{
    protected static function bootCodigoInstitucional(): void
    {
        static::creating(function ($modelo): void {
            $clave = $modelo->claveInstitucional ?? $modelo->getKeyName();
            if (! $modelo->getAttribute($clave)) {
                $modelo->setAttribute($clave, strtoupper(substr($clave, -3)).'_'.strtoupper(bin2hex(random_bytes(8))));
            }
        });
    }
}
