<?php

namespace Tests\Feature;

use App\Livewire\Shared\AcademicPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AcademicGoalDraftTest extends TestCase
{
    private function student(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_GOAL', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Estudiante');
        $user->shouldReceive('can')->with('Perfil_Academico')->andReturnTrue();

        return $user;
    }

    public function test_draft_form_validates_and_clears_success_without_schema_or_persistence(): void
    {
        config(['features.academic_goals' => false]);
        DB::shouldReceive('transaction')->never();
        $this->actingAs($this->student());
        Livewire::test(AcademicPlan::class)->assertSee('Revisar borrador sin guardar')
            ->call('validateDraft')->assertHasErrors(['titulo', 'objetivo'])
            ->set('titulo', 'Plan de lectura')->set('objetivo', 'Leer y discutir el material del curso.')
            ->call('validateDraft')->assertHasNoErrors()->assertSet('draftValid', true)
            ->assertSee('No se guardó; necesitarás registrarlo')
            ->set('titulo', 'Plan actualizado')->assertSet('draftValid', false)
            ->call('cancel')->assertSet('titulo', '');
    }

    public function test_new_draft_cannot_claim_completed_state(): void
    {
        config(['features.academic_goals' => false]);
        $this->actingAs($this->student());
        Livewire::test(AcademicPlan::class)->set('titulo', 'Plan')->set('objetivo', 'Completar la lectura.')
            ->set('estado', 'COMPLETADA')->call('validateDraft')->assertHasErrors(['estado'])->assertSet('draftValid', false);
    }
}
