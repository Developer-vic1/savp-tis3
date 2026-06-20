<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $fillable = [
        'codigo',
        'tipo_reporte',
        'formato',
        'nombre_archivo',
        'ruta_archivo',
        'tamano_bytes',
        'hash_archivo',
        'generado_por',
        'estado',
        'observacion',
    ];

    /**
     * Registra un reporte generado correctamente.
     */
    public static function registrar(
        string $codigo,
        string $tipo,
        string $formato,
        string $nombreArchivo,
        string $ruta,
        string $rutaAbsoluta
    ): self {
        $usuario = Auth::user();

        return self::create([
            'codigo'         => $codigo,
            'tipo_reporte'   => $tipo,
            'formato'        => $formato,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo'   => $ruta,
            'tamano_bytes'   => file_exists($rutaAbsoluta) ? filesize($rutaAbsoluta) : 0,
            'hash_archivo'   => file_exists($rutaAbsoluta) ? hash_file('sha256', $rutaAbsoluta) : null,
            'generado_por'   => $usuario
                ? (($usuario->persona?->nom_per ?? '') . ' ' . ($usuario->persona?->ape_pat_per ?? '') ?: $usuario->email)
                : 'Sistema',
            'estado'         => 'generado',
        ]);
    }

    /**
     * Devuelve los últimos reportes generados con archivo existente.
     */
    public static function recientes(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::orderByDesc('created_at')->limit($limit)->get();
    }

    /**
     * Obtiene la ruta absoluta del archivo.
     */
    public function rutaAbsoluta(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('local')->path($this->ruta_archivo);
    }

    /**
     * Verifica si el archivo físico existe.
     */
    public function archivoExiste(): bool
    {
        return file_exists($this->rutaAbsoluta());
    }
}
