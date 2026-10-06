<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function funcionAnterior(): string
    {
        $fuente = str_replace("\r\n", "\n", file_get_contents(__DIR__.'/2026_10_04_000003_separar_trayectos_regulares_y_tecnicos.php'));
        if (! preg_match('/CREATE FUNCTION public\.ofi_comprobar_trayecto\(v public\.inscripcion_vigencia\).*?END \$\$;/s', $fuente, $coincidencia)) {
            throw new RuntimeException('No se encontró la función original para una reversión verificable.');
        }

        return str_replace('CREATE FUNCTION ', 'CREATE OR REPLACE FUNCTION ', $coincidencia[0]);
    }

    private function funcionNueva(): string
    {
        $anterior = <<<'SQL'
SELECT 1 FROM public.horario h JOIN public.plantilla_horaria ph USING(cod_pho) JOIN public.horario_detalle d USING(cod_hor) JOIN public.plan_especialidad p USING(cod_pes)
   WHERE h.cod_gac=v.cod_gac AND ph.cod_tur=grupo.cod_tur AND p.cod_esp=v.cod_esp_tec AND p.cod_gac=v.cod_gac AND h.est_hor IN ('ACTIVO','FINALIZADO') AND d.est_hde='ACTIVO'
SQL;
        $nueva = <<<'SQL'
SELECT 1 FROM public.horario h JOIN public.plantilla_horaria ph USING(cod_pho)
   WHERE h.cod_gac=v.cod_gac AND ph.cod_tur=grupo.cod_tur AND h.est_hor IN ('ACTIVO','FINALIZADO')
   AND h.fii_hor<=v.fii_ivg AND coalesce(h.ffi_hor,gestion.ffi_gea)>=coalesce(v.ffi_ivg,gestion.ffi_gea)
SQL;
        $funcion = str_replace($anterior, $nueva, $this->funcionAnterior(), $reemplazos);
        if ($reemplazos !== 1) {
            throw new RuntimeException('La condición original difiere de la corrección aprobada; no se alteró el validador.');
        }

        return $funcion;
    }

    private function aplicar(string $funcion): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La inscripción técnica requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db, $funcion): void {
            $db->statement('LOCK TABLE public.inscripcion_vigencia IN ACCESS EXCLUSIVE MODE');
            $db->unprepared($funcion);
            // Ante datos incompatibles, PostgreSQL revierte también el cambio de función.
            $db->statement("SELECT public.ofi_comprobar_trayecto(v) FROM public.inscripcion_vigencia v WHERE v.est_ivg<>'ANULADO'");
        });
    }

    public function up(): void
    {
        $this->aplicar($this->funcionNueva());
    }

    public function down(): void
    {
        $this->aplicar($this->funcionAnterior());
    }
};
