<?php

namespace App\Models\Oficial\Academico;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarioEvento extends Model
{
    protected $table = 'calendario_evento';

    protected $primaryKey = 'cod_cae';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['cod_cae', 'created_by'];

    protected $casts = ['fii_cae' => 'date', 'ffi_cae' => 'date', 'fec_emi_cae' => 'date',
        'fec_pub_cae' => 'date',
    ];

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

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function horarioDetalle(): BelongsTo
    {
        return $this->belongsTo(HorarioDetalle::class, 'cod_hde', 'cod_hde');
    }

    public function calendarioEvento(): BelongsTo
    {
        return $this->belongsTo(CalendarioEvento::class, 'cod_cae_ori', 'cod_cae');
    }

    public function calendarioEventoPorCodCaeAnt(): BelongsTo
    {
        return $this->belongsTo(CalendarioEvento::class, 'cod_cae_ant', 'cod_cae');
    }

    public function calendarioEventoRegistros(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'cod_cae_ori', 'cod_cae');
    }

    public function sesionAcademicaRegistros(): HasMany
    {
        return $this->hasMany(SesionAcademica::class, 'cod_cae', 'cod_cae');
    }
}
