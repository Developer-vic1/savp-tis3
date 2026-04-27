<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $table = 'director';
    protected $primaryKey = 'cod_dir';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_dir', // Código director
        'cod_pin', // Código personal institucional
        'est_dir', // Estado director
    ];

    protected static function booted(): void
    {
        static::creating(function ($director) {

            if (!$director->cod_dir) {

                $ultimo = self::where('cod_dir', 'like', 'DIR_%')
                    ->orderByDesc('cod_dir')
                    ->value('cod_dir');

                $numero = $ultimo
                    ? ((int) str_replace('DIR_', '', $ultimo)) + 1
                    : 1;

                $director->cod_dir = 'DIR_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }
}
