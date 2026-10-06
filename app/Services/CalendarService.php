<?php

namespace App\Services;

use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Academico\CalendarioEvento;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CalendarService
{
    public function institutionalAvailable(): bool
    {
        return config('features.institutional_calendar', false) && Schema::hasTable('calendario_evento');
    }

    public function institutionalQuery(User $user): ?Builder
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        abort_unless($actor && $user->can(self::PERMISSIONS[$actor]), 403);
        if (! $this->institutionalAvailable()) {
            return null;
        }
        $query = CalendarioEvento::query()->whereIn('est_cae', ['CONFIRMADO', 'FINALIZADO']);
        if (in_array($actor, ['Docente', 'Estudiante', 'Regente'], true)) {
            if ($actor === 'Regente') {
                $plans = app(RegencyAccessService::class)->constrain(PlanAsignatura::query(), $user, 'plan_asignatura');
            } else {
                $courses = app(CursoVirtualService::class);
                $classes = $actor === 'Docente' ? $courses->teacherQuery($user) : $courses->studentQuery($user);
                $plans = PlanAsignatura::whereIn('cod_pas', $classes->reorder()->select('cod_pas')->withoutEagerLoads());
            }
            $plans->join('grupo_academico as contexto_calendario', 'contexto_calendario.cod_gac', '=', 'plan_asignatura.cod_gac')
                ->selectRaw('1')->whereColumn('contexto_calendario.cod_gea', 'calendario_evento.cod_gea');
            foreach (['cod_cur', 'cod_par', 'cod_tur'] as $field) {
                $plans->where(fn ($q) => $q->whereNull('calendario_evento.'.$field)->orWhereColumn('contexto_calendario.'.$field, 'calendario_evento.'.$field));
            }
            $query->whereExists($plans->toBase());
        }

        return $query;
    }

    public const PERMISSIONS = ['Administrador' => 'Gestion_Academica', 'Director' => 'cursos.ver.institucional', 'Secretaria' => 'Gestion_Academica',
        'Regente' => 'cursos.ver.institucional', 'Docente' => 'Calendario_Aula', 'Estudiante' => 'Calendario_Aula'];

    public function query(User $user): Builder
    {
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        abort_unless($actor && $user->can(self::PERMISSIONS[$actor]), 403);
        $query = Tarea::with('claseVirtual.planAsignatura.asignatura', 'claseVirtual.planAsignatura.curso')
            ->whereNotNull('fec_lim_tar')->whereIn('est_tar', ['PUBLICADA', 'CERRADA']);
        if (in_array($actor, ['Docente', 'Estudiante'], true)) {
            $courses = app(CursoVirtualService::class);
            $scope = $actor === 'Docente' ? $courses->teacherQuery($user) : $courses->studentQuery($user);
            $query->whereIn('cod_cla', $scope->reorder()->select('cod_cla')->withoutEagerLoads());
        } elseif ($actor === 'Regente') {
            $query->whereHas('claseVirtual.planAsignatura', fn ($plan) => app(RegencyAccessService::class)->constrain($plan, $user, 'plan_asignatura'));
        }

        return $query;
    }
}
