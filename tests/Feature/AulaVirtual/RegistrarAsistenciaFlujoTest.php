<?php

namespace Tests\Feature\AulaVirtual;

use App\Livewire\AulaVirtual\Asistencia\RegistrarAsistencia;
use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
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

class RegistrarAsistenciaFlujoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUser;
    protected string $codCla;
    protected string $codEst;
    protected string $codEstAsi;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear Estado Asistencia
        $this->codEstAsi = 'EST_ASI_P';
        EstadoAsistencia::updateOrCreate(
            ['cod_est_asi' => $this->codEstAsi],
            [
                'nom_est_asi' => 'Presente',
                'abr_est_asi' => 'P',
                'est_est_asi' => 'ACTIVO',
                'valor_porcentual' => 100,
            ]
        );

        // 0. Spatie Permissions
        \Spatie\Permission\Models\Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        \Spatie\Permission\Models\Permission::findOrCreate('Aula_Virtual_Docente', 'web');

        // 2. Crear Persona y Usuario Docente
        $personaDoc = Persona::create([
            'nom_per' => 'Profesor',
            'ape_pat_per' => 'Mamani',
            'ci_per' => '7770001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1985-03-10',
            'est_per' => true,
        ]);

        $pin = PersonalInstitucional::create([
            'cod_pin' => 'PIN_TEST_01',
            'cod_per' => $personaDoc->cod_per,
            'car_pin' => 'DOCENTE DE SECUNDARIA',
            'est_pin' => 'ACTIVO',
        ]);

        $docente = Docente::create([
            'cod_doc' => 'DOC_TEST_01',
            'cod_pin' => $pin->cod_pin,
            'esp_doc' => 'MATEMATICAS',
            'est_doc' => 'ACTIVO',
        ]);

        $this->docenteUser = User::create([
            'cod_usu' => 'USU_DOC_01',
            'cod_per' => $personaDoc->cod_per,
            'email' => 'profesor.mamani@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUser->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // 3. Crear Gestión, Curso, Paralelo, Turno, Asignatura, Plan y Clase Virtual
        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_1SEC'],
            ['nom_cur' => '1ro de Secundaria', 'est_cur' => 'ACTIVO']
        );
        DB::table('paralelo')->updateOrInsert(
            ['cod_par' => 'PAR_A'],
            ['nom_par' => 'Paralelo A', 'est_par' => 'ACTIVO']
        );
        DB::table('turno')->updateOrInsert(
            ['cod_tur' => 'TUR_MAN'],
            ['nom_tur' => 'Mañana', 'hor_ini_tur' => '07:30:00', 'hor_fin_tur' => '13:00:00', 'est_tur' => 'ACTIVO']
        );
        DB::table('asignatura')->updateOrInsert(
            ['cod_asi' => 'ASI_MAT'],
            ['nom_asi' => 'Matemática', 'est_asi' => 'ACTIVO']
        );

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_MAT_1A'],
            [
                'cod_asi' => 'ASI_MAT',
                'cod_doc' => $docente->cod_doc,
                'cod_cur' => 'CUR_1SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 5,
                'est_pas' => 'ACTIVO',
            ]
        );

        $this->codCla = 'CLA_MAT_1A';
        ClaseVirtual::updateOrCreate(
            ['cod_cla' => $this->codCla],
            [
                'cod_pas' => 'PAS_MAT_1A',
                'nom_cla' => 'Matemática 1ro A 2026',
                'est_cla' => 'ACTIVA',
            ]
        );

        // 4. Crear Estudiante matriculado en la clase
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        $personaEst = Persona::create([
            'nom_per' => 'David',
            'ape_pat_per' => 'Condori',
            'ci_per' => '7770002',
            'exp_per' => 'LP',
            'fec_nac_per' => '2012-05-15',
            'est_per' => true,
        ]);

        $this->codEst = 'EST_TEST_02';
        Estudiante::updateOrCreate(
            ['cod_est' => $this->codEst],
            [
                'rud_est' => 'RUDE777000201',
                'cod_per' => $personaEst->cod_per,
                'cod_tve' => 'TVE_REG',
                'est_est' => 'ACTIVO',
            ]
        );

        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_TEST_01'],
            ['cod_cla' => $this->codCla, 'cod_est' => $this->codEst, 'est_cla_est' => 'ACTIVO']
        );
    }

    public function test_fecha_futura_es_rechazada_por_validacion(): void
    {
        $this->actingAs($this->docenteUser);

        Livewire::test(RegistrarAsistencia::class, ['codCla' => $this->codCla])
            ->set('fecha', Carbon::tomorrow()->format('Y-m-d'))
            ->assertHasErrors(['fecha' => 'before_or_equal']);
    }

    public function test_flujo_completo_registro_asistencia_exitoso(): void
    {
        $this->actingAs($this->docenteUser);

        $fechaHoy = Carbon::today()->format('Y-m-d');

        Livewire::test(RegistrarAsistencia::class, ['codCla' => $this->codCla])
            ->set('fecha', $fechaHoy)
            ->call('marcarTodosPresentes')
            ->assertSet("asistencias.{$this->codEst}", $this->codEstAsi)
            ->call('guardarAsistencia')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('asistencia_clase', [
            'cod_cla' => $this->codCla,
            'fec_asi_cla' => $fechaHoy,
            'est_asi_cla' => 'CERRADA',
        ]);
    }
}
