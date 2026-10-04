<?php

declare(strict_types=1);

use Database\Migrations\Oficial\Soporte\IntegracionAditiva;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__).'/Soporte/IntegracionAditiva.php';

/**
 * Entrada descubierta por Laravel; los contratos se organizan por dominio.
 * FASE 1 únicamente. NO ejecutar sobre la BD institucional antes de revisión.
 * 104 tablas objetivo + legado preservado; PK VARCHAR(20), sin rekey/backfill.
 * Las migraciones pendientes anteriores no se ejecutan implícitamente aquí.
 * Revisar este archivo con --path antes de una aplicación aditiva autorizada.
 */
return new class extends Migration
{
    private const DOMINIOS = ['Personal', 'Institucional', 'Traslados', 'Academico', 'AulaVirtual', 'Orientacion', 'Universidades', 'Sistema'];

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Auxiliar: permite conservar las pruebas del esquema legado. No
            // simula EXCLUDE/triggers ni constituye validación oficial del DDL.
            return;
        }
        IntegracionAditiva::verificarMotor();
        DB::unprepared(<<<'SQL'
DO $$ BEGIN
 IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname='telefono_e164' AND typnamespace='public'::regnamespace) THEN
  CREATE DOMAIN public.telefono_e164 AS varchar(16) CHECK (VALUE IS NULL OR VALUE ~ '^\+[1-9][0-9]{1,14}$');
 END IF;
 IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname='hash_sha256' AND typnamespace='public'::regnamespace) THEN
  CREATE DOMAIN public.hash_sha256 AS varchar(64) CHECK (VALUE IS NULL OR VALUE ~ '^[0-9A-Fa-f]{64}$');
 END IF;
 IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname='codigo_pais_iso2' AND typnamespace='public'::regnamespace) THEN
  CREATE DOMAIN public.codigo_pais_iso2 AS char(2) CHECK (VALUE IS NULL OR VALUE ~ '^[A-Z]{2}$');
 END IF;
 IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname='url_http' AND typnamespace='public'::regnamespace) THEN
  CREATE DOMAIN public.url_http AS text CHECK (VALUE IS NULL OR VALUE ~* '^https?://');
 END IF;
 IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname='porcentaje_0_100' AND typnamespace='public'::regnamespace) THEN
  CREATE DOMAIN public.porcentaje_0_100 AS numeric(5,2) CHECK (VALUE IS NULL OR VALUE BETWEEN 0 AND 100);
 END IF;
