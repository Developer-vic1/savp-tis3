<?php

namespace Tests\Feature;

use App\Support\Academico\ConsultaParalelosInstitucionales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsultaParalelosInstitucionalesTest extends TestCase
{
    public function test_separa_historia_y_deduplica_el_trayecto_tecnico_del_grupo(): void
    {
        // Tablas mínimas y datos ficticios exclusivamente en SQLite :memory:.
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Carbon::setTestNow('2026-10-04');
        try {
            foreach ([
                'gestion_academica'=>['cod_gea','ani_gea','est_gea'], 'paralelo'=>['cod_par','nom_par'], 'curso'=>['cod_cur','nom_cur','ord_cur'], 'turno'=>['cod_tur','nom_tur'],
                'grupo_academico'=>['cod_gac','cod_gea','cod_cur','cod_par','cod_tur','cap_gac','est_gac'],
                'inscripcion_estudiante'=>['cod_ins','cod_est','cod_gea','cod_cur','cod_par','cod_tur','est_ins'],
                'inscripcion_vigencia'=>['cod_ins','cod_gac','fii_ivg','ffi_ivg','est_ivg'],
                'plan_asignatura'=>['cod_gac','est_pas'], 'plan_especialidad'=>['cod_gac','est_pes'],
                'horario'=>['cod_gac','fii_hor','ffi_hor','est_hor'],
            ] as $tabla=>$columnas) Schema::create($tabla, function ($t) use ($columnas) { foreach ($columnas as $columna) $t->string($columna)->nullable(); });
            DB::table('gestion_academica')->insert([['cod_gea'=>'actual','ani_gea'=>'2026','est_gea'=>'ACTIVO'], ['cod_gea'=>'pasada','ani_gea'=>'2025','est_gea'=>'CERRADO']]);
            DB::table('paralelo')->insert(['cod_par'=>'A','nom_par'=>'A']);
            DB::table('curso')->insert(['cod_cur'=>'C','nom_cur'=>'Primero','ord_cur'=>'1']);
            DB::table('turno')->insert([['cod_tur'=>'M','nom_tur'=>'Mañana'], ['cod_tur'=>'T','nom_tur'=>'Tarde']]);
            foreach ([['G','actual','M'], ['GT','actual','T'], ['antiguo','pasada','M']] as [$id,$gestion,$turno]) DB::table('grupo_academico')->insert(['cod_gac'=>$id,'cod_gea'=>$gestion,'cod_cur'=>'C','cod_par'=>'A','cod_tur'=>$turno,'cap_gac'=>'35','est_gac'=>'ACTIVO']);
            foreach ([['I','S','actual','ACTIVA'], ['IA','S','pasada','ARCHIVADA'], ['anulada','otro','actual','ANULADA']] as [$id,$est,$gestion,$estado]) DB::table('inscripcion_estudiante')->insert(['cod_ins'=>$id,'cod_est'=>$est,'cod_gea'=>$gestion,'cod_cur'=>'C','cod_par'=>'A','cod_tur'=>'M','est_ins'=>$estado]);
            foreach (['G','GT','GT'] as $grupo) DB::table('inscripcion_vigencia')->insert(['cod_ins'=>'I','cod_gac'=>$grupo,'fii_ivg'=>'2026-02-01','est_ivg'=>'ACTIVO']);
            DB::table('inscripcion_vigencia')->insert(['cod_ins'=>'anulada','cod_gac'=>'G','fii_ivg'=>'2026-02-01','est_ivg'=>'ACTIVO']);
            DB::table('plan_asignatura')->insert([['cod_gac'=>'G','est_pas'=>'ACTIVO'], ['cod_gac'=>'antiguo','est_pas'=>'ACTIVO'], ['cod_gac'=>'G','est_pas'=>'INACTIVO']]);
            DB::table('plan_especialidad')->insert(['cod_gac'=>'GT','est_pes'=>'ACTIVO']);
            DB::table('horario')->insert([['cod_gac'=>'G','fii_hor'=>'2026-02-01','ffi_hor'=>null,'est_hor'=>'ACTIVO'], ['cod_gac'=>'G','fii_hor'=>'2026-01-01','ffi_hor'=>'2026-01-31','est_hor'=>'ACTIVO'], ['cod_gac'=>'antiguo','fii_hor'=>'2025-02-01','ffi_hor'=>null,'est_hor'=>'ACTIVO']]);
            $datos = (new ConsultaParalelosInstitucionales)->consultar();
            $uso = $datos['usos']['A'];
            $this->assertSame(1, $uso['estudiantes']);
            $this->assertSame(1, $uso['planes_asignatura']);
            $this->assertSame(1, $uso['planes_especialidad']);
            $this->assertSame(1, $uso['horarios']);
            $this->assertCount(2, $datos['mapa']);
            $this->assertSame([1,1], array_column($datos['mapa'], 'estudiantes'));
        } finally {
            Carbon::setTestNow();
        }
    }
}
