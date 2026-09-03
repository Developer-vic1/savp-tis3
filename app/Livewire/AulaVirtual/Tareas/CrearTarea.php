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

class CrearTarea extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $codCla = '';

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
        'puede_guardar' => false,
        'puede_continuar' => false,
        'estado' => 'OK',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'datos_calculados' => [],
    ];

    public function mount(string $codCla = ''): void
    {
        $this->codCla = $codCla;
        $this->fechaPublicacion = now()->format('Y-m-d\TH:i');
        $this->fechaLimite = now()->addDays(7)->format('Y-m-d\TH:i');

        if ($this->codCla !== '') {
            $this->clase = ClaseVirtual::with(['planAsignatura.asignatura', 'planAsignatura.curso', 'planAsignatura.paralelo'])->find($this->codCla);
            if ($this->clase) {
                $this->authorize('crearTarea', $this->clase);
            }
            $this->analizarEnTiempoReal();
        }
    }

    public function updated($propertyName): void
    {
        $this->analizarEnTiempoReal();
    }

    public function analizarEnTiempoReal(): void
    {
        $soporte = app(TareaInteligente::class);
        $this->analisis = $soporte->analizar([
            'cod_cla' => $this->codCla,
            'tit_tar' => $this->titulo,
            'des_tar' => $this->descripcion,
            'tip_tar' => $this->tipo,
            'fec_pub_tar' => $this->fechaPublicacion,
            'fec_lim_tar' => $this->fechaLimite,
            'pun_max_tar' => $this->puntajeMaximo,
        ]);
    }

    public function guardarTarea(): void
    {
        if (! $this->clase) {
            $this->clase = ClaseVirtual::find($this->codCla);
        }
        abort_if(! $this->clase, 404);
        $this->authorize('crearTarea', $this->clase);

        $this->validate([
            'codCla' => ['required', 'string', 'exists:clase_virtual,cod_cla'],
            'titulo' => ['required', 'string', 'min:3', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:TAREA,PRACTICA,PROYECTO,INVESTIGACION,LABORATORIO,EVALUACION'],
            'fechaPublicacion' => ['required', 'date'],
            'fechaLimite' => ['required', 'date', 'after:fechaPublicacion'],
            'puntajeMaximo' => ['required', 'numeric', 'min:1', 'max:100'],
            'permiteEntregaTardia' => ['boolean'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADA'],
        ], [
            'titulo.required' => 'El título de la actividad es obligatorio.',
            'fechaLimite.after' => 'La fecha límite debe ser posterior a la fecha de publicación.',
            'puntajeMaximo.min' => 'El puntaje mínimo es de 1 punto.',
            'puntajeMaximo.max' => 'El puntaje máximo es de 100 puntos.',
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
            $tarea = $tareaService->crear([
                'cod_cla' => $this->codCla,
                'tit_tar' => $this->titulo,
                'des_tar' => $this->descripcion,
                'tip_tar' => $this->tipo,
                'fec_pub_tar' => $this->fechaPublicacion,
                'fec_lim_tar' => $this->fechaLimite,
                'pun_max_tar' => $this->puntajeMaximo,
                'perm_ent_tardia' => $this->permiteEntregaTardia,
                'est_tar' => $this->estado,
            ], $docente, $user);

            $this->dispatch('success-general', mensaje: 'Actividad académica creada correctamente.');
            $this->dispatch('tarea-creada', codTar: $tarea->cod_tar);
            $this->reset(['titulo', 'descripcion']);
            $this->analizarEnTiempoReal();
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $primerError = collect($ve->errors())->flatten()->first() ?? 'Observaciones al crear la tarea.';
            $this->dispatch('error-general', mensaje: $primerError);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No fue posible guardar la tarea. Inténtalo nuevamente.');
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.crear-tarea');
    }
}
