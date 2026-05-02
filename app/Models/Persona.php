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