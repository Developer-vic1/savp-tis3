<?php

namespace App\Models\Legado;

use App\Models\Administrador;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Oficial\Academico\DocumentoPersonal;
use App\Models\Oficial\Academico\VinculoPersonal;
use App\Models\Persona;
use App\Models\SecretariaGeneral;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

            if (! $personal->cod_pin) {
                $personal->cod_pin = 'PIN_'.strtoupper(bin2hex(random_bytes(8)));
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

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'cod_pin', 'cod_pin');
    }

    public function director()
    {
        return $this->hasOne(Director::class, 'cod_pin', 'cod_pin');
    }

    public function usuario()
    {
        return $this->hasOneThrough(
            User::class,
            Persona::class,
            'cod_per',
            'cod_per',
            'cod_per',
            'cod_per'
        );
    }

    public function vinculoPersonalRegistros(): HasMany
    {
        return $this->hasMany(VinculoPersonal::class, 'cod_pin', 'cod_pin');
    }

    public function documentoPersonalRegistros(): HasMany
    {
        return $this->hasMany(DocumentoPersonal::class, 'cod_pin', 'cod_pin');
    }

    public function docenteRegistros(): HasOne
    {
        return $this->hasOne(\App\Models\Oficial\Academico\Docente::class, 'cod_pin', 'cod_pin');
    }
}
