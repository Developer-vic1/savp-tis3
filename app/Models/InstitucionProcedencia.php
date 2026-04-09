<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected static function booted(): void
    {
        static::creating(function ($institucion) {

            if (!$institucion->cod_ipe) {

                $ultimo = self::where('cod_ipe', 'like', 'IPE_%')
                    ->orderByDesc('cod_ipe')
                    ->value('cod_ipe');

                $numero = $ultimo
                    ? ((int) str_replace('IPE_', '', $ultimo)) + 1
                    : 1;

                $institucion->cod_ipe = 'IPE_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'cod_ipe', 'cod_ipe');
    }
}
