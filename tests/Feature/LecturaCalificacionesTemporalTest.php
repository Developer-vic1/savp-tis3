<?php

namespace Tests\Feature;

use App\Livewire\Admin\Calificaciones;
use App\Models\Oficial\Sistema\User;
use App\Support\Evaluacion\RendimientoEstudiantil;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class LecturaCalificacionesTemporalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['calificaciones.cache_store' => 'array']);
        Cache::store('array')->flush();
        foreach ([
            'persona' => ['cod_per','nom_per','ape_pat_per','ape_mat_per','gen_per'],
            'estudiante' => ['cod_est','cod_per'],
            'curso' => ['cod_cur','nom_cur','ord_cur','niv_cur'],
            'paralelo' => ['cod_par','nom_par'],
            'gestion_academica' => ['cod_gea','ani_gea','est_gea'],
            'inscripcion_estudiante' => ['cod_ins','cod_est','cod_cur','cod_par','cod_gea','est_ins'],
            'calificacion' => ['cod_ins','cod_pev','est_cal','not_cal','obs_cal'],
            'orientacion_actividades' => ['id','cod_est','cod_gea','avance','finalizado_at','analysis_completed_at','analysis_snapshot','riasec_score'],
            'orientacion_resultados' => ['orientacion_actividad_id','perfil_predominante'],
            'resultado_anual' => ['cod_ins','est_ran','res_ran'],
            'periodo_evaluacion' => ['cod_pev','nom_pev','ord_pev'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $estructura) use ($campos) {
                foreach ($campos as $campo) $estructura->string($campo)->nullable();
            });
        }
        DB::table('gestion_academica')->insert(['cod_gea'=>'G','ani_gea'=>2026,'est_gea'=>'ACTIVA']);
        DB::table('curso')->insert(['cod_cur'=>'C','nom_cur'=>'Sexto','ord_cur'=>6,'niv_cur'=>'SECUNDARIA']);
        DB::table('paralelo')->insert(['cod_par'=>'P','nom_par'=>'A']);
        DB::table('periodo_evaluacion')->insert(['cod_pev'=>'T','nom_pev'=>'Primero','ord_pev'=>1]);
        for ($i = 1; $i <= 12; $i++) {
            $codigo = sprintf('E%02d', $i);
            DB::table('persona')->insert(['cod_per'=>$codigo,'nom_per'=>$codigo,'ape_pat_per'=>'Quispe','gen_per'=>$i % 2 ? 'M' : 'F']);
            DB::table('estudiante')->insert(['cod_est'=>$codigo,'cod_per'=>$codigo]);
            DB::table('inscripcion_estudiante')->insert(['cod_ins'=>$codigo,'cod_est'=>$codigo,'cod_cur'=>'C','cod_par'=>'P','cod_gea'=>'G','est_ins'=>'ACTIVA']);
            DB::table('calificacion')->insert(['cod_ins'=>$codigo,'cod_pev'=>'T','est_cal'=>'VIGENTE','not_cal'=>100-$i]);
        }
        $this->actor();
    }

    private function actor(string $codigo = 'ADMIN', bool $orientacion = false): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->cod_usu = $codigo;
        $actor->est_usu = 'ACTIVO';
        $actor->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Administrador');
        $actor->shouldReceive('can')->andReturnUsing(fn ($permiso) => $permiso === 'orientacion.ver.institucional' ? $orientacion : true);
        $this->actingAs($actor);
    }

    private function componente(string $vista = 'honor'): Calificaciones
    {
        $componente = new Calificaciones;
        $componente->gestionFiltro = 'G';
        $componente->vista = $vista;
        return $componente;
    }

    private function leer(Calificaciones $componente): array
    {
        $componente->boot();
        return $componente->render()->getData();
    }

    private function clave(Calificaciones $componente, string $vista = 'honor'): string
    {
        return (new ReflectionMethod($componente, 'claveLectura'))->invoke($componente, $vista);
    }

    public function test_json_restaurado_conserva_honor_puestos_y_paginacion_sin_reconsultar(): void
    {
        $componente = $this->componente();
        $primera = $this->leer($componente);
        $almacenada = Cache::store('array')->get($this->clave($componente));
        $this->assertIsString($almacenada['datos']);
        $this->assertSame($almacenada, unserialize(serialize($almacenada), ['allowed_classes'=>false]));
        $this->assertIsArray(json_decode($almacenada['datos'], true, 512, JSON_THROW_ON_ERROR));
        DB::enableQueryLog();
        DB::flushQueryLog();
        $segunda = $this->leer($componente);
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame(12, $segunda['estudiantes']->total());
        $this->assertSame(10, $segunda['estudiantes']->count());
        $this->assertSame(2, $segunda['estudiantes']->lastPage());
        $this->assertSame($primera['estudiantes']->pluck('cod_ins')->all(), $segunda['estudiantes']->pluck('cod_ins')->all());
        $this->assertSame([1,2,3], $segunda['honor']['grados']->first()['estudiantes']->pluck('puesto')->map(fn ($p) => (int)$p)->all());
        $this->assertSame(['E01','E02'], $segunda['honor']['historial']->first()['estudiantes']->pluck('cod_ins')->all());
    }

    public function test_cambio_busqueda_y_pagina_reemplaza_lectura_sin_total_obsoleto(): void
    {
        $componente = $this->componente('estudiantes');
        $this->leer($componente);
        $componente->setPage(2);
        $pagina = $this->leer($componente);
        $this->assertSame(12, $pagina['estudiantes']->total());
        $this->assertSame(['E11','E12'], $pagina['estudiantes']->pluck('cod_ins')->all());
        $componente->search = 'E12';
        $componente->updated('search');
        $filtrada = $this->leer($componente);
        $this->assertSame(1, $filtrada['estudiantes']->total());
        $this->assertSame(1, $filtrada['estudiantes']->currentPage());
        $this->assertSame(['E12'], $filtrada['estudiantes']->pluck('cod_ins')->all());
    }

    public function test_lectura_expira_en_veinte_segundos_y_actualizacion_manual_la_invalida(): void
    {
        $componente = $this->componente();
        $this->leer($componente);
        DB::table('calificacion')->where('cod_ins','E12')->update(['not_cal'=>100]);
        $this->travel(19)->seconds();
        $this->assertSame('E01', $this->leer($componente)['estudiantes']->first()->cod_ins);
        $this->travel(2)->seconds();
        $this->assertSame('E12', $this->leer($componente)['estudiantes']->first()->cod_ins);
        foreach (['honor','comparativas','proyecto','estudiantes'] as $vista) {
            Cache::store('array')->put($this->clave($componente, $vista), ['marca'=>'vieja'], 20);
        }
        $componente->actualizarDatos();
        foreach (['honor','comparativas','proyecto','estudiantes'] as $vista) {
            $this->assertNull(Cache::store('array')->get($this->clave($componente, $vista)));
        }
        DB::table('calificacion')->where('cod_ins','E12')->update(['not_cal'=>90]);
        DB::table('calificacion')->where('cod_ins','E11')->update(['not_cal'=>100]);
        $this->assertSame('E11', $this->leer($componente)['estudiantes']->first()->cod_ins);
    }

    public function test_cache_separa_actor_y_orientacion_incluso_al_retirar_permiso(): void
    {
        DB::table('orientacion_actividades')->insert(['id'=>1,'cod_est'=>'E01','cod_gea'=>'G','analysis_completed_at'=>'2026-01-01','analysis_snapshot'=>'{}']);
        $servicio = Mockery::mock(RendimientoEstudiantil::class);
        $servicio->shouldReceive('estudio')->andReturnUsing(fn ($actividad) => $actividad
            ? ['estado'=>'contrato_valido','datos'=>['career_evidence_profiles'=>[['career_name'=>'Opción documentada','university'=>'Fuente']]]]
            : ['estado'=>'pendiente','datos'=>null]);
        $this->app->instance(RendimientoEstudiantil::class, $servicio);
        $componente = $this->componente();
        $claveSin = $this->clave($componente);
        $this->actor(orientacion: true);
        $claveCon = $this->clave($componente);
        $this->assertNotSame($claveSin, $claveCon);
        $visible = $this->leer($componente);
        $this->assertNotEmpty($visible['honor']['promocion']['estudiantes']->firstWhere('cod_est','E01')->carreras);
        $this->actor();
        $oculta = $this->leer($componente);
        $this->assertFalse($oculta['puedeVerOrientacion']);
        $this->assertSame([], $oculta['honor']['promocion']['estudiantes']->firstWhere('cod_est','E01')->carreras);
        $this->actor('OTRO');
        $this->assertNotSame($claveSin, $this->clave($componente));
    }
}
