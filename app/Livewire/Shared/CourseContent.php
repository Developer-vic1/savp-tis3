<?php

namespace App\Livewire\Shared;

use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\PublicacionService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CourseContent extends Component
{
    #[Locked]
    public string $curso = '';

    #[Locked]
    public ?string $publication = null;

    public string $tit_pub = '';

    public string $con_pub = '';

    public string $tip_pub = 'ANUNCIO';

    public string $est_pub = 'BORRADOR';

    public string $motivo = '';

    private function clase()
    {
        $user = auth()->user();
        abort_unless($user && $user->can('Acceso_Aula_Virtual') && $user->can('Materiales_Aula'), 403);
        $class = app(CursoVirtualService::class)->cursoParaDocente($user, $this->curso);
        abort_unless($class && $class->est_cla === 'ACTIVA', 403);
        Gate::authorize('manage', $class);

        return $class;
    }

    public function editar(string $id): void
    {
        $record = $this->clase()->publicaciones()->findOrFail($id);
        $this->publication = $id;
        foreach ($record->only(['tit_pub', 'con_pub', 'tip_pub', 'est_pub']) as $field => $value) {
            $this->$field = $value ?? '';
        }
        $this->motivo = '';
    }

    public function cancelar(): void
    {
        $this->clase();
        $this->reset('publication', 'tit_pub', 'con_pub', 'tip_pub', 'est_pub', 'motivo');
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->clase();
        app(PublicacionService::class)->guardar($this->curso, $this->publication, $this->only(['tit_pub', 'con_pub', 'tip_pub', 'est_pub', 'motivo']));
        $this->cancelar();
        $this->dispatch('course-updated');
        $this->dispatch('swal:success', text: 'La publicación se guardó correctamente.');
    }

    public function render()
    {
        return view('livewire.shared.course-content', ['publications' => $this->clase()->publicaciones()->orderByDesc('created_at')->limit(30)->get()]);
    }
}
