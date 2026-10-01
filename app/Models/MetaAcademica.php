<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAcademica extends Model
{
    protected $table = 'metas_academicas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id', 'cod_est', 'created_by'];

    protected $casts = ['fecha_objetivo' => 'date'];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('Las metas conservan historial; se cancelan, no se eliminan.'));
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function gestion()
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'created_by', 'cod_usu');
    }
}
