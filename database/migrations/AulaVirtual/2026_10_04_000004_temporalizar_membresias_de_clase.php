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
            throw new RuntimeException('Las membresías históricas requieren PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.clase_estudiante IN ACCESS EXCLUSIVE MODE');
            if (! $db->selectOne("SELECT EXISTS(SELECT 1 FROM pg_extension WHERE extname='btree_gist') AS existe")->existe) {
                throw new RuntimeException('Falta btree_gist. No se instala automáticamente.');
            }
            $tipos = $db->select("SELECT column_name,data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='clase_estudiante' AND column_name IN ('fec_inc_cla_est','fec_ret_cla_est')");
            if (count($tipos) !== 2 || array_unique(array_column($tipos, 'data_type')) !== ['date']) {
                throw new RuntimeException('Las fechas de membresía no son DATE; revisar el rango sin cambiar tipos.');
            }
            $ambiguas = $db->select(<<<'SQL'
SELECT m.cod_cla_est
FROM public.clase_estudiante m
WHERE m.fec_inc_cla_est IS NULL
 OR m.fec_ret_cla_est<=m.fec_inc_cla_est
 OR (m.est_cla_est='ACTIVO' AND m.fec_ret_cla_est IS NOT NULL)
 OR (m.est_cla_est IN ('RETIRADO','TRANSFERIDO','INACTIVO') AND m.fec_ret_cla_est IS NULL)
 OR EXISTS(SELECT 1 FROM public.clase_estudiante n
   WHERE n.cod_cla=m.cod_cla AND n.cod_est=m.cod_est AND n.cod_cla_est<>m.cod_cla_est
    AND n.est_cla_est<>'ANULADO' AND m.est_cla_est<>'ANULADO'
    AND CASE WHEN n.fec_inc_cla_est IS NULL OR n.fec_ret_cla_est<=n.fec_inc_cla_est
      OR m.fec_inc_cla_est IS NULL OR m.fec_ret_cla_est<=m.fec_inc_cla_est THEN false
     ELSE daterange(n.fec_inc_cla_est,n.fec_ret_cla_est,'[)') && daterange(m.fec_inc_cla_est,m.fec_ret_cla_est,'[)') END)
ORDER BY m.cod_cla_est
SQL);
            if ($ambiguas !== []) {
                throw new RuntimeException('Membresías ambiguas; no se modificó nada: '.implode(', ', array_column($ambiguas, 'cod_cla_est')));
            }
            $unico = $db->selectOne("SELECT pg_get_constraintdef(oid) AS ddl FROM pg_constraint WHERE conrelid='public.clase_estudiante'::regclass AND conname='uq_clase_estudiante_unico' AND contype='u'");
            if ($unico === null || $unico->ddl !== 'UNIQUE (cod_cla, cod_est)') {
                throw new RuntimeException('La unicidad actual difiere del contrato revisado.');
            }
            $db->unprepared(<<<'SQL'
CREATE FUNCTION public.ofi_comprobar_membresia(m public.clase_estudiante) RETURNS void
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE aula public.clase_virtual; grupo varchar(20); especialidad varchar(20);
 gea varchar(20); inicio date; fin date; limite date; cobertura datemultirange; intervalo daterange;
BEGIN
 IF m.est_cla_est='ANULADO' THEN RETURN; END IF;
 SELECT * INTO aula FROM public.clase_virtual WHERE cod_cla=m.cod_cla FOR SHARE;
 IF aula.cod_cla IS NULL THEN RAISE EXCEPTION 'Aula inexistente para membresía %',m.cod_cla_est USING ERRCODE='23503'; END IF;
 IF aula.cod_pas IS NOT NULL THEN
  SELECT p.cod_gac,NULL::varchar,p.fii_pas,p.ffi_pas INTO grupo,especialidad,inicio,fin
   FROM public.plan_asignatura p WHERE p.cod_pas=aula.cod_pas AND p.est_pas IN ('ACTIVO','FINALIZADO') FOR SHARE;
 ELSE
  SELECT p.cod_gac,p.cod_esp,p.fii_pes,p.ffi_pes INTO grupo,especialidad,inicio,fin
   FROM public.plan_especialidad p WHERE p.cod_pes=aula.cod_pes AND p.est_pes IN ('ACTIVO','FINALIZADO') FOR SHARE;
 END IF;
 SELECT g.cod_gea,a.ffi_gea INTO gea,limite FROM public.grupo_academico g
  JOIN public.gestion_academica a USING(cod_gea) WHERE g.cod_gac=grupo;
 IF grupo IS NULL OR limite IS NULL OR inicio IS NULL THEN
  RAISE EXCEPTION 'Membresía % sin plan/gestión válidos',m.cod_cla_est USING ERRCODE='23514';
 END IF;
 limite=least(limite,coalesce(fin,limite),coalesce(aula.fec_fin_cla,limite));
 IF m.fec_inc_cla_est IS NULL OR m.fec_inc_cla_est<greatest(inicio,coalesce(aula.fec_ini_cla,inicio))
  OR m.fec_inc_cla_est>limite OR (m.fec_ret_cla_est IS NOT NULL AND (m.fec_ret_cla_est<=m.fec_inc_cla_est OR m.fec_ret_cla_est>limite+1)) THEN
  RAISE EXCEPTION 'Intervalo de membresía % fuera de aula/plan/gestión',m.cod_cla_est USING ERRCODE='23514';
 END IF;
 -- Una membresía abierta se valida hasta el límite de su aula anual, no sobre años posteriores.
 intervalo=daterange(m.fec_inc_cla_est,coalesce(m.fec_ret_cla_est,limite+1),'[)');
 PERFORM 1 FROM public.inscripcion_estudiante i WHERE i.cod_est=m.cod_est AND i.cod_gea=gea FOR UPDATE;
 SELECT range_agg(daterange(v.fii_ivg,coalesce(v.ffi_ivg,limite),'[]')) INTO cobertura
 FROM public.inscripcion_vigencia v JOIN public.inscripcion_estudiante i USING(cod_ins)
 WHERE i.cod_est=m.cod_est AND i.cod_gea=gea AND i.est_ins<>'ANULADA'
  AND v.cod_gac=grupo AND v.cod_esp_tec IS NOT DISTINCT FROM especialidad AND v.est_ivg<>'ANULADO';
 IF cobertura IS NULL OR NOT (cobertura @> intervalo) THEN
  RAISE EXCEPTION 'Membresía % sin cobertura completa del trayecto y especialidad exactos',m.cod_cla_est USING ERRCODE='23514';
 END IF;
 IF (m.ult_acc_cla_est IS NOT NULL AND NOT (intervalo @> m.ult_acc_cla_est::date))
  OR (m.ult_act_cla_est IS NOT NULL AND NOT (intervalo @> m.ult_act_cla_est::date)) THEN
  RAISE EXCEPTION 'Membresía % deja último acceso/actividad fuera del intervalo',m.cod_cla_est USING ERRCODE='23514';
 END IF;
END $$;

CREATE FUNCTION public.ofi_validar_membresia_intervalo() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
BEGIN
 IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No borrar membresía histórica %',OLD.cod_cla_est; END IF;
 IF TG_OP='UPDATE' THEN
  IF (NEW.cod_cla,NEW.cod_est,NEW.fec_inc_cla_est) IS DISTINCT FROM (OLD.cod_cla,OLD.cod_est,OLD.fec_inc_cla_est) THEN
   RAISE EXCEPTION 'Cambiar aula/estudiante/inicio requiere otra membresía: %',OLD.cod_cla_est;
  END IF;
  IF OLD.est_cla_est IN ('RETIRADO','TRANSFERIDO','INACTIVO') AND
   (NEW.fec_ret_cla_est IS DISTINCT FROM OLD.fec_ret_cla_est OR NEW.est_cla_est='ACTIVO') THEN
   RAISE EXCEPTION 'No reabrir ni sobrescribir membresía histórica %',OLD.cod_cla_est;
  END IF;
 END IF;
 PERFORM public.ofi_comprobar_membresia(NEW);
 RETURN NEW;
END $$;

CREATE FUNCTION public.ofi_validar_estado_membresia() RETURNS trigger
LANGUAGE plpgsql SET search_path = pg_catalog, public AS $$
DECLARE actual public.clase_estudiante;
BEGIN
 SELECT * INTO actual FROM public.clase_estudiante WHERE cod_cla_est=NEW.cod_cla_est;
 IF (actual.est_cla_est='ACTIVO' AND actual.fec_ret_cla_est IS NOT NULL)
  OR (actual.est_cla_est IN ('RETIRADO','TRANSFERIDO','INACTIVO') AND actual.fec_ret_cla_est IS NULL) THEN
  RAISE EXCEPTION 'Estado incompatible con cierre de membresía %',actual.cod_cla_est USING ERRCODE='23514';
 END IF;
 RETURN NULL;
END $$;
SQL);
            $db->statement("SELECT public.ofi_comprobar_membresia(m) FROM public.clase_estudiante m WHERE m.est_cla_est<>'ANULADO'");
            $db->statement('ALTER TABLE public.clase_estudiante ADD CONSTRAINT ofi_ck_membresia_fechas CHECK (fec_inc_cla_est IS NOT NULL AND (fec_ret_cla_est IS NULL OR fec_ret_cla_est>fec_inc_cla_est))');
            $db->statement("ALTER TABLE public.clase_estudiante ADD CONSTRAINT ofi_ex_membresia_intervalo EXCLUDE USING gist (cod_cla WITH =,cod_est WITH =,daterange(fec_inc_cla_est,fec_ret_cla_est,'[)') WITH &&) WHERE (est_cla_est<>'ANULADO')");
            $db->statement('CREATE TRIGGER ofi_membresia_intervalo BEFORE INSERT OR UPDATE OR DELETE ON public.clase_estudiante FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_membresia_intervalo()');
            $db->statement('CREATE CONSTRAINT TRIGGER ofi_membresia_estado AFTER INSERT OR UPDATE ON public.clase_estudiante DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION public.ofi_validar_estado_membresia()');
            $db->statement('ALTER TABLE public.clase_estudiante DROP CONSTRAINT uq_clase_estudiante_unico');
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        if ($db->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Las membresías históricas requieren PostgreSQL.');
        }
        $db->transaction(function () use ($db): void {
            $db->statement('LOCK TABLE public.clase_estudiante IN ACCESS EXCLUSIVE MODE');
            $multiples = $db->select('SELECT cod_cla,cod_est FROM public.clase_estudiante GROUP BY cod_cla,cod_est HAVING count(*)>1');
            if ($multiples !== []) {
                throw new RuntimeException('Rollback incompatible: varias membresías históricas por aula/estudiante. No se borró nada.');
            }
            $db->statement('ALTER TABLE public.clase_estudiante ADD CONSTRAINT uq_clase_estudiante_unico UNIQUE(cod_cla,cod_est)');
            $db->statement('DROP TRIGGER ofi_membresia_estado ON public.clase_estudiante');
            $db->statement('DROP TRIGGER ofi_membresia_intervalo ON public.clase_estudiante');
            $db->statement('DROP FUNCTION public.ofi_validar_estado_membresia()');
            $db->statement('DROP FUNCTION public.ofi_validar_membresia_intervalo()');
            $db->statement('DROP FUNCTION public.ofi_comprobar_membresia(public.clase_estudiante)');
            $db->statement('ALTER TABLE public.clase_estudiante DROP CONSTRAINT ofi_ex_membresia_intervalo,DROP CONSTRAINT ofi_ck_membresia_fechas');
        });
    }
};
