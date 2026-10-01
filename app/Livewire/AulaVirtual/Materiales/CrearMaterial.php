<?php

namespace App\Livewire\AulaVirtual\Materiales;

use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\MaterialService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearMaterial extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $curso = '';

    public string $nom_mat = '';

    public string $tip_mat = 'DOCUMENTO';

    public string $url_mat = '';

    public string $est_mat = 'OCULTO';

    public $archivo;

    #[Locked]
    public bool $editing = false;

    public function guardar(): void
    {
        $data = ['cod_cla' => $this->curso, 'nom_mat' => $this->nom_mat, 'tip_mat' => $this->tip_mat,
            'url_mat' => $this->url_mat ?: null, 'est_mat' => $this->est_mat];
        app(MaterialService::class)->crear($data, auth()->user(), $this->archivo);
        $this->reset('nom_mat', 'url_mat', 'archivo');
        $this->dispatch('course-updated');
        $this->dispatch('swal:success', text: 'El material se guardó correctamente.');
    }

    public function render()
    {
        abort_unless(app(CursoVirtualService::class)->cursoParaDocente(auth()->user(), $this->curso), 403);

        return view('livewire.aula-virtual.materiales.crear-material');
    }
}
