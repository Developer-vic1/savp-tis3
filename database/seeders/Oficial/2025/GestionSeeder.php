<?php

declare(strict_types=1);
use Database\Seeders\Oficial\Soporte\HistorialInstitucional;

// Directorio numérico: se carga explícitamente, sin namespace PSR-4 inválido.
return static function (HistorialInstitucional $historial): void {
    $historial->gestion(2025);
};
