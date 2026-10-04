<?php

declare(strict_types=1);

namespace Database\Migrations\Oficial\Soporte;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Instalación inicial, separada de la transformación de una base poblada. */
final class InstalacionCanonica
{
    public const DOMINIOS = ['Academico', 'AulaVirtual', 'AporteAcademicoVocacional'];

    // Tablas técnicas creadas únicamente por las migraciones originales.
    public const TABLAS_FRAMEWORK = [
        'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'personal_access_tokens',
        'roles', 'permissions', 'model_has_roles', 'model_has_permissions',
        'role_has_permissions', 'migrations',
    ];

    public static function preparar(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            if (! app()->environment('testing')) {
                throw new RuntimeException('SQLite se admite solo como auxiliar de pruebas; la instalación oficial requiere PostgreSQL.');
            }
            if (! Schema::hasTable('persona')) {
                (require dirname(__DIR__, 2).'/Legado/0001_01_01_000000_create_persona_table.php')->up();
            }

            return;
        }
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La base canónica requiere PostgreSQL.');
        }
        $existentes = array_filter(Schema::getTables(), fn ($tabla) => $tabla['name'] !== 'migrations');
        if ($existentes !== []) {
            throw new RuntimeException('Instalación oficial bloqueada: la base ya contiene tablas. No se elimina ni migra el legado automáticamente; requiere transición y aprobación específica.');
        }
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext');
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::unprepared(<<<'SQL'
DO $$ BEGIN
 IF to_regtype('public.telefono_e164') IS NULL THEN
  CREATE DOMAIN public.telefono_e164 AS varchar(16) CHECK (VALUE IS NULL OR VALUE ~ '^\+[1-9][0-9]{1,14}$');
 END IF;
 IF to_regtype('public.hash_sha256') IS NULL THEN
  CREATE DOMAIN public.hash_sha256 AS varchar(64) CHECK (VALUE IS NULL OR VALUE ~ '^[0-9A-Fa-f]{64}$');
 END IF;
 IF to_regtype('public.codigo_pais_iso2') IS NULL THEN
  CREATE DOMAIN public.codigo_pais_iso2 AS char(2) CHECK (VALUE IS NULL OR VALUE ~ '^[A-Z]{2}$');
 END IF;
 IF to_regtype('public.url_http') IS NULL THEN
  CREATE DOMAIN public.url_http AS text CHECK (VALUE IS NULL OR VALUE ~* '^https?://');
 END IF;
 IF to_regtype('public.porcentaje_0_100') IS NULL THEN
  CREATE DOMAIN public.porcentaje_0_100 AS numeric(5,2) CHECK (VALUE IS NULL OR VALUE BETWEEN 0 AND 100);
 END IF;
END $$;
SQL);
    }

    /** Fixture SQLite del código anterior: auxiliar, fuera del esquema canónico. */
    public static function prepararAuxiliar(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || ! app()->environment('testing')) {
            return;
        }
        foreach (glob(dirname(__DIR__, 2).'/Legado/*.php') as $archivo) {
            if (str_contains(basename($archivo), 'create_persona_table')) {
                continue;
            }
            (require $archivo)->up();
        }
    }

    public static function crear(string $tabla, string $dominio): void
    {
        if (DB::connection()->getDriverName() === 'sqlite' && app()->environment('testing')) {
            return;
        }
        if (DB::connection()->getDriverName() !== 'pgsql' || ! in_array($dominio, self::DOMINIOS, true)) {
            throw new RuntimeException('Contexto de instalación oficial inválido.');
        }
        foreach (require dirname(__DIR__).'/Canonico/'.$dominio.'/estructura.php' as $definicion) {
            if ($definicion['tabla'] === $tabla) {
                if ($tabla === 'user_status_logs' && Schema::hasTable($tabla)) {
                    // El archivo original de users también crea este historial.
                    // Solo se normaliza su estructura en una instalación vacía.
                    if (DB::table($tabla)->exists()) {
                        throw new RuntimeException('No transformar el historial de usuarios con datos sin transición aprobada.');
                    }
                    DB::unprepared(<<<'SQL'
ALTER TABLE public.user_status_logs RENAME COLUMN est_usu TO nue_usl;
ALTER TABLE public.user_status_logs RENAME COLUMN motivo TO mot_usl;
ALTER TABLE public.user_status_logs ALTER COLUMN nue_usl TYPE varchar(20);
ALTER TABLE public.user_status_logs ALTER COLUMN mot_usl TYPE text;
ALTER TABLE public.user_status_logs ALTER COLUMN created_at TYPE timestamptz USING created_at AT TIME ZONE 'America/La_Paz';
ALTER TABLE public.user_status_logs ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE public.user_status_logs DROP COLUMN updated_at;
ALTER TABLE public.user_status_logs ADD COLUMN ant_usl varchar(20);
ALTER TABLE public.user_status_logs ADD COLUMN fec_usl timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP;
SQL);
                } else {
                    DB::unprepared($definicion['sql']);
                }
                foreach ($definicion['indices'] as $indice) {
                    // Las claves candidatas declaradas dentro del CREATE ya
                    // tienen índice; no crear un duplicado por comodidad.
                    if (preg_match('/^CREATE UNIQUE INDEX (\w+) ON "\w+" \(([^)]+)\)$/', $indice, $partes)) {
                        require_once __DIR__.'/IntegracionAditiva.php';
                        $columnas = array_map(fn ($campo) => trim($campo, ' "'), explode(',', $partes[2]));
                        IntegracionAditiva::unico($tabla, $columnas, $partes[1]);

                        continue;
                    }
                    DB::statement($indice);
                }
                foreach ($definicion['checks'] as [$nombre, $expresion]) {
                    DB::statement('ALTER TABLE "'.$tabla.'" ADD CONSTRAINT "'.$nombre.'" '.$expresion);
                }

                return;
            }
        }
        throw new RuntimeException('Tabla ausente del contrato oficial: '.$tabla);
    }

    public static function relaciones(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite' && app()->environment('testing')) {
            self::prepararAuxiliar();

            return;
        }
        require_once __DIR__.'/IntegracionAditiva.php';
        $contrato = [];
        foreach (self::DOMINIOS as $dominio) {
            foreach (require dirname(__DIR__).'/Canonico/'.$dominio.'/estructura.php' as $tabla) {
                $contrato[$tabla['tabla']] = $tabla;
            }
        }
        foreach (require __DIR__.'/referencias.php' as $referencia) {
            IntegracionAditiva::referencia($referencia);
        }
        require __DIR__.'/integridad.php';
        $esperadas = array_merge(array_keys($contrato), self::TABLAS_FRAMEWORK);
        $reales = array_column(Schema::getTables(), 'name');
        if (count($reales) !== 104 || array_diff($esperadas, $reales) || array_diff($reales, $esperadas)) {
            throw new RuntimeException('El esquema creado no coincide exactamente con las 104 tablas aprobadas.');
        }
        // En instalación vacía todas las restricciones quedan validadas;
        // NOT VALID solo es una estrategia para la transición con datos previos.
        foreach (DB::select("SELECT conrelid::regclass::text AS tabla,conname FROM pg_constraint WHERE NOT convalidated AND connamespace='public'::regnamespace") as $restriccion) {
            DB::statement('ALTER TABLE "'.$restriccion->tabla.'" VALIDATE CONSTRAINT "'.$restriccion->conname.'"');
        }
    }

    public static function bloquearReversion(): never
    {
        throw new RuntimeException('Reversión protegida: no borrar historia institucional mediante rollback automático.');
    }
}
