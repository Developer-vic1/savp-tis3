<?php

namespace App\Support;

use App\Models\Oficial\Sistema\User;
use App\Services\RoleDashboardResolver;

/** Delegación explícita de este módulo, sin convertir al docente en administrador. */
final class AccesoGestionCursos
{
    public const PERMISO_DELEGADO = 'cursos.gestionar.global';

    public static function permite(?User $usuario): bool
    {
        if (!$usuario) return false;
        return match (app(RoleDashboardResolver::class)->roleFor($usuario)) {
            'Administrador' => $usuario->can('Cursos'),
            // Incluye vigencias expresas; no se usa el permiso histórico Cursos del docente.
            'Docente' => $usuario->hasPermissionTo(self::PERMISO_DELEGADO),
            default => false,
        };
    }

    public static function autorizar(): void
    {
        abort_unless(self::permite(auth()->user()), 403, 'Necesitas autorización expresa para gestionar el catálogo institucional de cursos.');
    }
}
