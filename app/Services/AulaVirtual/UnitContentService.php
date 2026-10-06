<?php

namespace App\Services\AulaVirtual;

use App\Models\Oficial\AulaVirtual\SeccionClase;
use App\Models\Oficial\Sistema\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\Schema;

class UnitContentService
{
    public function available(): bool
    {
        return config('features.curricular_units', false) && Schema::hasTable('seccion_clase');
    }

    public function forCourse(User $user, string $course)
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        abort_unless(in_array($actor, ['Docente', 'Estudiante'], true) && $user->can('Acceso_Aula_Virtual') && $user->can('Materiales_Aula'), 403);
        $courses = app(CursoVirtualService::class);
        $class = $actor === 'Docente' ? $courses->cursoParaDocente($user, $course) : $courses->cursoParaEstudiante($user, $course);
        abort_unless($class, 403);
        if (! $this->available()) {
            return null;
        }

        return SeccionClase::where('cod_cla', $course)->where('est_sec', 'ACTIVO')
            ->when($actor === 'Estudiante', fn ($q) => $q->where('vis_sec', true))
            ->withCount(['materiales' => fn ($q) => $q->where('est_mat', 'ACTIVO'), 'tareas' => fn ($q) => $q->whereIn('est_tar', ['PUBLICADA', 'CERRADA'])])
            ->orderBy('ord_sec')->paginate(15, ['*'], 'unitsPage');
    }
}
