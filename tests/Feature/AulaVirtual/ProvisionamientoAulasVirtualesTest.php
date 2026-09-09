<?php

namespace Tests\Feature\AulaVirtual;

use App\Livewire\Admin\GestionCurso;
use App\Models\Asignatura;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\Tarea;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\Horario;
use App\Models\HorarioBloque;
use App\Models\HorarioDetalle;
use App\Models\InscripcionEstudiante;
use App\Models\InscripcionVigencia;
use App\Models\Paralelo;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\PlantillaHoraria;
use App\Models\Turno;
use App\Models\User;
use App\Services\AulaVirtual\AulaVirtualProvisioningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProvisionamientoAulasVirtualesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $unauthorizedUser;

    protected GestionAcademica $gestion;

    protected Curso $curso;

    protected Paralelo $paralelo;

    protected Turno $turno;

    protected Asignatura $asignatura;

    protected EspecialidadTecnica $especialidad;

    protected Docente $docente;

    protected PlanAsignatura $planAsignatura;

    protected PlanEspecialidad $planEspecialidad;

    protected Horario $horario;

    protected HorarioBloque $bloque1;

    protected HorarioBloque $bloque2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Permisos Spatie
        Permission::findOrCreate('Gestion_Academica', 'web');
        Permission::findOrCreate('Cursos', 'web');
        Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        Permission::findOrCreate('Aula_Virtual_Admin', 'web');

        // 2. Gestión Académica
        $this->gestion = GestionAcademica::firstOrCreate(
            ['cod_gea' => 'GEA_PROV_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );

        // 3. Estructura Académica Base
        $this->curso = Curso::firstOrCreate(
            ['cod_cur' => 'CUR_PROV_1'],
            ['nom_cur' => '1ro de Secundaria', 'niv_cur' => 'Secundaria', 'est_cur' => 'ACTIVO']
        );

        $this->paralelo = Paralelo::firstOrCreate(
            ['cod_par' => 'PAR_PROV_A'],
            ['nom_par' => 'A', 'est_par' => 'ACTIVO']
        );

        $this->turno = Turno::firstOrCreate(
            ['cod_tur' => 'TUR_PROV_M'],
            ['nom_tur' => 'Mañana', 'hor_ini_tur' => '07:30:00', 'hor_fin_tur' => '13:00:00', 'est_tur' => 'ACTIVO']
        );

        $this->asignatura = Asignatura::firstOrCreate(
            ['cod_asi' => 'ASI_PROV_MAT'],
            ['nom_asi' => 'Matemática', 'sig_asi' => 'MAT', 'hor_asi' => 5, 'est_asi' => 'ACTIVO']
        );

        $this->especialidad = EspecialidadTecnica::firstOrCreate(
            ['cod_esp' => 'ESP_PROV_SIS'],
            ['nom_esp' => 'Sistemas Informáticos', 'des_esp' => 'Especialidad técnica BTH', 'est_esp' => 'ACTIVO']
        );

        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        // 4. Docente
        $personaDoc = Persona::create([
            'nom_per' => 'Roberto',
            'ape_pat_per' => 'Flores',
            'ape_mat_per' => 'Mamani',
            'ci_per' => '8881234',
            'exp_per' => 'LP',
            'fec_nac_per' => '1985-05-15',
            'tel_per' => '71234567',
            'est_per' => true,
        ]);

        $pinDoc = PersonalInstitucional::create([
            'cod_pin' => 'PIN_PROV_01',
            'cod_per' => $personaDoc->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);

        $this->docente = Docente::create([
            'cod_doc' => 'DOC_PROV_01',
            'cod_pin' => $pinDoc->cod_pin,
            'esp_doc' => 'MATEMATICA',
            'est_doc' => 'ACTIVO',
        ]);

        // 5. Usuario Administrador con permisos
        $personaAdmin = Persona::create([
            'nom_per' => 'Admin',
            'ape_pat_per' => 'Principal',
            'ci_per' => '8880001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1980-01-01',
            'est_per' => true,
        ]);

        $this->adminUser = User::create([
            'cod_usu' => 'USU_ADMIN_PROV',
            'cod_per' => $personaAdmin->cod_per,
            'email' => 'admin.prov@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->adminUser->givePermissionTo(['Gestion_Academica', 'Cursos', 'Acceso_Aula_Virtual']);

        // Usuario no autorizado
        $personaNoAuth = Persona::create([
            'nom_per' => 'Sin',
            'ape_pat_per' => 'Permisos',
            'ci_per' => '8880002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1990-01-01',
            'est_per' => true,
        ]);

        $this->unauthorizedUser = User::create([
            'cod_usu' => 'USU_NOAUTH_PROV',
            'cod_per' => $personaNoAuth->cod_per,
            'email' => 'noauth.prov@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        // 6. Planes Académicos
        $this->planAsignatura = PlanAsignatura::create([
            'cod_pas' => 'PAS_PROV_MAT1A',
            'cod_asi' => $this->asignatura->cod_asi,
            'cod_doc' => $this->docente->cod_doc,
            'cod_cur' => $this->curso->cod_cur,
            'cod_par' => $this->paralelo->cod_par,
            'cod_tur' => $this->turno->cod_tur,
            'cod_gea' => $this->gestion->cod_gea,
            'hor_pas' => 5,
            'est_pas' => 'ACTIVO',
        ]);

        $this->planEspecialidad = PlanEspecialidad::create([
            'cod_pes' => 'PES_PROV_SIS1A',
            'cod_esp' => $this->especialidad->cod_esp,
            'cod_doc' => $this->docente->cod_doc,
            'cod_cur' => $this->curso->cod_cur,
            'cod_par' => $this->paralelo->cod_par,
            'cod_tur' => $this->turno->cod_tur,
            'cod_gea' => $this->gestion->cod_gea,
            'hor_pes' => 4,
            'est_pes' => 'ACTIVO',
        ]);

        // 7. Plantilla y Horario
        $plantilla = PlantillaHoraria::firstOrCreate(
            ['cod_pho' => 'PHO_PROV_01'],
            ['nom_pho' => 'Plantilla Secundaria Mañana', 'cod_tur' => $this->turno->cod_tur, 'est_pho' => true]
        );

        $this->bloque1 = HorarioBloque::firstOrCreate(
            ['cod_hbl' => 'HBL_PROV_01'],
            [
                'cod_pho' => $plantilla->cod_pho,
                'num_hbl' => 1,
                'hor_ini_hbl' => '08:00:00',
                'hor_fin_hbl' => '08:45:00',
                'tip_hbl' => 'CLASE',
                'est_hbl' => 'ACTIVO',
            ]
        );

        $this->bloque2 = HorarioBloque::firstOrCreate(
            ['cod_hbl' => 'HBL_PROV_02'],
            [
                'cod_pho' => $plantilla->cod_pho,
                'num_hbl' => 2,
                'hor_ini_hbl' => '08:45:00',
                'hor_fin_hbl' => '09:30:00',
                'tip_hbl' => 'CLASE',
                'est_hbl' => 'ACTIVO',
            ]
        );

        $this->horario = Horario::create([
            'cod_hor' => 'HOR_PROV_01',
            'cod_gea' => $this->gestion->cod_gea,
            'cod_cur' => $this->curso->cod_cur,
            'cod_par' => $this->paralelo->cod_par,
            'cod_pho' => $plantilla->cod_pho,
            'est_hor' => 'ACTIVO',
        ]);

        // Asociar bloques en HorarioDetalle para planAsignatura y planEspecialidad
        HorarioDetalle::create([
            'cod_hde' => 'HDE_PROV_01',
            'cod_hor' => $this->horario->cod_hor,
            'cod_hbl' => $this->bloque1->cod_hbl,
            'dia_hde' => 'LUNES',
            'cod_pas' => $this->planAsignatura->cod_pas,
            'cod_pes' => null,
            'est_hde' => 'ACTIVO',
        ]);

        HorarioDetalle::create([
            'cod_hde' => 'HDE_PROV_02',
            'cod_hor' => $this->horario->cod_hor,
            'cod_hbl' => $this->bloque2->cod_hbl,
            'dia_hde' => 'MARTES',
            'cod_pas' => null,
            'cod_pes' => $this->planEspecialidad->cod_pes,
            'est_hde' => 'ACTIVO',
        ]);
    }

    public function test_previsualizacion_identifica_planes_programados_nuevos_y_bloqueados(): void
    {
        $service = app(AulaVirtualProvisioningService::class);

        // Crear un plan huérfano sin horario para verificar clasificación de bloqueo
        $planHuerfano = PlanAsignatura::create([
            'cod_pas' => 'PAS_PROV_HUERFANO',
            'cod_asi' => $this->asignatura->cod_asi,
            'cod_doc' => $this->docente->cod_doc,
            'cod_cur' => $this->curso->cod_cur,
            'cod_par' => $this->paralelo->cod_par,
            'cod_tur' => $this->turno->cod_tur,
            'cod_gea' => $this->gestion->cod_gea,
            'hor_pas' => 4,
            'est_pas' => 'ACTIVO',
        ]);

        $preview = $service->previewForGestion($this->gestion->cod_gea);

        $this->assertTrue($preview['valido']);
        $this->assertEquals(2, $preview['metricas']['planes_programados']);
        $this->assertEquals(2, $preview['metricas']['aulas_nuevas']);
        $this->assertEquals(1, $preview['metricas']['bloqueadas']);
        $this->assertEquals(1, $preview['metricas']['asignaturas']);
        $this->assertEquals(1, $preview['metricas']['especialidades']);

        // Verificar detalle del plan bloqueado
        $itemHuerfano = collect($preview['items'])->firstWhere('codigo_plan', 'PAS_PROV_HUERFANO');
        $this->assertNotNull($itemHuerfano);
        $this->assertEquals('BLOQUEADO', $itemHuerfano['estado_provision']);
        $this->assertContains('El plan no tiene horario asignado en la matriz semanal.', $itemHuerfano['bloqueos']);
    }

    public function test_provisionamiento_crea_aulas_para_plan_asignatura_y_plan_especialidad(): void
    {
        $service = app(AulaVirtualProvisioningService::class);

        $resultado = $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);

        $this->assertTrue($resultado['exito'], $resultado['mensaje'] ?? 'Error desconocido');
        $this->assertEquals(2, $resultado['creadas']);

        // Verificar que se crearon en la BD
        $claseAsignatura = ClaseVirtual::where('cod_pas', $this->planAsignatura->cod_pas)->first();
        $this->assertNotNull($claseAsignatura);
        $this->assertEquals('Matemática - 1.º A', $claseAsignatura->nom_cla);
        $this->assertEquals('ACTIVA', $claseAsignatura->est_cla);
        $this->assertFalse($claseAsignatura->vis_cla, 'El aula debe crearse inicialmente oculta');

        $claseEspecialidad = ClaseVirtual::where('cod_pes', $this->planEspecialidad->cod_pes)->first();
        $this->assertNotNull($claseEspecialidad);
        $this->assertEquals('Sistemas Informáticos - 1.º A', $claseEspecialidad->nom_cla);
        $this->assertEquals('ACTIVA', $claseEspecialidad->est_cla);
        $this->assertFalse($claseEspecialidad->vis_cla, 'El aula técnica debe crearse inicialmente oculta');
    }

    public function test_proceso_es_completamente_idempotente(): void
    {
        $service = app(AulaVirtualProvisioningService::class);

        // Primera ejecución
        $res1 = $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);
        $this->assertEquals(2, $res1['creadas']);

        $conteoTotal = ClaseVirtual::count();

        // Segunda ejecución inmediata
        $res2 = $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);
        $this->assertTrue($res2['exito']);
        $this->assertEquals(0, $res2['creadas']);
        $this->assertEquals(2, $res2['existentes']);

        // No debe haberse creado ninguna fila duplicada
        $this->assertEquals($conteoTotal, ClaseVirtual::count());
    }

    public function test_sincronizacion_no_altera_tareas_ni_materiales_existentes(): void
    {
        $service = app(AulaVirtualProvisioningService::class);

        // Provisionar clase
        $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);
        $clase = ClaseVirtual::where('cod_pas', $this->planAsignatura->cod_pas)->firstOrFail();

        // Crear una tarea y un material dentro del aula
        $tarea = Tarea::create([
            'cod_tar' => 'TAR_PROV_01',
            'cod_cla' => $clase->cod_cla,
            'cod_doc' => $this->docente->cod_doc,
            'tit_tar' => 'Tarea de Ecuaciones',
            'des_tar' => 'Resolver ejercicios de la página 45.',
            'fec_lim_tar' => '2026-06-30 23:59:00',
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        $material = MaterialClase::create([
            'cod_mat' => 'MAT_PROV_01',
            'cod_cla' => $clase->cod_cla,
            'cod_usu' => $this->adminUser->cod_usu,
            'nom_mat' => 'Guía de Polinomios',
            'tip_mat' => 'ENLACE',
            'url_mat' => 'https://example.com/guia',
            'est_mat' => 'ACTIVO',
        ]);

        // Volver a provisionar
        $resSync = $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);
        $this->assertTrue($resSync['exito']);

        // Comprobar que la tarea y el material siguen existiendo intactos
        $this->assertDatabaseHas('tarea', ['cod_tar' => 'TAR_PROV_01', 'tit_tar' => 'Tarea de Ecuaciones']);
        $this->assertDatabaseHas('material_clase', ['cod_mat' => 'MAT_PROV_01', 'nom_mat' => 'Guía de Polinomios']);
    }

    public function test_bloqueo_de_plan_con_docente_inactivo(): void
    {
        // Poner docente en estado inactivo
        $this->docente->update(['est_doc' => 'INACTIVO']);

        $service = app(AulaVirtualProvisioningService::class);
        $preview = $service->previewForGestion($this->gestion->cod_gea);

        $item = collect($preview['items'])->firstWhere('codigo_plan', $this->planAsignatura->cod_pas);
        $this->assertEquals('BLOQUEADO', $item['estado_provision']);
        $this->assertContains('El docente asignado se encuentra inactivo.', $item['bloqueos']);
    }

    public function test_bloqueo_de_plan_con_curso_o_paralelo_inactivo(): void
    {
        // Poner curso en estado inactivo
        $this->curso->update(['est_cur' => 'INACTIVO']);

        $service = app(AulaVirtualProvisioningService::class);
        $preview = $service->previewForGestion($this->gestion->cod_gea);

        $item = collect($preview['items'])->firstWhere('codigo_plan', $this->planAsignatura->cod_pas);
        $this->assertEquals('BLOQUEADO', $item['estado_provision']);
        $this->assertContains('El curso asignado se encuentra inactivo.', $item['bloqueos']);
    }

    public function test_matricula_automatica_de_estudiantes_en_clase_estudiante(): void
    {
        // Crear estudiante inscrito en el curso y paralelo de la gestión
        $personaEst = Persona::create([
            'nom_per' => 'Lucas',
            'ape_pat_per' => 'Vargas',
            'ci_per' => '8883333',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-03-20',
            'est_per' => true,
        ]);

        $tipoVinc = DB::table('tipo_vinculacion_estudiante')->first();
        $codTve = $tipoVinc ? $tipoVinc->cod_tve : 'TVE_REG';

        $estudiante = Estudiante::create([
            'cod_est' => 'EST_PROV_01',
            'rud_est' => '80730000001',
            'cod_per' => $personaEst->cod_per,
            'cod_tve' => $codTve,
            'est_est' => 'ACTIVO',
        ]);

        $inscripcion = InscripcionEstudiante::create([
            'cod_ins' => 'INS_PROV_01',
            'cod_est' => $estudiante->cod_est,
            'cod_gea' => $this->gestion->cod_gea,
            'cod_cur' => $this->curso->cod_cur,
            'cod_par' => $this->paralelo->cod_par,
            'cod_tur' => $this->turno->cod_tur,
            'fei_ins' => '2026-02-01',
            'est_ins' => 'CONFIRMADA',
        ]);
        InscripcionVigencia::create($inscripcion->only(['cod_ins', 'cod_cur', 'cod_par', 'cod_tur']) + [
            'cod_ivg' => 'IVG_PROV_01', 'fii_ivg' => '2026-02-01', 'est_ivg' => 'ACTIVA', 'tip_ivg' => 'INICIAL',
        ]);

        $service = app(AulaVirtualProvisioningService::class);
        $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);

        $clase = ClaseVirtual::where('cod_pas', $this->planAsignatura->cod_pas)->firstOrFail();

        // Verificar que el estudiante fue matriculado automáticamente en clase_estudiante
        $this->assertDatabaseHas('clase_estudiante', [
            'cod_cla' => $clase->cod_cla,
            'cod_est' => $estudiante->cod_est,
            'est_cla_est' => 'ACTIVO',
        ]);
    }

    public function test_registro_en_bitacora_al_provisionar(): void
    {
        $service = app(AulaVirtualProvisioningService::class);
        $service->provisionForGestion($this->gestion->cod_gea, $this->adminUser);

        $this->assertDatabaseHas('bitacora', [
            'acc_bit' => 'CREAR_AULAS_VIRTUALES',
            'tab_bit' => 'clase_virtual',
            'reg_bit' => $this->gestion->cod_gea,
        ]);
    }

    public function test_integracion_livewire_modal_y_ejecucion(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(GestionCurso::class)
            ->set('gestionFiltro', $this->gestion->cod_gea)
            ->call('abrirModalProvisionamiento')
            ->assertSet('modalProvisionamiento', true)
            ->assertSee('Preparar aulas virtuales desde horarios')
            ->assertSee('Matemática')
            ->assertSee('Sistemas Informáticos')
            ->call('ejecutarProvisionamiento')
            ->assertDispatched('swal:alert')
            ->assertDispatched('success-general');

        // Confirmar creación en BD
        $this->assertDatabaseHas('clase_virtual', ['cod_pas' => $this->planAsignatura->cod_pas]);
        $this->assertDatabaseHas('clase_virtual', ['cod_pes' => $this->planEspecialidad->cod_pes]);
    }

    public function test_usuario_sin_permiso_no_puede_ejecutar_provisionamiento(): void
    {
        $this->actingAs($this->unauthorizedUser);

        Livewire::test(GestionCurso::class)
            ->call('abrirModalProvisionamiento')
            ->assertForbidden();
    }
}
