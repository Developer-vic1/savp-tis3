<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\Docente;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\AulaVirtual\MaterialInteligente;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MaterialService
{
    /**
     * Crea y almacena un nuevo material de clase.
     */
    public function crear(array $datos, User $usuario, ?UploadedFile $archivo = null, ?Docente $docente = null): MaterialClase
    {
        $codCla = $datos['cod_cla'] ?? '';
        $clase = ClaseVirtual::with('planAsignatura')->find($codCla);

        if ($docente && $clase && $clase->planAsignatura && $clase->planAsignatura->cod_doc !== $docente->cod_doc) {
            abort(403, 'No estás autorizado para publicar materiales en este curso.');
        }

        // Ejecutar soporte inteligente
        $soporte = app(MaterialInteligente::class);
        $analisis = $soporte->analizar([
            'cod_cla' => $codCla,
            'nom_mat' => $datos['nom_mat'] ?? '',
            'tip_mat' => $datos['tip_mat'] ?? 'DOCUMENTO',
            'url_mat' => $datos['url_mat'] ?? '',
            'archivo' => $archivo ? ['nombre' => $archivo->getClientOriginalName(), 'tamano' => $archivo->getSize()] : null,
        ]);

        if (! ($analisis['puede_guardar'] ?? false)) {
            $primerBloqueo = $analisis['bloqueos'][0] ?? 'El recurso no cumple con los estándares institucionales requeridos.';
            throw ValidationException::withMessages(['material' => $primerBloqueo]);
        }

        $disco = config('savp.aula_virtual.materiales.disco_almacenamiento', 'local');
        $rutaArchivo = null;

        return DB::transaction(function () use ($datos, $codCla, $usuario, $archivo, $disco, &$rutaArchivo) {
            try {
                if ($archivo) {
                    $rutaArchivo = $archivo->store('aula-virtual/materiales', $disco);
                }

                $ultimo = MaterialClase::where('cod_mat', 'like', 'MATC_%')
                    ->orderByDesc('cod_mat')
                    ->value('cod_mat');
                $num = $ultimo ? ((int) str_replace('MATC_', '', $ultimo)) + 1 : 1;
                $codMat = 'MATC_' . str_pad($num, 4, '0', STR_PAD_LEFT);

                $material = MaterialClase::create([
                    'cod_mat' => $codMat,
                    'cod_cla' => $codCla,
                    'cod_usu' => $usuario->cod_usu,
                    'nom_mat' => trim($datos['nom_mat']),
                    'tip_mat' => $datos['tip_mat'] ?? 'DOCUMENTO',
                    'rut_mat' => $rutaArchivo,
                    'url_mat' => isset($datos['url_mat']) ? trim($datos['url_mat']) : null,
                    'mime_mat' => $archivo ? $archivo->getMimeType() : null,
                    'tam_mat' => $archivo ? $archivo->getSize() : null,
                    'est_mat' => in_array($datos['est_mat'] ?? '', ['ACTIVO', 'INACTIVO'], true) ? $datos['est_mat'] : 'ACTIVO',
                ]);

                if (class_exists(BitacoraService::class)) {
                    app(BitacoraService::class)->registrar(
                        accion: 'CREAR_MATERIAL',
                        tabla: 'material_clase',
                        registro: $material->cod_mat,
                        descripcion: "Se publicó el material '{$material->nom_mat}' para el curso {$codCla}.",
                        nivel: 'SUCCESS'
                    );
                }

                return $material;
            } catch (\Throwable $e) {
                if ($rutaArchivo && Storage::disk($disco)->exists($rutaArchivo)) {
                    Storage::disk($disco)->delete($rutaArchivo);
                }
                throw $e;
            }
        });
    }

    /**
     * Descarga segura de material educativo.
     */
    public function descargar(MaterialClase $material, User $usuario)
    {
        $disco = config('savp.aula_virtual.materiales.disco_almacenamiento', 'local');
        abort_if(! $material->rut_mat || ! Storage::disk($disco)->exists($material->rut_mat), 404, 'Material no encontrado en almacenamiento.');

        return Storage::disk($disco)->download($material->rut_mat, $material->nom_mat);
    }

    public function eliminar(MaterialClase $material, User $usuario): void
    {
        $disco = config('savp.aula_virtual.materiales.disco_almacenamiento', 'local');
        if ($material->rut_mat && Storage::disk($disco)->exists($material->rut_mat)) {
            Storage::disk($disco)->delete($material->rut_mat);
        }
        $material->delete();
    }
}
