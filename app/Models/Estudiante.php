<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'cod_est';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_est',
        'rud_est',
        'cod_per',
        'cod_tve',
        'cod_ipe',
        'cod_esp',
        'est_est',
    ];

    protected static function booted(): void
    {
        static::creating(function ($estudiante) {

            if (!$estudiante->cod_est) {

                $ultimo = self::where('cod_est', 'like', 'EST_%')
                    ->orderByDesc('cod_est')
                    ->value('cod_est');

                $numero = $ultimo
                    ? ((int) str_replace('EST_', '', $ultimo)) + 1
                    : 1;

                $estudiante->cod_est = 'EST_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }

    public function tipoVinculacion()
    {
        return $this->belongsTo(TipoVinculacionEstudiante::class, 'cod_tve', 'cod_tve');
    }

    public function institucionProcedencia()
    {
        return $this->belongsTo(InstitucionProcedencia::class, 'cod_ipe', 'cod_ipe');
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp', 'cod_esp');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'cod_est', 'cod_est');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_est', 'cod_est');
    }
}