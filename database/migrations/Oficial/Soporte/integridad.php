<?php

declare(strict_types=1);

use Database\Migrations\Oficial\Soporte\IntegracionAditiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Integridad relacional y temporal. No calcula promoción ni promedios oficiales.
DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

foreach (['estudiante' => 'cod_per', 'personal_institucional' => 'cod_per', 'docente' => 'cod_pin', 'director' => 'cod_pin', 'regente' => 'cod_pin'] as $tabla => $columna) {
    if (Schema::hasTable($tabla)) {
        IntegracionAditiva::unico($tabla, [$columna], 'ofi_uq_'.$tabla.'_'.$columna);
    }
}
IntegracionAditiva::unico('calificacion', ['cod_ins', 'cod_pas', 'cod_pev'], 'ofi_uq_cal_pas', "cod_ins IS NOT NULL AND cod_pas IS NOT NULL AND upper(est_cal) NOT IN ('ANULADA','ANULADO')");
IntegracionAditiva::unico('calificacion', ['cod_ins', 'cod_pes', 'cod_pev'], 'ofi_uq_cal_pes', "cod_ins IS NOT NULL AND cod_pes IS NOT NULL AND upper(est_cal) NOT IN ('ANULADA','ANULADO')");
IntegracionAditiva::unico('vinculo_personal', ['cod_pin', 'cod_cai'], 'ofi_uq_vpe_abierto', "ffi_vpe IS NULL AND est_vpe = 'ACTIVO'");
foreach ([
    ['plan_asignatura', ['cod_gac', 'cod_asi']], ['plan_asignatura', ['cod_doc']],
    ['plan_especialidad', ['cod_gac', 'cod_esp']], ['plan_especialidad', ['cod_doc']],
    ['inscripcion_estudiante', ['cod_gea', 'est_ins']], ['inscripcion_vigencia', ['cod_gac', 'fii_ivg']],
    ['sesion_academica', ['fec_ses', 'est_ses']], ['asistencia_estudiante', ['cod_est', 'cod_asi_cla']],
    ['calificacion', ['cod_ins', 'cod_pev']], ['expediente_traslado', ['cod_est']],
    ['nota_traslado', ['cod_ext', 'ani_ntr']], ['captura_fuente', ['cod_rfu', 'fec_cfu']],
    ['evidencia_academica', ['cod_cfu']], ['respaldo_gestion_academica', ['cod_gea', 'fec_rga']],
] as [$tabla, $columnas]) {
    IntegracionAditiva::indice($tabla, $columnas, 'ofi_idx_'.substr(sha1($tabla.implode(',', $columnas)), 0, 16));
}

// Una UNIQUE abierta no evita cruces entre intervalos ya cerrados: EXCLUDE sí.
foreach ([
    ['inscripcion_vigencia', 'cod_ins', 'fii_ivg', 'ffi_ivg', 'est_ivg', 'fii_ivg IS NOT NULL'],
    ['vinculo_personal', 'cod_pin, cod_cai', 'fii_vpe', 'ffi_vpe', 'est_vpe', 'fii_vpe IS NOT NULL'],
    ['version_oferta_academica', 'cod_ofa', 'fii_vof', 'ffi_vof', 'est_vof', 'fii_vof IS NOT NULL'],
    ['horario', 'cod_gac', 'fii_hor', 'ffi_hor', 'est_hor', 'cod_gac IS NOT NULL AND fii_hor IS NOT NULL'],
] as [$tabla, $contexto, $inicio, $fin, $estado, $ambito]) {
    $clave = implode(', ', array_map(fn ($columna) => trim($columna).' WITH =', explode(',', $contexto)));
    IntegracionAditiva::restriccion($tabla, 'ofi_ex_'.$tabla, 'EXCLUDE USING gist ('.$clave.', daterange('.$inicio.', '.$fin.", '[]') WITH &&) WHERE (".$ambito.' AND upper('.$estado.") NOT IN ('ANULADO','ANULADA'))");
}

DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.ofi_actualizar_fecha() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
BEGIN NEW.updated_at=CURRENT_TIMESTAMP; RETURN NEW; END $$;

