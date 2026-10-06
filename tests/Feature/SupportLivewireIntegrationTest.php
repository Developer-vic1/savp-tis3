<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionPersonas;
use App\Models\Oficial\Sistema\User;
use App\Support\Personas\PersonaInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\PresenceVerifierInterface;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/** Hooks y acciones de producción; render acotado para evitar consultas del listado institucional. */
class SupportLivewireIntegrationTest extends TestCase
{
    private function actor(string $role = 'Secretaria', bool $permission = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_SUPPORT', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->with('Registro_Personas')->andReturn($permission);
        $user->shouldReceive('checkPermissionTo')->andReturnUsing(fn ($ability) => $ability === 'Registro_Personas' && $permission);

        return $user;
    }

    private function support(array $matches = []): void
    {
        Schema::shouldReceive('hasTable')->with('persona')->andReturnTrue();
        $support = Mockery::mock(PersonaInteligente::class)->makePartial();
        $support->shouldReceive('buscarCoincidencias')->andReturn($matches);
        $this->app->instance(PersonaInteligente::class, $support);
        View::addNamespace('support-tests', base_path('tests/Fixtures/views'));
        $this->actingAs($this->actor());
    }

    private function validForm(): array
    {
        return [...(new GestionPersonas)->form, 'nom_per' => 'Ana', 'ape_pat_per' => 'Perez', 'ci_per' => '12345678', 'exp_per' => 'LP',
            'fec_nac_per' => '2000-01-01', 'gen_per' => 'FEMENINO', 'tel_per' => '', 'ema_per' => '',
            'dep_per' => '', 'mun_per' => '', 'ciu_per' => '', 'dir_per' => '', 'est_per' => 1];
    }

    public function test_real_livewire_hook_shows_and_removes_future_date_blocker(): void
    {
        $this->support();
        Livewire::test(PreventivePersonaForm::class)
            ->set('form', $this->validForm())
            ->set('form.fec_nac_per', now()->addYear()->toDateString())
            ->assertSet('analisisPersona.puede_continuar', false)
            ->assertSee('La fecha de nacimiento no puede ser futura.')
            ->set('form.fec_nac_per', '2000-01-01')
            ->assertSet('analisisPersona.puede_continuar', true)
            ->assertDontSee('La fecha de nacimiento no puede ser futura.');
    }

    public function test_duplicate_block_prevents_save_before_validation_or_transaction(): void
    {
        $this->support(['duplicado_ci' => true]);
        DB::shouldReceive('transaction')->never();
        Livewire::test(PreventivePersonaForm::class)->set('form', $this->validForm())
            ->call('guardarPersona')
            ->assertSet('analisisPersona.puede_continuar', false)
            ->assertSee('Ya existe una persona activa registrada con este CI.')
            ->assertDispatched('error-general');
    }

    public function test_suggestions_allow_continuation_without_claiming_persistence(): void
    {
        $this->support();
        $presence = Mockery::mock(PresenceVerifierInterface::class);
        $presence->shouldReceive('getCount')->andReturn(0);
        Validator::setPresenceVerifier($presence);
        DB::shouldReceive('transaction')->once()->andReturnNull();
        Livewire::test(PreventivePersonaForm::class)->set('form', $this->validForm())
            ->call('guardarPersona')->assertHasNoErrors()
            ->assertSet('analisisPersona.puede_continuar', true)
            ->assertSee('Registra al menos un medio de contacto institucional.')
            ->assertSee('Sugerencias');
    }

    public function test_edit_preserves_non_duplicate_blockers_and_uses_server_identity(): void
    {
        $this->support();
        $support = $this->app->make(PersonaInteligente::class);
        $support->shouldReceive('reconocerEnTiempoReal')->with(Mockery::type('array'), 'PER_SELF')->once()->passthru();
        Livewire::test(PreventivePersonaForm::class)
            ->call('prepararEdicion')
            ->set('formEditar.fec_nac_per', now()->addYear()->toDateString())
            ->assertSet('analisisPersonaEditar.puede_continuar', false)
            ->assertSee('La fecha de nacimiento no puede ser futura.');
    }

    public function test_student_cannot_run_admin_persona_assistance(): void
    {
        $this->support();
        $this->actingAs($this->actor('Estudiante'));
        Livewire::test(PreventivePersonaForm::class)->set('form.nom_per', 'Ana')->assertForbidden();
    }

    public function test_revoked_module_permission_denies_support_before_queries(): void
    {
        $this->support();
        $this->actingAs($this->actor('Secretaria', false));
        $this->app->make(PersonaInteligente::class)->shouldReceive('reconocerEnTiempoReal')->never();
        Livewire::test(PreventivePersonaForm::class)->set('form.nom_per', 'Ana')->assertForbidden();
    }
}

class PreventivePersonaForm extends GestionPersonas
{
    public function mount(): void {}

    public function prepararEdicion(): void
    {
        $this->personaEditando = 'PER_SELF';
        $this->formEditar = ['cod_per' => 'PER_SELF', 'nom_per' => 'Ana', 'ape_pat_per' => 'Perez', 'ci_per' => '12345678'];
    }

    public function render()
    {
        return view('support-tests::persona-form');
    }
}
