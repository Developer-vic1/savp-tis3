<?php

namespace App\Models\Oficial\Academico;

use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        'cod_usu',
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
            'codigo' => $codigo,
            'tipo_reporte' => $tipo,
            'formato' => $formato,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $ruta,
            'tamano_bytes' => file_exists($rutaAbsoluta) ? filesize($rutaAbsoluta) : 0,
            'hash_archivo' => file_exists($rutaAbsoluta) ? hash_file('sha256', $rutaAbsoluta) : null,
            'generado_por' => $usuario
                ? (($usuario->persona?->nom_per ?? '').' '.($usuario->persona?->ape_pat_per ?? '') ?: $usuario->email)
                : 'Sistema',
            'estado' => 'generado',
            'cod_usu' => $usuario?->cod_usu,
        ]);
    }

    /**
     * Devuelve los últimos reportes generados con archivo existente.
     */
    public static function recientes(int $limit = 10): Collection
    {
        return self::orderByDesc('created_at')->limit($limit)->get();
    }

    /**
     * Obtiene la ruta absoluta del archivo.
     */
    public function rutaAbsoluta(): string
    {
        return Storage::disk('local')->path($this->ruta_archivo);
    }

    /**
     * Verifica si el archivo físico existe.
     */
    public function archivoExiste(): bool
    {
        return file_exists($this->rutaAbsoluta());
    }

    protected $casts = [
        'tamano_bytes' => 'integer',
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}
