<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\Tarea;
use App\Models\Docente;

class TareaService
{
    public function crear(array $datos, Docente $docente): Tarea
    {
        $datos['cod_doc'] = $docente->cod_doc;
        $datos['est_tar'] = $datos['est_tar'] ?? 'BORRADOR';

        return Tarea::create($datos);
    }

    public function publicar(Tarea $tarea): void
    {
        $tarea->publicar();
    }

    public function cerrar(Tarea $tarea): void
    {
        $tarea->cerrar();
    }
}
