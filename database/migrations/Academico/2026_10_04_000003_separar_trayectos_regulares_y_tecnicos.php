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
            throw new RuntimeException('Los trayectos históricos requieren PostgreSQL.');
        }
        $conexion->transaction(function () use ($conexion): void {
            $conexion->statement('LOCK TABLE public.inscripcion_vigencia IN ACCESS EXCLUSIVE MODE');
            // No reclasifica ni corrige registros. Primero verifica su semántica.
            $ambiguas = $conexion->select(<<<'SQL'
SELECT v.cod_ivg
FROM public.inscripcion_vigencia v
LEFT JOIN public.inscripcion_estudiante i USING(cod_ins)
LEFT JOIN public.grupo_academico g USING(cod_gac)
WHERE g.cod_gea IS DISTINCT FROM i.cod_gea
   OR (v.cod_esp_tec IS NULL AND NOT EXISTS (
       SELECT 1 FROM public.plan_asignatura p WHERE p.cod_gac=v.cod_gac
   ))
   OR (v.cod_esp_tec IS NOT NULL AND NOT EXISTS (
       SELECT 1 FROM public.plan_especialidad p
       WHERE p.cod_gac=v.cod_gac AND p.cod_esp=v.cod_esp_tec
   ))
ORDER BY v.cod_ivg
SQL);
            if ($ambiguas !== []) {
                throw new RuntimeException('Vigencias ambiguas; no se modificó nada: '.implode(', ', array_column($ambiguas, 'cod_ivg')));
            }

            $conexion->unprepared(<<<'SQL'
CREATE FUNCTION public.ofi_comprobar_trayecto(v public.inscripcion_vigencia) RETURNS void
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE grupo public.grupo_academico; inscr public.inscripcion_estudiante; gestion public.gestion_academica; cobertura datemultirange;
BEGIN
 SELECT * INTO inscr FROM public.inscripcion_estudiante WHERE cod_ins=v.cod_ins FOR UPDATE;
 SELECT * INTO grupo FROM public.grupo_academico WHERE cod_gac=v.cod_gac;
 IF inscr.cod_ins IS NULL OR grupo.cod_gac IS NULL THEN RAISE EXCEPTION 'Inscripción o grupo inexistente' USING ERRCODE='23503'; END IF;
 SELECT * INTO gestion FROM public.gestion_academica WHERE cod_gea=inscr.cod_gea;
 IF grupo.cod_gea IS DISTINCT FROM inscr.cod_gea OR v.fii_ivg<gestion.fii_gea OR coalesce(v.ffi_ivg,gestion.ffi_gea)>gestion.ffi_gea THEN
  RAISE EXCEPTION 'Trayecto fuera de inscripción o gestión' USING ERRCODE='23514';
 END IF;
 IF v.cod_esp_tec IS NULL THEN
  IF NOT EXISTS(SELECT 1 FROM public.plan_asignatura p WHERE p.cod_gac=v.cod_gac AND p.est_pas IN ('ACTIVO','FINALIZADO') AND p.fii_pas<=v.fii_ivg AND coalesce(p.ffi_pas,gestion.ffi_gea)>=coalesce(v.ffi_ivg,gestion.ffi_gea)) THEN
   RAISE EXCEPTION 'Vigencia regular sin asignación general válida' USING ERRCODE='23514';
  END IF;
 ELSE
  IF NOT EXISTS(SELECT 1 FROM public.especialidad_tecnica e WHERE e.cod_esp=v.cod_esp_tec) THEN
   RAISE EXCEPTION 'Especialidad inexistente' USING ERRCODE='23503';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM public.especialidad_tecnica e WHERE e.cod_esp=v.cod_esp_tec AND upper(e.est_esp)='ACTIVO') THEN
   RAISE EXCEPTION 'Especialidad sin vigencia válida' USING ERRCODE='23514';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM public.plan_especialidad p WHERE p.cod_gac=v.cod_gac AND p.cod_esp=v.cod_esp_tec AND p.est_pes IN ('ACTIVO','FINALIZADO') AND p.fii_pes<=v.fii_ivg AND coalesce(p.ffi_pes,gestion.ffi_gea)>=coalesce(v.ffi_ivg,gestion.ffi_gea)) THEN
   RAISE EXCEPTION 'Especialidad y grupo técnico incompatibles' USING ERRCODE='23514';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM public.horario h JOIN public.plantilla_horaria ph USING(cod_pho) JOIN public.horario_detalle d USING(cod_hor) JOIN public.plan_especialidad p USING(cod_pes)
   WHERE h.cod_gac=v.cod_gac AND ph.cod_tur=grupo.cod_tur AND p.cod_esp=v.cod_esp_tec AND p.cod_gac=v.cod_gac AND h.est_hor IN ('ACTIVO','FINALIZADO') AND d.est_hde='ACTIVO') THEN
   RAISE EXCEPTION 'Grupo técnico sin horario y turno oficiales coherentes' USING ERRCODE='23514';
  END IF;
  SELECT range_agg(daterange(r.fii_ivg,coalesce(r.ffi_ivg,gestion.ffi_gea),'[]')) INTO cobertura
   FROM public.inscripcion_vigencia r JOIN public.grupo_academico rg USING(cod_gac)
   WHERE r.cod_ins=v.cod_ins AND r.cod_esp_tec IS NULL AND r.est_ivg<>'ANULADO' AND rg.cod_cur=grupo.cod_cur;
  IF cobertura IS NULL OR NOT (cobertura @> daterange(v.fii_ivg,coalesce(v.ffi_ivg,gestion.ffi_gea),'[]')) THEN
   RAISE EXCEPTION 'Participación técnica interna sin trayecto regular que cubra fechas y nivel' USING ERRCODE='23514';
  END IF;
 END IF;
