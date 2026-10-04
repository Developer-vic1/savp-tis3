<?php

namespace App\Models\Oficial\Academico;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'persona';

    protected $primaryKey = 'cod_per';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_per',
        'nom_per',
        'ape_pat_per',
        'ape_mat_per',
        'ci_per',
        'com_per',
        'exp_per',
        'fec_nac_per',
        'gen_per',
        'tel_per',
        'ema_per',
        'dir_per',
        'fot_per',
        'est_per',
    ];

    protected static function booted(): void
    {
        static::creating(function ($persona) {
            if (! $persona->cod_per) {
                $persona->cod_per = 'PER_'.strtoupper(bin2hex(random_bytes(8)));
            }
        });
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'cod_per', 'cod_per');
    }

    protected $casts = [
        'fec_nac_per' => 'date',
    ];

    public function usersRegistros(): HasOne
    {
        return $this->hasOne(User::class, 'cod_per', 'cod_per');
    }

    public function personalInstitucionalRegistros(): HasOne
    {
        return $this->hasOne(PersonalInstitucional::class, 'cod_per', 'cod_per');
    }

    public function estudianteRegistros(): HasOne
    {
        return $this->hasOne(Estudiante::class, 'cod_per', 'cod_per');
    }

    public function estudianteResponsableRegistros(): HasMany
    {
        return $this->hasMany(EstudianteResponsable::class, 'cod_per', 'cod_per');
    }
}
