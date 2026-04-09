<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInstitucional extends Model
{
    protected $table = 'personal_institucional';
    protected $primaryKey = 'cod_pin';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_pin', // Código personal institucional
        'cod_per', // Código persona
        'car_pin', // Cargo institucional
        'est_pin', // Estado del personal
    ];

    protected static function booted(): void
    {
        static::creating(function ($personal) {

            if (!$personal->cod_pin) {

                $ultimo = self::where('cod_pin', 'like', 'PIN_%')
                    ->orderByDesc('cod_pin')
                    ->value('cod_pin');

                $numero = $ultimo
                    ? ((int) str_replace('PIN_', '', $ultimo)) + 1
                    : 1;

                $personal->cod_pin = 'PIN_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relaciones

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }

    public function docente()
    {
        return $this->hasOne(Docente::class, 'cod_pin', 'cod_pin');
    }

    public function secretariaGeneral()
    {
        return $this->hasOne(SecretariaGeneral::class, 'cod_pin', 'cod_pin');
    }
}