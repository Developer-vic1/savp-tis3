<?php

namespace Tests\Feature;

use App\Livewire\Shared\KardexDraft;
use App\Models\SeguimientoAcademico;
use App\Models\User;
use App\Support\Academico\KardexInteligente;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class KardexPreventiveDraftTest extends TestCase
{
    private function actor(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_DRAFT', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->with('kardex.registrar.curso')->andReturnTrue();

        return $user;
    }

    public function test_teacher_preview_preserves_warnings_and_never_persists(): void
    {
        DB::shouldReceive('transaction')->never();
        $this->actingAs($this->actor('Docente'));
        Livewire::test(KardexDraft::class)->call('analizar')
            ->assertSet('analisis.puede_guardar', false)
            ->assertSee('Describe la observación antes de continuar.')
            ->set('form.mot_seg', 'Falta contexto')
            ->assertSet('analisis.puede_guardar', true)
            ->assertSee('La descripción es breve; revisa si permite comprender lo ocurrido.')
            ->set('form.mot_seg', 'Durante la actividad de lectura solicitó apoyo para comprender la consigna.')
            ->assertDontSee('La descripción es breve; revisa si permite comprender lo ocurrido.')
            ->assertSee('Completa el contexto de la observación.');
    }

    public function test_regent_cannot_use_teacher_draft_even_with_write_permission(): void
    {
        $this->actingAs($this->actor('Regente'));
        Livewire::test(KardexDraft::class)->assertForbidden();
    }

    public function test_related_event_warns_without_assigning_sanction_or_blocking(): void
    {
        $event = (new SeguimientoAcademico)->forceFill(['cod_seg' => 'SEG1', 'mot_seg' => 'Solicitó acompañamiento durante la actividad de lectura.']);
        $result = (new KardexInteligente)->analizar(['mot_seg' => $event->mot_seg, 'ori_seg' => 'Clase de lectura'], [$event]);
        $this->assertTrue($result['puede_guardar']);
        $this->assertNotEmpty($result['advertencias']);
        $this->assertSame('SEG1', $result['duplicidad']['registro']['codigo']);
        $this->assertArrayNotHasKey('sancion', $result);
        $this->assertArrayNotHasKey('nivel_riesgo', $result);
    }
}