END $$;

CREATE FUNCTION public.ofi_validar_trayecto() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
BEGIN
 IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No borrar trayectos académicos históricos'; END IF;
 IF TG_OP='UPDATE' AND (NEW.cod_ins,NEW.cod_gac,NEW.cod_esp_tec,NEW.fii_ivg) IS DISTINCT FROM (OLD.cod_ins,OLD.cod_gac,OLD.cod_esp_tec,OLD.fii_ivg) THEN
  RAISE EXCEPTION 'Cambiar grupo o especialidad requiere cerrar la vigencia y crear otra';
 END IF;
 IF TG_OP='UPDATE' AND OLD.est_ivg='CERRADO' AND NEW.ffi_ivg IS DISTINCT FROM OLD.ffi_ivg THEN
  RAISE EXCEPTION 'No sobrescribir el intervalo histórico cerrado';
 END IF;
 IF NEW.est_ivg<>'ANULADO' THEN PERFORM public.ofi_comprobar_trayecto(NEW); END IF;
 RETURN NEW;
END $$;

CREATE FUNCTION public.ofi_validar_cobertura_tecnica() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE tecnica public.inscripcion_vigencia;
BEGIN
 FOR tecnica IN SELECT * FROM public.inscripcion_vigencia WHERE cod_ins=NEW.cod_ins AND cod_esp_tec IS NOT NULL AND est_ivg<>'ANULADO' LOOP
  PERFORM public.ofi_comprobar_trayecto(tecnica);
 END LOOP;
 RETURN NULL;
END $$;
SQL);
            // Validación completa ANTES de sustituir la exclusión actual.
            $conexion->statement("SELECT public.ofi_comprobar_trayecto(v) FROM public.inscripcion_vigencia v WHERE v.est_ivg<>'ANULADO'");
            $conexion->statement("ALTER TABLE public.inscripcion_vigencia ADD CONSTRAINT ofi_ex_ivg_regular EXCLUDE USING gist (cod_ins WITH =, daterange(fii_ivg,ffi_ivg,'[]') WITH &&) WHERE (cod_esp_tec IS NULL AND est_ivg<>'ANULADO')");
            $conexion->statement("ALTER TABLE public.inscripcion_vigencia ADD CONSTRAINT ofi_ex_ivg_tecnico EXCLUDE USING gist (cod_ins WITH =, daterange(fii_ivg,ffi_ivg,'[]') WITH &&) WHERE (cod_esp_tec IS NOT NULL AND est_ivg<>'ANULADO')");
            $conexion->statement('CREATE TRIGGER ofi_trayecto BEFORE INSERT OR UPDATE OR DELETE ON public.inscripcion_vigencia FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_trayecto()');
            $conexion->statement('CREATE CONSTRAINT TRIGGER ofi_trayecto_cobertura AFTER INSERT OR UPDATE ON public.inscripcion_vigencia DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_cobertura_tecnica()');
            $conexion->statement('ALTER TABLE public.inscripcion_vigencia DROP CONSTRAINT ofi_ex_inscripcion_vigencia');
        });
    }

    public function down(): void
    {
        $conexion = DB::connection($this->getConnection());
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Los trayectos históricos requieren PostgreSQL.');
        }
        $conexion->transaction(function () use ($conexion): void {
            $conexion->statement('LOCK TABLE public.inscripcion_vigencia IN ACCESS EXCLUSIVE MODE');
            $solapadas = $conexion->select("SELECT DISTINCT a.cod_ins FROM public.inscripcion_vigencia a JOIN public.inscripcion_vigencia b ON a.cod_ins=b.cod_ins AND a.cod_ivg<b.cod_ivg WHERE a.est_ivg<>'ANULADO' AND b.est_ivg<>'ANULADO' AND daterange(a.fii_ivg,a.ffi_ivg,'[]') && daterange(b.fii_ivg,b.ffi_ivg,'[]') ORDER BY a.cod_ins");
            if ($solapadas !== []) {
                throw new RuntimeException('Rollback incompatible con trayectos simultáneos; se conserva la historia: '.implode(', ', array_column($solapadas, 'cod_ins')));
            }
            $conexion->statement("ALTER TABLE public.inscripcion_vigencia ADD CONSTRAINT ofi_ex_inscripcion_vigencia EXCLUDE USING gist (cod_ins WITH =, daterange(fii_ivg,ffi_ivg,'[]') WITH &&) WHERE (fii_ivg IS NOT NULL AND upper(est_ivg) NOT IN ('ANULADO','ANULADA'))");
            $conexion->statement('DROP TRIGGER ofi_trayecto_cobertura ON public.inscripcion_vigencia');
            $conexion->statement('DROP TRIGGER ofi_trayecto ON public.inscripcion_vigencia');
            $conexion->statement('DROP FUNCTION public.ofi_validar_cobertura_tecnica()');
            $conexion->statement('DROP FUNCTION public.ofi_validar_trayecto()');
            $conexion->statement('DROP FUNCTION public.ofi_comprobar_trayecto(public.inscripcion_vigencia)');
            $conexion->statement('ALTER TABLE public.inscripcion_vigencia DROP CONSTRAINT ofi_ex_ivg_regular, DROP CONSTRAINT ofi_ex_ivg_tecnico');
        });
    }
};
