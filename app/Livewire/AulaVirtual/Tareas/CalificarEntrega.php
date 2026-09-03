<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Models\AulaVirtual\CalificacionTarea;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\CalificacionTareaInteligente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CalificarEntrega extends Component
{
    public string $codEnt = '';
    public ?EntregaTarea $entrega = null;

    public float $puntaje = 0.0;
    public string $retroalimentacion = '';

    public array $analisis = [
        'puede_guardar' => true,
        'puede_continuar' => true,
        'estado' => 'OK',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'datos_calculados' => [],
    ];

    public function mount(string $codEnt): void
    {
        $this->codEnt = $codEnt;
        $this->entrega = EntregaTarea::with(['tarea', 'estudiante.persona', 'archivos', 'calificacion'])->find($this->codEnt);

        if ($this->entrega) {
            if ($this->entrega->calificacion) {
                $this->puntaje = (float) $this->entrega->calificacion->pun_obt_cal_tar;
                $this->retroalimentacion = (string) $this->entrega->calificacion->com_cal_tar;
            } else {
                $this->puntaje = (float) $this->entrega->tarea->pun_max_tar;
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
        if (! $this->entrega) {
            return;
        }

        $soporte = app(CalificacionTareaInteligente::class);
        $this->analisis = $soporte->analizarCalificacion(
            codEnt: $this->codEnt,
            puntaje: (float) $this->puntaje,
            retroalimentacion: $this->retroalimentacion
        );
    }

    public function guardarCalificacion(): void
    {
        if (! $this->entrega) {
            $this->entrega = EntregaTarea::with(['tarea', 'estudiante.persona', 'archivos', 'calificacion'])->find($this->codEnt);
        }

        if (! $this->entrega) {
            $this->dispatch('error-general', mensaje: 'No se encontró la entrega.');
            return;
        }

        $max = (float) $this->entrega->tarea->pun_max_tar;

        // 1. FRONTEND + BACKEND VALIDATION
        $this->validate([
            'puntaje' => ['required', 'numeric', 'min:0', "max:{$max}"],
            'retroalimentacion' => ['nullable', 'string', 'max:1000'],
        ], [
            'puntaje.min' => 'El puntaje no puede ser negativo.',
            'puntaje.max' => "El puntaje no puede superar el máximo de {$max} pts.",
        ]);

        // 2. BACKEND DEFENSIVO: Autorización y revalidación
        $user = Auth::user();
        if (! $user) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión para calificar.');
            return;
        }

        $soporte = app(CalificacionTareaInteligente::class);
        $analisisServidor = $soporte->analizarCalificacion(
            codEnt: $this->codEnt,
            puntaje: (float) $this->puntaje,
            retroalimentacion: $this->retroalimentacion
        );

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'La calificación no cumple con los rangos permitidos.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        $docente = Docente::join('personal_institucional', 'docente.cod_pin', '=', 'personal_institucional.cod_pin')
            ->where('personal_institucional.cod_per', $user->cod_per)
            ->select('docente.*')
            ->first();
        $codDoc = $docente ? $docente->cod_doc : ($this->entrega->tarea->cod_doc ?? null);

        // 3. PERSISTENCIA TRANSACCIONAL
        DB::beginTransaction();
        try {
            $calificacion = CalificacionTarea::where('cod_ent', $this->codEnt)->first();

            if (! $calificacion) {
                CalificacionTarea::create([
                    'cod_ent' => $this->codEnt,
                    'cod_tar' => $this->entrega->cod_tar,
                    'cod_est' => $this->entrega->cod_est,
                    'cod_doc' => $codDoc,
                    'pun_obt' => $this->puntaje,
                    'pun_max' => $max,
                    'com_cal' => trim($this->retroalimentacion),
                    'fec_cal' => now(),
                    'est_cal' => 'REGISTRADO',
                ]);
            } else {
                $calificacion->update([
                    'cod_doc' => $codDoc,
                    'pun_obt' => $this->puntaje,
                    'pun_max' => $max,
                    'com_cal' => trim($this->retroalimentacion),
                    'fec_cal' => now(),
                    'est_cal' => 'RECTIFICADO',
                ]);
            }

            $this->entrega->update(['est_ent' => 'CALIFICADO']);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'CALIFICAR_TAREA',
                    tabla: 'calificacion_tarea',
                    registro: $this->codEnt,
                    descripcion: "Se calificó la entrega {$this->codEnt} con nota {$this->puntaje}/{$max}.",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Calificación registrada y publicada exitosamente.');
            $this->dispatch('calificacion-guardada');
            $this->mount($this->codEnt);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al registrar la calificación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.calificar-entrega');
    }
}
