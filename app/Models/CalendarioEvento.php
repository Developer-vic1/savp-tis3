<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarioEvento extends Model
{
    protected $table = 'calendario_evento';

    protected $primaryKey = 'cod_cae';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['cod_cae', 'created_by'];

    protected $casts = ['fii_cae' => 'date', 'ffi_cae' => 'date'];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('Los eventos se cancelan conservando sus revisiones.'));
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'created_by', 'cod_usu');
    }
}
