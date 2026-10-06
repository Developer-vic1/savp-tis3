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
            throw new RuntimeException('Los contextos históricos requieren PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.inscripcion_vigencia, public.calificacion, public.asistencia_estudiante, public.clase_estudiante, public.entrega_tarea, public.intento_cuestionario, public.foro_mensaje, public.registro_actividad_clase, public.plan_asignatura, public.plan_especialidad, public.configuracion_calendario_gestion IN SHARE ROW EXCLUSIVE MODE');
            $db->unprepared(<<<'SQL'
CREATE FUNCTION public.ofi_comprobar_hecho_plan(ins varchar, estudiante varchar, pas varchar, pes varchar, fecha date, periodo varchar, registro text) RETURNS void
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE grupo varchar; especialidad varchar; gea varchar; inicio date; fin date; ano public.gestion_academica; matricula public.inscripcion_estudiante;
BEGIN
 IF fecha IS NULL OR num_nonnulls(pas,pes)<>1 THEN RAISE EXCEPTION 'Hecho % sin fecha/plan inequívocos',registro USING ERRCODE='23514'; END IF;
 IF pas IS NOT NULL THEN
  SELECT p.cod_gac,NULL::varchar,p.fii_pas,p.ffi_pas INTO grupo,especialidad,inicio,fin FROM public.plan_asignatura p WHERE p.cod_pas=pas AND p.est_pas IN ('ACTIVO','FINALIZADO') FOR SHARE;
 ELSE
  SELECT p.cod_gac,p.cod_esp,p.fii_pes,p.ffi_pes INTO grupo,especialidad,inicio,fin FROM public.plan_especialidad p WHERE p.cod_pes=pes AND p.est_pes IN ('ACTIVO','FINALIZADO') FOR SHARE;
 END IF;
 SELECT g.cod_gea INTO gea FROM public.grupo_academico g WHERE g.cod_gac=grupo;
 SELECT * INTO ano FROM public.gestion_academica WHERE cod_gea=gea;
 IF grupo IS NULL OR inicio IS NULL OR fecha<inicio OR fecha>coalesce(fin,ano.ffi_gea) OR fecha<ano.fii_gea OR fecha>ano.ffi_gea THEN
  RAISE EXCEPTION 'Hecho % fuera de plan/gestión',registro USING ERRCODE='23514';
 END IF;
 IF ins IS NOT NULL THEN SELECT * INTO matricula FROM public.inscripcion_estudiante WHERE cod_ins=ins FOR UPDATE;
 ELSE SELECT * INTO matricula FROM public.inscripcion_estudiante WHERE cod_est=estudiante AND cod_gea=gea FOR UPDATE; END IF;
 IF matricula.cod_ins IS NULL OR matricula.cod_gea IS DISTINCT FROM gea OR matricula.est_ins='ANULADA' OR (estudiante IS NOT NULL AND matricula.cod_est IS DISTINCT FROM estudiante) THEN
  RAISE EXCEPTION 'Hecho % de otra inscripción/gestión',registro USING ERRCODE='23514';
 END IF;
 IF NOT EXISTS(SELECT 1 FROM public.inscripcion_vigencia v WHERE v.cod_ins=matricula.cod_ins AND v.cod_gac=grupo
   AND v.cod_esp_tec IS NOT DISTINCT FROM especialidad AND v.est_ivg<>'ANULADO'
   AND fecha BETWEEN v.fii_ivg AND coalesce(v.ffi_ivg,ano.ffi_gea)) THEN
  RAISE EXCEPTION 'Hecho % sin trayecto/especialidad exactos en fecha %',registro,fecha USING ERRCODE='23514';
 END IF;
 IF periodo IS NOT NULL AND NOT EXISTS(SELECT 1 FROM public.configuracion_calendario_gestion c
   WHERE c.cod_gea=gea AND c.cod_pev=periodo AND fecha BETWEEN c.fii_tri_ccg AND c.ffi_tri_ccg) THEN
  RAISE EXCEPTION 'Hecho % fuera de periodo académico',registro USING ERRCODE='23514';
 END IF;
END $$;

CREATE FUNCTION public.ofi_comprobar_hecho_lms(aula varchar, estudiante varchar, fecha date, registro text) RETURNS void
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE clase public.clase_virtual;
BEGIN
 SELECT * INTO clase FROM public.clase_virtual WHERE cod_cla=aula;
 PERFORM public.ofi_comprobar_hecho_plan(NULL,estudiante,clase.cod_pas,clase.cod_pes,fecha,NULL,registro);
 IF NOT EXISTS(SELECT 1 FROM public.clase_estudiante m WHERE m.cod_cla=aula AND m.cod_est=estudiante AND m.est_cla_est<>'ANULADO'
   AND fecha>=m.fec_inc_cla_est AND (m.fec_ret_cla_est IS NULL OR fecha<m.fec_ret_cla_est)) THEN
  RAISE EXCEPTION 'Hecho LMS % fuera de membresía en fecha %',registro,fecha USING ERRCODE='23514';
 END IF;
END $$;

CREATE FUNCTION public.ofi_comprobar_calificacion(q public.calificacion) RETURNS void
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
BEGIN
 IF q.est_cal NOT IN ('ANULADO','ANULADA') THEN
  PERFORM public.ofi_comprobar_hecho_plan(q.cod_ins,NULL,q.cod_pas,q.cod_pes,q.fea_cal,q.cod_pev,q.cod_cal);
 END IF;
END $$;

CREATE FUNCTION public.ofi_comprobar_asistencia(q public.asistencia_estudiante) RETURNS void
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE clase public.clase_virtual; fecha date; ses varchar;
BEGIN
 SELECT c.* INTO clase FROM public.clase_virtual c JOIN public.asistencia_clase x ON x.cod_cla=c.cod_cla WHERE x.cod_asi_cla=q.cod_asi_cla;
 SELECT s.fec_ses,x.cod_ses INTO fecha,ses FROM public.asistencia_clase x JOIN public.sesion_academica s USING(cod_ses) WHERE x.cod_asi_cla=q.cod_asi_cla;
 IF ses IS NULL THEN RAISE EXCEPTION 'Asistencia % sin sesión/detalle horario real',q.cod_asi_est USING ERRCODE='23514'; END IF;
 PERFORM public.ofi_comprobar_hecho_plan(NULL,q.cod_est,clase.cod_pas,clase.cod_pes,fecha,NULL,q.cod_asi_est);
 PERFORM public.ofi_comprobar_hecho_lms(clase.cod_cla,q.cod_est,fecha,q.cod_asi_est);
END $$;

CREATE FUNCTION public.ofi_validar_hecho_historico() RETURNS trigger
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE aula varchar; estudiante varchar; fecha date;
BEGIN
 CASE TG_TABLE_NAME
 WHEN 'calificacion' THEN PERFORM public.ofi_comprobar_calificacion(NEW);
 WHEN 'asistencia_estudiante' THEN PERFORM public.ofi_comprobar_asistencia(NEW);
 WHEN 'entrega_tarea' THEN
  SELECT cod_cla INTO aula FROM public.tarea WHERE cod_tar=NEW.cod_tar;
  fecha=(coalesce(NEW.fec_ent,NEW.ini_ent) AT TIME ZONE 'America/La_Paz')::date;
  PERFORM public.ofi_comprobar_hecho_lms(aula,NEW.cod_est,fecha,NEW.cod_ent);
 WHEN 'intento_cuestionario' THEN
  SELECT cod_cla INTO aula FROM public.cuestionario WHERE cod_cue=NEW.cod_cue;
  PERFORM public.ofi_comprobar_hecho_lms(aula,NEW.cod_est,(NEW.ini_inc AT TIME ZONE 'America/La_Paz')::date,NEW.cod_inc);
  IF NEW.fin_inc IS NOT NULL THEN PERFORM public.ofi_comprobar_hecho_lms(aula,NEW.cod_est,(NEW.fin_inc AT TIME ZONE 'America/La_Paz')::date,NEW.cod_inc); END IF;
 WHEN 'foro_mensaje' THEN
  SELECT e.cod_est INTO estudiante FROM public.users u JOIN public.estudiante e USING(cod_per) WHERE u.cod_usu=NEW.cod_usu;
  IF estudiante IS NOT NULL THEN
   SELECT f.cod_cla INTO aula FROM public.foro_tema t JOIN public.foro_clase f USING(cod_for) WHERE t.cod_fte=NEW.cod_fte;
   PERFORM public.ofi_comprobar_hecho_lms(aula,estudiante,(NEW.fec_fme AT TIME ZONE 'America/La_Paz')::date,NEW.cod_fme);
  END IF;
 WHEN 'registro_actividad_clase' THEN
  IF NEW.cod_est IS NOT NULL THEN PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,(NEW.fec_rac AT TIME ZONE 'America/La_Paz')::date,NEW.cod_rac); END IF;
 END CASE;
 RETURN NEW;
