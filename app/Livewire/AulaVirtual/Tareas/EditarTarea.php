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
        $tarea = $this->obtenerTarea();

        abort_if(! $tarea, 404, 'Tarea no encontrada.');
        $this->authorize('update', $tarea);

        $this->titulo = $tarea->tit_tar;
        $this->descripcion = (string) $tarea->des_tar;
        $this->tipo = $tarea->tip_tar;
        $this->fechaPublicacion = $tarea->fec_pub_tar ? Carbon::parse($tarea->fec_pub_tar)->format('Y-m-d\TH:i') : null;
        $this->fechaLimite = $tarea->fec_lim_tar ? Carbon::parse($tarea->fec_lim_tar)->format('Y-m-d\TH:i') : null;
        $this->puntajeMaximo = (float) $tarea->pun_max_tar;
        $this->permiteEntregaTardia = (bool) $tarea->perm_ent_tardia;
        $this->estado = $tarea->est_tar;

        $this->analizarEnTiempoReal();
    }

    public function obtenerTarea(): ?Tarea
    {
        return Tarea::with(['claseVirtual.planAsignatura.asignatura', 'claseVirtual.planAsignatura.curso', 'claseVirtual.planAsignatura.paralelo'])->find($this->codTar);
    }

    public function updated($propertyName): void
    {
        $this->analizarEnTiempoReal();
    }

    public function analizarEnTiempoReal(): void
    {
        $tarea = $this->obtenerTarea();
        if (! $tarea) {
            return;
        }

        $soporte = app(TareaInteligente::class);
        $this->analisis = $soporte->analizar([
            'cod_cla' => $tarea->cod_cla,
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
        $tarea = $this->obtenerTarea();
        abort_if(! $tarea, 404);
        $this->authorize('update', $tarea);

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
            $tareaService->actualizar($tarea, [
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
        $tarea = $this->obtenerTarea();
        return view('livewire.aula-virtual.tareas.editar-tarea', [
            'tarea' => $tarea,
            'clase' => $tarea?->claseVirtual,
        ]);
    }
}
