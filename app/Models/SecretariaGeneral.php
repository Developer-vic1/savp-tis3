<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretariaGeneral extends Model
{
    protected $table = 'secretaria_general';
    protected $primaryKey = 'cod_sge';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_sge', // Código secretaria general
        'cod_pin', // Código personal institucional
        'est_sge', // Estado secretaria general
    ];

    protected static function booted(): void
    {
        static::creating(function ($secretaria) {

            if (!$secretaria->cod_sge) {

                $ultimo = self::where('cod_sge', 'like', 'SGE_%')
                    ->orderByDesc('cod_sge')
                    ->value('cod_sge');

                $numero = $ultimo
                    ? ((int) str_replace('SGE_', '', $ultimo)) + 1
                    : 1;

                $secretaria->cod_sge = 'SGE_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }
}