END $$;

CREATE FUNCTION public.ofi_revisar_historia_inscripcion(inscripcion varchar) RETURNS void
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE q public.calificacion; m public.clase_estudiante; a public.asistencia_estudiante; i public.inscripcion_estudiante; registro record;
BEGIN
 SELECT * INTO i FROM public.inscripcion_estudiante WHERE cod_ins=inscripcion;
 FOR q IN SELECT * FROM public.calificacion WHERE cod_ins=inscripcion LOOP PERFORM public.ofi_comprobar_calificacion(q); END LOOP;
 FOR m IN SELECT membresia.* FROM public.clase_estudiante membresia JOIN public.clase_virtual c USING(cod_cla)
  LEFT JOIN public.plan_asignatura p USING(cod_pas) LEFT JOIN public.plan_especialidad e USING(cod_pes)
  JOIN public.grupo_academico g ON g.cod_gac=coalesce(p.cod_gac,e.cod_gac)
  WHERE membresia.cod_est=i.cod_est AND g.cod_gea=i.cod_gea LOOP PERFORM public.ofi_comprobar_membresia(m); END LOOP;
 FOR a IN SELECT asistencia.* FROM public.asistencia_estudiante asistencia JOIN public.asistencia_clase c USING(cod_asi_cla)
  JOIN public.clase_virtual v USING(cod_cla) LEFT JOIN public.plan_asignatura p ON p.cod_pas=v.cod_pas LEFT JOIN public.plan_especialidad e ON e.cod_pes=v.cod_pes
  JOIN public.grupo_academico g ON g.cod_gac=coalesce(p.cod_gac,e.cod_gac)
  WHERE asistencia.cod_est=i.cod_est AND g.cod_gea=i.cod_gea LOOP PERFORM public.ofi_comprobar_asistencia(a); END LOOP;
