<?php

namespace App\Models\AulaVirtual;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UnidadClase extends Model
{
    protected $table = 'unidades_clase';

    protected $guarded = ['id', 'cod_cla', 'created_by'];

    protected $casts = ['publicada' => 'boolean', 'orden' => 'integer', 'archivada_at' => 'datetime'];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \DomainException('Las unidades se archivan conservando sus recursos.'));
    }

    public function clase()
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'created_by', 'cod_usu');
    }

    public function materiales()
    {
        return $this->hasMany(MaterialClase::class, 'unidad_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'unidad_id');
    }
}
