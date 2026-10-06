<?php

namespace App\Services\Kardex;

use App\Contracts\KardexRepository;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\SeguimientoAcademico;
use App\Models\Oficial\Sistema\User;
use App\Policies\KardexPolicy;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\RegencyAccessService;
use App\Services\RoleDashboardResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Lectura futura bajo aprobación explícita, esquema y catálogos presentes. */
class ScopedKardexRepository implements KardexRepository
{
    public function available(): bool
    {
        return config('features.kardex', false) && Schema::hasTable('seguimiento_academico')
            && Schema::hasTable('kardex_estados') && DB::table('kardex_estados')->where('activo', true)->exists();
    }

    public function scopedQuery(User $user, Estudiante $student): Builder
    {
        abort_unless(app(KardexPolicy::class)->view($user, $student), 403);
        $actor = app(RoleDashboardResolver::class)->roleFor($user);
        $query = SeguimientoAcademico::query()->where('cod_est', $student->cod_est);
        if ($actor === 'Regente') {
            app(RegencyAccessService::class)->constrain($query, $user, 'seguimiento_academico');
        }
        if ($actor === 'Estudiante') {
            $query->where('visible_estudiante', true)->where('vis_seg', 'NORMAL');
        }
        if ($actor === 'Docente') {
            $courses = app(CursoVirtualService::class);
            $query->whereIn('cod_pas', $courses->teacherQuery($user)->reorder()->select('cod_pas')->withoutEagerLoads())
                ->whereHas('planAsignatura', fn ($plan) => $plan->whereExists(function ($enrollment) {
                    $enrollment->selectRaw('1')->from('inscripcion_estudiante')->where('est_ins', 'ACTIVA')
                        ->whereColumn('inscripcion_estudiante.cod_est', 'seguimiento_academico.cod_est');
                    foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $field) {
                        $enrollment->whereColumn('inscripcion_estudiante.'.$field, 'plan_asignatura.'.$field);
                    }
                }));
        }
        $columns = ['cod_seg', 'cod_est', 'cod_gea', 'cod_cur', 'tip_seg', 'est_seg', 'fec_ape_seg', 'fec_pro_seg', 'fec_cie_seg'];
        if (! in_array($actor, ['Secretaria', 'Administrador'], true)) {
            $columns = [...$columns, 'mot_seg', 'res_seg', 'pro_acc_seg'];
        }

        return $query->select($columns);
    }

    public function timeline(User $user, Estudiante $student): array
    {
        abort_unless($this->available(), 409, 'Kardex requiere esquema y catálogos aprobados.');

        return $this->scopedQuery($user, $student)->orderByDesc('fec_ape_seg')->orderByDesc('cod_seg')->limit(50)->get()->toArray();
    }
}
