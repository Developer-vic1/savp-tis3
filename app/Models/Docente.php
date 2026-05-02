<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'cod_doc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc', // Código docente
        'cod_pin', // Código personal institucional
        'esp_doc', // Especialidad docente
        'est_doc', // Estado docente
        'num_mod_doc', // Numero de modificaciones
    ];

    protected static function booted(): void
    {
        static::creating(function ($docente) {

            if (!$docente->cod_doc) {

                $ultimo = self::where('cod_doc', 'like', 'DOC_%')
                    ->orderByDesc('cod_doc')
                    ->value('cod_doc');

                $numero = $ultimo
                    ? ((int) str_replace('DOC_', '', $ultimo)) + 1
                    : 1;

                $docente->cod_doc = 'DOC_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // 🔗 Relaciones

    public function personalInstitucional()
    {
        return $this->belongsTo(PersonalInstitucional::class, 'cod_pin', 'cod_pin');
    }

    public function planAsignaturas()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_doc', 'cod_doc');
    }

    public function planEspecialidades()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_doc', 'cod_doc');
    }
}