<?php

namespace Tests\Feature;

use App\Support\Evaluacion\CuadroHonorAcademico;
use App\Support\Evaluacion\PanelResultadosAcademicos;
use App\Support\Evaluacion\RendimientoEstudiantil;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CuadroHonorAcademicoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        $tablas = [
            'persona' => ['cod_per', 'nom_per', 'ape_pat_per', 'ape_mat_per', 'gen_per'],
            'estudiante' => ['cod_est', 'cod_per'],
            'curso' => ['cod_cur', 'nom_cur', 'ord_cur', 'niv_cur'],
            'paralelo' => ['cod_par', 'nom_par'],
            'gestion_academica' => ['cod_gea', 'ani_gea'],
            'inscripcion_estudiante' => ['cod_ins', 'cod_est', 'cod_cur', 'cod_par', 'cod_gea', 'est_ins'],
            'calificacion' => ['cod_ins', 'cod_pev', 'est_cal', 'not_cal', 'obs_cal'],
            'orientacion_actividades' => ['id', 'cod_est', 'cod_gea', 'avance', 'finalizado_at', 'analysis_completed_at', 'analysis_snapshot', 'riasec_score'],
            'resultado_anual' => ['cod_ins', 'est_ran', 'res_ran'],
        ];
        foreach ($tablas as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $estructura) use ($campos) {
                foreach ($campos as $campo) {
                    $estructura->string($campo)->nullable();
                }
            });
        }
        DB::table('curso')->insert([
            ['cod_cur' => 'C1', 'nom_cur' => 'Primero de secundaria', 'ord_cur' => 1, 'niv_cur' => 'SECUNDARIA'],
            ['cod_cur' => 'C6', 'nom_cur' => 'Sexto de secundaria', 'ord_cur' => 6, 'niv_cur' => 'SECUNDARIA'],
            ['cod_cur' => 'P6', 'nom_cur' => 'Sexto de primaria', 'ord_cur' => 6, 'niv_cur' => 'PRIMARIA'],
        ]);
        DB::table('paralelo')->insert([['cod_par' => 'A', 'nom_par' => 'A'], ['cod_par' => 'B', 'nom_par' => 'B']]);
        DB::table('gestion_academica')->insert([['cod_gea' => 'G1', 'ani_gea' => 2025], ['cod_gea' => 'G2', 'ani_gea' => 2026]]);
    }

    private function estudiante(string $codigo, ?float $nota, string $curso = 'C1', string $gestion = 'G2', string $genero = 'F', string $estado = 'ACTIVA', string $paralelo = 'A'): void
    {
        DB::table('persona')->insert(['cod_per' => $codigo, 'nom_per' => $codigo, 'ape_pat_per' => 'Quispe', 'gen_per' => $genero]);
        DB::table('estudiante')->insert(['cod_est' => $codigo, 'cod_per' => $codigo]);
        DB::table('inscripcion_estudiante')->insert(['cod_ins' => $codigo, 'cod_est' => $codigo, 'cod_cur' => $curso, 'cod_par' => $paralelo, 'cod_gea' => $gestion, 'est_ins' => $estado]);
        if ($nota !== null) {
            DB::table('calificacion')->insert(['cod_ins' => $codigo, 'cod_pev' => 'T1', 'est_cal' => 'VIGENTE', 'not_cal' => $nota]);
        }
    }

    private function resumen(string $gestion = 'G2', bool $orientacion = false): array
    {
        return (new CuadroHonorAcademico(new PanelResultadosAcademicos))->resumen($gestion, $orientacion);
    }

    public function test_tres_mejores_por_grado_reunen_paralelos_e_incluyen_la_promocion(): void
    {
        foreach (['C1', 'C6'] as $curso) {
            foreach ([95, 90, 85, 80] as $indice => $nota) {
                $this->estudiante($curso.$indice, $nota, $curso, paralelo: $indice % 2 === 0 ? 'A' : 'B');
            }
        }
        $grados = $this->resumen()['grados'];
        $this->assertSame(['C1', 'C6'], $grados->pluck('codigo')->all());
        foreach ($grados as $grado) {
            $this->assertSame([$grado['codigo'].'0', $grado['codigo'].'1', $grado['codigo'].'2'], $grado['estudiantes']->pluck('cod_est')->all());
            $this->assertSame([1, 2, 3], $grado['estudiantes']->pluck('puesto')->map(fn ($puesto) => (int) $puesto)->all());
            $this->assertEquals(4, $grado['total']);
        }
    }

    public function test_empates_redondeados_del_tercer_puesto_permanecen_visibles(): void
    {
        foreach (['A' => 99, 'B' => 95, 'C' => 90.004, 'D' => 90.001, 'E' => 89.99] as $codigo => $nota) {
            $this->estudiante($codigo, $nota);
        }
        $alumnos = $this->resumen()['grados']->first()['estudiantes'];
        $this->assertSame(['A', 'B', 'C', 'D'], $alumnos->pluck('cod_est')->all());
        $this->assertSame([1, 2, 3, 3], $alumnos->pluck('puesto')->map(fn ($puesto) => (int) $puesto)->all());
        $this->assertFalse($alumnos[0]->empatado);
        $this->assertTrue($alumnos[2]->empatado);
        $this->assertTrue($alumnos[3]->empatado);
    }

    public function test_excluye_retiros_anulaciones_inscripciones_sin_notas_y_notas_anuladas(): void
    {
        $this->estudiante('VALIDO', 85);
        $this->estudiante('RETIRADO', 100, estado: 'RETIRADA');
        $this->estudiante('ANULADO', 100, estado: 'ANULADA');
        $this->estudiante('SIN_NOTAS', null);
        $this->estudiante('NOTA_ANULADA', 100);
        DB::table('calificacion')->where('cod_ins', 'NOTA_ANULADA')->update(['est_cal' => 'ANULADA']);
        DB::table('calificacion')->insert(['cod_ins' => 'VALIDO', 'cod_pev' => 'T2', 'est_cal' => 'ANULADA', 'not_cal' => 0]);

        $honor = $this->resumen();
        $this->assertSame(['VALIDO'], $honor['grados']->first()['estudiantes']->pluck('cod_est')->all());
        $this->assertEquals(85, $honor['grados']->first()['estudiantes']->first()->promedio);
        $this->assertSame(['VALIDO'], $honor['historial']->first()['estudiantes']->pluck('cod_est')->all());
    }

    public function test_promocion_solo_considera_sexto_secundaria_y_genero_registrado(): void
    {
        $this->estudiante('OTRO_GRADO', 100, genero: 'M');
        $this->estudiante('PRIMARIA', 100, 'P6', genero: 'F');
        $this->estudiante('HOMBRE', 93, 'C6', genero: 'M');
        $this->estudiante('HOMBRE_MENOR', 92, 'C6', genero: 'M');
        $this->estudiante('MUJER', 94, 'C6', genero: 'F');
        $this->estudiante('SIN_GENERO', 100, 'C6', genero: '');
        $resumen = $this->resumen();
        $promocion = $resumen['promocion']['estudiantes'];
        $this->assertEquals(3, $resumen['promocion']['total']);
        $this->assertEqualsCanonicalizing(['HOMBRE', 'MUJER'], $promocion->pluck('cod_est')->all());
        $this->assertSame('Hombre', $promocion->firstWhere('cod_est', 'HOMBRE')->genero);
        $this->assertSame('Mujer', $promocion->firstWhere('cod_est', 'MUJER')->genero);
    }

    public function test_historial_muestra_dos_mejores_por_gestion_y_conserva_empates_independientemente_del_filtro(): void
    {
        foreach (['G1', 'G2'] as $gestion) {
            foreach ([98, 95, 95, 90] as $indice => $nota) {
                $this->estudiante($gestion.$indice, $nota, $indice === 0 ? 'C6' : 'C1', $gestion);
            }
        }
        $honor = $this->resumen('G1');
        $this->assertSame(['G2', 'G1'], $honor['historial']->pluck('gestion')->all());
        foreach ($honor['historial'] as $anio) {
            $this->assertSame([$anio['gestion'].'0', $anio['gestion'].'1', $anio['gestion'].'2'], $anio['estudiantes']->pluck('cod_est')->all());
        }
        $this->assertTrue($honor['grados']->every(fn ($grado) => $grado['estudiantes']->every(fn ($estudiante) => $estudiante->cod_gea === 'G1')));
    }

    public function test_evidencia_sintetica_no_se_presenta_como_cierre_acreditado(): void
    {
        $this->estudiante('SINTETICO', 99);
        $this->estudiante('CIERRE', 95, estado: 'ARCHIVADA');
        $this->estudiante('PARCIAL', 90);
        DB::table('calificacion')->where('cod_ins', 'SINTETICO')->update(['obs_cal' => 'VALOR SINTETICO; DATASET DE VALIDACION']);
        DB::table('resultado_anual')->insert([
            ['cod_ins' => 'SINTETICO', 'est_ran' => 'VIGENTE', 'res_ran' => 'EGRESADO'],
            ['cod_ins' => 'CIERRE', 'est_ran' => 'VIGENTE', 'res_ran' => 'PROMOVIDO'],
        ]);
        $alumnos = $this->resumen()['grados']->first()['estudiantes'];
        $this->assertSame('Validación sintética', $alumnos->firstWhere('cod_est', 'SINTETICO')->estado_honor);
        $this->assertSame('Cierre registrado · requiere revisión', $alumnos->firstWhere('cod_est', 'CIERRE')->estado_honor);
        $this->assertSame('Provisional', $alumnos->firstWhere('cod_est', 'PARCIAL')->estado_honor);
    }

    public function test_gestion_sin_seleccionar_no_mezcla_candidaturas_actuales_y_conserva_historia(): void
    {
        $this->estudiante('HISTORICO', 95, 'C6', 'G1');
        $honor = $this->resumen('');
        $this->assertTrue($honor['grados']->isEmpty());
        $this->assertTrue($honor['promocion']['estudiantes']->isEmpty());
        $this->assertSame(['HISTORICO'], $honor['historial']->first()['estudiantes']->pluck('cod_est')->all());
    }

    public function test_sin_permiso_de_orientacion_no_interpreta_snapshots_ni_muestra_carreras(): void
    {
        $this->estudiante('PROMOCION', 95, 'C6');
        DB::table('orientacion_actividades')->insert(['id' => 1, 'cod_est' => 'PROMOCION', 'cod_gea' => 'G2', 'analysis_completed_at' => '2026-01-01', 'analysis_snapshot' => '{}']);
        $servicio = \Mockery::mock(RendimientoEstudiantil::class);
        $servicio->shouldNotReceive('estudio');
        $this->app->instance(RendimientoEstudiantil::class, $servicio);
        $honor = $this->resumen();
        $this->assertSame([], $honor['promocion']['estudiantes']->first()->carreras);
        $html = view('livewire.admin.calificaciones-honor', compact('honor'))->render();
        $this->assertStringNotContainsString('Opciones de carrera analizadas', $html);
    }

    public function test_orientacion_usa_ultimo_estudio_de_la_gestion_y_exige_contrato_valido(): void
    {
        $this->estudiante('MUJER', 95, 'C6');
        $this->estudiante('HOMBRE', 93, 'C6', genero: 'M');
        DB::table('orientacion_actividades')->insert([
            ['id' => 1, 'cod_est' => 'MUJER', 'cod_gea' => 'G1', 'analysis_completed_at' => '2025-01-01'],
            ['id' => 2, 'cod_est' => 'MUJER', 'cod_gea' => 'G2', 'analysis_completed_at' => '2026-01-01'],
            ['id' => 3, 'cod_est' => 'MUJER', 'cod_gea' => 'G2', 'analysis_completed_at' => '2026-02-01'],
            ['id' => 4, 'cod_est' => 'HOMBRE', 'cod_gea' => 'G2', 'analysis_completed_at' => '2026-02-01'],
        ]);
        $servicio = \Mockery::mock(RendimientoEstudiantil::class);
        $servicio->shouldReceive('estudio')->once()->with(\Mockery::on(fn ($actividad) => (int) $actividad->id === 3 && $actividad->cod_gea === 'G2'))
            ->andReturn(['estado' => 'contrato_valido', 'datos' => ['career_evidence_profiles' => [['career_name' => 'Opción documentada', 'university' => 'Universidad de la fuente']]]]);
        $servicio->shouldReceive('estudio')->once()->with(\Mockery::on(fn ($actividad) => (int) $actividad->id === 4))
            ->andReturn(['estado' => 'contrato_invalido']);
        $this->app->instance(RendimientoEstudiantil::class, $servicio);
        $honor = $this->resumen(orientacion: true);
        $this->assertSame('Opción documentada', $honor['promocion']['estudiantes']->firstWhere('cod_est', 'MUJER')->carreras[0]['career_name']);
        $this->assertSame('01/02/2026', $honor['promocion']['estudiantes']->firstWhere('cod_est', 'MUJER')->fecha);
        $this->assertSame([], $honor['promocion']['estudiantes']->firstWhere('cod_est', 'HOMBRE')->carreras);
    }

    public function test_cuadro_de_honor_renderiza_fichas_y_memoria_sin_tablas(): void
    {
        $this->estudiante('Ana <script>', 95, 'C6');
        $honor = $this->resumen();
        $html = view('livewire.admin.calificaciones-honor', compact('honor'))->render();
        $this->assertStringContainsString('Los tres mejores de cada grado', $html);
        $this->assertStringContainsString('Memoria del cuadro de honor', $html);
        $this->assertStringContainsString('honor-ficha', $html);
        $this->assertStringContainsString('Revisar trayectoria', $html);
        $this->assertStringContainsString('Ana &lt;script&gt;', $html);
        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringNotContainsString('Ana <script>', $html);
    }

    public function test_cuadro_vacio_explica_la_ausencia_de_candidaturas_e_historia(): void
    {
        $honor = $this->resumen();
        $html = view('livewire.admin.calificaciones-honor', compact('honor'))->render();
        $this->assertStringContainsString('Aún no hay candidaturas para mostrar', $html);
        $this->assertStringContainsString('Esta gestión aún no tiene promedios para comparar', $html);
        $this->assertStringContainsString('Sin gestiones con notas para el historial', $html);
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_historial_incluye_inscripciones_archivadas_y_excluye_estados_previos_a_la_matricula(): void
    {
        $this->estudiante('ARCHIVADA', 95, 'C6', 'G1', estado: 'ARCHIVADA');
        foreach (['PENDIENTE', 'CONFIRMADA', 'OBSERVADA'] as $estado) {
            $this->estudiante($estado, 100, 'C6', 'G1', estado: $estado);
        }
        $honor = $this->resumen('G1');
        $this->assertSame(['ARCHIVADA'], $honor['grados']->first()['estudiantes']->pluck('cod_est')->all());
        $this->assertSame(['ARCHIVADA'], $honor['promocion']['estudiantes']->pluck('cod_est')->all());
        $this->assertSame(['ARCHIVADA'], $honor['historial']->first()['estudiantes']->pluck('cod_est')->all());
        $this->assertEquals(1, $honor['grados']->first()['total']);
    }

    private function actor(string $estado, bool $administrador, bool $permiso): void
    {
        $usuario = \Mockery::mock(\App\Models\Oficial\Sistema\User::class)->makePartial();
        $usuario->setRawAttributes(['est_usu' => $estado]);
        $usuario->shouldReceive('hasRole')->with('Administrador')->andReturn($administrador);
        $usuario->shouldReceive('can')->with('Calificaciones')->andReturn($permiso);
        auth()->setUser($usuario);
    }

    public function test_boot_rechaza_actor_inactivo_rol_distinto_y_permiso_revocado(): void
    {
        foreach ([['INACTIVO', true, true], ['ACTIVO', false, true], ['ACTIVO', true, false]] as [$estado, $administrador, $permiso]) {
            $this->actor($estado, $administrador, $permiso);
            try {
                (new \App\Livewire\Admin\Calificaciones)->boot();
                $this->fail('El componente permitió una consulta institucional no autorizada.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->actor('ACTIVO', true, true);
        (new \App\Livewire\Admin\Calificaciones)->boot();
        $this->assertTrue(auth()->user()->can('Calificaciones'));
    }

    public function test_ver_honor_conserva_ambito_anual_y_no_depende_de_filtros_de_busqueda(): void
    {
        $this->estudiante('LOCAL', 95, 'C6');
        $this->estudiante('PASADO', 99, 'C6', 'G1');
        $this->estudiante('RETIRADO', 100, 'C6', estado: 'RETIRADA');
        $this->estudiante('SIN_NOTAS', null, 'C6');
        $componente = new \App\Livewire\Admin\Calificaciones;
        $componente->gestionFiltro = 'G2';
        $componente->search = 'Busqueda ajena al cuadro de honor';
        $componente->gradoFiltro = 'C1';
        $componente->seguimiento = 'sin_notas';
        $componente->verHonor('LOCAL');
        $this->assertSame('LOCAL', $componente->seleccionado);
        $this->assertTrue($componente->detalleHonor);
        foreach (['PASADO', 'RETIRADO', 'SIN_NOTAS', str_repeat('X', 31)] as $codigo) {
            try {
                $componente->verHonor($codigo);
                $this->fail('El detalle de honor permitió una inscripción fuera del ámbito anual válido.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
                $this->assertSame(404, $error->getStatusCode());
            }
        }
    }

    public function test_cambio_de_vista_y_gestion_limpia_detalle_y_filtros(): void
    {
        $componente = new \App\Livewire\Admin\Calificaciones;
        $componente->seleccionado = 'DETALLE';
        $componente->detalleHonor = true;
        $componente->cambiarVista('comparativas');
        $this->assertSame('comparativas', $componente->vista);
        $this->assertNull($componente->seleccionado);
        $this->assertFalse($componente->detalleHonor);
        try {
            $componente->cambiarVista('vista_inexistente');
            $this->fail('El componente aceptó una vista inválida.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(422, $error->getStatusCode());
        }
        $componente->search = 'Filtro';
        $componente->gradoFiltro = 'C1';
        $componente->periodoFiltro = 'T1';
        $componente->seguimiento = 'riesgo';
        $componente->cambiarGestionHonor('G1');
        $this->assertSame('G1', $componente->gestionFiltro);
        $this->assertSame('honor', $componente->vista);
        $this->assertSame('', $componente->search);
        $this->assertSame('', $componente->gradoFiltro);
        $this->assertSame('', $componente->periodoFiltro);
        $this->assertSame('', $componente->seguimiento);
        try {
            $componente->cambiarGestionHonor('INEXISTENTE');
            $this->fail('El componente aceptó una gestión inexistente.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(404, $error->getStatusCode());
        }
    }

    public function test_exportacion_honor_conserva_empates_y_ambito_y_escapa_formulas_sin_consultar_orientacion(): void
    {
        $this->estudiante('F', 96, 'C6');
        $this->estudiante('M1', 95, 'C6', genero: 'M');
        $this->estudiante('M2', 95, 'C6', genero: 'M');
        DB::table('persona')->where('cod_per', 'F')->update(['nom_per' => '=SUM(1)']);
        DB::table('orientacion_actividades')->insert(['id' => 1, 'cod_est' => 'F', 'cod_gea' => 'G2', 'analysis_completed_at' => '2026-01-01', 'analysis_snapshot' => '{}']);
        $servicio = \Mockery::mock(RendimientoEstudiantil::class);
        $servicio->shouldNotReceive('estudio');
        $this->app->instance(RendimientoEstudiantil::class, $servicio);
        $this->actor('ACTIVO', true, true);
        $componente = new \App\Livewire\Admin\Calificaciones;
        $componente->gestionFiltro = 'G2';
        $capturar = function () use ($componente): string {
            $respuesta = $componente->descargarHonor();
            ob_start();
            try {
                ($respuesta->getCallback())();
                return ob_get_contents();
            } finally {
                ob_end_clean();
            }
        };
        $csv = $capturar();
        $lineas = preg_split('/\r\n|\r|\n/', trim($csv));
        $cabecera = str_getcsv(ltrim(array_shift($lineas), "\xEF\xBB\xBF"), ';', '"', '');
        $filas = collect($lineas)->map(fn ($linea) => str_getcsv($linea, ';', '"', ''));
        $this->assertSame('Propuesta académica · requiere revisión', $cabecera[0]);
        $this->assertCount(3, $filas->filter(fn ($fila) => $fila[0] === 'Reconocimiento por grado'));
        $this->assertCount(3, $filas->filter(fn ($fila) => $fila[0] === 'Candidatura de promoción'));
        $this->assertCount(2, $filas->filter(fn ($fila) => $fila[5] === "'=SUM(1) Quispe"));
        $this->assertCount(4, $filas->filter(fn ($fila) => $fila[8] === 'Sí'));
        $this->assertStringNotContainsString('analysis_snapshot', $csv);
        $componente->search = 'Busqueda que no coincide con ningún estudiante';
        $componente->gradoFiltro = 'C1';
        $componente->periodoFiltro = 'PERIODO_INEXISTENTE';
        $componente->seguimiento = 'sin_notas';
        $this->assertSame($csv, $capturar());
    }
}
