<?php

namespace App\Livewire\Shared;

use App\Services\AporteIngenieril\KnowledgeService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\RoleDashboardResolver;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AcademicSources extends Component
{
    public string $query = '';

    #[Locked]
    public array $sources = [];

    #[Locked]
    public ?string $statusMessage = null;

    public function buscar(): void
    {
        $this->authorizeStudent();
        $this->validate(['query' => 'required|string|min:2|max:1000']);
        $result = app(KnowledgeService::class)->search($this->query);
        $this->sources = $result->available ? $result->data['results'] : [];
        $this->statusMessage = ! $result->available ? 'La búsqueda especializada no está disponible. Puedes consultar los materiales de tus materias.'
            : (($result->data['insufficient_evidence'] ?? true) ? 'La evidencia recuperada es insuficiente; comprueba la procedencia antes de tomar decisiones.' : 'Fuentes recuperadas del corpus académico.');
    }

    private function authorizeStudent(): void
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Materiales_Aula')
            && app(CursoVirtualService::class)->estudianteDeUsuario($user), 403);
    }

    public function render()
    {
        $this->authorizeStudent();

        return view('livewire.shared.academic-sources');
    }
}
