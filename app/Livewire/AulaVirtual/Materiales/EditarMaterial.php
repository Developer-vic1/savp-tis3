<?php

namespace App\Livewire\AulaVirtual\Materiales;

use App\Models\Oficial\AulaVirtual\MaterialClase;
use App\Services\AulaVirtual\MaterialService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

class EditarMaterial extends CrearMaterial
{
    #[Locked]
    public string $material = '';

    public function mount(string $curso, string $material): void
    {
        $this->curso = $curso;
        $this->material = $material;
        $record = MaterialClase::where('cod_cla', $curso)->findOrFail($material);
        Gate::authorize('update', $record);
        foreach ($record->only(['nom_mat', 'tip_mat', 'url_mat', 'est_mat']) as $field => $value) {
            $this->$field = $value ?? '';
        }
        $this->editing = true;
    }

    public function guardar(): void
    {
        $record = MaterialClase::where('cod_cla', $this->curso)->findOrFail($this->material);
        app(MaterialService::class)->actualizar($record, [
            'nom_mat' => $this->nom_mat, 'tip_mat' => $this->tip_mat,
            'url_mat' => $this->url_mat ?: null, 'est_mat' => $this->est_mat,
        ], auth()->user());
        $this->dispatch('course-updated');
        $this->dispatch('swal:success', text: 'El material se actualizó correctamente.');
    }
}
