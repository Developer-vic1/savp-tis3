<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReportesAdministrativos;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportesAdministrativosRendimientoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['users', 'estudiante', 'docente', 'asignatura', 'especialidad_tecnica', 'curso', 'paralelo', 'turno', 'institucion_procedencia', 'tipo_vinculacion_estudiante', 'periodo_evaluacion'] as $tabla) {
            Schema::create($tabla, function (Blueprint $estructura) {
                $estructura->string('est_usu')->nullable();
            });
        }
        Schema::create('inscripcion_estudiante', function (Blueprint $estructura) {
            $estructura->string('cod_ins');
            $estructura->string('est_ins')->nullable();
            $estructura->text('obs_ins')->nullable();
        });
        Schema::create('bitacora', function (Blueprint $estructura) {
            foreach (['cod_bit', 'cod_usu', 'mod_bit', 'acc_bit', 'nom_reg_bit', 'reg_bit', 'res_bit', 'fec_bit', 'des_bit', 'val_ant_bit', 'val_nue_bit'] as $campo) {
                $estructura->text($campo)->nullable();
            }
        });
    }

    public function test_conteos_por_estado_conservan_total_sin_hidratar_inscripciones(): void
    {
        DB::table('users')->insert([['est_usu' => 'ACTIVO'], ['est_usu' => 'INACTIVO']]);
        DB::table('estudiante')->insert([['est_usu' => null], ['est_usu' => null]]);
        foreach (['ACTIVA', 'ACTIVA', 'ARCHIVADA', 'RETIRADA', 'ANULADA', null, ''] as $indice => $estado) {
            DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'I'.$indice, 'est_ins' => $estado, 'obs_ins' => str_repeat('Observación histórica ', 100)]);
        }
        $hidratadas = 0;
        InscripcionEstudiante::retrieved(function () use (&$hidratadas) { $hidratadas++; });
        DB::enableQueryLog();
        $datos = (new ReportesAdministrativos)->render()->getData();
        $consultas = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $this->assertSame(0, $hidratadas);
        $this->assertSame(7, $datos['metricas']['Inscripciones']);
        $this->assertSame(1, $datos['metricas']['Usuarios activos']);
        $this->assertSame(2, $datos['metricas']['Estudiantes registrados']);
        $this->assertSame(2, $datos['estadosInscripcion']->get('ACTIVA'));
        $this->assertSame(2, $datos['estadosInscripcion']->get(''));
        $this->assertSame(1, $datos['estadosInscripcion']->get('ARCHIVADA'));
        $this->assertSame(1, $datos['estadosInscripcion']->get('RETIRADA'));
        $this->assertSame(1, $datos['estadosInscripcion']->get('ANULADA'));
        $inscripciones = $consultas->filter(fn ($sql) => str_contains($sql, 'inscripcion_estudiante'));
        $this->assertCount(1, $inscripciones);
        $this->assertStringContainsString('group by', $inscripciones->first());
        $this->assertStringNotContainsString('select *', $inscripciones->first());
    }

    public function test_actividad_limitada_conserva_modulos_globales_sin_cargar_json_ni_relaciones(): void
    {
        for ($indice = 0; $indice < 25; $indice++) {
            DB::table('bitacora')->insert([
                'cod_bit' => 'B'.$indice, 'cod_usu' => 'USUARIO_NO_CONSULTADO',
                'mod_bit' => $indice < 10 ? 'Usuarios' : 'Cursos', 'acc_bit' => 'CONSULTAR',
                'nom_reg_bit' => 'Registro '.$indice, 'reg_bit' => 'R'.$indice, 'res_bit' => 'EXITOSO',
                'fec_bit' => sprintf('2026-01-01 10:%02d:00', $indice),
                'val_ant_bit' => str_repeat('Datos que no aparecen en la vista ', 100),
            ]);
        }
        $componente = new ReportesAdministrativos;
        $datos = $componente->render()->getData();
        $this->assertCount(20, $datos['actividad']);
        $this->assertSame('B24', $datos['actividad']->first()->cod_bit);
        $this->assertSame('B5', $datos['actividad']->last()->cod_bit);
        $this->assertSame(['Cursos', 'Usuarios'], $datos['modulos']->all());
        $this->assertFalse($datos['actividad']->first()->relationLoaded('usuario'));
        $this->assertArrayNotHasKey('val_ant_bit', $datos['actividad']->first()->getAttributes());

        $componente->moduloFiltro = 'Usuarios';
        $filtrados = $componente->render()->getData();
        $this->assertCount(10, $filtrados['actividad']);
        $this->assertTrue($filtrados['actividad']->every(fn ($actividad) => $actividad->mod_bit === 'Usuarios'));
        $this->assertSame(['Cursos', 'Usuarios'], $filtrados['modulos']->all());
        $this->assertSame($datos['metricas'], $filtrados['metricas']);
    }

    public function test_metricas_no_quedan_obsoletas_entre_consultas_y_vacio_permanece_explicito(): void
    {
        $componente = new ReportesAdministrativos;
        $vacio = $componente->render()->getData();
        $this->assertSame(0, $vacio['metricas']['Inscripciones']);
        $this->assertTrue($vacio['estadosInscripcion']->isEmpty());
        $this->assertTrue($vacio['actividad']->isEmpty());
        $this->assertSame('Requiere atención', $vacio['diagnostico']['estado']);
        DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'I1', 'est_ins' => 'ACTIVA']);
        $actualizado = $componente->render()->getData();
        $this->assertSame(1, $actualizado['metricas']['Inscripciones']);
        $this->assertSame(1, $actualizado['estadosInscripcion']->get('ACTIVA'));
    }
}
