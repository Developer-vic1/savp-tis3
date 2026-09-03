<?php

namespace Tests\Feature\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use App\Services\AulaVirtual\EntregaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Bloque4EntregasCompletoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected User $estudianteUserA;
    protected User $estudianteUserB;

    protected ClaseVirtual $claseA;
    protected Tarea $tareaA;
    protected Estudiante $estA;
    protected Estudiante $estB;

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
            ['cod_asi' => 'ASI_GEO'],
            ['nom_asi' => 'Geografía', 'est_asi' => 'ACTIVO']
        );
        DB::table('tipo_vinculacion_estudiante')->updateOrInsert(
            ['cod_tve' => 'TVE_REG'],
            ['nom_tve' => 'Regular', 'est_tve' => 'ACTIVO']
        );

        // Docente A
        $personaDocA = Persona::create([
            'nom_per' => 'Guillermo',
            'ape_pat_per' => 'Guzman',
            'ci_per' => '6220001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1985-05-05',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_ENT_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteA = Docente::create([
            'cod_doc' => 'DOC_ENT_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'GEOGRAFIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_ENT_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.ent.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Docente B
        $personaDocB = Persona::create([
            'nom_per' => 'Humberto',
            'ape_pat_per' => 'Heredia',
            'ci_per' => '6220002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1986-06-06',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_ENT_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_ENT_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'GEOGRAFIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_ENT_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.ent.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        // Clase A y Tarea A
        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_GEO_1A'],
            [
                'cod_asi' => 'ASI_GEO',
                'cod_doc' => $docenteA->cod_doc,
                'cod_cur' => 'CUR_1SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_GEO_1A'],
            ['cod_pas' => 'PAS_GEO_1A', 'nom_cla' => 'Geografía 1ro A', 'est_cla' => 'ACTIVA']
        );
        $this->tareaA = Tarea::create([
            'cod_tar' => 'TAR_GEO_01',
            'cod_cla' => $this->claseA->cod_cla,
            'cod_doc' => $docenteA->cod_doc,
            'tit_tar' => 'Relieve de Bolivia y Cordilleras',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(3),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);

        // Estudiante A (matriculado en Clase A)
        $personaEstA = Persona::create([
            'nom_per' => 'Ines',
            'ape_pat_per' => 'Iriarte',
            'ci_per' => '6220003',
            'exp_per' => 'LP',
            'fec_nac_per' => '2012-07-07',
            'est_per' => true,
        ]);
        $this->estA = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_ENT_01'],
            ['rud_est' => 'RUDE622000301', 'cod_per' => $personaEstA->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserA = User::create([
            'cod_usu' => 'USU_EST_ENT_01',
            'cod_per' => $personaEstA->cod_per,
            'email' => 'estudiante.ent.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->estudianteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante');
        DB::table('clase_estudiante')->updateOrInsert(
            ['cod_cla_est' => 'CLE_ENT_01'],
            ['cod_cla' => $this->claseA->cod_cla, 'cod_est' => $this->estA->cod_est, 'est_cla_est' => 'ACTIVO']
        );

        // Estudiante B (NO matriculado en Clase A)
        $personaEstB = Persona::create([
            'nom_per' => 'Jorge',
            'ape_pat_per' => 'Jimenez',
            'ci_per' => '6220004',
            'exp_per' => 'LP',
            'fec_nac_per' => '2012-08-08',
            'est_per' => true,
        ]);
        $this->estB = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_ENT_02'],
            ['rud_est' => 'RUDE622000401', 'cod_per' => $personaEstB->cod_per, 'cod_tve' => 'TVE_REG', 'est_est' => 'ACTIVO']
        );
        $this->estudianteUserB = User::create([
            'cod_usu' => 'USU_EST_ENT_02',
            'cod_per' => $personaEstB->cod_per,
            'email' => 'estudiante.ent.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->estudianteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante');
    }

    public function test_ruta_real_entregar_permite_matriculado(): void
    {
        $response = $this->actingAs($this->estudianteUserA)->get(route('aula-virtual.estudiante.tareas.entregar', $this->tareaA->cod_tar));
        $response->assertStatus(200);
        $response->assertSee('Relieve de Bolivia y Cordilleras');
    }

    public function test_ruta_real_entregar_bloquea_estudiante_no_matriculado(): void
    {
        $response = $this->actingAs($this->estudianteUserB)->get(route('aula-virtual.estudiante.tareas.entregar', $this->tareaA->cod_tar));
        $response->assertStatus(403);
    }

    public function test_guardar_entrega_con_archivo_en_almacenamiento_privado(): void
    {
        $file = UploadedFile::fake()->create('mapa_orografia.pdf', 500, 'application/pdf');

        $servicio = app(EntregaService::class);
        $entrega = $servicio->guardarEntrega(
            $this->tareaA,
            $this->estA,
            ['tex_ent' => 'Adjunto mapa de orografía andina.', 'accion' => 'enviar'],
            $file,
            $this->estudianteUserA
        );

        $this->assertNotNull($entrega);
        $this->assertSame('ENTREGADO', $entrega->est_ent);

        $archivo = EntregaArchivo::where('cod_ent', $entrega->cod_ent)->first();
        $this->assertNotNull($archivo);
        $this->assertSame('mapa_orografia.pdf', $archivo->nom_arc);
        Storage::disk('local')->assertExists($archivo->rut_arc);
    }

    protected function crearEntregaConArchivo(): EntregaArchivo
    {
        $file = UploadedFile::fake()->create('tarea_geografia.pdf', 300, 'application/pdf');
        $servicio = app(EntregaService::class);
        $entrega = $servicio->guardarEntrega(
            $this->tareaA,
            $this->estA,
            ['tex_ent' => 'Trabajo completo', 'accion' => 'enviar'],
            $file,
            $this->estudianteUserA
        );
        return EntregaArchivo::where('cod_ent', $entrega->cod_ent)->first();
    }

    public function test_descarga_de_archivo_permite_estudiante_dueno(): void
    {
        $archivo = $this->crearEntregaConArchivo();
        $responseDueno = $this->actingAs($this->estudianteUserA)->get(route('aula-virtual.entregas.archivos.descargar', $archivo->cod_ent_arc));
        $responseDueno->assertStatus(200);
    }

    public function test_descarga_de_archivo_permite_docente_del_curso(): void
    {
        $archivo = $this->crearEntregaConArchivo();
        $responseDocente = $this->actingAs($this->docenteUserA)->get(route('aula-virtual.entregas.archivos.descargar', $archivo->cod_ent_arc));
        $responseDocente->assertStatus(200);
    }

    public function test_descarga_de_archivo_bloquea_estudiante_ajeno(): void
    {
        $archivo = $this->crearEntregaConArchivo();
        $responseEstAjeno = $this->actingAs($this->estudianteUserB)->get(route('aula-virtual.entregas.archivos.descargar', $archivo->cod_ent_arc));
        $responseEstAjeno->assertStatus(403);
    }
}
