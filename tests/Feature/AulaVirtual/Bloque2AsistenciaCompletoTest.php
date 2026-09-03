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
use App\Services\AulaVirtual\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Bloque2AsistenciaCompletoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected User $estudianteUser;
    protected ClaseVirtual $claseA;
    protected string $codEst;
    protected string $codEstAsiPresente;
    protected string $codEstAsiJustificado;

    protected function setUp(): void
    {
        parent::setUp();

        // 0. Spatie Permissions
        Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        Permission::findOrCreate('Aula_Virtual_Docente', 'web');
        Permission::findOrCreate('Aula_Virtual_Estudiante', 'web');

        // 1. Estados Asistencia
        $this->codEstAsiPresente = 'EST_ASI_PRES_TEST';
        EstadoAsistencia::updateOrCreate(
            ['cod_est_asi' => $this->codEstAsiPresente],
            ['nom_est_asi' => 'Presente', 'abr_est_asi' => 'P', 'est_est_asi' => 'ACTIVO', 'valor_porcentual' => 100, 'requiere_observacion' => false]
        );

        $this->codEstAsiJustificado = 'EST_ASI_JUST_TEST';
        EstadoAsistencia::updateOrCreate(
            ['cod_est_asi' => $this->codEstAsiJustificado],
            ['nom_est_asi' => 'Licencia / Justificado', 'abr_est_asi' => 'J', 'est_est_asi' => 'ACTIVO', 'valor_porcentual' => 100, 'requiere_observacion' => true]
        );

        // 2. Estructura Académica
        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_5SEC'],
            ['nom_cur' => '5to de Secundaria', 'est_cur' => 'ACTIVO']
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
            ['cod_asi' => 'ASI_BIO'],
            ['nom_asi' => 'Biología - Geografía', 'est_asi' => 'ACTIVO']
        );

        // 3. Docente A y Docente B
        $personaDocA = Persona::create([
            'nom_per' => 'Daniel',
            'ape_pat_per' => 'Flores',
            'ci_per' => '8880001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1983-07-07',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_ASI_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteA = Docente::create([
            'cod_doc' => 'DOC_ASI_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'BIOLOGIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_ASI_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.asi.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        $personaDocB = Persona::create([
            'nom_per' => 'Esteban',
            'ape_pat_per' => 'Gutierrez',
            'ci_per' => '8880002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1984-08-08',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_ASI_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_ASI_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'BIOLOGIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_ASI_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.asi.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // 4. Plan Asignatura y Clase Virtual A
        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_BIO_5A'],
            [
                'cod_asi' => 'ASI_BIO',
                'cod_doc' => $docenteA->cod_doc,
                'cod_cur' => 'CUR_5SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_BIO_5A'],
            ['cod_pas' => 'PAS_BIO_5A', 'nom_cla' => 'Biología 5to A', 'est_cla' => 'ACTIVA']
        );

        // 5. Estudiante
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );
        $personaEst = Persona::create([
            'nom_per' => 'Camila',
            'ape_pat_per' => 'Castro',
            'ci_per' => '8880003',
            'exp_per' => 'LP',
            'fec_nac_per' => '2009-09-09',
            'est_per' => true,
        ]);
        $this->codEst = 'EST_ASI_01';
        $est = Estudiante::updateOrCreate(
            ['cod_est' => $this->codEst],
            ['rud_est' => 'RUDE888000301', 'cod_per' => $personaEst->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUser = User::create([
            'cod_usu' => 'USU_EST_ASI_01',
            'cod_per' => $personaEst->cod_per,
            'email' => 'estudiante.asi@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->estudianteUser->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante');

        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_ASI_01'],
            ['cod_cla' => $this->claseA->cod_cla, 'cod_est' => $this->codEst, 'est_cla_est' => 'ACTIVO']
        );
    }

    public function test_ruta_real_registrar_asistencia_permite_docente_titular(): void
    {
        $responseA = $this->actingAs($this->docenteUserA)->get(route('aula-virtual.docente.asistencia.registrar', $this->claseA->cod_cla));
        $responseA->assertStatus(200);
        $responseA->assertSee('Biología');
        $responseA->assertSee('5to de Secundaria');
    }

    public function test_ruta_real_registrar_asistencia_bloquea_docente_ajeno(): void
    {
        $responseB = $this->actingAs($this->docenteUserB)->get(route('aula-virtual.docente.asistencia.registrar', $this->claseA->cod_cla));
        $responseB->assertStatus(403);
    }

    public function test_marcar_pendientes_como_presentes_y_guardar_asistencia_servicio(): void
    {
        $this->actingAs($this->docenteUserA);
        $fechaHoy = Carbon::today()->format('Y-m-d');

        Livewire::test(RegistrarAsistencia::class, ['codCla' => $this->claseA->cod_cla])
            ->set('fecha', $fechaHoy)
            ->call('marcarPendientesComoPresentes')
            ->assertSet("asistencias.{$this->codEst}", $this->codEstAsiPresente)
            ->call('guardarAsistencia')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('asistencia_clase', [
            'cod_cla' => $this->claseA->cod_cla,
            'fec_asi_cla' => $fechaHoy,
            'est_asi_cla' => 'CERRADA',
        ]);
    }

    public function test_estado_que_requiere_observacion_obliga_justificacion(): void
    {
        $this->actingAs($this->docenteUserA);
        $fechaHoy = Carbon::today()->format('Y-m-d');

        Livewire::test(RegistrarAsistencia::class, ['codCla' => $this->claseA->cod_cla])
            ->set('fecha', $fechaHoy)
            ->set("asistencias.{$this->codEst}", $this->codEstAsiJustificado)
            ->set("observaciones.{$this->codEst}", '') // Sin justificación
            ->call('guardarAsistencia')
            ->assertDispatched('error-general');

        // Ahora con justificación -> Exitoso
        Livewire::test(RegistrarAsistencia::class, ['codCla' => $this->claseA->cod_cla])
            ->set('fecha', $fechaHoy)
            ->set("asistencias.{$this->codEst}", $this->codEstAsiJustificado)
            ->set("observaciones.{$this->codEst}", 'Permiso médico presentado en secretaría')
            ->call('guardarAsistencia')
            ->assertDispatched('success-general');
    }

    public function test_estudiante_puede_ver_su_asistencia_en_ruta_real(): void
    {
        $response = $this->actingAs($this->estudianteUser)->get(route('aula-virtual.estudiante.asistencia'));
        $response->assertStatus(200);
        $response->assertSee('Mi asistencia');
    }
}
