<?php

namespace Tests\Feature\AulaVirtual;

use App\Livewire\AulaVirtual\Tareas\CrearTarea;
use App\Livewire\AulaVirtual\Tareas\EditarTarea;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Bloque3TareasCompletoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $docenteUserA;
    protected User $docenteUserB;
    protected ClaseVirtual $claseA;
    protected Tarea $tareaA;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('Acceso_Aula_Virtual', 'web');
        Permission::findOrCreate('Aula_Virtual_Docente', 'web');

        DB::table('gestion_academica')->updateOrInsert(
            ['cod_gea' => 'GEA_2026'],
            ['ani_gea' => 2026, 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );
        DB::table('curso')->updateOrInsert(
            ['cod_cur' => 'CUR_6SEC'],
            ['nom_cur' => '6to de Secundaria', 'est_cur' => 'ACTIVO']
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
            ['cod_asi' => 'ASI_FIL'],
            ['nom_asi' => 'Filosofía y Psicología', 'est_asi' => 'ACTIVO']
        );

        $personaDocA = Persona::create([
            'nom_per' => 'Felix',
            'ape_pat_per' => 'Flores',
            'ci_per' => '7110001',
            'exp_per' => 'LP',
            'fec_nac_per' => '1981-01-11',
            'est_per' => true,
        ]);
        $pinA = PersonalInstitucional::create([
            'cod_pin' => 'PIN_TAR_01',
            'cod_per' => $personaDocA->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteA = Docente::create([
            'cod_doc' => 'DOC_TAR_01',
            'cod_pin' => $pinA->cod_pin,
            'esp_doc' => 'FILOSOFIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserA = User::create([
            'cod_usu' => 'USU_DOC_TAR_01',
            'cod_per' => $personaDocA->cod_per,
            'email' => 'docente.tar.a@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserA->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        $personaDocB = Persona::create([
            'nom_per' => 'Gonzalo',
            'ape_pat_per' => 'Gomez',
            'ci_per' => '7110002',
            'exp_per' => 'LP',
            'fec_nac_per' => '1982-02-12',
            'est_per' => true,
        ]);
        $pinB = PersonalInstitucional::create([
            'cod_pin' => 'PIN_TAR_02',
            'cod_per' => $personaDocB->cod_per,
            'car_pin' => 'DOCENTE',
            'est_pin' => 'ACTIVO',
        ]);
        $docenteB = Docente::create([
            'cod_doc' => 'DOC_TAR_02',
            'cod_pin' => $pinB->cod_pin,
            'esp_doc' => 'FILOSOFIA',
            'est_doc' => 'ACTIVO',
        ]);
        $this->docenteUserB = User::create([
            'cod_usu' => 'USU_DOC_TAR_02',
            'cod_per' => $personaDocB->cod_per,
            'email' => 'docente.tar.b@savp.edu.bo',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'est_usu' => 'ACTIVO',
        ]);
        $this->docenteUserB->givePermissionTo('Acceso_Aula_Virtual', 'Aula_Virtual_Docente');

        DB::table('plan_asignatura')->updateOrInsert(
            ['cod_pas' => 'PAS_FIL_6A'],
            [
                'cod_asi' => 'ASI_FIL',
                'cod_doc' => $docenteA->cod_doc,
                'cod_cur' => 'CUR_6SEC',
                'cod_par' => 'PAR_A',
                'cod_tur' => 'TUR_MAN',
                'cod_gea' => 'GEA_2026',
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );
        $this->claseA = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_FIL_6A'],
            ['cod_pas' => 'PAS_FIL_6A', 'nom_cla' => 'Filosofía 6to A', 'est_cla' => 'ACTIVA']
        );
        $this->tareaA = Tarea::create([
            'cod_tar' => 'TAR_FIL_01',
            'cod_cla' => $this->claseA->cod_cla,
            'cod_doc' => $docenteA->cod_doc,
            'tit_tar' => 'Ensayo sobre Lógica Formal',
            'fec_pub_tar' => now()->subDay(),
            'fec_lim_tar' => now()->addDays(4),
            'pun_max_tar' => 100,
            'est_tar' => 'PUBLICADA',
        ]);
    }

    public function test_ruta_real_show_docente_permite_titular(): void
    {
        $responseA = $this->actingAs($this->docenteUserA)->get(route('aula-virtual.docente.curso', $this->claseA->cod_cla));
        $responseA->assertStatus(200);
        $responseA->assertSee('Filosofía 6to A');
    }

    public function test_ruta_real_show_docente_bloquea_ajeno(): void
    {
        $responseB = $this->actingAs($this->docenteUserB)->get(route('aula-virtual.docente.curso', $this->claseA->cod_cla));
        $responseB->assertStatus(403);
    }

    public function test_crear_tarea_con_servicio_y_validacion_defensiva(): void
    {
        $this->actingAs($this->docenteUserA);

        Livewire::test(CrearTarea::class, ['codCla' => $this->claseA->cod_cla])
            ->set('titulo', 'Mapa Conceptual de Ética')
            ->set('descripcion', 'Realizar un mapa conceptual detallado con ejemplos de la vida cotidiana.')
            ->set('puntajeMaximo', 100)
            ->set('fechaPublicacion', now()->format('Y-m-d\TH:i'))
            ->set('fechaLimite', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('guardarTarea')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('tarea', [
            'cod_cla' => $this->claseA->cod_cla,
            'tit_tar' => 'Mapa Conceptual de Ética',
            'pun_max_tar' => 100,
        ]);
    }

    public function test_editar_tarea_permite_titular_y_bloquea_docente_ajeno(): void
    {
        // Titular actualiza nota y título
        $this->actingAs($this->docenteUserA);
        Livewire::test(EditarTarea::class, ['codTar' => $this->tareaA->cod_tar])
            ->set('titulo', 'Ensayo sobre Lógica Formal y Dialéctica')
            ->call('guardarCambios')
            ->assertDispatched('success-general');

        $this->assertDatabaseHas('tarea', [
            'cod_tar' => $this->tareaA->cod_tar,
            'tit_tar' => 'Ensayo sobre Lógica Formal y Dialéctica',
        ]);

        // Docente ajeno B es bloqueado
        $this->actingAs($this->docenteUserB);
        Livewire::test(EditarTarea::class, ['codTar' => $this->tareaA->cod_tar])
            ->assertForbidden();
    }
}
