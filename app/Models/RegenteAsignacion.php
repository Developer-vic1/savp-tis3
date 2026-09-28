<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegenteAsignacion extends Model
{
    protected $table = 'regente_asignaciones';
    protected $fillable = ['cod_reg', 'cod_gea', 'cod_cur', 'activa'];
    protected $casts = ['activa' => 'boolean'];

    public function regente() { return $this->belongsTo(Regente::class, 'cod_reg', 'cod_reg'); }
    public function gestion() { return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea'); }
    public function curso() { return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur'); }
}
