<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovedadEstudiante extends Model
{
    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('La novedad no se elimina. Utilice cancelación.'));
    }

    protected $table = 'novedad_estudiante';

    protected $primaryKey = 'cod_nes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['cod_nes', 'cod_est', 'cod_gea', 'tip_nes', 'fii_nes', 'ffi_nes', 'est_nes', 'mot_nes', 'obs_nes', 'rut_res_nes'];

    protected $casts = ['fii_nes' => 'date', 'ffi_nes' => 'date'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }
}
