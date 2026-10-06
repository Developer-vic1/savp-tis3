<?php

namespace Tests\Feature;

use App\Livewire\Shared\AcademicSources;
use App\Livewire\Shared\StudyAssistant;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SpecializedSupportBoundaryTest extends TestCase
{
    private function student(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Estudiante');
        $user->shouldReceive('can')->andReturnTrue();
        auth()->setUser($user);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('estudianteDeUsuario')->andReturn(new Estudiante(['cod_est' => 'SELF']));
        $this->app->instance(CursoVirtualService::class, $courses);
        config(['services.aporte_ingenieril.enabled' => false]);
        Http::preventStrayRequests();
        Http::fake();
    }

    public function test_offline_tutor_displays_fallback_without_an_http_request(): void
    {
        $this->student();
        Livewire::test(StudyAssistant::class)->set('question', 'Cómo puedo estudiar álgebra')->call('preguntar')
            ->assertSet('answer', null)->assertSee('no está disponible temporalmente')->assertSee('fuentes y materiales');
        Http::assertNothingSent();
    }

    public function test_empty_sources_query_is_validated_before_request(): void
    {
        $this->student();
        Livewire::test(AcademicSources::class)->call('buscar')->assertHasErrors(['query' => 'required']);
        Http::assertNothingSent();
    }

    public function test_offline_sources_do_not_invent_evidence(): void
    {
        $this->student();
        Livewire::test(AcademicSources::class)->set('query', 'Becas oficiales')->call('buscar')
            ->assertSet('sources', [])->assertSee('no está disponible');
        Http::assertNothingSent();
    }

    public function test_tutor_answer_cannot_be_forged_by_livewire_client(): void
    {
        $this->student();
        $component = Livewire::test(StudyAssistant::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('answer', 'Una recomendación falsificada');
    }
}
