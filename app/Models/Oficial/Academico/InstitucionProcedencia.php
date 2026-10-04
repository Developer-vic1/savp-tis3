<?php

namespace App\Models\Oficial\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstitucionProcedencia extends Model
{
    protected $table = 'institucion_procedencia';

    protected $primaryKey = 'cod_ipe';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_ipe', // Código institución procedencia
        'nom_ipe', // Nombre institución
        'tip_ipe', // Tipo institución
        'ciu_ipe', // Ciudad institución
        'est_ipe', // Estado institución
        'dep_ipe',
        'dir_ipe',
        'tel_ipe',
        'web_ipe',
    ];

    protected static function booted(): void
    {
        static::creating(function ($institucion) {

            if (! $institucion->cod_ipe) {
                $institucion->cod_ipe = 'IPE_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    // 🔗 Relaciones

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_ipe', 'cod_ipe');
    }

    public function expedienteTrasladoRegistros(): HasMany
    {
        return $this->hasMany(ExpedienteTraslado::class, 'cod_ipe', 'cod_ipe');
    }
}
