<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conexion = DB::connection($this->getConnection());
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Esta corrección requiere PostgreSQL, la base canónica.');
        }

        $conexion->transaction(function () use ($conexion): void {
            // Impide insertar o cambiar planes entre la inspección y el ALTER.
            $conexion->statement('LOCK TABLE public.clase_virtual IN ACCESS EXCLUSIVE MODE');
            $invalidas = $conexion->table('clase_virtual')
                ->whereRaw('num_nonnulls(cod_pas, cod_pes) <> 1')
                ->orderBy('cod_cla')->pluck('cod_cla');
            if ($invalidas->isNotEmpty()) {
                throw new RuntimeException('Aulas con planes incompatibles; no se modificaron datos: '.$invalidas->implode(', '));
            }

            $conexion->statement('ALTER TABLE public.clase_virtual ALTER COLUMN cod_pas DROP NOT NULL');
            // El contrato inicial ya incluye esta restricción. Se conserva sin duplicarla.
            $existe = $conexion->selectOne("SELECT count(*) AS cantidad FROM pg_constraint WHERE conrelid = 'public.clase_virtual'::regclass AND contype = 'c' AND conname IN ('can_ofi_ck_354f090848992c5d', 'ofi_clase_virtual_un_solo_plan_check')");
            if ((int) $existe->cantidad === 0) {
                $conexion->statement('ALTER TABLE public.clase_virtual ADD CONSTRAINT ofi_clase_virtual_un_solo_plan_check CHECK (num_nonnulls(cod_pas, cod_pes) = 1)');
            }
            foreach (['cod_pas', 'cod_pes'] as $columna) {
                $indice = $conexion->selectOne("SELECT count(*) AS cantidad FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = i.indkey[0] WHERE i.indrelid = 'public.clase_virtual'::regclass AND i.indisvalid AND a.attname = ?", [$columna]);
                if ((int) $indice->cantidad === 0) {
                    $conexion->statement("CREATE INDEX ofi_clase_virtual_{$columna}_index ON public.clase_virtual ({$columna})");
                }
            }
            // Las dos FK existentes se conservan, incluidos sus DELETE RESTRICT.
        });
    }

    public function down(): void
    {
        $conexion = DB::connection($this->getConnection());
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Esta corrección requiere PostgreSQL, la base canónica.');
        }
        $conexion->transaction(function () use ($conexion): void {
            $conexion->statement('LOCK TABLE public.clase_virtual IN ACCESS EXCLUSIVE MODE');
            $tecnicas = $conexion->table('clase_virtual')->whereNull('cod_pas')->orderBy('cod_cla')->pluck('cod_cla');
            if ($tecnicas->isNotEmpty()) {
                throw new RuntimeException('No se puede restaurar NOT NULL mientras existan aulas técnicas: '.$tecnicas->implode(', '));
            }
            $conexion->statement('ALTER TABLE public.clase_virtual ALTER COLUMN cod_pas SET NOT NULL');
            $conexion->statement('ALTER TABLE public.clase_virtual DROP CONSTRAINT IF EXISTS ofi_clase_virtual_un_solo_plan_check');
            $conexion->statement('DROP INDEX IF EXISTS public.ofi_clase_virtual_cod_pas_index');
            $conexion->statement('DROP INDEX IF EXISTS public.ofi_clase_virtual_cod_pes_index');
        });
    }
};
