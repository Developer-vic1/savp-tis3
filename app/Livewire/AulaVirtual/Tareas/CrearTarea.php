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

class CrearTarea extends Component
{
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
        // 1. FRONTEND + BACKEND VALIDATION
        $this->validate([
            'codCla' => ['required', 'string', 'exists:clase_virtual,cod_cla'],
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
            'titulo.min' => 'El título debe tener al menos 3 caracteres.',
            'fechaLimite.after' => 'La fecha límite debe ser posterior a la fecha de publicación.',
            'puntajeMaximo.min' => 'El puntaje mínimo es de 1 punto.',
            'puntajeMaximo.max' => 'El puntaje máximo es de 100 puntos.',
        ]);

        // 2. BACKEND DEFENSIVO: Autorización y soporte
        $user = Auth::user();
        if (! $user) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión para crear tareas.');
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

        $soporte = app(TareaInteligente::class);
        $analisisServidor = $soporte->analizar([
            'cod_cla' => $this->codCla,
            'tit_tar' => $this->titulo,
            'des_tar' => $this->descripcion,
            'tip_tar' => $this->tipo,
            'fec_pub_tar' => $this->fechaPublicacion,
            'fec_lim_tar' => $this->fechaLimite,
            'pun_max_tar' => $this->puntajeMaximo,
        ]);

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'Existen observaciones que impiden crear la tarea.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        // 3. PERSISTENCIA TRANSACCIONAL
        DB::beginTransaction();
        try {
            $ultimo = Tarea::where('cod_tar', 'like', 'TAR_%')
                ->orderByDesc('cod_tar')
                ->value('cod_tar');
            $num = $ultimo ? ((int) str_replace('TAR_', '', $ultimo)) + 1 : 1;
            $codTar = 'TAR_' . str_pad($num, 5, '0', STR_PAD_LEFT);

            $tarea = Tarea::create([
                'cod_tar' => $codTar,
                'cod_cla' => $this->codCla,
                'cod_doc' => $codDoc,
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
                    accion: 'CREAR_TAREA',
                    tabla: 'tarea',
                    registro: $tarea->cod_tar,
                    descripcion: "Se creó la actividad '{$tarea->tit_tar}' para la clase {$this->codCla}.",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Actividad académica creada correctamente.');
            $this->dispatch('tarea-creada');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al crear la tarea: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.crear-tarea');
    }
}
