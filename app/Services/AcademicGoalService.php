<?php

namespace App\Services;

use App\Models\Oficial\Sistema\User;

class AcademicGoalService
{
    public function available(): bool
    {
        return false;
    }

    public function snapshot(User $user): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);

        return ['available' => false, 'goals' => null];
    }

    public function save(User $user, array $data, ?string $id = null): never
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        abort(409, 'Las metas personales no forman parte del esquema oficial.');
    }

    public function find(User $user, string $id): never
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        abort(409, 'Las metas personales no forman parte del esquema oficial.');
    }

    public function validateDraft(User $user, array $data, bool $revision = false): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);

        return validator($data, ['titulo' => 'required|string|max:180', 'objetivo' => 'required|string|max:4000',
            'accion' => 'nullable|string|max:4000', 'fecha_objetivo' => 'nullable|date',
            'estado' => $revision ? 'required|in:BORRADOR,ACTIVA,COMPLETADA,CANCELADA' : 'required|in:BORRADOR,ACTIVA',
            'motivo' => ($revision ? 'required' : 'nullable').'|string|max:2000'])->validate();
    }
}
