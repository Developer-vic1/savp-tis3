<?php

namespace App\Livewire\AulaVirtual\Asistencia;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Services\AulaVirtual\AsistenciaService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\AulaVirtual\AsistenciaInteligente;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RegistrarAsistencia extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $codCla = '';

    public ?ClaseVirtual $clase = null;
    public string $fecha = '';
    public string $tipoAsistencia = 'CLASE';
    public string $titulo = '';
    public string $observacionGeneral = '';

    // Mapeo de estados: [cod_est => cod_est_asi]
    public array $asistencias = [];
    // Mapeo de observaciones: [cod_est => obs]
    public array $observaciones = [];
    // Mapeo de minutos de retraso: [cod_est => int]
    public array $minutosRetraso = [];

    // Análisis inteligente
    public array $analisis = [
        'puede_guardar' => false,
        'puede_continuar' => false,
        'estado' => 'OBSERVADO',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'datos_calculados' => [],
    ];

    public function mount(string $codCla): void
    {
        $this->codCla = $codCla;
        $this->fecha = Carbon::today()->format('Y-m-d');

        $this->cargarClase();
    }

    public function cargarClase(): void
    {
        $cursoService = app(CursoVirtualService::class);
        $user = Auth::user();

        $this->clase = ClaseVirtual::with([
            'planAsignatura.asignatura',
            'planAsignatura.curso',
            'planAsignatura.paralelo',
            'planAsignatura.turno',
            'estudiantes.estudiante.persona',
        ])->find($this->codCla);

        abort_if(! $this->clase, 404, 'Clase virtual no encontrada.');
        $this->authorize('registrarAsistencia', $this->clase);

        // Inicializar estudiantes
        $estudiantes = $this->obtenerEstudiantesClase();
        foreach ($estudiantes as $est) {
            if (! isset($this->asistencias[$est->cod_est])) {
                $this->asistencias[$est->cod_est] = '';
            }
            if (! isset($this->minutosRetraso[$est->cod_est])) {
                $this->minutosRetraso[$est->cod_est] = 0;
            }
        }

        $this->ejecutarAnalisisInteligente();
    }

    public function updatedFecha(): void
    {
        $this->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'fecha.required' => 'La fecha de la sesión es obligatoria.',
            'fecha.before_or_equal' => 'La fecha de asistencia no puede ser posterior a hoy.',
        ]);

        $this->ejecutarAnalisisInteligente();
    }

    public function updatedAsistencias(): void
    {
        $this->ejecutarAnalisisInteligente();
    }

    public function updatedObservaciones(): void
    {
        $this->ejecutarAnalisisInteligente();
    }

    /**
     * Marca únicamente a los estudiantes que aún no tienen estado asignado.
     */
    public function marcarPendientesComoPresentes(): void
    {
        $codPresente = $this->obtenerCodigoEstadoPresente();
        if ($codPresente) {
            $estudiantes = $this->obtenerEstudiantesClase();
            foreach ($estudiantes as $est) {
                if (empty($this->asistencias[$est->cod_est])) {
                    $this->asistencias[$est->cod_est] = $codPresente;
                }
            }
            $this->ejecutarAnalisisInteligente();
        }
    }

    /**
     * Marca a todos los estudiantes de la clase como presentes.
     */
    public function marcarTodosPresentes(): void
    {
        $codPresente = $this->obtenerCodigoEstadoPresente();
        if ($codPresente) {
            $estudiantes = $this->obtenerEstudiantesClase();
            foreach ($estudiantes as $est) {
                $this->asistencias[$est->cod_est] = $codPresente;
            }
            $this->ejecutarAnalisisInteligente();
        }
    }

    protected function obtenerCodigoEstadoPresente(): string
    {
        $estadoPresente = EstadoAsistencia::where('est_est_asi', 'ACTIVO')
            ->where(function ($q) {
                $q->where('nom_est_asi', 'like', '%PRES%')
                    ->orWhere('abr_est_asi', 'P')
                    ->orWhere('valor_porcentual', '>=', 100);
            })
            ->first();

        return $estadoPresente ? $estadoPresente->cod_est_asi : (EstadoAsistencia::where('est_est_asi', 'ACTIVO')->value('cod_est_asi') ?? '');
    }

    public function ejecutarAnalisisInteligente(): void
    {
        if (! $this->clase) {
            return;
        }

        $soporte = app(AsistenciaInteligente::class);
        $this->analisis = $soporte->analizarSesion(
            codCla: $this->codCla,
            estudiantesMarcados: $this->asistencias,
            fecha: $this->fecha,
            modoCierre: true
        );
    }

    public function guardarAsistencia(): void
    {
        if (! $this->clase) {
            $this->cargarClase();
        }

        $this->authorize('registrarAsistencia', $this->clase);

        $this->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'tipoAsistencia' => ['required', 'in:CLASE,LABORATORIO,PRACTICA,EVALUACION,ACTIVIDAD'],
        ], [
            'fecha.before_or_equal' => 'La fecha de asistencia no puede ser posterior al día de hoy.',
        ]);

        $user = Auth::user();
        $cursoService = app(CursoVirtualService::class);
        $docente = $cursoService->docenteDeUsuario($user);

        if (! $docente) {
            $this->dispatch('error-general', mensaje: 'No se identificó el registro de docente activo correspondiente.');
            return;
        }

        try {
            $datosFormulario = [
                'cod_cla' => $this->codCla,
                'fec_asi_cla' => $this->fecha,
                'tip_asi_cla' => $this->tipoAsistencia,
                'tit_asi_cla' => $this->titulo ?: ('Sesión de ' . Carbon::parse($this->fecha)->format('d/m/Y')),
                'obs_asi_cla' => $this->observacionGeneral,
                'asistencias' => [],
            ];

            foreach ($this->asistencias as $codEst => $codEstAsi) {
                $datosFormulario['asistencias'][$codEst] = [
                    'cod_est_asi' => $codEstAsi,
                    'min_retraso' => (int) ($this->minutosRetraso[$codEst] ?? 0),
                    'obs_asi_est' => $this->observaciones[$codEst] ?? null,
                ];
            }

            $asistenciaService = app(AsistenciaService::class);
            $asistenciaService->guardar($datosFormulario, $docente, $user);

            $this->dispatch('success-general', mensaje: 'Asistencia registrada y consolidada correctamente.');
            $this->dispatch('asistencia-guardada');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $primerError = collect($ve->errors())->flatten()->first() ?? 'Observaciones en el registro de asistencia.';
            $this->dispatch('error-general', mensaje: $primerError);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No fue posible guardar la asistencia. Inténtalo nuevamente.');
        }
    }

    public function obtenerEstudiantesClase()
    {
        if (! $this->clase) {
            return collect();
        }

        return $this->clase->estudiantes()
            ->where('est_cla_est', 'ACTIVO')
            ->with(['estudiante.persona'])
            ->get()
            ->map(fn ($ce) => (object) [
                'cod_est' => $ce->cod_est,
                'rud_est' => $ce->estudiante->rud_est ?? '',
                'nom_per' => $ce->estudiante->persona->nom_per ?? '',
                'ape_pat_per' => $ce->estudiante->persona->ape_pat_per ?? '',
                'ape_mat_per' => $ce->estudiante->persona->ape_mat_per ?? '',
            ])
            ->sortBy('ape_pat_per')
            ->values();
    }

    public function render()
    {
        $estudiantes = $this->obtenerEstudiantesClase();
        $estadosAsistencia = EstadoAsistencia::where('est_est_asi', 'ACTIVO')->get();

        $totalEstudiantes = $estudiantes->count();
        $marcados = collect($this->asistencias)->filter(fn ($v) => ! empty($v))->count();

        return view('livewire.aula-virtual.asistencia.registrar-asistencia', [
            'estudiantes' => $estudiantes,
            'estadosAsistencia' => $estadosAsistencia,
            'totalEstudiantes' => $totalEstudiantes,
            'marcados' => $marcados,
        ]);
    }
}