CREATE OR REPLACE FUNCTION public.ofi_validar_contexto() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE a record; b record; contexto varchar(20); alumno varchar(20); otro integer;
BEGIN
 CASE TG_TABLE_NAME
 WHEN 'documento_personal' THEN
  IF NEW.ven_dpe IS NULL AND EXISTS(SELECT 1 FROM public.tipo_documento_personal WHERE cod_tdp=NEW.cod_tdp AND ven_tdp) THEN RAISE EXCEPTION 'Tipo documental requiere fecha de vencimiento'; END IF;
  IF NEW.cod_vpe IS NOT NULL THEN
   SELECT cod_pin INTO alumno FROM public.vinculo_personal WHERE cod_vpe=NEW.cod_vpe;
   IF alumno IS DISTINCT FROM NEW.cod_pin THEN RAISE EXCEPTION 'Documento y vínculo pertenecen a diferente personal'; END IF;
  END IF;
 WHEN 'formacion_docente','experiencia_docente','capacitacion_docente' THEN
  IF NEW.cod_dpe IS NOT NULL THEN
   SELECT cod_pin INTO alumno FROM public.docente WHERE cod_doc=NEW.cod_doc;
   SELECT cod_pin INTO contexto FROM public.documento_personal WHERE cod_dpe=NEW.cod_dpe;
   IF alumno IS DISTINCT FROM contexto THEN RAISE EXCEPTION 'Evidencia documental de otro docente'; END IF;
  END IF;
 WHEN 'configuracion_calendario_gestion' THEN
  SELECT * INTO a FROM public.gestion_academica WHERE cod_gea=NEW.cod_gea;
  IF NEW.fii_tri_ccg < a.fii_gea OR NEW.ffi_tri_ccg > a.ffi_gea THEN RAISE EXCEPTION 'Trimestre fuera de gestión'; END IF;
 WHEN 'inscripcion_estudiante' THEN
  SELECT ani_gea INTO a FROM public.gestion_academica WHERE cod_gea=NEW.cod_gea;
  -- Inscribir antes del inicio de clases es válido: el calendario lectivo no
  -- constituye una ventana de matrícula. Se valida el año de la inscripción.
  IF extract(year FROM NEW.fei_ins)<>a.ani_gea THEN RAISE EXCEPTION 'Inscripción de otro año de gestión'; END IF;
 WHEN 'plan_asignatura','plan_especialidad' THEN
  IF NEW.cod_gac IS NOT NULL THEN
   SELECT * INTO a FROM public.grupo_academico WHERE cod_gac=NEW.cod_gac;
   -- Contexto legado se mantiene durante la transición y debe coincidir.
   IF (to_jsonb(NEW)->>'cod_gea') IS NOT NULL AND (to_jsonb(NEW)->>'cod_gea') <> a.cod_gea THEN RAISE EXCEPTION 'Plan de otra gestión'; END IF;
   IF (to_jsonb(NEW)->>'cod_cur') IS NOT NULL AND (to_jsonb(NEW)->>'cod_cur') <> a.cod_cur THEN RAISE EXCEPTION 'Plan de otro curso'; END IF;
   IF (to_jsonb(NEW)->>'cod_par') IS NOT NULL AND (to_jsonb(NEW)->>'cod_par') <> a.cod_par THEN RAISE EXCEPTION 'Plan de otro paralelo'; END IF;
   IF (to_jsonb(NEW)->>'cod_tur') IS NOT NULL AND (to_jsonb(NEW)->>'cod_tur') <> a.cod_tur THEN RAISE EXCEPTION 'Plan de otro turno'; END IF;
  END IF;
 WHEN 'inscripcion_vigencia' THEN
  PERFORM 1 FROM public.inscripcion_estudiante WHERE cod_ins=NEW.cod_ins FOR UPDATE;
  SELECT i.cod_gea,g.fii_gea,g.ffi_gea INTO a FROM public.inscripcion_estudiante i JOIN public.gestion_academica g USING(cod_gea) WHERE i.cod_ins=NEW.cod_ins;
  IF NEW.fii_ivg<a.fii_gea OR NEW.ffi_ivg>a.ffi_gea THEN RAISE EXCEPTION 'Vigencia fuera de gestión'; END IF;
  IF NEW.cod_gac IS NOT NULL THEN
   SELECT * INTO b FROM public.grupo_academico WHERE cod_gac=NEW.cod_gac;
   IF b.cod_gea IS DISTINCT FROM a.cod_gea THEN RAISE EXCEPTION 'Grupo de otra gestión'; END IF;
   IF (to_jsonb(NEW)->>'cod_cur') IS NOT NULL AND (to_jsonb(NEW)->>'cod_cur') <> b.cod_cur THEN RAISE EXCEPTION 'Curso de vigencia inconsistente'; END IF;
   IF (to_jsonb(NEW)->>'cod_par') IS NOT NULL AND (to_jsonb(NEW)->>'cod_par') <> b.cod_par THEN RAISE EXCEPTION 'Paralelo de vigencia inconsistente'; END IF;
   IF (to_jsonb(NEW)->>'cod_tur') IS NOT NULL AND (to_jsonb(NEW)->>'cod_tur') <> b.cod_tur THEN RAISE EXCEPTION 'Turno de vigencia inconsistente'; END IF;
   NEW=jsonb_populate_record(NEW,jsonb_build_object('cod_cur',b.cod_cur,'cod_par',b.cod_par,'cod_tur',b.cod_tur));
  END IF;
 WHEN 'horario' THEN
  IF NEW.cod_gac IS NOT NULL THEN
   SELECT * INTO a FROM public.grupo_academico WHERE cod_gac=NEW.cod_gac;
   SELECT cod_tur INTO contexto FROM public.plantilla_horaria WHERE cod_pho=NEW.cod_pho;
   IF a.cod_tur IS DISTINCT FROM contexto THEN RAISE EXCEPTION 'Plantilla de otro turno'; END IF;
   IF (to_jsonb(NEW)->>'cod_gea') IS NOT NULL AND (to_jsonb(NEW)->>'cod_gea') <> a.cod_gea THEN RAISE EXCEPTION 'Horario de otra gestión'; END IF;
   IF (to_jsonb(NEW)->>'cod_cur') IS NOT NULL AND (to_jsonb(NEW)->>'cod_cur') <> a.cod_cur THEN RAISE EXCEPTION 'Horario de otro curso'; END IF;
   IF (to_jsonb(NEW)->>'cod_par') IS NOT NULL AND (to_jsonb(NEW)->>'cod_par') <> a.cod_par THEN RAISE EXCEPTION 'Horario de otro paralelo'; END IF;
   NEW=jsonb_populate_record(NEW,jsonb_build_object('cod_gea',a.cod_gea,'cod_cur',a.cod_cur,'cod_par',a.cod_par));
  END IF;
 WHEN 'horario_detalle' THEN
  SELECT * INTO a FROM public.horario WHERE cod_hor=NEW.cod_hor;
  SELECT cod_pho INTO contexto FROM public.horario_bloque WHERE cod_hbl=NEW.cod_hbl;
  IF contexto IS DISTINCT FROM a.cod_pho THEN RAISE EXCEPTION 'Bloque de otra plantilla'; END IF;
  IF NEW.cod_pas IS NOT NULL THEN SELECT * INTO b FROM public.plan_asignatura WHERE cod_pas=NEW.cod_pas;
  ELSE SELECT * INTO b FROM public.plan_especialidad WHERE cod_pes=NEW.cod_pes; END IF;
  IF a.cod_gac IS NOT NULL AND b.cod_gac IS DISTINCT FROM a.cod_gac THEN RAISE EXCEPTION 'Plan de otro grupo'; END IF;
 WHEN 'sesion_academica' THEN
  SELECT h.* INTO a FROM public.horario h JOIN public.horario_detalle d USING(cod_hor) WHERE d.cod_hde=NEW.cod_hde;
  IF NEW.fec_ses<a.fii_hor OR NEW.fec_ses>a.ffi_hor THEN RAISE EXCEPTION 'Sesión fuera de vigencia horaria'; END IF;
  IF (to_jsonb(NEW)->>'cod_gea') IS NOT NULL AND (to_jsonb(NEW)->>'cod_gea')<>(to_jsonb(a)->>'cod_gea') THEN RAISE EXCEPTION 'Sesión de otra gestión'; END IF;
  NEW=jsonb_populate_record(NEW,jsonb_build_object('cod_gea',to_jsonb(a)->>'cod_gea'));
 WHEN 'asistencia_clase' THEN
  IF NEW.cod_ses IS NOT NULL THEN
   SELECT d.cod_pas,d.cod_pes,s.fec_ses,s.est_ses INTO a FROM public.sesion_academica s JOIN public.horario_detalle d USING(cod_hde) WHERE s.cod_ses=NEW.cod_ses;
   IF a.est_ses IN ('SUSPENDIDA','CANCELADA','ANULADA') THEN RAISE EXCEPTION 'No registrar asistencia de sesión suspendida/cancelada'; END IF;
   IF (to_jsonb(NEW)->>'cod_cla') IS NOT NULL THEN
    SELECT cod_pas,cod_pes INTO b FROM public.clase_virtual WHERE cod_cla=NEW.cod_cla;
    IF a.cod_pas IS DISTINCT FROM b.cod_pas OR a.cod_pes IS DISTINCT FROM b.cod_pes THEN RAISE EXCEPTION 'Asistencia de otra clase'; END IF;
   END IF;
  END IF;
 WHEN 'asistencia_estudiante' THEN
  SELECT h.cod_gac,s.fec_ses INTO a FROM public.asistencia_clase x JOIN public.sesion_academica s USING(cod_ses) JOIN public.horario_detalle d USING(cod_hde) JOIN public.horario h USING(cod_hor) WHERE x.cod_asi_cla=NEW.cod_asi_cla;
  IF a.cod_gac IS NOT NULL AND NOT EXISTS(SELECT 1 FROM public.inscripcion_vigencia v JOIN public.inscripcion_estudiante i USING(cod_ins) WHERE i.cod_est=NEW.cod_est AND v.cod_gac=a.cod_gac AND a.fec_ses BETWEEN v.fii_ivg AND coalesce(v.ffi_ivg,'infinity'::date) AND upper(v.est_ivg)<>'ANULADO') THEN RAISE EXCEPTION 'Estudiante fuera del grupo/fecha de asistencia'; END IF;
  IF NEW.cod_nes IS NOT NULL THEN
   SELECT i.cod_est,n.fii_nes,n.ffi_nes INTO b FROM public.novedad_estudiante n JOIN public.inscripcion_estudiante i USING(cod_ins) WHERE n.cod_nes=NEW.cod_nes;
   IF b.cod_est IS DISTINCT FROM NEW.cod_est OR a.fec_ses<b.fii_nes OR a.fec_ses>b.ffi_nes THEN RAISE EXCEPTION 'Novedad de otro estudiante/fecha'; END IF;
  END IF;
 WHEN 'calificacion' THEN
  IF NEW.cod_ins IS NOT NULL THEN
   PERFORM 1 FROM public.inscripcion_estudiante WHERE cod_ins=NEW.cod_ins FOR UPDATE;
   IF EXISTS(SELECT 1 FROM public.resultado_anual WHERE cod_ins=NEW.cod_ins AND est_ran='VIGENTE') THEN RAISE EXCEPTION 'Notas con resolución anual conservan cierre histórico'; END IF;
   SELECT cod_est,cod_gea INTO a FROM public.inscripcion_estudiante WHERE cod_ins=NEW.cod_ins;
   IF NEW.cod_pas IS NOT NULL THEN SELECT cod_gac,cod_asi INTO b FROM public.plan_asignatura WHERE cod_pas=NEW.cod_pas;
   ELSE SELECT cod_gac,cod_esp AS cod_asi INTO b FROM public.plan_especialidad WHERE cod_pes=NEW.cod_pes; END IF;
   IF b.cod_gac IS NULL THEN RAISE EXCEPTION 'Plan oficial pendiente de conciliación de grupo'; END IF;
   SELECT cod_gea INTO contexto FROM public.grupo_academico WHERE cod_gac=b.cod_gac;
   IF contexto IS DISTINCT FROM a.cod_gea THEN RAISE EXCEPTION 'Nota de otra gestión'; END IF;
   IF NOT EXISTS(SELECT 1 FROM public.configuracion_calendario_gestion WHERE cod_gea=a.cod_gea AND cod_pev=NEW.cod_pev) THEN RAISE EXCEPTION 'Trimestre no configurado para la gestión'; END IF;
   IF NOT EXISTS(SELECT 1 FROM public.inscripcion_vigencia v JOIN public.configuracion_calendario_gestion cc ON cc.cod_gea=a.cod_gea AND cc.cod_pev=NEW.cod_pev WHERE v.cod_ins=NEW.cod_ins AND v.cod_gac=b.cod_gac AND upper(v.est_ivg)<>'ANULADO' AND daterange(v.fii_ivg,v.ffi_ivg,'[]') && daterange(cc.fii_tri_ccg,cc.ffi_tri_ccg,'[]')) THEN RAISE EXCEPTION 'Nota fuera del trayecto vigente del estudiante'; END IF;
   IF (to_jsonb(NEW)->>'cod_est') IS NOT NULL AND (to_jsonb(NEW)->>'cod_est')<>a.cod_est THEN RAISE EXCEPTION 'Estudiante de nota no corresponde a inscripción'; END IF;
   IF NEW.cod_pas IS NOT NULL AND (to_jsonb(NEW)->>'cod_asi') IS NOT NULL AND (to_jsonb(NEW)->>'cod_asi')<>b.cod_asi THEN RAISE EXCEPTION 'Asignatura de nota no corresponde al plan'; END IF;
   NEW=jsonb_populate_record(NEW,jsonb_build_object('cod_est',a.cod_est));
   IF NEW.cod_pas IS NOT NULL THEN NEW=jsonb_populate_record(NEW,jsonb_build_object('cod_asi',b.cod_asi)); END IF;
   IF upper(NEW.est_cal) NOT IN ('ANULADO','ANULADA') THEN
    SELECT count(*) INTO otro FROM public.calificacion q LEFT JOIN public.plan_asignatura p ON p.cod_pas=q.cod_pas LEFT JOIN public.plan_especialidad e ON e.cod_pes=q.cod_pes WHERE q.cod_ins=NEW.cod_ins AND q.cod_pev=NEW.cod_pev AND q.cod_cal<>NEW.cod_cal AND upper(q.est_cal) NOT IN ('ANULADO','ANULADA') AND ((NEW.cod_pas IS NOT NULL AND q.cod_pas IS NOT NULL AND p.cod_asi=b.cod_asi) OR (NEW.cod_pes IS NOT NULL AND q.cod_pes IS NOT NULL AND e.cod_esp=b.cod_asi));
    IF otro>0 THEN RAISE EXCEPTION 'Ya existe nota oficial de la materia/trimestre, incluso con otro docente'; END IF;
   END IF;
  END IF;
 WHEN 'resultado_anual' THEN
  SELECT i.est_ins,g.est_gea INTO a FROM public.inscripcion_estudiante i JOIN public.gestion_academica g USING(cod_gea) WHERE i.cod_ins=NEW.cod_ins FOR UPDATE OF i;
  IF NEW.est_ran='VIGENTE' THEN
   IF upper(a.est_ins)='ANULADA' THEN RAISE EXCEPTION 'Inscripción anulada no tiene resultado vigente'; END IF;
   IF NEW.res_ran IN ('PROMOVIDO','RETENIDO','EGRESADO') AND upper(a.est_gea) NOT IN ('CERRADA','CERRADO') THEN RAISE EXCEPTION 'Resultado académico requiere gestión cerrada'; END IF;
   IF nullif(btrim(NEW.ref_ran),'') IS NULL OR NEW.fec_ran>(CURRENT_TIMESTAMP AT TIME ZONE 'America/La_Paz')::date THEN RAISE EXCEPTION 'Resultado oficial requiere referencia y fecha real'; END IF;
  END IF;
 WHEN 'clase_estudiante' THEN
  SELECT coalesce(p.cod_gac,e.cod_gac) INTO contexto FROM public.clase_virtual c LEFT JOIN public.plan_asignatura p USING(cod_pas) LEFT JOIN public.plan_especialidad e USING(cod_pes) WHERE c.cod_cla=NEW.cod_cla;
  IF contexto IS NOT NULL AND NOT EXISTS(SELECT 1 FROM public.inscripcion_vigencia v JOIN public.inscripcion_estudiante i USING(cod_ins) WHERE i.cod_est=NEW.cod_est AND v.cod_gac=contexto AND NEW.fec_inc_cla_est::date BETWEEN v.fii_ivg AND coalesce(v.ffi_ivg,'infinity'::date) AND upper(v.est_ivg)<>'ANULADO') THEN RAISE EXCEPTION 'Membresía fuera del trayecto académico'; END IF;
 WHEN 'regente_asignaciones' THEN
  IF NEW.cod_vpe IS NOT NULL THEN
   SELECT v.*,c.cla_cai INTO a FROM public.vinculo_personal v JOIN public.cargo_institucional c USING(cod_cai) WHERE v.cod_vpe=NEW.cod_vpe;
   IF upper(a.cla_cai)<>'REGENTE' OR NEW.fii_ras<a.fii_vpe OR NEW.ffi_ras>a.ffi_vpe THEN RAISE EXCEPTION 'Ámbito sin vínculo vigente de regente'; END IF;
   -- El rol antiguo solo se contrasta en una transición que aún lo conserve.
   IF (to_jsonb(NEW)->>'cod_reg') IS NOT NULL AND to_regclass('public.regente') IS NOT NULL THEN
    EXECUTE 'SELECT EXISTS(SELECT 1 FROM public.regente WHERE cod_reg=$1 AND cod_pin=$2)' INTO b USING to_jsonb(NEW)->>'cod_reg',a.cod_pin;
    IF NOT b.exists THEN RAISE EXCEPTION 'Perfil de regente y vínculo de otro personal'; END IF;
   END IF;
  END IF;
 WHEN 'respuesta_cuestionario' THEN
  SELECT cod_cue INTO contexto FROM public.intento_cuestionario WHERE cod_inc=NEW.cod_inc;
  SELECT cod_cue,pun_prc INTO b FROM public.pregunta_cuestionario WHERE cod_prc=NEW.cod_prc;
  IF contexto IS DISTINCT FROM b.cod_cue OR NEW.pun_rcu>b.pun_prc THEN RAISE EXCEPTION 'Respuesta de otro cuestionario o puntaje inválido'; END IF;
 WHEN 'respuesta_opcion' THEN
  SELECT cod_prc INTO contexto FROM public.respuesta_cuestionario WHERE cod_rcu=NEW.cod_rcu;
  SELECT cod_prc INTO alumno FROM public.opcion_pregunta WHERE cod_opr=NEW.cod_opr;
  IF contexto IS DISTINCT FROM alumno THEN RAISE EXCEPTION 'Opción de otra pregunta'; END IF;
 WHEN 'intento_cuestionario' THEN
  SELECT * INTO a FROM public.cuestionario WHERE cod_cue=NEW.cod_cue FOR UPDATE;
  IF NEW.num_inc>a.int_cue OR NEW.ini_inc<a.ape_cue OR NEW.ini_inc>a.cie_cue THEN RAISE EXCEPTION 'Intento fuera de cantidad/plazo permitidos'; END IF;
  IF NOT EXISTS(SELECT 1 FROM public.clase_estudiante WHERE cod_cla=a.cod_cla AND cod_est=NEW.cod_est AND upper(est_cla_est)='ACTIVO') THEN RAISE EXCEPTION 'Intento sin matrícula activa en el aula'; END IF;
 WHEN 'foro_mensaje' THEN
  -- Serializar cambios del árbol por tema impide ciclos creados simultáneamente.
  PERFORM 1 FROM public.foro_tema WHERE cod_fte=NEW.cod_fte FOR UPDATE;
  IF NEW.pad_fme IS NOT NULL THEN
   SELECT cod_fte INTO contexto FROM public.foro_mensaje WHERE cod_fme=NEW.pad_fme;
   IF contexto IS DISTINCT FROM NEW.cod_fte OR NEW.pad_fme=NEW.cod_fme THEN RAISE EXCEPTION 'Mensaje padre de otro tema o referencia circular'; END IF;
   IF EXISTS(WITH RECURSIVE ancestros AS (
    SELECT cod_fme,pad_fme FROM public.foro_mensaje WHERE cod_fme=NEW.pad_fme
    UNION SELECT m.cod_fme,m.pad_fme FROM public.foro_mensaje m JOIN ancestros anc ON m.cod_fme=anc.pad_fme
   ) SELECT 1 FROM ancestros WHERE cod_fme=NEW.cod_fme) THEN RAISE EXCEPTION 'Ciclo en árbol de mensajes'; END IF;
  END IF;
 WHEN 'orientacion_respuestas' THEN
  SELECT cod_ior,estado INTO a FROM public.orientacion_actividades WHERE id=NEW.orientacion_actividad_id FOR UPDATE;
  IF upper(a.estado) IN ('FINALIZADO','REVISADO','REQUIERE_SEGUIMIENTO') THEN RAISE EXCEPTION 'Orientación finalizada: crear otro intento'; END IF;
  IF NEW.cod_ipr IS NOT NULL THEN
   SELECT cod_ior INTO contexto FROM public.instrumento_pregunta WHERE cod_ipr=NEW.cod_ipr;
   IF a.cod_ior IS DISTINCT FROM contexto THEN RAISE EXCEPTION 'Pregunta de otro instrumento'; END IF;
  END IF;
 WHEN 'registro_actividad_clase' THEN
  IF NEW.pub_rac IS NOT NULL THEN SELECT cod_cla INTO contexto FROM public.publicacion_clase WHERE cod_pub=NEW.pub_rac;
  ELSIF NEW.mat_rac IS NOT NULL THEN SELECT cod_cla INTO contexto FROM public.material_clase WHERE cod_mat=NEW.mat_rac;
  ELSIF NEW.tar_rac IS NOT NULL THEN SELECT cod_cla INTO contexto FROM public.tarea WHERE cod_tar=NEW.tar_rac;
  ELSIF NEW.cue_rac IS NOT NULL THEN SELECT cod_cla INTO contexto FROM public.cuestionario WHERE cod_cue=NEW.cue_rac;
  ELSIF NEW.for_rac IS NOT NULL THEN SELECT cod_cla INTO contexto FROM public.foro_clase WHERE cod_for=NEW.for_rac;
  ELSIF NEW.men_rac IS NOT NULL THEN SELECT f.cod_cla INTO contexto FROM public.foro_mensaje m JOIN public.foro_tema t USING(cod_fte) JOIN public.foro_clase f USING(cod_for) WHERE m.cod_fme=NEW.men_rac;
  ELSE contexto=NEW.cod_cla; END IF;
  IF contexto IS DISTINCT FROM NEW.cod_cla THEN RAISE EXCEPTION 'Objeto de actividad pertenece a otra aula'; END IF;
 WHEN 'orientacion_ofertas_sugeridas' THEN
  SELECT s.cod_car,actividad_origen.finalizado_at INTO a FROM public.orientacion_carreras_sugeridas s JOIN public.orientacion_resultados r ON r.id=s.orientacion_resultado_id JOIN public.orientacion_actividades actividad_origen ON actividad_origen.id=r.orientacion_actividad_id WHERE s.id=NEW.sug_oos;
  SELECT o.cod_car,v.fii_vof,v.ffi_vof INTO b FROM public.version_oferta_academica v JOIN public.oferta_academica o USING(cod_ofa) WHERE v.cod_vof=NEW.cod_vof;
  IF a.cod_car IS DISTINCT FROM b.cod_car OR a.finalizado_at::date<b.fii_vof OR a.finalizado_at::date>b.ffi_vof THEN RAISE EXCEPTION 'Oferta de otra carrera o fuera de vigencia de orientación'; END IF;
 WHEN 'recurso_fuente' THEN
  IF NEW.cod_sed IS NOT NULL AND NEW.cod_uni IS NOT NULL THEN
   SELECT cod_uni INTO contexto FROM public.sede_universidad WHERE cod_sed=NEW.cod_sed;
   IF contexto IS DISTINCT FROM NEW.cod_uni THEN RAISE EXCEPTION 'Sede y universidad de fuente incoherentes'; END IF;
  END IF;
 WHEN 'captura_fuente' THEN
  IF NEW.est_cfu='VALIDADA' AND NEW.val_cfu IS NULL THEN RAISE EXCEPTION 'Confirmación humana requerida para validar captura'; END IF;
 END CASE;
 RETURN NEW;
