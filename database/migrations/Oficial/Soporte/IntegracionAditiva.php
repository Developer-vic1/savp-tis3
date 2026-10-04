<?php

declare(strict_types=1);

namespace Database\Migrations\Oficial\Soporte;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * FASE 1: contrato aprobado de 104 tablas, manteniendo PK, nombres y legado.
 * Las adiciones a tablas pobladas son nullable: el backfill no pertenece al DDL.
 * No carga datos, no reemplaza tablas existentes y no modifica servicios.
 */
final class IntegracionAditiva
{
    public static function verificarMotor(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La integración oficial requiere PostgreSQL; SQLite solo es auxiliar.');
        }
    }

    public static function estructura(array $tabla): void
    {
        $nombre = $tabla['tabla'];
        foreach (require __DIR__.'/referencias.php' as $fk) {
            if ($fk['tabla'] !== $nombre || count($fk['columnas']) !== 1 || ! Schema::hasTable($fk['madre'])) {
                continue;
            }
            $columna = $fk['columnas'][0];
            if (! isset($tabla['columnas'][$columna])) {
                continue;
            }
            $tipo = self::tipo($fk['madre'], $fk['referencias'][0]);
            // Las columnas nuevas adoptan el tipo real de su madre, incluida
            // longitud (cod_ses ya es VARCHAR(30) en PostgreSQL institucional).
            $patron = '/("'.preg_quote($columna, '/').'"\s+)(?:VARCHAR\(\d+\)|BIGINT|INTEGER)/i';
            $tabla['creacion'] = preg_replace($patron, '${1}'.$tipo, $tabla['creacion']);
            if (isset($tabla['adiciones'][$columna])) {
                $tabla['adiciones'][$columna] = preg_replace($patron, '${1}'.$tipo, $tabla['adiciones'][$columna]);
            }
        }
        if (! Schema::hasTable($nombre)) {
            DB::unprepared($tabla['creacion']);

            return;
        }

        // Un CREATE conceptual jamás es autorización para modificar tipos existentes.
        foreach ($tabla['adiciones'] as $columna => $definicion) {
            if (! Schema::hasColumn($nombre, $columna)) {
                DB::statement('ALTER TABLE "'.$nombre.'" ADD COLUMN '.$definicion);
            }
        }
    }

    public static function restriccion(string $tabla, string $nombre, string $definicion): void
    {
        $existe = DB::selectOne(
            'SELECT 1 FROM pg_constraint WHERE conrelid = to_regclass(?) AND conname = ?',
            [$tabla, $nombre],
        );
        if ($existe) {
            return;
        }

        DB::statement('ALTER TABLE "'.$tabla.'" ADD CONSTRAINT "'.$nombre.'" '.$definicion);
    }

    public static function referencia(array $fk): void
    {
        $locales = $fk['columnas'];
        $remotas = $fk['referencias'];
        // Evitar duplicar una FK de iguales columnas. Cambios en CASCADE/SET NULL
        // se revisan por separado, nunca se pierde la protección actual en silencio.
        foreach (Schema::getForeignKeys($fk['tabla']) as $existente) {
            if ($existente['columns'] === $locales && $existente['foreign_table'] === $fk['madre']
                && $existente['foreign_columns'] === $remotas) {
                return;
            }
        }

        foreach ($locales as $posicion => $columna) {
            $hija = self::tipo($fk['tabla'], $columna);
            $madre = self::tipo($fk['madre'], $remotas[$posicion]);
            if ($hija !== $madre) {
                throw new RuntimeException('Tipos incompatibles en '.$fk['tabla'].'.'.$columna.' → '.$fk['madre'].'.'.$remotas[$posicion].'; se requiere conciliación, no rekey automático.');
            }
        }

        $sql = 'FOREIGN KEY ('.self::identificadores($locales).') REFERENCES "'.$fk['madre'].'" ('.self::identificadores($remotas).') ON UPDATE CASCADE ON DELETE RESTRICT';
        self::restriccion($fk['tabla'], $fk['nombre'], $sql);
    }

    public static function unico(string $tabla, array $columnas, string $nombre, ?string $predicado = null): void
    {
        foreach (Schema::getIndexes($tabla) as $indice) {
            if ($indice['name'] === $nombre) {
                return;
            }
            // Solo reutilizar unicidad global realmente equivalente; no confundir
            // la introspección de un índice parcial con una UNIQUE global.
            if ($predicado === null && $indice['unique'] && $indice['columns'] === $columnas) {
                $parcial = DB::selectOne('SELECT indpred IS NOT NULL AS parcial FROM pg_index WHERE indexrelid = to_regclass(?)', [$indice['name']]);
                if ($parcial && ! $parcial->parcial) {
                    return;
                }
            }
        }
        $cols = self::identificadores($columnas);
        $filtro = $predicado ? ' WHERE '.$predicado : '';
        $duplicados = DB::selectOne('SELECT count(*) AS total FROM (SELECT '.$cols.' FROM "'.$tabla.'"'.$filtro.' GROUP BY '.$cols.' HAVING count(*) > 1 AND '.implode(' AND ', array_map(fn ($c) => '"'.$c.'" IS NOT NULL', $columnas)).') d');
        if ((int) $duplicados->total > 0) {
            throw new RuntimeException('Duplicados bloquean '.$nombre.'; conciliar antes de migrar, sin borrar filas.');
        }
        DB::statement('CREATE UNIQUE INDEX "'.$nombre.'" ON "'.$tabla.'" ('.$cols.')'.$filtro);
    }

    public static function tipo(string $tabla, string $columna): string
    {
        $dato = DB::selectOne('SELECT format_type(a.atttypid, a.atttypmod) AS tipo FROM pg_attribute a WHERE a.attrelid = to_regclass(?) AND a.attname = ? AND NOT a.attisdropped', [$tabla, $columna]);
        if (! $dato) {
            throw new RuntimeException('Falta '.$tabla.'.'.$columna.' en la integración.');
        }

        return $dato->tipo;
    }

    public static function indice(string $tabla, array $columnas, string $nombre): void
    {
        foreach (Schema::getIndexes($tabla) as $indice) {
            if (array_slice($indice['columns'], 0, count($columnas)) === $columnas) {
                return;
            }
        }
        DB::statement('CREATE INDEX IF NOT EXISTS "'.$nombre.'" ON "'.$tabla.'" ('.self::identificadores($columnas).')');
    }

    private static function identificadores(array $columnas): string
    {
        return implode(', ', array_map(fn ($columna) => '"'.$columna.'"', $columnas));
    }
}
