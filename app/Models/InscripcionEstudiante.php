<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscripcionEstudiante extends Model
{
    protected $table = 'inscripcion_estudiante';
    protected $primaryKey = 'cod_ins';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins', // Código inscripción estudiante
        'cod_est', // Código estudiante
        'cod_cur', // Código curso
        'cod_par', // Código paralelo
        'cod_tur', // Código turno
        'cod_gea', // Código gestión académica
        'fei_ins', // Fecha inscripción
        'est_ins', // Estado inscripción
    ];

    protected static function booted(): void
    {
        static::creating(function ($inscripcion) {

            if (!$inscripcion->cod_ins) {

                $ultimo = self::where('cod_ins', 'like', 'INS_%')
                    ->orderByDesc('cod_ins')
                    ->value('cod_ins');

                $numero = $ultimo
                    ? ((int) str_replace('INS_', '', $ultimo)) + 1
                    : 1;

                $inscripcion->cod_ins = 'INS_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function gestionAcademica()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }
}