<?php

declare(strict_types=1);

namespace App\Support\Modelos;

use Illuminate\Database\Eloquent\Builder;

/** Genera e inserta el código dentro de una misma transacción. */
trait CodigoInstitucional
{
    protected function performInsert(Builder $query)
    {
        if ($this->getKey() !== null && $this->getKey() !== '') {
            return parent::performInsert($query);
        }
        $conexion = $this->getConnection();
        try {
            return $conexion->transaction(function () use ($conexion, $query) {
                $clave = $this->getKeyName();
                $codigo = FormatoCodigoInstitucional::siguiente($conexion, $this->getTable());
                $this->setAttribute($clave, $codigo);
                $insertado = parent::performInsert($query);
                if (! $insertado) {
                    $this->setAttribute($clave, null);
                }

                return $insertado;
            });
        } catch (\Throwable $error) {
            $this->setAttribute($this->getKeyName(), null);
            $this->exists = false;
            throw $error;
        }
    }
}
