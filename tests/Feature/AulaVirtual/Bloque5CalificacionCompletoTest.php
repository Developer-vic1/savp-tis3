<?php

namespace Tests\Feature\AulaVirtual;

use App\Models\AulaVirtual\CalificacionTarea;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Bloque5CalificacionCompletoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected ClaseVirtual $claseA;
    protected Tarea $tareaA;
    protected EntregaTarea $entregaA;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        Permission::findOrCreate('Aula_Virtual_Docente', 'web');
        Permission::findOrCreate('Aula_Virtual_Estudiante', 'web');

        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_2SEC'],
            ['nom_cur' => '2do de Secundaria', 'est_cur' => 'ACTIVO']
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
            ['cod_asi' => 'ASI_HIS'],
            ['nom_asi' => 'Historia', 'est_asi' => 'ACTIVO']
        );
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        // Docente A
        $personaDocA = Persona::create([
            'nom_per' => 'Jaime',
            'ape_pat_per' => 'Jauregui',
            'ci_per' => '5110001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1980-10-10',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_CAL_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteA = Docente::create([
            'cod_doc' => 'DOC_CAL_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'HISTORIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_CAL_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.cal.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Docente B
        $personaDocB = Persona::create([
            'nom_per' => 'Kevin',
            'ape_pat_per' => 'Kholer',
            'ci_per' => '5110002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1981-11-11',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_CAL_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_CAL_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'HISTORIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_CAL_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.cal.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Clase A y Tarea A
        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_HIS_2A'],
            [
                'cod_asi' => 'ASI_HIS',
                'cod_doc' => $docenteA->cod_doc,
                'cod_cur' => 'CUR_2SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_HIS_2A'],
            ['cod_pas' => 'PAS_HIS_2A', 'nom_cla' => 'Historia 2do A', 'est_cla' => 'ACTIVA']
        );
        $this->tareaA = Tarea::create([
            'cod_tar' => 'TAR_HIS_01',
            'cod_cla' => $this->claseA->cod_cla,
            'cod_doc' => $docenteA->cod_doc,
            'tit_tar' => 'Revoluciones del Siglo XIX',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(3),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        // Estudiante
        $personaEst = Persona::create([
            'nom_per' => 'Laura',
            'ape_pat_per' => 'Lara',
            'ci_per' => '5110003',
            'exp_per' => 'LP',
            'fec_nac_per' => '2011-12-12',
            'est_per' => true,
        ]);
        $est = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_CAL_01'],
            ['rud_est' => 'RUDE511000301', 'cod_per' => $personaEst->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_CAL_01'],
            ['cod_cla' => $this->claseA->cod_cla, 'cod_est' => $est->cod_est, 'est_cla_est' => 'ACTIVO']
        );

        // Entrega existente
        $this->entregaA = EntregaTarea::create([
            'cod_ent' => 'ENT_CAL_01',
            'cod_tar' => $this->tareaA->cod_tar,
            'cod_est' => $est->cod_est,
            'tex_ent' => 'Mi análisis sobre los próceres.',
            'fec_ent' => now(),
            'est_ent' => 'ENTREGADO',
        ]);
    }

    public function test_ruta_real_revisar_permite_titular(): void
    {
        $response = $this->actingAs($this->docenteUserA)->get(route('aula-virtual.docente.tareas.revisar', $this->tareaA->cod_tar));
        $response->assertStatus(200);
        $response->assertSee('Revoluciones del Siglo XIX');
    }

    public function test_ruta_real_revisar_bloquea_docente_ajeno(): void
    {
        $response = $this->actingAs($this->docenteUserB)->get(route('aula-virtual.docente.tareas.revisar', $this->tareaA->cod_tar));
        $response->assertStatus(403);
    }

    public function test_calificar_entrega_servicio_valido(): void
    {
        $response = $this->actingAs($this->docenteUserA)->post(route('aula-virtual.docente.entregas.calificar', $this->entregaA->cod_ent), [
            'pun_obt' => 95,
            'com_cal' => 'Excelente trabajo y fuentes.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('calificacion_tarea', [
            'cod_ent' => $this->entregaA->cod_ent,
            'pun_obt' => 95,
            'est_cal' => 'REGISTRADO',
        ]);

        $this->assertDatabaseHas('entrega_tarea', [
            'cod_ent' => $this->entregaA->cod_ent,
            'est_ent' => 'CALIFICADO',
        ]);
    }

    public function test_calificar_con_nota_mayor_al_maximo_es_rechazada(): void
    {
        $response = $this->actingAs($this->docenteUserA)->post(route('aula-virtual.docente.entregas.calificar', $this->entregaA->cod_ent), [
            'pun_obt' => 150, // Mayor a 100
            'com_cal' => 'Nota fuera de rango',
        ]);

        $response->assertSessionHasErrors(['puntaje']);
    }

    public function test_devolver_entrega_actualiza_estado(): void
    {
        $response = $this->actingAs($this->docenteUserA)->post(route('aula-virtual.docente.entregas.devolver', $this->entregaA->cod_ent), [
            'obs_ent' => 'Por favor amplía la conclusión del tema.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('entrega_tarea', [
            'cod_ent' => $this->entregaA->cod_ent,
            'est_ent' => 'DEVUELTO',
            'obs_ent' => 'Por favor amplía la conclusión del tema.',
        ]);
    }
}
