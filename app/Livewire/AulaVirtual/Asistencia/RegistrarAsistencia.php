<?php

namespace App\Livewire\AulaVirtual\Asistencia;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\AsistenciaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class RegistrarAsistencia extends Component
{
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

    public function mount(string $codCla = ''): void
    {
        $this->codCla = $codCla;
        $this->fecha = Carbon::today()->format('Y-m-d');

        if ($this->codCla !== '') {
            $this->cargarClase();
        }
    }

    public function cargarClase(): void
    {
        $this->clase = ClaseVirtual::with(['planAsignatura.asignatura', 'planAsignatura.curso', 'planAsignatura.paralelo', 'estudiantes.estudiante.persona'])
            ->find($this->codCla);

        if ($this->clase) {
            // Inicializar estudiantes vacíos si no están marcados
            $estudiantes = $this->obtenerEstudiantesClase();
            foreach ($estudiantes as $est) {
                if (! isset($this->asistencias[$est->cod_est])) {
                    $this->asistencias[$est->cod_est] = '';
                }
            }
            $this->ejecutarAnalisisInteligente();
        }
    }

    public function updatedFecha(): void
    {
        $this->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'fecha.required' => 'La fecha de la sesión es obligatoria.',
            'fecha.date' => 'Ingresa una fecha válida.',
            'fecha.before_or_equal' => 'La fecha de asistencia no puede ser posterior a hoy.',
        ]);

        $this->ejecutarAnalisisInteligente();
    }

    public function updatedAsistencias(): void
    {
        $this->ejecutarAnalisisInteligente();
    }

    public function marcarTodosPresentes(): void
    {
        $estadoPresente = EstadoAsistencia::where('est_est_asi', 'ACTIVO')
            ->where(function ($q) {
                $q->where('nom_est_asi', 'like', '%PRES%')
                    ->orWhere('abr_est_asi', 'P');
            })
            ->first();

        $codPresente = $estadoPresente ? $estadoPresente->cod_est_asi : (EstadoAsistencia::where('est_est_asi', 'ACTIVO')->value('cod_est_asi') ?? '');

        if ($codPresente) {
            $estudiantes = $this->obtenerEstudiantesClase();
            foreach ($estudiantes as $est) {
                $this->asistencias[$est->cod_est] = $codPresente;
            }
            $this->ejecutarAnalisisInteligente();
        }
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
        // 1. FRONTEND + BACKEND VALIDATION
        $this->validate([
            'codCla' => ['required', 'string', 'exists:clase_virtual,cod_cla'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'tipoAsistencia' => ['required', 'in:CLASE,LABORATORIO,PRACTICA,EVALUACION,ACTIVIDAD'],
        ], [
            'fecha.before_or_equal' => 'La fecha de asistencia no puede ser posterior al día de hoy.',
            'tipoAsistencia.required' => 'Debe seleccionar un tipo de sesión válido.',
        ]);

        // 2. BACKEND DEFENSIVO: Autorización y Pertenencia
        $user = Auth::user();
        if (! $user) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión para registrar asistencia.');
            return;
        }

        $docente = Docente::join('personal_institucional', 'docente.cod_pin', '=', 'personal_institucional.cod_pin')
            ->where('personal_institucional.cod_per', $user->cod_per)
            ->select('docente.*')
            ->first();
        $codDoc = $docente ? $docente->cod_doc : ($this->clase->planAsignatura->cod_doc ?? null);

        if (! $codDoc) {
            $this->dispatch('error-general', mensaje: 'No se identificó el registro de docente correspondiente.');
            return;
        }

        // Revalidar soporte inteligente en backend
        $soporte = app(AsistenciaInteligente::class);
        $analisisServidor = $soporte->analizarSesion(
            codCla: $this->codCla,
            estudiantesMarcados: $this->asistencias,
            fecha: $this->fecha,
            modoCierre: true
        );

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'Existen observaciones críticas que impiden guardar la sesión.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        // 3. PERSISTENCIA TRANSACCIONAL CON BASE DE DATOS
        DB::beginTransaction();
        try {
            // Generar código único para la cabecera de asistencia
            $ultimo = AsistenciaClase::where('cod_asi_cla', 'like', 'ASC_%')
                ->orderByDesc('cod_asi_cla')
                ->value('cod_asi_cla');
            $num = $ultimo ? ((int) str_replace('ASC_', '', $ultimo)) + 1 : 1;
            $codAsiCla = 'ASC_' . str_pad($num, 5, '0', STR_PAD_LEFT);

            $asistenciaCabecera = AsistenciaClase::create([
                'cod_asi_cla' => $codAsiCla,
                'cod_cla' => $this->codCla,
                'cod_doc' => $codDoc,
                'cod_usu_reg' => $user->cod_usu,
                'fec_asi_cla' => $this->fecha,
                'tip_asi_cla' => $this->tipoAsistencia,
                'tit_asi_cla' => $this->titulo ?: 'Sesión de ' . Carbon::parse($this->fecha)->format('d/m/Y'),
                'obs_asi_cla' => $this->observacionGeneral,
                'ori_asi_cla' => 'MANUAL',
                'est_asi_cla' => 'CERRADA',
            ]);

            // Insertar detalles individuales
            $estudiantes = $this->obtenerEstudiantesClase();
            $numDet = 1;
            foreach ($estudiantes as $est) {
                $codEstAsi = $this->asistencias[$est->cod_est] ?? null;
                if (! $codEstAsi) {
                    continue;
                }

                $codAsiEst = 'ASE_' . str_pad($num, 4, '0', STR_PAD_LEFT) . '_' . str_pad($numDet++, 3, '0', STR_PAD_LEFT);

                AsistenciaEstudiante::create([
                    'cod_asi_est' => $codAsiEst,
                    'cod_asi_cla' => $asistenciaCabecera->cod_asi_cla,
                    'cod_est' => $est->cod_est,
                    'cod_est_asi' => $codEstAsi,
                    'cod_usu_reg' => $user->cod_usu,
                    'obs_asi_est' => $this->observaciones[$est->cod_est] ?? null,
                    'fec_reg_asi_est' => now(),
                    'est_asi_est' => 'REGISTRADO',
                ]);
            }

            // Registrar Bitácora
            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'REGISTRAR_ASISTENCIA',
                    tabla: 'asistencia_clase',
                    registro: $asistenciaCabecera->cod_asi_cla,
                    descripcion: "Se registró la asistencia de la clase {$this->codCla} para la fecha {$this->fecha}.",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Asistencia registrada y consolidada correctamente.');
            $this->dispatch('asistencia-guardada');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al guardar la asistencia: ' . $e->getMessage());
        }
    }

    public function obtenerEstudiantesClase()
    {
        if (! $this->clase) {
            return collect();
        }

        return DB::table('clase_estudiante')
            ->join('estudiante', 'clase_estudiante.cod_est', '=', 'estudiante.cod_est')
            ->join('persona', 'estudiante.cod_per', '=', 'persona.cod_per')
            ->where('clase_estudiante.cod_cla', $this->codCla)
            ->where('clase_estudiante.est_cla_est', 'ACTIVO')
            ->select('estudiante.cod_est', 'estudiante.rud_est', 'persona.nom_per', 'persona.ape_pat_per', 'persona.ape_mat_per')
            ->orderBy('persona.ape_pat_per')
            ->orderBy('persona.ape_mat_per')
            ->orderBy('persona.nom_per')
            ->get();
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