END $$;

CREATE OR REPLACE FUNCTION public.ofi_proteger_historico() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE estado text; cambios jsonb; actividad bigint;
BEGIN
 IF TG_TABLE_NAME='orientacion_respuestas' THEN
  actividad=OLD.orientacion_actividad_id;
  SELECT upper(a.estado) INTO estado FROM public.orientacion_actividades a WHERE a.id=actividad FOR UPDATE;
  IF estado IN ('FINALIZADO','REVISADO','REQUIERE_SEGUIMIENTO') THEN RAISE EXCEPTION 'Respuestas vocacionales cerradas son históricas'; END IF;
 ELSIF TG_TABLE_NAME='orientacion_resultados' THEN
  SELECT upper(oac.estado) INTO estado FROM public.orientacion_actividades oac WHERE oac.id=OLD.orientacion_actividad_id FOR UPDATE;
  IF estado IN ('FINALIZADO','REVISADO','REQUIERE_SEGUIMIENTO') THEN RAISE EXCEPTION 'Resultado vocacional cerrado conserva su snapshot'; END IF;
 ELSIF TG_TABLE_NAME='orientacion_actividades' THEN
  estado=upper(OLD.estado);
  IF estado IN ('FINALIZADO','REVISADO','REQUIERE_SEGUIMIENTO') THEN
   IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No borrar orientación finalizada'; END IF;
   cambios=to_jsonb(NEW)-ARRAY['estado','revisado_por','rev_oac','updated_at'];
   IF cambios IS DISTINCT FROM (to_jsonb(OLD)-ARRAY['estado','revisado_por','rev_oac','updated_at']) OR upper(NEW.estado) NOT IN ('FINALIZADO','REVISADO','REQUIERE_SEGUIMIENTO') THEN RAISE EXCEPTION 'No sobrescribir prueba finalizada'; END IF;
  END IF;
 ELSIF TG_TABLE_NAME='resultado_anual' THEN
  IF OLD.est_ran='VIGENTE' THEN RAISE EXCEPTION 'Resultado anual oficial: rectificación requiere procedimiento institucional revisado'; END IF;
 ELSIF TG_TABLE_NAME='calificacion' THEN
  PERFORM 1 FROM public.inscripcion_estudiante WHERE cod_ins=OLD.cod_ins FOR UPDATE;
  IF EXISTS(SELECT 1 FROM public.resultado_anual WHERE cod_ins=OLD.cod_ins AND est_ran='VIGENTE') THEN RAISE EXCEPTION 'No alterar nota de gestión con resultado anual'; END IF;
 ELSIF TG_TABLE_NAME='version_oferta_academica' THEN
  IF TG_OP='DELETE' THEN RAISE EXCEPTION 'Conservar versión histórica de oferta'; END IF;
  IF (to_jsonb(NEW)-ARRAY['ffi_vof','est_vof','updated_at']) IS DISTINCT FROM (to_jsonb(OLD)-ARRAY['ffi_vof','est_vof','updated_at']) THEN RAISE EXCEPTION 'Cambio académico requiere nueva versión de oferta'; END IF;
  IF OLD.ffi_vof IS NOT NULL AND NEW.ffi_vof IS DISTINCT FROM OLD.ffi_vof THEN RAISE EXCEPTION 'Cierre histórico de oferta no se sobrescribe'; END IF;
 END IF;
 IF TG_OP='DELETE' THEN RETURN OLD; END IF;
 RETURN NEW;
