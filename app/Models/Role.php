<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/** Identidad canónica sobre la misma tabla Spatie, sin inventar columnas de estado. */
class Role extends SpatieRole
{
    public const INSTITUTIONAL = ['Administrador', 'Director', 'Secretaria', 'Regente', 'Docente', 'Estudiante'];
}
