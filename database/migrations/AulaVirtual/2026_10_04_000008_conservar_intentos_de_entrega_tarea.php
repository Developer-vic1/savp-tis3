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
            throw new RuntimeException('La corrección de intentos requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.entrega_tarea IN SHARE ROW EXCLUSIVE MODE');
            $ambiguas = $db->select(<<<'SQL'
SELECT e.cod_ent FROM public.entrega_tarea e
WHERE e.int_ent IS NULL OR e.int_ent < 1
 OR EXISTS (SELECT 1 FROM public.entrega_tarea n
   WHERE n.cod_tar=e.cod_tar AND n.cod_est=e.cod_est AND n.int_ent=e.int_ent AND n.cod_ent<>e.cod_ent)
ORDER BY e.cod_ent LIMIT 20
SQL);
            if ($ambiguas !== []) {
                throw new RuntimeException('Intentos ausentes, inválidos o duplicados; no se modifica ningún registro: '.implode(', ', array_column($ambiguas, 'cod_ent')));
            }
            $actual = $db->selectOne("SELECT pg_get_constraintdef(oid) ddl FROM pg_constraint WHERE conrelid='public.entrega_tarea'::regclass AND conname='uq_entrega_tarea_estudiante' AND contype='u'");
            if ($actual?->ddl !== 'UNIQUE (cod_tar, cod_est)') {
                throw new RuntimeException('La unicidad actual no coincide con el contrato auditado.');
            }
            $porIntento = $db->selectOne("SELECT 1 FROM pg_constraint WHERE conrelid='public.entrega_tarea'::regclass AND contype='u' AND pg_get_constraintdef(oid)='UNIQUE (cod_tar, cod_est, int_ent)'");
            $positivo = $db->selectOne("SELECT 1 FROM pg_constraint WHERE conrelid='public.entrega_tarea'::regclass AND contype='c' AND pg_get_constraintdef(oid) IN ('CHECK ((int_ent > 0))','CHECK ((int_ent >= 1))')");
            if (! $porIntento || ! $positivo) {
                throw new RuntimeException('Faltan la unicidad por intento o su CHECK positivo canónicos. Revisar el esquema sin duplicar restricciones.');
            }
            // Reutilizar UNIQUE/CHECK canónicos; retirar únicamente la unicidad antigua incompatible.
            $db->statement('ALTER TABLE public.entrega_tarea ALTER COLUMN int_ent SET NOT NULL');
            $db->statement('ALTER TABLE public.entrega_tarea DROP CONSTRAINT uq_entrega_tarea_estudiante');
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La reversión de intentos requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.entrega_tarea IN SHARE ROW EXCLUSIVE MODE');
            if ($db->selectOne('SELECT 1 FROM public.entrega_tarea GROUP BY cod_tar,cod_est HAVING count(*) > 1 LIMIT 1')) {
                throw new RuntimeException('Rollback bloqueado: existen múltiples intentos históricos. No se eliminarán entregas ni calificaciones para recuperar la unicidad anterior.');
            }
            $db->statement('ALTER TABLE public.entrega_tarea ADD CONSTRAINT uq_entrega_tarea_estudiante UNIQUE (cod_tar, cod_est)');
            $db->statement('ALTER TABLE public.entrega_tarea ALTER COLUMN int_ent DROP NOT NULL');
        });
    }
};
