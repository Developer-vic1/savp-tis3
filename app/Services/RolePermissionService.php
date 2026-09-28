<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionService
{
    private const ADMIN_CRITICAL = [
        'Panel_Administrador',
        'roles-permisos.gestionar',
        'usuarios.asignar_roles',
    ];

    public function sync(Role $role, array $permissionNames, User $actor): void
    {
        if (! $actor->hasRole('Administrador') || ! $actor->can('roles-permisos.gestionar') || $actor->est_usu !== 'ACTIVO' || $role->guard_name !== 'web') {
            throw new AuthorizationException('No tienes autorización para administrar roles y permisos.');
        }

        $valid = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_values(array_unique($permissionNames)))
            ->pluck('name')
            ->all();

        if (count($valid) !== count(array_unique($permissionNames))) {
            throw ValidationException::withMessages([
                'permissions' => 'La selección contiene permisos inexistentes o no autorizados.',
            ]);
        }

        if ($role->name === 'Administrador') {
            $existingCritical = Permission::query()->whereIn('name', self::ADMIN_CRITICAL)->pluck('name')->all();
            $missing = array_diff($existingCritical, $valid);

            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'permissions' => 'El rol Administrador debe conservar sus permisos críticos.',
                ]);
            }
        }

        $before = $role->permissions()->pluck('name')->sort()->values()->all();

        DB::transaction(function () use ($role, $valid, $actor, $before): void {
            $role->syncPermissions($valid);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            BitacoraService::registrar(
                accion: 'ACTUALIZAR_PERMISOS_ROL',
                tabla: 'roles',
                registro: (string) $role->getKey(),
                modulo: 'Roles y Permisos',
                nombreRegistro: $role->name,
                descripcion: 'Se actualizaron los permisos del rol por un Administrador autorizado.',
                valoresAnteriores: ['permisos' => $before, 'actor' => $actor->cod_usu],
                valoresNuevos: ['permisos' => collect($valid)->sort()->values()->all()]
            );
        });
    }
}
