<?php

namespace App\Support\Academico;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use WeakMap;

/** Metadatos del esquema, compartidos únicamente durante la petición actual. */
final class EsquemaAcademico
{
    private static ?WeakMap $peticiones = null;

    public static function hasTable(string $tabla): bool
    {
        return self::columnas($tabla) !== [];
    }

    public static function hasColumn(string $tabla, string $columna): bool
    {
        return in_array($columna, self::columnas($tabla), true);
    }

    public static function getColumnListing(string $tabla): array
    {
        return self::columnas($tabla);
    }

    private static function columnas(string $tabla): array
    {
        self::$peticiones ??= new WeakMap;
        $peticion = request();
        $datos = self::$peticiones[$peticion] ?? [];
        $clave = DB::connection()->getName().':'.DB::connection()->getDatabaseName().':'.$tabla;
        if (! array_key_exists($clave, $datos)) {
            $datos[$clave] = Schema::getColumnListing($tabla);
            self::$peticiones[$peticion] = $datos;
        }

        return $datos[$clave];
    }
}