END $$;

CREATE FUNCTION public.ofi_proteger_historia_al_cambiar_contexto() RETURNS trigger
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE inscripcion varchar; grupo varchar; q public.calificacion; m public.clase_estudiante;
BEGIN
 IF TG_TABLE_NAME='inscripcion_vigencia' AND
   (to_jsonb(NEW)-ARRAY['updated_at','est_ivg'])=(to_jsonb(OLD)-ARRAY['updated_at','est_ivg'])
   AND NEW.est_ivg<>'ANULADO' AND OLD.est_ivg<>'ANULADO' THEN RETURN NULL; END IF;
 IF TG_TABLE_NAME='inscripcion_vigencia' THEN PERFORM public.ofi_revisar_historia_inscripcion(NEW.cod_ins);
 ELSIF TG_TABLE_NAME='configuracion_calendario_gestion' THEN
  FOR q IN SELECT nota.* FROM public.calificacion nota JOIN public.inscripcion_estudiante i USING(cod_ins) WHERE i.cod_gea=NEW.cod_gea AND nota.cod_pev=NEW.cod_pev LOOP PERFORM public.ofi_comprobar_calificacion(q); END LOOP;
 ELSE
  grupo=NEW.cod_gac;
  FOR inscripcion IN SELECT DISTINCT v.cod_ins FROM public.inscripcion_vigencia v WHERE v.cod_gac=grupo LOOP PERFORM public.ofi_revisar_historia_inscripcion(inscripcion); END LOOP;
 END IF;
 RETURN NULL;
