<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'zona_per',
        'ave_per',
        'cal_per',
        'num_per',
        'ref_per',
        'ciu_per',
        'mun_per',
        'dep_per',
        'fot_per',
        'est_per',
    ];

    protected function casts(): array
    {
        return [
            'est_per' => 'boolean',
            'fec_nac_per' => 'date',
        ];
    }

    public function setEstPerAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['est_per'] = strtoupper($value) === 'ACTIVO' || $value === '1' || $value === 'true';
        } else {
            $this->attributes['est_per'] = (bool) $value;
        }
    }

    protected static function booted(): void
    {
        static::creating(function ($persona) {
            if (! $persona->cod_per) {
                $codigos = self::where('cod_per', 'like', 'PER_%')->pluck('cod_per');
                $maxNumero = 0;
                foreach ($codigos as $cod) {
                    if (preg_match('/^PER_(\d+)$/', $cod, $matches)) {
                        $num = (int) $matches[1];
                        if ($num > $maxNumero) {
                            $maxNumero = $num;
                        }
                    }
                }

                $persona->cod_per = 'PER_'.str_pad((string) ($maxNumero + 1), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'cod_per', 'cod_per');
    }
}
