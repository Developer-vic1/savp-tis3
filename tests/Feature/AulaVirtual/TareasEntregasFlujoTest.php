<?php

namespace Tests\Feature\AulaVirtual;

use App\Livewire\AulaVirtual\Tareas\CalificarEntrega;
use App\Livewire\AulaVirtual\Tareas\CrearTarea;
use App\Livewire\AulaVirtual\Tareas\EntregarTarea;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class TareasEntregasFlujoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUser;
    protected User $estudianteUser;
    protected string $codCla;
    protected string $codDoc;
    protected string $codEst;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Docente
        $personaDoc = Persona::create([
            'nom_per' => 'Elena',
            'ape_pat_per' => 'Rios',
            'ci_per' => '6660001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1987-05-12',
            'est_per' => true,
        ]);

        $pin = PersonalInstitucional::create([
            'cod_pin' => 'PIN_TEST_02',
            'cod_per' => $personaDoc->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);

        $docente = Docente::create([
            'cod_doc' => 'DOC_TEST_02',
            'cod_pin' => $pin->cod_pin,
            'esp_doc' => 'LITERATURA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->codDoc = $docente->cod_doc;

        $this->docenteUser = User::create([
            'cod_usu' => 'USU_DOC_02',
            'cod_per' => $personaDoc->cod_per,
            'email' => 'elena.rios@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        // 2. Estudiante
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        $personaEst = Persona::create([
            'nom_per' => 'Gabriel',
            'ape_pat_per' => 'Torres',
            'ci_per' => '6660002',
            'exp_per' => 'LP',
            'fec_nac_per' => '2011-09-18',
            'est_per' => true,
        ]);

        $this->codEst = 'EST_TEST_03';
        Estudiante::updateOrCreate(
            ['cod_est' => $this->codEst],
            [
                'rud_est' => 'RUDE666000201',
                'cod_per' => $personaEst->cod_per,
                'cod_tve' => 'TVE_REG',
                'est_est' => 'ACTIVO',
            ]
        );

        $this->estudianteUser = User::create([
            'cod_usu' => 'USU_EST_02',
            'cod_per' => $personaEst->cod_per,
            'email' => 'gabriel.torres@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        // 3. Estructura Académica y Clase
        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_2SEC'],
            ['nom_cur' => '2do de Secundaria', 'est_cur' => 'ACTIVO']
        );
        DB::table('paralelo')->updateOrInsert(
            ['cod_par' => 'PAR_B'],
            ['nom_par' => 'Paralelo B', 'est_par' => 'ACTIVO']
        );
        DB::table('turno')->updateOrInsert(
            ['cod_tur' => 'TUR_TAR'],
            ['nom_tur' => 'Tarde', 'hor_ini_tur' => '13:30:00', 'hor_fin_tur' => '18:30:00', 'est_tur' => 'ACTIVO']
        );
        DB::table('asignatura')->updateOrInsert(
            ['cod_asi' => 'ASI_LIT'],
            ['nom_asi' => 'Lengua Castellana', 'est_asi' => 'ACTIVO']
        );

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_LIT_2B'],
            [
                'cod_asi' => 'ASI_LIT',
                'cod_doc' => $this->codDoc,
                'cod_cur' => 'CUR_2SEC',
                'cod_par' => 'PAR_B',
                'cod_tur' => 'TUR_TAR',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );

        $this->codCla = 'CLA_LIT_2B';
        ClaseVirtual::updateOrCreate(
            ['cod_cla' => $this->codCla],
            [
                'cod_pas' => 'PAS_LIT_2B',
                'nom_cla' => 'Lengua Castellana 2do B',
                'est_cla' => 'ACTIVA',
            ]
        );

        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_TEST_02'],
            ['cod_cla' => $this->codCla, 'cod_est' => $this->codEst, 'est_cla_est' => 'ACTIVO']
        );
    }

    public function test_crear_tarea_exitoso(): void
    {
        $this->actingAs($this->docenteUser);

        Livewire::test(CrearTarea::class, ['codCla' => $this->codCla])
            ->set('titulo', 'Ensayo sobre Literatura Boliviana')
            ->set('descripcion', 'Escribir un ensayo crítico de 2 páginas.')
            ->set('puntajeMaximo', 100)
            ->set('fechaPublicacion', now()->format('Y-m-d\TH:i'))
            ->set('fechaLimite', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->call('guardarTarea')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('tarea', [
            'cod_cla' => $this->codCla,
            'tit_tar' => 'Ensayo sobre Literatura Boliviana',
            'pun_max_tar' => 100,
        ]);
    }

    public function test_entregar_y_calificar_tarea_flujo(): void
    {
        // 1. Crear Tarea directa
        $tarea = Tarea::create([
            'cod_tar' => 'TAR_TEST_01',
            'cod_cla' => $this->codCla,
            'cod_doc' => $this->codDoc,
            'tit_tar' => 'Análisis de Cuento',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(3),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        // 2. Estudiante realiza entrega
        $this->actingAs($this->estudianteUser);

        Livewire::test(EntregarTarea::class, ['codTar' => $tarea->cod_tar])
            ->set('texto', 'Adjunto mi análisis completo sobre el autor y personajes.')
            ->call('enviarEntrega')
            ->assertDispatched('success-general');

        $entrega = EntregaTarea::where('cod_tar', $tarea->cod_tar)
            ->where('cod_est', $this->codEst)
            ->first();

        $this->assertNotNull($entrega);
        $this->assertSame('ENTREGADO', $entrega->est_ent);

        // 3. Docente califica entrega
        $this->actingAs($this->docenteUser);

        Livewire::test(CalificarEntrega::class, ['codEnt' => $entrega->cod_ent])
            ->set('puntaje', 90)
            ->set('retroalimentacion', 'Excelente capacidad de síntesis y redacción.')
            ->call('guardarCalificacion')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('calificacion_tarea', [
            'cod_ent' => $entrega->cod_ent,
            'pun_obt' => 90,
        ]);

        $this->assertDatabaseHas('entrega_tarea', [
            'cod_ent' => $entrega->cod_ent,
            'est_ent' => 'CALIFICADO',
        ]);
    }
}
