<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\TareaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EditarTarea extends Component
{
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

        if ($this->tarea) {
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
        // 1. FRONTEND + BACKEND VALIDATION
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

        // 2. BACKEND DEFENSIVO: Autorización y soporte
        $user = Auth::user();
        if (! $user) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión para modificar actividades.');
            return;
        }

        $soporte = app(TareaInteligente::class);
        $analisisServidor = $soporte->analizar([
            'cod_cla' => $this->tarea->cod_cla,
            'tit_tar' => $this->titulo,
            'des_tar' => $this->descripcion,
            'tip_tar' => $this->tipo,
            'fec_pub_tar' => $this->fechaPublicacion,
            'fec_lim_tar' => $this->fechaLimite,
            'pun_max_tar' => $this->puntajeMaximo,
        ], $this->codTar);

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'Existen observaciones que impiden actualizar la tarea.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        // 3. PERSISTENCIA TRANSACCIONAL
        DB::beginTransaction();
        try {
            $this->tarea->update([
                'tit_tar' => trim($this->titulo),
                'des_tar' => trim($this->descripcion),
                'tip_tar' => $this->tipo,
                'fec_pub_tar' => $this->fechaPublicacion,
                'fec_lim_tar' => $this->fechaLimite,
                'pun_max_tar' => $this->puntajeMaximo,
                'perm_ent_tardia' => $this->permiteEntregaTardia,
                'est_tar' => $this->estado,
            ]);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'EDITAR_TAREA',
                    tabla: 'tarea',
                    registro: $this->tarea->cod_tar,
                    descripcion: "Se actualizó la actividad '{$this->tarea->tit_tar}'.",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Actividad académica actualizada correctamente.');
            $this->dispatch('tarea-actualizada');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al actualizar la tarea: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.editar-tarea');
    }
}
