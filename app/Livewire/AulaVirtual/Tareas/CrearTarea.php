<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\TareaService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearTarea extends Component
{
    use WithFileUploads;

    public $archivo;

    #[Locked]
    public string $curso = '';

    public string $tit_tar = '';

    public string $des_tar = '';

    public string $tip_tar = 'TAREA';

    public string $fec_lim_tar = '';

    public string $est_tar = 'BORRADOR';

    public int $pun_max_tar = 100;

    public bool $perm_ent_tardia = false;

    public string $motivo = '';

    #[Locked]
    public bool $editing = false;

    public function mount(string $curso, ?string $tarea = null, string $tipo = 'TAREA'): void
    {
        abort_unless(in_array($tipo, ['TAREA', 'PRACTICA'], true), 422);
        $this->curso = $curso;
        $this->tip_tar = $tipo;
    }

    public function guardar(): void
    {
        $user = auth()->user();
        $courses = app(CursoVirtualService::class);
        $class = $courses->cursoParaDocente($user, $this->curso);
        $teacher = $courses->docenteDeUsuario($user);
        abort_unless($class && $class->est_cla === 'ACTIVA' && $teacher, 403);
        app(TareaService::class)->crear([
            'cod_cla' => $this->curso, 'tit_tar' => $this->tit_tar, 'des_tar' => $this->des_tar,
            'tip_tar' => $this->tip_tar, 'fec_lim_tar' => $this->fec_lim_tar ?: null,
            'pun_max_tar' => $this->pun_max_tar, 'est_tar' => $this->est_tar, 'perm_ent_tardia' => $this->perm_ent_tardia,
        ], $teacher, $this->archivo);
        $this->reset('tit_tar', 'des_tar', 'fec_lim_tar', 'archivo');
        $this->dispatch('course-updated');
        $this->dispatch('swal:success', text: 'La tarea se guardó correctamente.');
    }

    public function render()
    {
        abort_unless(app(CursoVirtualService::class)->cursoParaDocente(auth()->user(), $this->curso), 403);

        return view('livewire.aula-virtual.tareas.crear-tarea');
    }
}
