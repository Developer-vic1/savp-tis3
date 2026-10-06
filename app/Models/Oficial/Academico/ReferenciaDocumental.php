<?php

namespace App\Models\Oficial\Academico;

use Illuminate\Database\Eloquent\Model;

class ReferenciaDocumental extends Model
{
    protected $table='referencia_documental';
    public $incrementing=false;
    protected $keyType='string';
    protected $guarded=[];
    protected function casts(): array { return ['vigente'=>'boolean']; }
}
