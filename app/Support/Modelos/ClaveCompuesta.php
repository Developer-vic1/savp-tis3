<?php

declare(strict_types=1);

namespace App\Support\Modelos;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/** Conserva la PK compuesta real; nunca identifica una fila por una sola FK. */
trait ClaveCompuesta
{
    public function getKey(): array
    {
        return array_intersect_key($this->getAttributes(), array_flip($this->columnasClave));
    }

    public function scopePorClave(Builder $query, array $clave): Builder
    {
        if (count($clave) !== count($this->columnasClave) || array_diff($this->columnasClave, array_keys($clave))) {
            throw new InvalidArgumentException('Debes proporcionar todas las columnas de la clave compuesta.');
        }

        foreach ($this->columnasClave as $columna) {
            $query->where($this->qualifyColumn($columna), $clave[$columna]);
        }

        return $query;
    }

    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->columnasClave as $columna) {
            $valor = $this->getRawOriginal($columna) ?? $this->getAttribute($columna);
            if ($valor === null) {
                throw new InvalidArgumentException('Falta la columna de clave compuesta '.$columna.'.');
            }
            $query->where($columna, $valor);
        }

        return $query;
    }

    protected function setKeysForSelectQuery($query)
    {
        return $this->setKeysForSaveQuery($query);
    }

    public function getRouteKey()
    {
        throw new InvalidArgumentException('Una relación con PK compuesta requiere identificar explícitamente todas sus claves.');
    }

    public function delete()
    {
        $this->mergeAttributesFromCachedCasts();
        if (! $this->exists) {
            return null;
        }
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }
        $this->touchOwners();
        $this->performDeleteOnModel();
        $this->fireModelEvent('deleted', false);

        return true;
    }
}
