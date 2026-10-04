<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;

/** Compatibilidad de consumidores existentes; implementación organizada por dominio. */
class RegenteAsignacion extends Oficial\Academico\RegenteAsignacion
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Solo la estructura heredada usa id; la instalación de 104 tablas
        // conserva cod_ras como identidad institucional.
        if (! Schema::hasColumn($this->getTable(), 'id')) {
            $this->primaryKey = 'cod_ras';
            $this->incrementing = false;
            $this->keyType = 'string';
        } else {
            $this->fillable = array_merge($this->fillable, ['cod_reg', 'activa']);
            $this->casts['activa'] = 'boolean';
        }
    }

    // Identidad previa para el servicio de regencia, hasta su adaptación aprobada.
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected static function booted(): void
    {
        static::creating(function ($asignacion): void {
            // La fixture auxiliar/estructura anterior todavía no tiene cod_ras.
            if (! Schema::hasColumn($asignacion->getTable(), 'cod_ras')) {
                unset($asignacion->cod_ras);
            }
        });
    }
}
