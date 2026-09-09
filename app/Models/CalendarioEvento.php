<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarioEvento extends Model
{
    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('El evento no se elimina. Utilice cancelación.'));
    }

    protected $table = 'calendario_evento';

    protected $primaryKey = 'cod_cae';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['cod_cae', 'cod_gea', 'nom_cae', 'tip_cae', 'fii_cae', 'ffi_cae', 'hoi_cae', 'hof_cae', 'cod_tur', 'cod_cur', 'cod_par', 'cod_hde', 'est_cae', 'efe_cae', 'com_cae', 'cod_cae_ori', 'mot_cae', 'fue_cae', 'ent_cae', 'tip_doc_cae', 'num_doc_cae', 'fec_emi_cae', 'fec_pub_cae', 'url_cae', 'niv_cae', 'cer_cae', 'cod_cae_ant'];

    protected $casts = ['fii_cae' => 'date', 'ffi_cae' => 'date', 'com_cae' => 'boolean'];

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function origen()
    {
        return $this->belongsTo(self::class, 'cod_cae_ori', 'cod_cae');
    }

    public function recuperaciones()
    {
        return $this->hasMany(self::class, 'cod_cae_ori', 'cod_cae');
    }
}
