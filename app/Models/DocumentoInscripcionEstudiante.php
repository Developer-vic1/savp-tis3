<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoInscripcionEstudiante extends Model
{
    protected $table = 'documento_inscripcion_estudiante';
    protected $primaryKey = 'cod_die';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_die',          // Código documento inscripción
        'cod_ins',          // Inscripción relacionada
        'nom_die',          // Nombre del documento
        'tip_die',          // Tipo documental
        'est_die',          // Estado documental
        'obs_die',          // Observación
        'fec_pre_die',      // Fecha de presentación
        'registrado_por',   // Usuario que registra
    ];

    protected $casts = [
        'fec_pre_die' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($documento) {
            if (! $documento->cod_die) {
                $ultimo = self::where('cod_die', 'like', 'DIE_%')
                    ->orderByDesc('cod_die')
                    ->value('cod_die');

                $numero = $ultimo
                    ? ((int) str_replace('DIE_', '', $ultimo)) + 1
                    : 1;

                $documento->cod_die = 'DIE_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }
}
