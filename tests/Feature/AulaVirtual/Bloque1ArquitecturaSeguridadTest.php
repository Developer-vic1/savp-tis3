<?php

namespace Tests\Feature\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class Bloque1ArquitecturaSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected User $estudianteUserA;
    protected User $estudianteUserB;

    protected ClaseVirtual $claseA;
    protected ClaseVirtual $claseB;
    protected Tarea $tareaA;
    protected Tarea $tareaB;

    protected function setUp(): void
    {
        parent::setUp();

        // 0. Permisos Spatie
        \Spatie\Permission\Models\Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        \Spatie\Permission\Models\Permission::findOrCreate('Aula_Virtual_Docente', 'web');
        \Spatie\Permission\Models\Permission::findOrCreate('Aula_Virtual_Estudiante', 'web');

        // 1. Configuración académica base
        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_4SEC'],
            ['nom_cur' => '4to de Secundaria', 'est_cur' => 'ACTIVO']
        );
        DB::table('paralelo')->updateOrInsert(
            ['cod_par' => 'PAR_A'],
            ['nom_par' => 'Paralelo A', 'est_par' => 'ACTIVO']
        );
        DB::table('paralelo')->updateOrInsert(
            ['cod_par' => 'PAR_B'],
            ['nom_par' => 'Paralelo B', 'est_par' => 'ACTIVO']
        );
        DB::table('turno')->updateOrInsert(
            ['cod_tur' => 'TUR_MAN'],
            ['nom_tur' => 'Mañana', 'hor_ini_tur' => '07:30:00', 'hor_fin_tur' => '13:00:00', 'est_tur' => 'ACTIVO']
        );
        DB::table('asignatura')->updateOrInsert(
            ['cod_asi' => 'ASI_QUI'],
            ['nom_asi' => 'Química', 'est_asi' => 'ACTIVO']
        );
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        // 2. Docente A y Clase A
        $personaDocA = Persona::create([
            'nom_per' => 'Armando',
            'ape_pat_per' => 'Paredes',
            'ci_per' => '9000001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1980-01-01',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_SEC_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteA = Docente::create([
            'cod_doc' => 'DOC_SEC_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'QUIMICA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_SEC_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.a@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_QUI_4A'],
            [
                'cod_asi' => 'ASI_QUI',
                'cod_doc' => $docenteA->cod_doc,
                'cod_cur' => 'CUR_4SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_QUI_4A'],
            ['cod_pas' => 'PAS_QUI_4A', 'nom_cla' => 'Química 4to A', 'est_cla' => 'ACTIVA']
        );
        $this->tareaA = Tarea::create([
            'cod_tar' => 'TAR_SEC_A',
            'cod_cla' => $this->claseA->cod_cla,
            'cod_doc' => $docenteA->cod_doc,
            'tit_tar' => 'Laboratorio Reacciones Químicas',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(5),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        // 3. Docente B y Clase B
        $personaDocB = Persona::create([
            'nom_per' => 'Bernardo',
            'ape_pat_per' => 'Quiroga',
            'ci_per' => '9000002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1982-02-02',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_SEC_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_SEC_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'QUIMICA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_SEC_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.b@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_QUI_4B'],
            [
                'cod_asi' => 'ASI_QUI',
                'cod_doc' => $docenteB->cod_doc,
                'cod_cur' => 'CUR_4SEC',
                'cod_par' => 'PAR_B',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseB = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_QUI_4B'],
            ['cod_pas' => 'PAS_QUI_4B', 'nom_cla' => 'Química 4to B', 'est_cla' => 'ACTIVA']
        );
        $this->tareaB = Tarea::create([
            'cod_tar' => 'TAR_SEC_B',
            'cod_cla' => $this->claseB->cod_cla,
            'cod_doc' => $docenteB->cod_doc,
            'tit_tar' => 'Estequiometría y Balanceo',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(5),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        // 4. Estudiante A en Clase A
        $personaEstA = Persona::create([
            'nom_per' => 'Ana',
            'ape_pat_per' => 'Alvarez',
            'ci_per' => '9000003',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-03-03',
            'est_per' => true,
        ]);
        $estA = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_SEC_01'],
            ['rud_est' => 'RUDE900000301', 'cod_per' => $personaEstA->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserA = User::create([
            'cod_usu' => 'USU_EST_SEC_01',
            'cod_per' => $personaEstA->cod_per,
            'email' => 'estudiante.a@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_SEC_01'],
            ['cod_cla' => $this->claseA->cod_cla, 'cod_est' => $estA->cod_est, 'est_cla_est' => 'ACTIVO']
        );

        // 5. Estudiante B en Clase B
        $personaEstB = Persona::create([
            'nom_per' => 'Bruno',
            'ape_pat_per' => 'Benitez',
            'ci_per' => '9000004',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-04-04',
            'est_per' => true,
        ]);
        $estB = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_SEC_02'],
            ['rud_est' => 'RUDE900000401', 'cod_per' => $personaEstB->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserB = User::create([
            'cod_usu' => 'USU_EST_SEC_02',
            'cod_per' => $personaEstB->cod_per,
            'email' => 'estudiante.b@savp.edu.bo',
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_SEC_02'],
            ['cod_cla' => $this->claseB->cod_cla, 'cod_est' => $estB->cod_est, 'est_cla_est' => 'ACTIVO']
        );
    }

    public function test_timezone_y_config_savp_estan_establecidos_correctamente(): void
    {
        $this->assertSame('America/La_Paz', config('app.timezone'));
        $this->assertNotNull(config('savp.aula_virtual.tareas.puntaje_maximo'));
        $this->assertSame(100, config('savp.aula_virtual.tareas.puntaje_maximo'));
        $this->assertSame(10, config('savp.aula_virtual.entregas.tamano_maximo_mb'));
        $this->assertSame('local', config('savp.aula_virtual.entregas.disco_almacenamiento'));
    }

    public function test_resolucion_segura_de_identidad_en_curso_virtual_service(): void
    {
        $service = app(CursoVirtualService::class);

        $docenteA = $service->docenteDeUsuario($this->docenteUserA);
        $this->assertNotNull($docenteA);
        $this->assertSame('DOC_SEC_01', $docenteA->cod_doc);

        $estA = $service->estudianteDeUsuario($this->estudianteUserA);
        $this->assertNotNull($estA);
        $this->assertSame('EST_SEC_01', $estA->cod_est);

        // Búsqueda directa
        $cursoA = $service->cursoParaDocente($this->docenteUserA, $this->claseA->cod_cla);
        $this->assertNotNull($cursoA);
        $this->assertSame($this->claseA->cod_cla, $cursoA->cod_cla);

        $cursoBParaA = $service->cursoParaDocente($this->docenteUserA, $this->claseB->cod_cla);
        $this->assertNull($cursoBParaA, 'Docente A no debe poder resolver directamente la Clase B ajena.');
    }

    public function test_autorizacion_horizontal_docente_cruzado_es_rechazada(): void
    {
        // Otorgar permiso general Spatie simulado
        $this->docenteUserA->givePermissionTo('Aula_Virtual_Docente', 'Acceso_Aula_Virtual');

        // Docente A sobre su propia clase A -> Permitido
        $this->assertTrue(Gate::forUser($this->docenteUserA)->allows('manage', $this->claseA));
        $this->assertTrue(Gate::forUser($this->docenteUserA)->allows('crearTarea', $this->claseA));
        $this->assertTrue(Gate::forUser($this->docenteUserA)->allows('update', $this->tareaA));

        // Docente A sobre Clase B ajena -> Rechazado
        $this->assertFalse(Gate::forUser($this->docenteUserA)->allows('manage', $this->claseB));
        $this->assertFalse(Gate::forUser($this->docenteUserA)->allows('crearTarea', $this->claseB));
        $this->assertFalse(Gate::forUser($this->docenteUserA)->allows('update', $this->tareaB));
        $this->assertFalse(Gate::forUser($this->docenteUserA)->allows('review', $this->tareaB));
    }

    public function test_autorizacion_horizontal_estudiante_cruzado_es_rechazada(): void
    {
        $this->estudianteUserA->givePermissionTo('Aula_Virtual_Estudiante', 'Acceso_Aula_Virtual');

        // Estudiante A sobre Clase A -> Permitido
        $this->assertTrue(Gate::forUser($this->estudianteUserA)->allows('view', $this->claseA));
        $this->assertTrue(Gate::forUser($this->estudianteUserA)->allows('deliver', $this->tareaA));

        // Estudiante A sobre Clase B ajena -> Rechazado
        $this->assertFalse(Gate::forUser($this->estudianteUserA)->allows('view', $this->claseB));
        $this->assertFalse(Gate::forUser($this->estudianteUserA)->allows('deliver', $this->tareaB));
    }
}
