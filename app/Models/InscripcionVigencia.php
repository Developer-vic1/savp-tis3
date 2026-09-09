<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionVigencia extends Model
{
    protected $table = 'inscripcion_vigencia';

    protected $primaryKey = 'cod_ivg';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['cod_ivg', 'cod_ins', 'cod_cur', 'cod_par', 'cod_tur', 'cod_esp_tec', 'fii_ivg', 'ffi_ivg', 'tip_ivg', 'cie_ivg', 'mot_ivg', 'est_ivg'];

    protected $casts = ['fii_ivg' => 'date', 'ffi_ivg' => 'date'];

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function scopeEnFecha($query, string $fecha)
    {
        return $query->where('est_ivg', '<>', 'ANULADA')->whereDate('fii_ivg', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('ffi_ivg')->orWhereDate('ffi_ivg', '>=', $fecha));
    }

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('La vigencia histórica no se elimina. Utilice una corrección administrativa.'));
    }
}
