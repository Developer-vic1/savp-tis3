<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionAsignatura;
use App\Models\Asignatura;
use App\Models\User;
use App\Support\Academico\AsignaturaInteligente;
use Database\Seeders\SAVPInstitucionalOficialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionAsignaturaTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SAVPInstitucionalOficialSeeder::class);

        $this->adminUser = User::where('email', 'asturizagavictor@gmail.com')->first();
    }

    public function test_ruta_gestion_asignaturas_carga_correctamente_para_administrador(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/gestion-asignaturas');
        $response->assertStatus(200);
        $response->assertSee('Asignaturas');
    }

    public function test_asignatura_inteligente_calcula_similitud_e_interpreta_sin_fatal_error(): void
    {
        $similitud = AsignaturaInteligente::calcularSimilitudAsignatura('Matemáticas', 'Matemática');
        $this->assertGreaterThan(80, $similitud);

        $analisis = AsignaturaInteligente::interpretar('Matemática Avanzada');
        $this->assertIsArray($analisis);
        $this->assertArrayHasKey('valido', $analisis);
    }

    public function test_componente_gestion_asignatura_crea_y_edita_asignatura(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(GestionAsignatura::class)
            ->set('form.nom_asi', 'Programación Web')
            ->set('form.sig_asi', 'PWB')
            ->set('form.hor_asi', 4)
            ->set('form.est_asi', 'ACTIVO')
            ->call('guardarAsignatura')
            ->assertDispatched('asignatura-creada');

        $this->assertDatabaseHas('asignatura', [
            'nom_asi' => 'Programación Web',
            'est_asi' => 'ACTIVO',
        ]);

        $asignatura = Asignatura::where('nom_asi', 'Programación Web')->first();
        $this->assertNotNull($asignatura);

        Livewire::actingAs($this->adminUser)
            ->test(GestionAsignatura::class)
            ->call('abrirModalEditar', $asignatura->cod_asi)
            ->set('formEditar.nom_asi', 'Programación Web Aplicada')
            ->set('formEditar.hor_asi', 5)
            ->call('guardarEdicionAsignatura')
            ->assertDispatched('asignatura-actualizada');

        $this->assertDatabaseHas('asignatura', [
            'cod_asi' => $asignatura->cod_asi,
            'nom_asi' => 'Programación Web Aplicada',
            'hor_asi' => 5,
        ]);
    }
}
