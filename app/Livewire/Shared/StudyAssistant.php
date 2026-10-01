<?php

namespace App\Livewire\Shared;

use App\Services\AporteIngenieril\TutorService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\RoleDashboardResolver;
use Livewire\Attributes\Locked;
use Livewire\Component;

class StudyAssistant extends Component
{
    public string $question = '';

    #[Locked]
    public ?string $answer = null;

    #[Locked]
    public ?string $statusMessage = null;

    #[Locked]
    public array $sources = [];

    public function preguntar(): void
    {
        $this->validate(['question' => ['required', 'string', 'min:5', 'max:2000']]);
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        $student = app(CursoVirtualService::class)->estudianteDeUsuario($user);
        abort_unless($student, 403);
        $result = app(TutorService::class)->ask($student->cod_est, $this->question);
        $this->answer = $result->available && is_string($result->data['answer'] ?? null) ? $result->data['answer'] : null;
        $this->statusMessage = $this->answer ? 'Apoyo de estudio disponible.' : 'El análisis especializado no está disponible temporalmente.';
        $this->sources = $this->answer ? ($result->data['sources'] ?? []) : [];
        if ($this->answer && ($result->data['insufficient_evidence'] ?? true)) {
            $this->statusMessage = 'La evidencia disponible es insuficiente. Revisa las fuentes y consulta a tu docente.';
        }
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);

        return view('livewire.shared.study-assistant');
    }
}
