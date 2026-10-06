<?php

namespace App\Support\Modelos;

/** Conserva el instante aunque la sesión de PostgreSQL use America/La_Paz. */
trait FechasConZonaHoraria
{
    public function getDateFormat()
    {
        return $this->getConnection()->getDriverName() === 'pgsql' ? 'Y-m-d H:i:sP' : parent::getDateFormat();
    }
}