END $$;
SQL);

        $contrato = [];
        $previas = [];
        foreach (self::DOMINIOS as $dominio) {
            foreach (require dirname(__DIR__).'/'.$dominio.'/estructura.php' as $tabla) {
                $nombre = $tabla['tabla'];
                $previas[$nombre] = Schema::hasTable($nombre) ? Schema::getColumnListing($nombre) : [];
                IntegracionAditiva::estructura($tabla);
                $contrato[$nombre] = $tabla;
            }
        }

        // Compatibilidad con contratos de modelos ya publicados, sin cargar ni
        // inferir respuestas/snapshots. No se ejecuta otra migración pendiente.
        foreach (['riasec_public' => 'json', 'riasec_score' => 'json', 'analysis_snapshot' => 'json',
            'analysis_completed_at' => 'timestamp', 'riasec_input_hash' => 'char(64)'] as $columna => $tipo) {
            if (! Schema::hasColumn('orientacion_actividades', $columna)) {
                DB::statement('ALTER TABLE orientacion_actividades ADD COLUMN "'.$columna.'" '.$tipo.' NULL');
            }
        }
        if (! Schema::hasColumn('regente_asignaciones', 'cod_reg')) {
            DB::statement('ALTER TABLE regente_asignaciones ADD COLUMN cod_reg varchar(20) NULL');
        }
        if (! Schema::hasColumn('regente_asignaciones', 'activa')) {
            DB::statement('ALTER TABLE regente_asignaciones ADD COLUMN activa boolean NULL');
        }
        if (! Schema::hasColumn('regente_asignaciones', 'cod_ras')) {
            // La cadena histórica crea id como PK; la BD desplegada ya usa
            // cod_ras. Conservar ambas claves candidatas, sin recodificar filas.
            DB::statement('ALTER TABLE regente_asignaciones ADD COLUMN cod_ras varchar(20) NULL UNIQUE');
        }
        if (! Schema::hasColumn('regente_asignaciones', 'id')) {
            // Identidad técnica compatible con los consumidores ya publicados;
            // cod_ras continúa siendo la clave del modelo organizado objetivo.
            DB::statement('ALTER TABLE regente_asignaciones ADD COLUMN id bigserial UNIQUE');
        }
        IntegracionAditiva::referencia(['tabla' => 'regente_asignaciones', 'columnas' => ['cod_reg'],
            'madre' => 'regente', 'referencias' => ['cod_reg'], 'nombre' => 'ofi_fk_ras_reg_compatibilidad']);
        DB::statement('ALTER TABLE regente_asignaciones ALTER COLUMN cod_reg DROP NOT NULL');
        IntegracionAditiva::restriccion('regente_asignaciones', 'ofi_ck_ras_vinculo', 'CHECK (cod_reg IS NOT NULL OR cod_vpe IS NOT NULL) NOT VALID');

        // La alternativa BTH no debe exigir una asignatura ficticia. Las notas
        // tradicionales conservan su validación por la alternativa de plan.
        if (Schema::hasColumn('calificacion', 'cod_asi')) {
            DB::statement('ALTER TABLE calificacion ALTER COLUMN cod_asi DROP NOT NULL');
        }

        // Primero existen todas las madres y sus claves candidatas; luego FKs.
        foreach ($contrato as $nombre => $tabla) {
            foreach ($tabla['unicos'] as $posicion => $columnas) {
                IntegracionAditiva::unico($nombre, $columnas, 'ofi_uq_'.substr(sha1($nombre.implode(',', $columnas)), 0, 16));
            }
            foreach ($tabla['checks'] as $posicion => $expresion) {
                // Estados/nombres/tipos ya desplegados permanecen compatibles.
                // No imponer los enums conceptuales sobre ACTIVO/ACTIVA, bool,
                // etc. ni transformar las filas antiguas para pasar un CHECK.
                $columnasPrevias = $previas[$nombre];
                $dominioLegado = preg_match('/\b(est_\w+|estado|tipo|tip_\w+)\s*(?:IN\b|=)/i', $expresion, $m)
                    && in_array($m[1], $columnasPrevias, true);
                if ($dominioLegado) {
                    continue;
                }
                if ($nombre === 'persona' && in_array('est_per', $columnasPrevias, true)) {
                    continue;
                }
                $incompatible = false;
                foreach ($columnasPrevias as $columna) {
                    if (! isset($tabla['columnas'][$columna]) || ! preg_match('/\b'.preg_quote($columna, '/').'\b/', $expresion)) {
                        continue;
                    }
                    $tipo = IntegracionAditiva::tipo($nombre, $columna);
                    $objetivo = $tabla['columnas'][$columna];
                    if (preg_match('/^(SMALLINT|INTEGER|BIGINT|NUMERIC|porcentaje_0_100)/', $objetivo)
                        && ! preg_match('/^(smallint|integer|bigint|numeric)/', $tipo)) {
                        $incompatible = true;
                    }
                    if ($tipo === 'json') {
                        $expresion = str_replace('jsonb_typeof('.$columna.')', 'jsonb_typeof('.$columna.'::jsonb)', $expresion);
                    }
                }
                if ($incompatible) {
                    // Ejemplo: día de semana textual desplegado frente al SMALLINT
                    // conceptual. No cambiar tipo ni datos sin backfill revisado.
                    continue;
                }
                if ($nombre === 'calificacion' && $columnasPrevias !== [] && str_contains($expresion, 'num_nonnulls')) {
                    $expresion = 'cod_ins IS NULL OR ('.$expresion.')';
                }
                // NOT VALID no reescribe legado y sí comprueba escrituras nuevas.
                IntegracionAditiva::restriccion($nombre, 'ofi_ck_'.substr(sha1($nombre.$expresion), 0, 16), 'CHECK ('.$expresion.') NOT VALID');
            }
        }
        foreach (require dirname(__DIR__).'/Soporte/referencias.php' as $referencia) {
            IntegracionAditiva::referencia($referencia);
        }

        require dirname(__DIR__).'/Soporte/integridad.php';
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        throw new RuntimeException('Rollback protegido: esta integración aditiva conserva datos y legado. Preparar una reversión revisada, con respaldo y conciliación, antes de retirar estructuras oficiales.');
    }
};
