<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'cod_per';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_per',      // Código Persona
        'nom_per',      // Nombre Persona
        'ape_pat_per',  // Apellido Paterno Persona
        'ape_mat_per',  // Apellido Materno Persona
        'ci_per',       // Carnet de Identidad Persona
        'com_per',      // Complemento del Carnet de Identidad Persona
        'exp_per',      // Expedición del Carnet de Identidad Persona
        'fec_nac_per',  // Fecha de Nacimiento Persona
        'gen_per',      // Genero Persona
        'tel_per',      // Teléfono Persona
        'ema_per',      // Email Persona
        'dir_per',      // Dirección Persona
        'zona_per',     // Zona Persona
        'ave_per',      // Avenida Persona
        'cal_per',      // Calle Persona
        'num_per',      // Número Persona
        'ref_per',      // Referencia Persona
        'ciu_per',      // Ciudad Persona
        'mun_per',      // Municipio Persona
        'dep_per',      // Departamento Persona
        'fot_per',      // Foto Persona
        'est_per',      // Estado Persona
    ];

    protected static function booted(): void
    {
        static::creating(function ($persona) {
            if (!$persona->cod_per) {
                $ultimo = self::where('cod_per', 'like', 'PER_%')
                    ->orderByDesc('cod_per')
                    ->value('cod_per');

                $numero = $ultimo
                    ? ((int) str_replace('PER_', '', $ultimo)) + 1
                    : 1;

                $persona->cod_per = 'PER_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'cod_per', 'cod_per');
    }
}
