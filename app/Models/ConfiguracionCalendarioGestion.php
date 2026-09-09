<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configuración de días lectivos por gestión y trimestre.
 *
 * Reemplaza cualquier constante dispersa del tipo "200 días" o "66/68/66".
 * Cada gestión puede tener su propia distribución.
 *
 * Ejemplo 2026:
 *   Trim 1 → 66 días (fii: 2026-02-02, ffi: 2026-05-15 aprox.)
 *   Trim 2 → 68 días (fii: 2026-05-18, ffi: 2026-08-28 aprox.)
 *   Trim 3 → 66 días (fii: 2026-09-01, ffi: 2026-12-02 aprox.)
 *   Total  → 200 días
 */
class ConfiguracionCalendarioGestion extends Model
{
    protected $table = 'configuracion_calendario_gestion';

    protected $primaryKey = 'cod_ccg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ccg', 'cod_gea', 'num_tri_ccg', 'dias_req_ccg',
        'fii_tri_ccg', 'ffi_tri_ccg', 'est_ccg',
    ];

    protected $casts = [
        'fii_tri_ccg' => 'date',
        'ffi_tri_ccg' => 'date',
        'dias_req_ccg' => 'integer',
        'num_tri_ccg' => 'integer',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Devuelve el total de días lectivos requeridos para toda la gestión.
     */
    public static function totalDiasGestion(string $codGea): int
    {
        return (int) static::where('cod_gea', $codGea)
            ->where('est_ccg', 'ACTIVO')
            ->sum('dias_req_ccg');
    }

    /**
     * Devuelve la distribución de días por trimestre para una gestión.
     * Ejemplo: [1 => 66, 2 => 68, 3 => 66]
     */
    public static function distribucionPorTrimestre(string $codGea): array
    {
        return static::where('cod_gea', $codGea)
            ->where('est_ccg', 'ACTIVO')
            ->orderBy('num_tri_ccg')
            ->pluck('dias_req_ccg', 'num_tri_ccg')
            ->all();
    }
}
