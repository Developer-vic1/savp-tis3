<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migración incremental para asegurar que la función y el disparador de solapamiento
     * de vigencias estén aplicados de manera idempotente en todas las bases existentes y nuevas.
     */
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION validar_solapamiento_ivg() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                PERFORM 1 FROM inscripcion_estudiante WHERE cod_ins = NEW.cod_ins FOR UPDATE;
                IF NEW.est_ivg <> 'ANULADA' AND EXISTS (
                    SELECT 1 FROM inscripcion_vigencia v
                    WHERE v.cod_ins = NEW.cod_ins AND v.cod_ivg <> NEW.cod_ivg AND v.est_ivg <> 'ANULADA'
                    AND daterange(v.fii_ivg, v.ffi_ivg, '[]') && daterange(NEW.fii_ivg, NEW.ffi_ivg, '[]')
                ) THEN
                    RAISE EXCEPTION 'Las vigencias de la inscripción no pueden solaparse' USING ERRCODE = '23514';
                END IF;
                RETURN NEW;
            END $$;
            DROP TRIGGER IF EXISTS comprobar_solapamiento_ivg ON inscripcion_vigencia;
            CREATE TRIGGER comprobar_solapamiento_ivg BEFORE INSERT OR UPDATE ON inscripcion_vigencia
            FOR EACH ROW EXECUTE FUNCTION validar_solapamiento_ivg();
            SQL);
    }

    public function down(): void
    {
        // No destructivo: la función protege la integridad referencial histórica.
    }
};