END $$;

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

CREATE OR REPLACE FUNCTION public.ofi_proteger_plan() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE campos text[]; campo text; usado boolean;
BEGIN
 campos=ARRAY['cod_doc','cod_asi','cod_esp','cod_gea','cod_cur','cod_par','cod_tur'];
 IF TG_TABLE_NAME='plan_asignatura' THEN
  usado=EXISTS(SELECT 1 FROM public.calificacion WHERE cod_pas=OLD.cod_pas) OR EXISTS(SELECT 1 FROM public.horario_detalle WHERE cod_pas=OLD.cod_pas) OR EXISTS(SELECT 1 FROM public.clase_virtual WHERE cod_pas=OLD.cod_pas);
 ELSE
  usado=EXISTS(SELECT 1 FROM public.calificacion WHERE cod_pes=OLD.cod_pes) OR EXISTS(SELECT 1 FROM public.horario_detalle WHERE cod_pes=OLD.cod_pes) OR EXISTS(SELECT 1 FROM public.clase_virtual WHERE cod_pes=OLD.cod_pes);
 END IF;
 IF usado THEN
  FOREACH campo IN ARRAY campos LOOP
   IF (to_jsonb(NEW)->campo) IS DISTINCT FROM (to_jsonb(OLD)->campo) THEN RAISE EXCEPTION 'Asignación utilizada: crear otra vigencia, no sobrescribir contexto'; END IF;
  END LOOP;
  IF OLD.cod_gac IS NOT NULL AND NEW.cod_gac IS DISTINCT FROM OLD.cod_gac THEN RAISE EXCEPTION 'No cambiar grupo de asignación utilizada'; END IF;
 END IF;
 RETURN NEW;
