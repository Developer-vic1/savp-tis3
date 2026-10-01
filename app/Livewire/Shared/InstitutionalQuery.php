<?php

namespace App\Livewire\Shared;

use App\Services\InstitutionalQueryService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InstitutionalQuery extends Component
{
    use WithPagination;

    #[Locked]
    public string $area;

    #[Locked]
    public string $workspace;

    #[Url(keep: true)]
    public string $search = '';

    #[Url(keep: true)]
    public string $gestion = '';

    #[Url(as: 'curso', keep: true)]
    public string $course = '';

    #[Url(as: 'estudiante', keep: true)]
    public string $studentFilter = '';

    #[Url(as: 'nivel', keep: true)]
    public string $level = '';

    #[Url(as: 'paralelo', keep: true)]
    public string $parallel = '';

    #[Url(as: 'turno', keep: true)]
    public string $shift = '';

    #[Url(as: 'estado', keep: true)]
    public string $state = '';

    #[Url(as: 'desde', keep: true)]
    public string $from = '';

    #[Url(as: 'hasta', keep: true)]
    public string $until = '';

    public function updated(string $property): void
    {
        $this->resetValidation();
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCourse(): void
    {
        $this->resetPage();
    }

    public function updatedStudentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGestion(): void
    {
        $this->course = '';
        $this->parallel = $this->shift = '';
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->search = $this->gestion = $this->course = $this->studentFilter = '';
        $this->level = $this->parallel = $this->shift = $this->state = $this->from = $this->until = '';
        $this->resetValidation();
        $this->resetPage();
    }

    public function render()
    {
        abort_unless(auth()->user(), 403);
        $request = request()->duplicate(['search' => $this->search, 'gestion' => $this->gestion, 'curso' => $this->course, 'estudiante' => $this->studentFilter,
            'nivel' => $this->level, 'paralelo' => $this->parallel, 'turno' => $this->shift, 'estado' => $this->state, 'desde' => $this->from, 'hasta' => $this->until]);
        $request->setUserResolver(fn () => auth()->user());

        return view('livewire.shared.institutional-query', app(InstitutionalQueryService::class)->search($request, $this->area, $this->workspace));
    }
}
