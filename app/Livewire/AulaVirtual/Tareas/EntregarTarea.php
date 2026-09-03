<?php

namespace App\Livewire\AulaVirtual\Tareas;

use App\Models\AulaVirtual\ClaseEstudiante;
use App\Models\AulaVirtual\EntregaArchivo;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\Tarea;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\EntregaTareaInteligente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EntregarTarea extends Component
{
    use WithFileUploads;

    public string $codTar = '';
    public ?Tarea $tarea = null;
    public ?Estudiante $estudiante = null;
    public ?EntregaTarea $entrega = null;

    public string $texto = '';
    public $archivo = null;

    public array $analisis = [
        'puede_guardar' => false,
        'puede_continuar' => false,
        'estado' => 'OK',
        'bloqueos' => [],
        'advertencias' => [],
        'sugerencias' => [],
        'datos_calculados' => [],
    ];

    public function mount(string $codTar): void
    {
        $this->codTar = $codTar;
        $this->tarea = Tarea::with(['claseVirtual.planAsignatura.asignatura'])->find($this->codTar);

        $user = Auth::user();
        if ($user) {
            $this->estudiante = Estudiante::where('cod_per', $user->cod_per)->first();
            if ($this->estudiante && $this->tarea) {
                $this->entrega = EntregaTarea::with('archivos')
                    ->where('cod_tar', $this->codTar)
                    ->where('cod_est', $this->estudiante->cod_est)
                    ->first();

                if ($this->entrega) {
                    $this->texto = (string) $this->entrega->tex_ent;
                }
            }
        }

        $this->analizarEnTiempoReal();
    }

    public function updated($propertyName): void
    {
        $this->analizarEnTiempoReal();
    }

    public function analizarEnTiempoReal(): void
    {
        if (! $this->tarea || ! $this->estudiante) {
            return;
        }

        $archivosInfo = [];
        if ($this->archivo) {
            $archivosInfo[] = ['nombre' => $this->archivo->getClientOriginalName()];
        } elseif ($this->entrega && $this->entrega->archivos->isNotEmpty()) {
            $archivosInfo[] = ['nombre' => 'archivo_existente'];
        }

        $soporte = app(EntregaTareaInteligente::class);
        $this->analisis = $soporte->analizarEnvio(
            codTar: $this->codTar,
            codEst: $this->estudiante->cod_est,
            datos: [
                'tex_ent' => $this->texto,
                'archivos' => $archivosInfo,
                'fec_ent' => now(),
            ]
        );
    }

    public function enviarEntrega(): void
    {
        if (! $this->tarea) {
            $this->tarea = Tarea::find($this->codTar);
        }
        if (! $this->estudiante && Auth::user()) {
            $this->estudiante = Estudiante::where('cod_per', Auth::user()->cod_per)->first();
        }

        // 1. FRONTEND + BACKEND VALIDATION
        $this->validate([
            'texto' => ['nullable', 'string', 'max:5000'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,docx,doc,xlsx,xls,pptx,ppt,zip,rar,jpg,jpeg,png'],
        ], [
            'archivo.max' => 'El archivo supera el tamaño máximo permitido de 10 MB.',
            'archivo.mimes' => 'Formato no permitido. Solo se aceptan documentos (PDF, DOCX, XLSX, PPTX, ZIP, imágenes).',
        ]);

        $user = Auth::user();
        if (! $user || ! $this->estudiante) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión como estudiante para entregar actividades.');
            return;
        }

        // 2. BACKEND DEFENSIVO: Pertenencia y revalidación de soporte
        $pertenece = ClaseEstudiante::where('cod_cla', $this->tarea->cod_cla)
            ->where('cod_est', $this->estudiante->cod_est)
            ->where('est_cla_est', 'ACTIVO')
            ->exists();

        if (! $pertenece) {
            $this->dispatch('error-general', mensaje: 'No perteneces a la clase de esta tarea.');
            return;
        }

        $archivosInfo = [];
        if ($this->archivo) {
            $archivosInfo[] = ['nombre' => $this->archivo->getClientOriginalName()];
        } elseif ($this->entrega && $this->entrega->archivos->isNotEmpty()) {
            $archivosInfo[] = ['nombre' => 'archivo_previo'];
        }

        $soporte = app(EntregaTareaInteligente::class);
        $analisisServidor = $soporte->analizarEnvio(
            codTar: $this->codTar,
            codEst: $this->estudiante->cod_est,
            datos: [
                'tex_ent' => $this->texto,
                'archivos' => $archivosInfo,
                'fec_ent' => now(),
            ]
        );

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'No se puede enviar una entrega vacía.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        $esTardia = $analisisServidor['datos_calculados']['es_tardia'] ?? false;
        $estadoEntrega = $esTardia ? 'ENTREGADO_TARDE' : 'ENTREGADO';

        // 3. PERSISTENCIA TRANSACCIONAL
        DB::beginTransaction();
        try {
            if (! $this->entrega) {
                $ultimo = EntregaTarea::where('cod_ent', 'like', 'ENT_%')
                    ->orderByDesc('cod_ent')
                    ->value('cod_ent');
                $num = $ultimo ? ((int) str_replace('ENT_', '', $ultimo)) + 1 : 1;
                $codEnt = 'ENT_' . str_pad($num, 5, '0', STR_PAD_LEFT);

                $this->entrega = EntregaTarea::create([
                    'cod_ent' => $codEnt,
                    'cod_tar' => $this->codTar,
                    'cod_est' => $this->estudiante->cod_est,
                    'fec_ent' => now(),
                    'tex_ent' => trim($this->texto),
                    'est_ent' => $estadoEntrega,
                ]);
            } else {
                $this->entrega->update([
                    'fec_ent' => now(),
                    'tex_ent' => trim($this->texto),
                    'est_ent' => $estadoEntrega,
                ]);
            }

            // Procesar archivo adjunto si se subió uno nuevo
            if ($this->archivo) {
                $path = $this->archivo->store('entregas_tareas', 'public');

                $ultimoArc = EntregaArchivo::where('cod_ear', 'like', 'EAR_%')
                    ->orderByDesc('cod_ear')
                    ->value('cod_ear');
                $numArc = $ultimoArc ? ((int) str_replace('EAR_', '', $ultimoArc)) + 1 : 1;
                $codEar = 'EAR_' . str_pad($numArc, 5, '0', STR_PAD_LEFT);

                EntregaArchivo::create([
                    'cod_ear' => $codEar,
                    'cod_ent' => $this->entrega->cod_ent,
                    'nom_ear' => $this->archivo->getClientOriginalName(),
                    'url_ear' => $path,
                    'tam_ear' => (int) $this->archivo->getSize(),
                    'mim_ear' => $this->archivo->getMimeType(),
                ]);
            }

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'ENTREGAR_TAREA',
                    tabla: 'entrega_tarea',
                    registro: $this->entrega->cod_ent,
                    descripcion: "El estudiante {$this->estudiante->cod_est} realizó la entrega de la tarea {$this->codTar} ({$estadoEntrega}).",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Tu entrega ha sido enviada exitosamente.');
            $this->dispatch('entrega-guardada');
            $this->archivo = null;
            $this->mount($this->codTar);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al enviar tu entrega: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.tareas.entregar-tarea');
    }
}