END $$;
SQL);

$contextos = ['documento_personal', 'formacion_docente', 'experiencia_docente', 'capacitacion_docente', 'configuracion_calendario_gestion', 'inscripcion_estudiante', 'plan_asignatura', 'plan_especialidad', 'inscripcion_vigencia', 'horario', 'horario_detalle', 'sesion_academica', 'asistencia_clase', 'asistencia_estudiante', 'calificacion', 'resultado_anual', 'clase_estudiante', 'regente_asignaciones', 'respuesta_cuestionario', 'respuesta_opcion', 'intento_cuestionario', 'foro_mensaje', 'registro_actividad_clase', 'orientacion_respuestas', 'orientacion_ofertas_sugeridas', 'recurso_fuente', 'captura_fuente'];
foreach ($contextos as $tabla) {
    DB::statement('CREATE OR REPLACE TRIGGER ofi_contexto BEFORE INSERT OR UPDATE ON "'.$tabla.'" FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_contexto()');
}
foreach ($contrato as $tabla) {
    if (Schema::hasColumn($tabla['tabla'], 'updated_at')) {
        DB::statement('CREATE OR REPLACE TRIGGER ofi_fecha BEFORE UPDATE ON "'.$tabla['tabla'].'" FOR EACH ROW EXECUTE FUNCTION public.ofi_actualizar_fecha()');
    }
}
foreach (['plan_asignatura', 'plan_especialidad'] as $tabla) {
    DB::statement('CREATE OR REPLACE TRIGGER ofi_plan_historico BEFORE UPDATE ON "'.$tabla.'" FOR EACH ROW EXECUTE FUNCTION public.ofi_proteger_plan()');
}
foreach (['orientacion_respuestas', 'orientacion_resultados', 'orientacion_actividades', 'resultado_anual', 'calificacion', 'version_oferta_academica'] as $tabla) {
    DB::statement('CREATE OR REPLACE TRIGGER ofi_historico BEFORE UPDATE OR DELETE ON "'.$tabla.'" FOR EACH ROW EXECUTE FUNCTION public.ofi_proteger_historico()');
}
foreach (['calificacion' => 'cod_cal', 'resultado_anual' => 'cod_ran', 'inscripcion_vigencia' => 'cod_ivg', 'version_oferta_academica' => 'cod_vof'] as $tabla => $pk) {
    DB::statement('CREATE OR REPLACE TRIGGER ofi_auditoria AFTER INSERT OR UPDATE OR DELETE ON "'.$tabla.'" FOR EACH ROW EXECUTE FUNCTION public.ofi_auditar_fila(\''.$pk.'\')');
}

