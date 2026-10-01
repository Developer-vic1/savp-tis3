<?php

namespace App\Livewire\Shared;

use App\Services\AulaVirtual\CursoVirtualService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class CourseList extends Component
{
    use WithPagination;

    protected const ACTOR = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $gestion = '';

    public function updated($name): void
    {
        if (in_array($name, ['search', 'gestion'], true)) {
            $this->resetPage();
        }
    }

    public function render(CursoVirtualService $service)
    {
        $this->validate(['search' => 'string|max:100', 'gestion' => 'nullable|digits:4']);
        $teacher = static::ACTOR === 'Docente';
        $user = auth()->user();
        abort_unless($user, 403);
        $cursos = $service->paginarCursos($user, $teacher, trim($this->search), $this->gestion);
        $student = $teacher ? null : $service->estudianteDeUsuario($user);

        return view('livewire.shared.course-list', compact('cursos', 'service', 'teacher', 'student'));
    }
}
