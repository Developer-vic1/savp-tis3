<?php

namespace App\Policies;

use App\Models\User;
use App\Services\RoleDashboardResolver;

class UserPolicy
{
    public function view(User $actor, User $target): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($actor) && ($actor->is($target)
            || ($actor->hasRole('Administrador') && $actor->can('usuarios.ver.global')));
    }

    public function update(User $actor, User $target): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($actor) && ($actor->is($target) || ($actor->hasRole('Administrador') && $actor->can('usuarios.editar')));
    }

    public function assignRoles(User $actor, User $target): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador' && $actor->can('usuarios.asignar_roles') && $actor->cod_usu !== $target->cod_usu;
    }

    public function deactivate(User $actor, User $target): bool
    {
        return app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador' && $actor->can('usuarios.desactivar') && $actor->cod_usu !== $target->cod_usu;
    }
}
