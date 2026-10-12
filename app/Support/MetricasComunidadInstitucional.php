<?php

namespace App\Support;

use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Facades\DB;

/** Métricas de lectura con las identidades y vigencias del contrato institucional. */
class MetricasComunidadInstitucional
{
    public function roles(User $actor): array
    {
        if (!$actor->can('Gestion_Usuarios')) return [];
        return DB::table('model_has_roles as mhr')->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('mhr.model_type', (new User)->getMorphClass())->where('r.guard_name', 'web')
            ->whereIn('r.name', app(InstitutionalRoleGovernance::class)->rolesInstitucionales())
            ->select('r.name')->selectRaw('COUNT(*) AS total')->groupBy('r.name')->orderBy('r.name')->pluck('total', 'name')->all();
    }

    public function especialidades(User $actor, ?string $gestion, string $fecha): array
    {
        if (!$actor->can('Estudiantes') || !$gestion) return [];
        return DB::table('inscripcion_vigencia as v')
            ->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
            ->join('estudiante as e', 'e.cod_est', '=', 'i.cod_est')
            ->join('especialidad_tecnica as et', 'et.cod_esp', '=', 'v.cod_esp_tec')
            ->where('i.cod_gea', $gestion)->where('i.est_ins', 'ACTIVA')->where('e.est_est', 'ACTIVO')
            ->where('v.est_ivg', 'ACTIVO')->where('et.est_esp', 'ACTIVO')
            ->where('v.fii_ivg', '<=', $fecha)->where(fn ($q) => $q->whereNull('v.ffi_ivg')->orWhere('v.ffi_ivg', '>=', $fecha))
            ->select('et.nom_esp')->selectRaw('COUNT(DISTINCT i.cod_est) AS total')
            ->groupBy('et.nom_esp')->orderByDesc('total')->orderBy('et.nom_esp')->limit(6)->pluck('total', 'nom_esp')->all();
    }
}
