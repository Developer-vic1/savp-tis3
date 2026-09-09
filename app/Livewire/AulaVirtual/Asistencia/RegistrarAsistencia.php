<?php

namespace App\Livewire\AulaVirtual\Asistencia;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Services\AulaVirtual\AsistenciaService;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Support\Academico\CalendarioAcademicoInteligente;
use App\Support\AulaVirtual\AsistenciaInteligente;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RegistrarAsistencia extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public string $codCla = '';

    public string $fecha = '';

    public string $codHbl = '';

    public string $tipoAsistencia = 'CLASE';

    public string $titulo = '';

    public string $observacionGeneral = '';

    // Mapeo de estados: [cod_est => cod_est_asi]
    public array $asistencias = [];

    // Mapeo de observaciones: [cod_est => obs]
    public array $observaciones = [];

    public string $motivoRectificacion = '';

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

        $clase = $this->obtenerClase();
        abort_if(! $clase, 404, 'Clase virtual no encontrada.');
        $this->authorize('registrarAsistencia', $clase);

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

    public function obtenerClase(): ?ClaseVirtual
    {
        return ClaseVirtual::with([
            'planAsignatura.asignatura',
            'planAsignatura.curso',
            'planAsignatura.paralelo',
            'planAsignatura.turno',
        ])->find($this->codCla);
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

    public function updatedCodHbl(): void
    {
        $this->ejecutarAnalisisInteligente();
    }

    public function updatedObservaciones(): void
    {
        $this->ejecutarAnalisisInteligente();
    }

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
        if (empty($this->codCla)) {
            return;
        }

        $soporte = app(AsistenciaInteligente::class);
        $this->analisis = $soporte->analizarSesion(
            codCla: $this->codCla,
            estudiantesMarcados: $this->asistencias,
            fecha: $this->fecha,
            modoCierre: true,
            codHbl: $this->codHbl ?: null,
        );
    }

    public function guardarAsistencia(): void
    {
        $clase = $this->obtenerClase();
        abort_if(! $clase, 404);
        $this->authorize('registrarAsistencia', $clase);

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
                'cod_hbl' => $this->codHbl ?: null,
                'fec_asi_cla' => $this->fecha,
                'tip_asi_cla' => $this->tipoAsistencia,
                'tit_asi_cla' => $this->titulo ?: ('Sesión de '.Carbon::parse($this->fecha)->format('d/m/Y')),
                'obs_asi_cla' => $this->observacionGeneral,
                'motivo_rectificacion' => $this->motivoRectificacion,
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
        } catch (ValidationException $ve) {
            $primerError = collect($ve->errors())->flatten()->first() ?? 'Observaciones en el registro de asistencia.';
            $this->dispatch('error-general', mensaje: $primerError);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('error-general', mensaje: 'No fue posible guardar la asistencia. Inténtalo nuevamente.');
        }
    }

    public function obtenerEstudiantesClase()
    {
        $clase = $this->obtenerClase();
        if (! $clase) {
            return collect();
        }

        $oficiales = app(AsistenciaInteligente::class)->analizarSesion($this->codCla, [], $this->fecha, codHbl: $this->codHbl ?: null)['datos_calculados']['estudiantes_oficiales'] ?? [];

        return $clase->estudiantes()
            ->whereIn('cod_est', $oficiales)
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
        $clase = $this->obtenerClase();
        $estudiantes = $this->obtenerEstudiantesClase();
        $estadosAsistencia = EstadoAsistencia::where('est_est_asi', 'ACTIVO')->get();

        $totalEstudiantes = $estudiantes->count();
        $marcados = collect($this->asistencias)->filter(fn ($v) => ! empty($v))->count();
        $plan = $clase?->planAsignatura ?? $clase?->planEspecialidad;
        $bloques = collect();
        if ($plan && $this->fecha) {
            $dia = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'][Carbon::parse($this->fecha)->isoWeekday()];
            $bloques = app(CalendarioAcademicoInteligente::class)->horariosAfectados([
                'cod_gea' => $plan->cod_gea, 'cod_cur' => $plan->cod_cur, 'cod_par' => $plan->cod_par,
                'cod_tur' => $plan->cod_tur, 'fii_cae' => $this->fecha, 'ffi_cae' => $this->fecha,
            ])->filter(fn ($d) => $d->dia_hde === $dia && ($clase->cod_pas ? $d->cod_pas === $clase->cod_pas : $d->cod_pes === $clase->cod_pes))->unique('cod_hbl');
        }

        return view('livewire.aula-virtual.asistencia.registrar-asistencia', [
            'clase' => $clase,
            'estudiantes' => $estudiantes,
            'estadosAsistencia' => $estadosAsistencia,
            'totalEstudiantes' => $totalEstudiantes,
            'marcados' => $marcados,
            'bloques' => $bloques,
        ]);
    }
}