END $$;

CREATE FUNCTION public.ofi_proteger_hechos_membresia() RETURNS trigger
LANGUAGE plpgsql SET search_path=pg_catalog,public AS $$
DECLARE r record;
BEGIN
 IF (NEW.cod_cla,NEW.cod_est,NEW.fec_inc_cla_est,NEW.fec_ret_cla_est) IS NOT DISTINCT FROM
    (OLD.cod_cla,OLD.cod_est,OLD.fec_inc_cla_est,OLD.fec_ret_cla_est)
    AND NEW.est_cla_est<>'ANULADO' AND OLD.est_cla_est<>'ANULADO' THEN RETURN NULL; END IF;
 FOR r IN SELECT e.cod_ent,(coalesce(e.fec_ent,e.ini_ent) AT TIME ZONE 'America/La_Paz')::date AS fecha FROM public.entrega_tarea e JOIN public.tarea t USING(cod_tar)
  WHERE t.cod_cla=NEW.cod_cla AND e.cod_est=NEW.cod_est LOOP PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,r.fecha,r.cod_ent); END LOOP;
 FOR r IN SELECT a.cod_inc,(a.ini_inc AT TIME ZONE 'America/La_Paz')::date AS inicio,(a.fin_inc AT TIME ZONE 'America/La_Paz')::date AS fin
  FROM public.intento_cuestionario a JOIN public.cuestionario c USING(cod_cue) WHERE c.cod_cla=NEW.cod_cla AND a.cod_est=NEW.cod_est LOOP
  PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,r.inicio,r.cod_inc);
  IF r.fin IS NOT NULL THEN PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,r.fin,r.cod_inc); END IF;
 END LOOP;
 FOR r IN SELECT a.cod_rac,(a.fec_rac AT TIME ZONE 'America/La_Paz')::date AS fecha FROM public.registro_actividad_clase a WHERE a.cod_cla=NEW.cod_cla AND a.cod_est=NEW.cod_est
  LOOP PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,r.fecha,r.cod_rac); END LOOP;
 FOR r IN SELECT m.cod_fme,(m.fec_fme AT TIME ZONE 'America/La_Paz')::date AS fecha FROM public.foro_mensaje m JOIN public.foro_tema t USING(cod_fte)
  JOIN public.foro_clase f USING(cod_for) JOIN public.users u ON u.cod_usu=m.cod_usu JOIN public.estudiante e ON e.cod_per=u.cod_per
  WHERE f.cod_cla=NEW.cod_cla AND e.cod_est=NEW.cod_est LOOP PERFORM public.ofi_comprobar_hecho_lms(NEW.cod_cla,NEW.cod_est,r.fecha,r.cod_fme); END LOOP;
 RETURN NULL;
