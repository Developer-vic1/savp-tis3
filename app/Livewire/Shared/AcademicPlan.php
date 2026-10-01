<?php

namespace App\Livewire\Shared;

use App\Services\AcademicGoalService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicPlan extends Component
{
    use WithPagination;

    #[Locked]
    public ?string $editing = null;

    public string $titulo = '';

    public string $objetivo = '';

    public string $accion = '';

    public string $fecha = '';

    public string $estado = 'BORRADOR';

    public string $motivo = '';

    public bool $draftValid = false;

    public function updated(): void
    {
        $this->draftValid = false;
    }

    public function validateDraft(): void
    {
        abort_unless(auth()->user(), 403);
        $this->draftValid = false;
        app(AcademicGoalService::class)->validateDraft(auth()->user(), ['titulo' => $this->titulo, 'objetivo' => $this->objetivo,
            'accion' => $this->accion ?: null, 'fecha_objetivo' => $this->fecha ?: null, 'estado' => $this->estado, 'motivo' => $this->motivo ?: null], $this->editing !== null);
        $this->resetValidation();
        $this->draftValid = true;
    }

    public function edit(string $id): void
    {
        validator(['id' => $id], ['id' => 'required|uuid'])->validate();
        abort_unless(auth()->user(), 403);
        $goal = app(AcademicGoalService::class)->find(auth()->user(), $id);
        Gate::authorize('update', $goal);
        $this->editing = $id;
        $this->titulo = $goal->titulo;
        $this->objetivo = $goal->objetivo;
        $this->accion = $goal->accion ?? '';
        $this->fecha = $goal->fecha_objetivo?->format('Y-m-d') ?? '';
        $this->estado = $goal->estado;
        $this->motivo = '';
    }

    public function cancel(): void
    {
        $this->reset('editing', 'titulo', 'objetivo', 'accion', 'fecha', 'estado', 'motivo', 'draftValid');
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(auth()->user(), 403);
        app(AcademicGoalService::class)->save(auth()->user(), ['titulo' => $this->titulo, 'objetivo' => $this->objetivo,
            'accion' => $this->accion ?: null, 'fecha_objetivo' => $this->fecha ?: null, 'estado' => $this->estado, 'motivo' => $this->motivo ?: null], $this->editing);
        $this->cancel();
        $this->dispatch('toast', type: 'success', message: 'Tu meta se guardó conservando su historial.');
    }

    public function render()
    {
        abort_unless(auth()->user(), 403);

        return view('livewire.shared.academic-plan', app(AcademicGoalService::class)->snapshot(auth()->user()));
    }
}
