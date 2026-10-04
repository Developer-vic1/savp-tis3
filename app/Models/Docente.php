<?php

namespace App\Models;

/** Compatibilidad de consumidores existentes; implementación organizada por dominio. */
class Docente extends Oficial\Academico\Docente
{
    public function planAsignaturas()
    {
        return $this->hasMany(PlanAsignatura::class, 'cod_doc', 'cod_doc');
    }

    public function planEspecialidades()
    {
        return $this->hasMany(PlanEspecialidad::class, 'cod_doc', 'cod_doc');
    }
}
