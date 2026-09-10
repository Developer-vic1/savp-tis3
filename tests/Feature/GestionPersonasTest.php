<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionPersonas;
use App\Models\Persona;
use App\Models\User;
use Database\Seeders\SAVPInstitucionalOficialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionPersonasTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SAVPInstitucionalOficialSeeder::class);

        $this->adminUser = User::where('email', 'asturizagavictor@gmail.com')->first();
    }

    public function test_ruta_gestion_personas_carga_correctamente_para_administrador(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/gestion-personas');
        $response->assertStatus(200);
        $response->assertSee('Gestión de personas');
    }

    public function test_gestion_personas_crea_persona_con_todos_los_campos_y_persistencia_completa(): void
    {
        $ci = '99112233';

        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Carlos Alberto')
            ->set('form.ape_pat_per', 'Mendoza')
            ->set('form.ape_mat_per', 'Flores')
            ->set('form.ci_per', $ci)
            ->set('form.com_per', '1A')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', '1995-08-20')
            ->set('form.gen_per', 'M')
            ->set('form.tel_per', '71234567')
            ->set('form.ema_per', 'carlos.mendoza.test@savp.edu.bo')
            ->set('form.zona_per', 'Miraflores')
            ->set('form.ave_per', 'Busch')
            ->set('form.cal_per', 'Villalobos')
            ->set('form.num_per', '1234')
            ->set('form.ref_per', 'Frente a la plaza')
            ->set('form.ciu_per', 'La Paz')
            ->set('form.mun_per', 'La Paz')
            ->set('form.dep_per', 'La Paz')
            ->set('form.est_per', 1)
            ->call('guardarPersona')
            ->assertDispatched('persona-creada');

        $persona = Persona::where('ci_per', $ci)->first();
        $this->assertNotNull($persona);
        $this->assertSame('Carlos Alberto', $persona->nom_per);
        $this->assertSame('Mendoza', $persona->ape_pat_per);
        $this->assertSame('Flores', $persona->ape_mat_per);
        $this->assertSame('1A', $persona->com_per);
        $this->assertSame('LP', $persona->exp_per);
        $this->assertSame('1995-08-20', $persona->fec_nac_per->format('Y-m-d'));
        $this->assertSame('M', $persona->gen_per);
        $this->assertSame('71234567', $persona->tel_per);
        $this->assertSame('carlos.mendoza.test@savp.edu.bo', $persona->ema_per);
        $this->assertNotEmpty($persona->dir_per);
        $this->assertTrue($persona->est_per);

        $freshPersona = $persona->fresh();
        $this->assertSame('1995-08-20', $freshPersona->fec_nac_per->format('Y-m-d'));
        $this->assertStringContainsString('Miraflores', $freshPersona->dir_per);
    }

    public function test_gestion_personas_edita_persona_y_preserva_fecha_nacimiento_correctamente(): void
    {
        $persona = Persona::create([
            'cod_per' => 'PER_TEST_EDIT',
            'nom_per' => 'Valeria',
            'ape_pat_per' => 'Gutiérrez',
            'ape_mat_per' => 'Torres',
            'ci_per' => '88776655',
            'exp_per' => 'CBBA',
            'fec_nac_per' => '2002-04-10',
            'gen_per' => 'F',
            'tel_per' => '77665544',
            'ema_per' => 'valeria.edit.test@savp.edu.bo',
            'dir_per' => 'Zona Central, Calle Sucre #100',
            'est_per' => true,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->call('abrirModalEditar', $persona->cod_per)
            ->assertSet('formEditar.fec_nac_per', '2002-04-10')
            ->set('formEditar.nom_per', 'Valeria Sofia')
            ->set('formEditar.fec_nac_per', '2001-11-25')
            ->call('actualizarPersona')
            ->assertDispatched('persona-actualizada');

        $personaActualizada = $persona->fresh();
        $this->assertSame('Valeria Sofia', $personaActualizada->nom_per);
        $this->assertSame('2001-11-25', $personaActualizada->fec_nac_per->format('Y-m-d'));
    }

    public function test_validaciones_de_fecha_de_nacimiento_en_personas(): void
    {
        // 1. Fecha futura rechazada
        $manana = now()->addDay()->format('Y-m-d');
        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Test')
            ->set('form.ape_pat_per', 'Fecha')
            ->set('form.ci_per', '55443322')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', $manana)
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasErrors(['form.fec_nac_per']);

        // 2. Más de 120 años rechazada
        $hace121Anos = now()->subYears(121)->format('Y-m-d');
        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Test')
            ->set('form.ape_pat_per', 'Fecha')
            ->set('form.ci_per', '55443322')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', $hace121Anos)
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasErrors(['form.fec_nac_per']);

        // 3. Fecha vacía rechazada
        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Test')
            ->set('form.ape_pat_per', 'Fecha')
            ->set('form.ci_per', '55443322')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', '')
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasErrors(['form.fec_nac_per']);

        // 4. Exactamente 120 años válida
        $exacto120Anos = now()->subYears(120)->format('Y-m-d');
        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Anciano')
            ->set('form.ape_pat_per', 'Valido')
            ->set('form.ci_per', '55443321')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', $exacto120Anos)
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasNoErrors(['form.fec_nac_per'])
            ->assertDispatched('persona-creada');

        // 5. Fecha de hoy válida
        $hoy = now()->format('Y-m-d');
        Livewire::actingAs($this->adminUser)
            ->test(GestionPersonas::class)
            ->set('form.nom_per', 'Recien')
            ->set('form.ape_pat_per', 'Nacido')
            ->set('form.ci_per', '55443320')
            ->set('form.exp_per', 'LP')
            ->set('form.fec_nac_per', $hoy)
            ->set('form.gen_per', 'M')
            ->call('guardarPersona')
            ->assertHasNoErrors(['form.fec_nac_per'])
            ->assertDispatched('persona-creada');
    }
}
