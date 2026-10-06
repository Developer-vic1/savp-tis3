<?php

namespace App\Models\Oficial\Academico;

use Illuminate\Database\Eloquent\Model;

class IncorporacionCurricular extends Model
{
    protected $table = 'incorporacion_curricular';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected function casts(): array { return ['datos'=>'array','evidencia'=>'array','gestion'=>'integer']; }
}
