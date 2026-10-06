<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Models\Oficial\AulaVirtual\Tarea;
use App\Services\AulaVirtual\TareaService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

class EditarTarea extends CrearTarea
{
    #[Locked]
    public string $tarea = '';

    public function mount(string $curso, ?string $tarea = null, string $tipo = 'TAREA'): void
    {
        abort_unless($tarea, 404);
        $this->curso = $curso;
        $this->tarea = $tarea;
        $record = Tarea::where('cod_cla', $curso)->findOrFail($tarea);
        Gate::authorize('review', $record);
        $this->tit_tar = $record->tit_tar;
        $this->des_tar = $record->des_tar ?? '';
        $this->tip_tar = $record->tip_tar;
        $this->fec_lim_tar = $record->fec_lim_tar?->format('Y-m-d\TH:i') ?? '';
        $this->pun_max_tar = (int) $record->pun_max_tar;
        $this->perm_ent_tardia = (bool) $record->perm_ent_tardia;
        $this->est_tar = $record->est_tar;
        $this->editing = true;
    }

    public function guardar(): void
    {
        $record = Tarea::where('cod_cla', $this->curso)->findOrFail($this->tarea);
        app(TareaService::class)->actualizar($record, [
            'tit_tar' => $this->tit_tar, 'des_tar' => $this->des_tar, 'tip_tar' => $this->tip_tar,
            'fec_lim_tar' => $this->fec_lim_tar ?: null, 'pun_max_tar' => $this->pun_max_tar,
            'perm_ent_tardia' => $this->perm_ent_tardia, 'est_tar' => $this->est_tar, 'motivo' => $this->motivo,
        ]);
        $this->dispatch('course-updated');
        $this->dispatch('swal:success', text: 'La tarea se actualizó correctamente.');
    }
}
