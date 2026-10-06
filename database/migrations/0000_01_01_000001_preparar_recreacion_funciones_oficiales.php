<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Limpia únicamente rutinas residuales después de db:wipe, antes de recrearlas. */
return new class extends Migration
{
    public function up(): void
    {
        $conexion = DB::connection($this->getConnection());
        if ($conexion->getDriverName() !== 'pgsql') {
            return;
        }
        // En una BD instalada no retirar funciones ni tocar hechos existentes.
        if ($conexion->selectOne("SELECT EXISTS (SELECT 1 FROM pg_tables WHERE schemaname = 'public' AND tablename <> 'migrations') AS poblada")->poblada) {
            return;
        }
        // DROP TABLE elimina triggers y funciones con argumentos de tipo fila,
        // pero conserva funciones sin dependencia catalogada de aquellas tablas.
        // Lista cerrada de firmas propias; DROP RESTRICT conserva dependencias ajenas.
        $firmas = [
            'public.ofi_validar_trayecto()',
            'public.ofi_validar_cobertura_tecnica()',
            'public.ofi_validar_membresia_intervalo()',
            'public.ofi_validar_estado_membresia()',
            'public.ofi_comprobar_hecho_plan(character varying, character varying, character varying, character varying, date, character varying, text)',
            'public.ofi_comprobar_hecho_lms(character varying, character varying, date, text)',
            'public.ofi_validar_hecho_historico()',
            'public.ofi_revisar_historia_inscripcion(character varying)',
            'public.ofi_proteger_historia_al_cambiar_contexto()',
            'public.ofi_proteger_hechos_membresia()',
        ];
        $conexion->transaction(function () use ($conexion, $firmas): void {
            foreach ($firmas as $firma) {
                $conexion->statement('DROP FUNCTION IF EXISTS '.$firma.' RESTRICT');
            }
        });
    }

    public function down(): void
    {
        // Las migraciones posteriores recrean estas rutinas con el mismo contrato.
        // Revertir esta preparación no debe retirar funciones de una BD instalada.
    }
};
