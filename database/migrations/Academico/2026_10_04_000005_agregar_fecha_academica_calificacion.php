<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La fecha académica oficial requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.calificacion IN ACCESS EXCLUSIVE MODE');
            if ($db->table('calificacion')->exists()) {
                throw new RuntimeException('Existen calificaciones sin fecha académica verificada. No se inventaron fechas ni se modificaron registros; aportar un backfill documentado antes de continuar.');
            }
            $db->statement('ALTER TABLE public.calificacion ADD COLUMN fea_cal date NOT NULL');
            $db->statement("COMMENT ON COLUMN public.calificacion.fea_cal IS 'Fecha académica efectiva de la nota consolidada; no es la fecha de inserción o edición. Sin valor predeterminado.'");
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.calificacion IN ACCESS EXCLUSIVE MODE');
            if ($db->table('calificacion')->exists()) {
                throw new RuntimeException('Rollback bloqueado: eliminar fea_cal destruiría evidencia académica histórica. No se borró ninguna fecha.');
            }
            $db->statement('ALTER TABLE public.calificacion DROP COLUMN fea_cal');
        });
    }
};