// Conservar los hijos históricos al eliminar una madre, incluso cuando hoy no
// existen filas. La FK reemplazada mantiene columnas/tipos, sin transformar datos.
// El contrato conserva hechos institucionales; CASCADE y SET NULL anteriores
// no deben borrar ni desvincular entregas, asistencias, notas o evidencias.
// Cache, sesiones de autenticación y demás tablas técnicas quedan fuera.
foreach (array_unique(array_merge(array_keys($contrato), ['users', 'director', 'regente', 'administrador', 'secretaria_general'])) as $tabla) {
    if (! Schema::hasTable($tabla)) {
        continue;
    }
    foreach (Schema::getForeignKeys($tabla) as $fk) {
        if (! in_array($fk['on_delete'], ['cascade', 'set null'], true)) {
            continue;
        }
        $columns = implode(', ', array_map(fn ($s) => '"'.$s.'"', $fk['columns']));
        $references = implode(', ', array_map(fn ($s) => '"'.$s.'"', $fk['foreign_columns']));
        DB::statement('ALTER TABLE "'.$tabla.'" DROP CONSTRAINT "'.$fk['name'].'"');
        DB::statement('ALTER TABLE "'.$tabla.'" ADD CONSTRAINT "'.$fk['name'].'" FOREIGN KEY ('.$columns.') REFERENCES "'.$fk['foreign_table'].'" ('.$references.') ON DELETE RESTRICT ON UPDATE CASCADE');
    }
}