END $$;
SQL);
            // La prevalidación detiene la migración ante hechos incompatibles, sin repararlos.
            $db->statement('SELECT public.ofi_comprobar_calificacion(q) FROM public.calificacion q');
            $db->statement('SELECT public.ofi_comprobar_asistencia(q) FROM public.asistencia_estudiante q');
            $db->statement("SELECT public.ofi_comprobar_hecho_lms(t.cod_cla,e.cod_est,(coalesce(e.fec_ent,e.ini_ent) AT TIME ZONE 'America/La_Paz')::date,e.cod_ent) FROM public.entrega_tarea e JOIN public.tarea t USING(cod_tar)");
            $db->statement("SELECT public.ofi_comprobar_hecho_lms(c.cod_cla,i.cod_est,(i.ini_inc AT TIME ZONE 'America/La_Paz')::date,i.cod_inc) FROM public.intento_cuestionario i JOIN public.cuestionario c USING(cod_cue)");
            $db->statement("SELECT public.ofi_comprobar_hecho_lms(c.cod_cla,i.cod_est,(i.fin_inc AT TIME ZONE 'America/La_Paz')::date,i.cod_inc) FROM public.intento_cuestionario i JOIN public.cuestionario c USING(cod_cue) WHERE i.fin_inc IS NOT NULL");
            $db->statement("SELECT public.ofi_comprobar_hecho_lms(r.cod_cla,r.cod_est,(r.fec_rac AT TIME ZONE 'America/La_Paz')::date,r.cod_rac) FROM public.registro_actividad_clase r WHERE r.cod_est IS NOT NULL");
            $db->statement("SELECT public.ofi_comprobar_hecho_lms(f.cod_cla,e.cod_est,(m.fec_fme AT TIME ZONE 'America/La_Paz')::date,m.cod_fme) FROM public.foro_mensaje m JOIN public.foro_tema t USING(cod_fte) JOIN public.foro_clase f USING(cod_for) JOIN public.users u ON u.cod_usu=m.cod_usu JOIN public.estudiante e ON e.cod_per=u.cod_per");
            foreach (['calificacion', 'asistencia_estudiante', 'entrega_tarea', 'intento_cuestionario', 'foro_mensaje', 'registro_actividad_clase'] as $tabla) {
                $db->statement("CREATE TRIGGER ofi_hecho_historico BEFORE INSERT OR UPDATE ON public.{$tabla} FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_hecho_historico()");
            }
            foreach (['inscripcion_vigencia', 'plan_asignatura', 'plan_especialidad', 'configuracion_calendario_gestion'] as $tabla) {
                $db->statement("CREATE CONSTRAINT TRIGGER ofi_historia_contexto AFTER UPDATE ON public.{$tabla} DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION public.ofi_proteger_historia_al_cambiar_contexto()");
            }
            $db->statement('CREATE CONSTRAINT TRIGGER ofi_historia_membresia AFTER UPDATE ON public.clase_estudiante DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION public.ofi_proteger_hechos_membresia()');
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        $db->transaction(function () use ($db): void {
            foreach (['calificacion', 'asistencia_estudiante', 'entrega_tarea', 'intento_cuestionario', 'foro_mensaje', 'registro_actividad_clase'] as $tabla) {
                $db->statement("DROP TRIGGER ofi_hecho_historico ON public.{$tabla}");
            }
            foreach (['inscripcion_vigencia', 'plan_asignatura', 'plan_especialidad', 'configuracion_calendario_gestion'] as $tabla) {
                $db->statement("DROP TRIGGER ofi_historia_contexto ON public.{$tabla}");
            }
            $db->statement('DROP TRIGGER ofi_historia_membresia ON public.clase_estudiante');
            foreach (['ofi_proteger_hechos_membresia()', 'ofi_proteger_historia_al_cambiar_contexto()', 'ofi_revisar_historia_inscripcion(varchar)', 'ofi_validar_hecho_historico()', 'ofi_comprobar_asistencia(public.asistencia_estudiante)', 'ofi_comprobar_calificacion(public.calificacion)', 'ofi_comprobar_hecho_lms(varchar,varchar,date,text)', 'ofi_comprobar_hecho_plan(varchar,varchar,varchar,varchar,date,varchar,text)'] as $funcion) {
                $db->statement('DROP FUNCTION public.'.$funcion);
            }
        });
    }
};
