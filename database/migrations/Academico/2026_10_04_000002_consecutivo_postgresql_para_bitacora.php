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
            throw new RuntimeException('El consecutivo de auditoría requiere PostgreSQL.');
        }
        $conexion->transaction(function () use ($conexion): void {
            $conexion->statement('LOCK TABLE public.bitacora IN ACCESS EXCLUSIVE MODE');
            if ($conexion->selectOne("SELECT to_regclass('public.ofi_bitacora_codigo_seq') AS secuencia")->secuencia !== null) {
                throw new RuntimeException('Ya existe ofi_bitacora_codigo_seq; inspeccionarla antes de aplicar esta migración.');
            }
            $mayor = 0;
            // Inspección inicial solamente: la función nunca calcula MAX ni cuenta filas.
            foreach ($conexion->table('bitacora')->select('cod_bit')->cursor() as $fila) {
                if (preg_match('/^BIT_([0-9]{6,})$/D', $fila->cod_bit, $coincidencia)) {
                    $numero = ltrim($coincidencia[1], '0');
                    if (strlen($numero) > 16) {
                        throw new RuntimeException('Consecutivo histórico incompatible con VARCHAR(20): '.$fila->cod_bit);
                    }
                    $mayor = max($mayor, (int) $numero);
                }
            }
            if ($mayor >= 9999999999999999) {
                throw new RuntimeException('El código de bitácora agotó la capacidad de VARCHAR(20).');
            }
            $inicio = $mayor + 1;
            $conexion->statement("CREATE SEQUENCE public.ofi_bitacora_codigo_seq AS bigint MINVALUE 1 MAXVALUE 9999999999999999 START WITH {$inicio} INCREMENT BY 1 NO CYCLE CACHE 1");
            $conexion->statement('ALTER SEQUENCE public.ofi_bitacora_codigo_seq OWNED BY public.bitacora.cod_bit');
            $conexion->unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.ofi_auditar_fila() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE antes jsonb; despues jsonb; actor varchar(20); clave text; numero bigint;
BEGIN
 actor=nullif(current_setting('app.cod_usu',true),'');
 IF actor IS NOT NULL AND NOT EXISTS(SELECT 1 FROM public.users WHERE cod_usu=actor) THEN RAISE EXCEPTION 'Actor de auditoría inválido'; END IF;
 IF TG_OP<>'INSERT' THEN antes=to_jsonb(OLD)-ARRAY['password','remember_token','two_factor_secret','two_factor_recovery_codes','token']; END IF;
 IF TG_OP<>'DELETE' THEN despues=to_jsonb(NEW)-ARRAY['password','remember_token','two_factor_secret','two_factor_recovery_codes','token']; END IF;
 clave=coalesce(despues,antes)->>TG_ARGV[0];
 numero=nextval('public.ofi_bitacora_codigo_seq'::regclass);
 INSERT INTO public.bitacora(cod_bit,cod_usu,acc_bit,tab_bit,reg_bit,val_ant_bit,val_nue_bit,fec_bit,niv_bit,res_bit,usr_bit,tra_bit)
 VALUES('BIT_'||lpad(numero::text,greatest(6,length(numero::text)),'0'),actor,TG_OP,TG_TABLE_NAME,clave,antes,despues,CURRENT_TIMESTAMP,'INFO','EXITOSO',CURRENT_USER,txid_current());
 IF TG_OP='DELETE' THEN RETURN OLD; END IF;
 RETURN NEW;
END $$;
SQL);
        });
    }

    public function down(): void
    {
        $conexion = DB::connection($this->getConnection());
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new RuntimeException('El consecutivo de auditoría requiere PostgreSQL.');
        }
        $conexion->transaction(function () use ($conexion): void {
            $conexion->statement('LOCK TABLE public.bitacora IN ACCESS EXCLUSIVE MODE');
            // Revierte el generador, nunca las filas históricas ni sus códigos.
            $conexion->unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.ofi_auditar_fila() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE antes jsonb; despues jsonb; actor varchar(20); clave text;
BEGIN
 actor=nullif(current_setting('app.cod_usu',true),'');
 IF actor IS NOT NULL AND NOT EXISTS(SELECT 1 FROM public.users WHERE cod_usu=actor) THEN RAISE EXCEPTION 'Actor de auditoría inválido'; END IF;
 IF TG_OP<>'INSERT' THEN antes=to_jsonb(OLD)-ARRAY['password','remember_token','two_factor_secret','two_factor_recovery_codes','token']; END IF;
 IF TG_OP<>'DELETE' THEN despues=to_jsonb(NEW)-ARRAY['password','remember_token','two_factor_secret','two_factor_recovery_codes','token']; END IF;
 clave=coalesce(despues,antes)->>TG_ARGV[0];
 INSERT INTO public.bitacora(cod_bit,cod_usu,acc_bit,tab_bit,reg_bit,val_ant_bit,val_nue_bit,fec_bit,niv_bit,res_bit,usr_bit,tra_bit)
 VALUES('BIT_'||upper(substr(md5(random()::text||clock_timestamp()::text),1,16)),actor,TG_OP,TG_TABLE_NAME,clave,antes,despues,CURRENT_TIMESTAMP,'INFO','EXITOSO',CURRENT_USER,txid_current());
 IF TG_OP='DELETE' THEN RETURN OLD; END IF;
 RETURN NEW;
END $$;
SQL);
            $conexion->statement('DROP SEQUENCE public.ofi_bitacora_codigo_seq');
        });
    }
};
