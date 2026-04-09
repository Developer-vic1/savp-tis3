<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVinculacionEstudiante extends Model
{
    protected $table = 'tipo_vinculacion_estudiante';
    protected $primaryKey = 'cod_tve';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_tve', // Código tipo vinculación estudiante
        'nom_tve', // Nombre tipo vinculación
        'des_tve', // Descripción tipo vinculación
        'est_tve', // Estado tipo vinculación
    ];

    protected static function booted(): void
    {
        static::creating(function ($tipo) {

            if (!$tipo->cod_tve) {

                $ultimo = self::where('cod_tve', 'like', 'TVE_%')
                    ->orderByDesc('cod_tve')
                    ->value('cod_tve');

                $numero = $ultimo
                    ? ((int) str_replace('TVE_', '', $ultimo)) + 1
                    : 1;

                $tipo->cod_tve = 'TVE_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function inscripciones()
    {
        return $this->hasMany(InscripcionEstudiante::class, 'cod_tve', 'cod_tve');
    }
}
