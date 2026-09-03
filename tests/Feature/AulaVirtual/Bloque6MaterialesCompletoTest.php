<?php

namespace Tests\Feature\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use App\Services\AulaVirtual\MaterialService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Bloque6MaterialesCompletoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected User $estudianteUserA;
    protected User $estudianteUserB;

    protected ClaseVirtual $claseA;
    protected Docente $docenteA;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        Permission::findOrCreate('Aula_Virtual_Docente', 'web');
        Permission::findOrCreate('Aula_Virtual_Estudiante', 'web');

        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_3SEC'],
            ['nom_cur' => '3ro de Secundaria', 'est_cur' => 'ACTIVO']
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
            ['cod_asi' => 'ASI_QUI'],
            ['nom_asi' => 'Química', 'est_asi' => 'ACTIVO']
        );
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        // Docente A
        $personaDocA = Persona::create([
            'nom_per' => 'Marcos',
            'ape_pat_per' => 'Morales',
            'ci_per' => '4110001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1987-01-01',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_MAT_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $this->docenteA = Docente::create([
            'cod_doc' => 'DOC_MAT_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'QUIMICA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_MAT_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.mat.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Docente B
        $personaDocB = Persona::create([
            'nom_per' => 'Nelson',
            'ape_pat_per' => 'Navarro',
            'ci_per' => '4110002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1988-02-02',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_MAT_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_MAT_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'QUIMICA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_MAT_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.mat.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Clase A
        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_QUI_3A'],
            [
                'cod_asi' => 'ASI_QUI',
                'cod_doc' => $this->docenteA->cod_doc,
                'cod_cur' => 'CUR_3SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_QUI_3A'],
            ['cod_pas' => 'PAS_QUI_3A', 'nom_cla' => 'Química 3ro A', 'est_cla' => 'ACTIVA']
        );

        // Estudiante A (inscrito)
        $personaEstA = Persona::create([
            'nom_per' => 'Oscar',
            'ape_pat_per' => 'Ortiz',
            'ci_per' => '4110003',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-03-03',
            'est_per' => true,
        ]);
        $estA = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_MAT_01'],
            ['rud_est' => 'RUDE411000301', 'cod_per' => $personaEstA->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserA = User::create([
            'cod_usu' => 'USU_EST_MAT_01',
            'cod_per' => $personaEstA->cod_per,
            'email' => 'estudiante.mat.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->estudianteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante');
        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_MAT_01'],
            ['cod_cla' => $this->claseA->cod_cla, 'cod_est' => $estA->cod_est, 'est_cla_est' => 'ACTIVO']
        );

        // Estudiante B (ajeno)
        $personaEstB = Persona::create([
            'nom_per' => 'Patricia',
            'ape_pat_per' => 'Perez',
            'ci_per' => '4110004',
            'exp_per' => 'LP',
            'fec_nac_per' => '2010-04-04',
            'est_per' => true,
        ]);
        $estB = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_MAT_02'],
            ['rud_est' => 'RUDE411000401', 'cod_per' => $personaEstB->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserB = User::create([
            'cod_usu' => 'USU_EST_MAT_02',
            'cod_per' => $personaEstB->cod_per,
            'email' => 'estudiante.mat.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->estudianteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante');
    }

    protected function crearMaterialConArchivo(): MaterialClase
    {
        $file = UploadedFile::fake()->create('tabla_periodica.pdf', 800, 'application/pdf');
        $servicio = app(MaterialService::class);
        return $servicio->crear([
            'cod_cla' => $this->claseA->cod_cla,
            'nom_mat' => 'Tabla Periódica Actualizada',
            'tip_mat' => 'PDF',
            'est_mat' => 'ACTIVO',
        ], $this->docenteUserA, $file, $this->docenteA);
    }

    public function test_crear_material_servicio_en_almacenamiento_privado(): void
    {
        $material = $this->crearMaterialConArchivo();

        $this->assertNotNull($material);
        $this->assertSame('Tabla Periódica Actualizada', $material->nom_mat);
        $this->assertDatabaseHas('material_clase', [
            'cod_mat' => $material->cod_mat,
            'nom_mat' => 'Tabla Periódica Actualizada',
        ]);
        Storage::disk('local')->assertExists($material->rut_mat);
    }

    public function test_descarga_material_permite_docente_titular(): void
    {
        $material = $this->crearMaterialConArchivo();
        $response = $this->actingAs($this->docenteUserA)->get(route('aula-virtual.materiales.descargar', $material->cod_mat));
        $response->assertStatus(200);
    }

    public function test_descarga_material_permite_estudiante_inscrito(): void
    {
        $material = $this->crearMaterialConArchivo();
        $response = $this->actingAs($this->estudianteUserA)->get(route('aula-virtual.materiales.descargar', $material->cod_mat));
        $response->assertStatus(200);
    }

    public function test_descarga_material_bloquea_estudiante_ajeno(): void
    {
        $material = $this->crearMaterialConArchivo();
        $response = $this->actingAs($this->estudianteUserB)->get(route('aula-virtual.materiales.descargar', $material->cod_mat));
        $response->assertStatus(403);
    }

    public function test_descarga_material_bloquea_docente_ajeno(): void
    {
        $material = $this->crearMaterialConArchivo();
        $response = $this->actingAs($this->docenteUserB)->get(route('aula-virtual.materiales.descargar', $material->cod_mat));
        $response->assertStatus(403);
    }
}
