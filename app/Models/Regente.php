<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regente extends Model
{
    protected $table = 'regente';
    protected $primaryKey = 'cod_reg';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_reg', // Código regente
        'cod_pin', // Código personal institucional
        'est_reg', // Estado regente
    ];

    protected static function booted(): void
    {
        static::creating(function ($regente) {

            if (!$regente->cod_reg) {

                $ultimo = self::where('cod_reg', 'like', 'REG_%')
                    ->orderByDesc('cod_reg')
                    ->value('cod_reg');

                $numero = $ultimo
                    ? ((int) str_replace('REG_', '', $ultimo)) + 1
                    : 1;

                $regente->cod_reg = 'REG_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }
}
