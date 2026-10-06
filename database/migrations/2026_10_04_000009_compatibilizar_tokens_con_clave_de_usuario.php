<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Sanctum conserva su tabla; la referencia polimórfica acepta la clave oficial USU. */
return new class extends Migration
{
    public function up(): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La compatibilidad de tokens oficiales requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.users IN ACCESS SHARE MODE');
            $db->statement('LOCK TABLE public.personal_access_tokens IN ACCESS EXCLUSIVE MODE');
            $tipo = $db->selectOne("SELECT format_type(atttypid,atttypmod) tipo FROM pg_attribute WHERE attrelid='public.users'::regclass AND attname='cod_usu' AND NOT attisdropped")->tipo ?? null;
            if (! $tipo || ! preg_match('/^character varying(?:\((\d+)\))?$/D', $tipo, $coincidencia)) {
                throw new RuntimeException('La clave oficial del usuario no coincide con el contrato VARCHAR esperado.');
            }
            $actual = $db->selectOne("SELECT format_type(atttypid,atttypmod) tipo FROM pg_attribute WHERE attrelid='public.personal_access_tokens'::regclass AND attname='tokenable_id' AND NOT attisdropped")->tipo ?? null;
            if ($actual !== 'bigint') {
                throw new RuntimeException('El tipo actual de tokenable_id no coincide con BIGINT auditado; no se altera el esquema.');
            }
            $huerfanos = $db->select(<<<'SQL'
SELECT t.id FROM public.personal_access_tokens t
WHERE t.tokenable_type IN ('App\Models\User','App\Models\Oficial\Sistema\User')
  AND NOT EXISTS (SELECT 1 FROM public.users u WHERE u.cod_usu=t.tokenable_id::text)
ORDER BY t.id LIMIT 20
SQL);
            if ($huerfanos !== []) {
                throw new RuntimeException('Tokens históricos sin usuario compatible; no se inventan ni renumeran identidades: '.implode(', ', array_column($huerfanos, 'id')));
            }
            if (isset($coincidencia[1]) && $db->selectOne('SELECT id FROM public.personal_access_tokens WHERE length(tokenable_id::text)>? LIMIT 1', [(int) $coincidencia[1]])) {
                throw new RuntimeException('Un identificador histórico supera la longitud del usuario; no se truncarán tokens.');
            }
            $db->statement('ALTER TABLE public.personal_access_tokens ALTER COLUMN tokenable_id TYPE '.$tipo.' USING tokenable_id::text');
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('La reversión de tokens oficiales requiere PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.personal_access_tokens IN ACCESS EXCLUSIVE MODE');
            $incompatibles = $db->select(<<<'SQL'
SELECT id FROM public.personal_access_tokens
WHERE CASE WHEN tokenable_id ~ '^-?(0|[1-9][0-9]*)$'
 THEN tokenable_id::numeric < -9223372036854775808 OR tokenable_id::numeric > 9223372036854775807
      OR tokenable_id::numeric::text <> tokenable_id
 ELSE true END
ORDER BY id LIMIT 20
SQL);
            if ($incompatibles !== []) {
                throw new RuntimeException('Rollback bloqueado: tokens con claves textuales oficiales o valores no representables en BIGINT. No se eliminarán ni renumerarán tokens: '.implode(', ', array_column($incompatibles, 'id')));
            }
            $db->statement('ALTER TABLE public.personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING tokenable_id::bigint');
        });
    }
};
