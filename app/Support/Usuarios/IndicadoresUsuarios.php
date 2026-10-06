<?php

namespace App\Support\Usuarios;

use App\Models\Oficial\Academico\Persona;
use App\Support\InstitutionalRoleGovernance;
use App\Support\Personas\IndicadoresPersonas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class IndicadoresUsuarios
{
    public function analizar(Builder $consulta): array
    {
        $base = (clone $consulta)->reorder()->withoutEagerLoads();
        $total = (clone $base)->count();
        $activos = Schema::hasColumn('users', 'est_usu') ? (clone $base)->where('est_usu', 'ACTIVO')->count() : $total;
        $inactivos = Schema::hasColumn('users', 'est_usu') ? (clone $base)->where('est_usu', 'INACTIVO')->count() : 0;
        $roles = collect(app(InstitutionalRoleGovernance::class)->rolesInstitucionales())->map(fn ($rol) => [
            'nombre' => $rol,
            'cantidad' => (clone $base)->whereHas('roles', fn ($q) => $q->where('guard_name', 'web')->where('name', $rol))->count(),
        ])->sortByDesc('cantidad')->values();
        $verificados = Schema::hasColumn('users', 'email_verified_at')
            ? (clone $base)->whereNotNull('email_verified_at')->count() : null;

        $personas = Persona::whereIn('cod_per', (clone $base)->select('cod_per'));
        $comunidad = app(IndicadoresPersonas::class)->analizar($personas);
        $edades = $comunidad['edades'];
        $generos = $comunidad['generos'];
        if ($sinPersona = max(0, $total - $comunidad['total'])) {
            foreach (['edades', 'generos'] as $clave) {
                ${$clave}['labels'][] = 'Sin persona vinculada';
                ${$clave}['data'][] = $sinPersona;
            }
        }

        return [
            'total' => $total,
            'edades' => $edades,
            'generos' => $generos,
            'estados' => ['labels' => ['Activas', 'Inactivas', 'Otro estado'], 'data' => [$activos, $inactivos, $total - $activos - $inactivos]],
            'roles' => ['labels' => $roles->pluck('nombre')->all(), 'data' => $roles->pluck('cantidad')->all()],
            'correos' => ['labels' => ['Verificados', 'Sin verificar'], 'data' => $verificados === null ? [] : [$verificados, $total - $verificados]],
        ];
    }
}
