<?php

namespace App\Livewire\AulaVirtual\Materiales;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\MaterialInteligente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearMaterial extends Component
{
    use WithFileUploads;

    public string $codCla = '';
    public ?ClaseVirtual $clase = null;

    public string $nombre = '';
    public string $tipo = 'DOCUMENTO';
    public string $url = '';
    public $archivo = null;
    public string $estado = 'ACTIVO';

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
        $soporte = app(MaterialInteligente::class);
        $this->analisis = $soporte->analizar([
            'cod_cla' => $this->codCla,
            'nom_mat' => $this->nombre,
            'url_mat' => $this->url,
            'archivo' => $this->archivo ? true : false,
        ]);
    }

    public function guardarMaterial(): void
    {
        // 1. FRONTEND + BACKEND VALIDATION
        $this->validate([
            'codCla' => ['required', 'string', 'exists:clase_virtual,cod_cla'],
            'nombre' => ['required', 'string', 'min:3', 'max:180'],
            'tipo' => ['required', 'in:DOCUMENTO,PRESENTACION,VIDEO,ENLACE,EJERCICIO,LECTURA'],
            'url' => ['nullable', 'url', 'max:500'],
            'archivo' => ['nullable', 'file', 'max:25600', 'mimes:pdf,docx,doc,xlsx,xls,pptx,ppt,zip,rar,mp4,mp3,jpg,jpeg,png'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ], [
            'nombre.required' => 'El nombre del material didáctico es obligatorio.',
            'url.url' => 'El formato del enlace no es una dirección web válida (ej. https://ejemplo.com).',
            'archivo.max' => 'El archivo supera el tamaño máximo permitido de 25 MB.',
        ]);

        $user = Auth::user();
        if (! $user) {
            $this->dispatch('error-general', mensaje: 'Debe iniciar sesión para publicar materiales.');
            return;
        }

        // 2. BACKEND DEFENSIVO: Revalidar soporte
        $soporte = app(MaterialInteligente::class);
        $analisisServidor = $soporte->analizar([
            'cod_cla' => $this->codCla,
            'nom_mat' => $this->nombre,
            'url_mat' => $this->url,
            'archivo' => $this->archivo ? true : false,
        ]);

        if (! ($analisisServidor['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisisServidor['bloqueos'][0] ?? 'El material no cumple con los requisitos de publicación.';
            $this->dispatch('error-general', mensaje: $primerBloqueo);
            return;
        }

        // 3. PERSISTENCIA TRANSACCIONAL
        DB::beginTransaction();
        try {
            $rutaArchivo = null;
            $mime = null;
            $tamano = null;

            if ($this->archivo) {
                $rutaArchivo = $this->archivo->store('materiales_clase', 'public');
                $mime = $this->archivo->getMimeType();
                $tamano = (int) $this->archivo->getSize();
            }

            $ultimo = MaterialClase::where('cod_mat', 'like', 'MATC_%')
                ->orderByDesc('cod_mat')
                ->value('cod_mat');
            $num = $ultimo ? ((int) str_replace('MATC_', '', $ultimo)) + 1 : 1;
            $codMat = 'MATC_' . str_pad($num, 4, '0', STR_PAD_LEFT);

            $material = MaterialClase::create([
                'cod_mat' => $codMat,
                'cod_cla' => $this->codCla,
                'cod_usu' => $user->cod_usu,
                'nom_mat' => trim($this->nombre),
                'tip_mat' => $this->tipo,
                'rut_mat' => $rutaArchivo,
                'url_mat' => $this->url ? trim($this->url) : null,
                'mime_mat' => $mime,
                'tam_mat' => $tamano,
                'est_mat' => $this->estado,
            ]);

            if (class_exists(BitacoraService::class)) {
                app(BitacoraService::class)->registrar(
                    accion: 'CREAR_MATERIAL',
                    tabla: 'material_clase',
                    registro: $material->cod_mat,
                    descripcion: "Se publicó el material '{$material->nom_mat}' en la clase {$this->codCla}.",
                    nivel: 'SUCCESS'
                );
            }

            DB::commit();

            $this->dispatch('success-general', mensaje: 'Material didáctico publicado exitosamente.');
            $this->dispatch('material-creado');
            $this->reset(['nombre', 'url', 'archivo']);
            $this->mount($this->codCla);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('error-general', mensaje: 'Ocurrió un error al publicar el material: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.aula-virtual.materiales.crear-material');
    }
}
