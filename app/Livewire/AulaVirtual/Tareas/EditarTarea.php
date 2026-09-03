<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\TareaService;
use App\Support\AulaVirtual\TareaInteligente;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditarTarea extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $codTar = '';

    public ?Tarea $tarea = null;
    public ?ClaseVirtual $clase = null;

    public string $titulo = '';
    public string $descripcion = '';
    public string $tipo = 'TAREA';
    public ?string $fechaPublicacion = null;
    public ?string $fechaLimite = null;
    public float $puntajeMaximo = 100.0;
    public bool $permiteEntregaTardia = false;
    public string $estado = 'PUBLICADA';

    public array $analisis = [
        'puede_guardar' => true,
        'puede_continuar' => true,
        'estado' => 'OK',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'datos_calculados' => [],
    ];

    public function mount(string $codTar): void
    {
        $this->codTar = $codTar;
        $this->tarea = Tarea::with(['claseVirtual.planAsignatura.asignatura', 'claseVirtual.planAsignatura.curso', 'claseVirtual.planAsignatura.paralelo'])->find($this->codTar);

        abort_if(! $this->tarea, 404, 'Tarea no encontrada.');
        $this->authorize('update', $this->tarea);

        $this->clase = $this->tarea->claseVirtual;
        $this->titulo = $this->tarea->tit_tar;
        $this->descripcion = (string) $this->tarea->des_tar;
        $this->tipo = $this->tarea->tip_tar;
        $this->fechaPublicacion = $this->tarea->fec_pub_tar ? Carbon::parse($this->tarea->fec_pub_tar)->format('Y-m-d\TH:i') : null;
        $this->fechaLimite = $this->tarea->fec_lim_tar ? Carbon::parse($this->tarea->fec_lim_tar)->format('Y-m-d\TH:i') : null;
        $this->puntajeMaximo = (float) $this->tarea->pun_max_tar;
        $this->permiteEntregaTardia = (bool) $this->tarea->perm_ent_tardia;
        $this->estado = $this->tarea->est_tar;

        $this->analizarEnTiempoReal();
    }

    public function updated($propertyName): void
    {
        $this->analizarEnTiempoReal();
    }

    public function analizarEnTiempoReal(): void
    {
        if (! $this->tarea) {
            return;
        }

        $soporte = app(TareaInteligente::class);
        $this->analisis = $soporte->analizar([
            'cod_cla' => $this->tarea->cod_cla,
            'tit_tar' => $this->titulo,
            'des_tar' => $this->descripcion,
            'tip_tar' => $this->tipo,
            'fec_pub_tar' => $this->fechaPublicacion,
            'fec_lim_tar' => $this->fechaLimite,
            'pun_max_tar' => $this->puntajeMaximo,
        ], $this->codTar);
    }

    public function guardarCambios(): void
    {
        if (! $this->tarea) {
            $this->tarea = Tarea::find($this->codTar);
        }
        abort_if(! $this->tarea, 404);
        $this->authorize('update', $this->tarea);

        $this->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:TAREA,PRACTICA,PROYECTO,INVESTIGACION,LABORATORIO,EVALUACION'],
            'fechaPublicacion' => ['required', 'date'],
            'fechaLimite' => ['required', 'date', 'after:fechaPublicacion'],
            'puntajeMaximo' => ['required', 'numeric', 'min:1', 'max:100'],
            'permiteEntregaTardia' => ['boolean'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADA,CERRADA,ANULADA'],
        ], [
            'titulo.required' => 'El título de la actividad es obligatorio.',
            'fechaLimite.after' => 'La fecha límite debe ser posterior a la fecha de publicación.',
        ]);

        $user = Auth::user();
        $cursoService = app(CursoVirtualService::class);
        $docente = $cursoService->docenteDeUsuario($user);

        if (! $docente) {
            $this->dispatch('error-general', mensaje: 'No se identificó el registro de docente activo correspondiente.');
            return;
        }

        try {
            $tareaService = app(TareaService::class);
            $tareaService->actualizar($this->tarea, [
                'tit_tar' => $this->titulo,
                'des_tar' => $this->descripcion,
                'tip_tar' => $this->tipo,
                'fec_pub_tar' => $this->fechaPublicacion,
                'fec_lim_tar' => $this->fechaLimite,
                'pun_max_tar' => $this->puntajeMaximo,
                'perm_ent_tardia' => $this->permiteEntregaTardia,
                'est_tar' => $this->estado,
            ], $docente, $user);

            $this->dispatch('success-general', mensaje: 'Actividad académica actualizada correctamente.');
            $this->dispatch('tarea-actualizada');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $primerError = collect($ve->errors())->flatten()->first() ?? 'Observaciones al actualizar la tarea.';
            $this->dispatch('error-general', mensaje: $primerError);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No fue posible actualizar la tarea. Inténtalo nuevamente.');
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.editar-tarea');
    }
}
