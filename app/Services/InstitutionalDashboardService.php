<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\Persona;
use App\Models\User;

class InstitutionalDashboardService
{
    public function for(string $actor): array
    {
        $user = auth()->user();
        if (! $user || app(RoleDashboardResolver::class)->roleFor($user) !== $actor) {
            return ['metrics' => [], 'gestion' => null];
        }
        if ($actor === 'Regente' && ! app(RegencyAccessService::class)->available()) {
            return ['metrics' => [], 'gestion' => null];
        }
        $gestiones = GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])->limit(2)->get();
        $gestion = $gestiones->count() === 1 ? $gestiones->first() : null;
        $enrollments = InscripcionEstudiante::where('est_ins', 'ACTIVA');
        if ($gestion) {
            $enrollments->where('cod_gea', $gestion->cod_gea);
        }
        if ($actor === 'Regente') {
            $enrollments = app(RegencyAccessService::class)->constrain($enrollments, $user, 'inscripcion_estudiante');
        }
        $metrics = [];
        if ($user->can('inscripciones.ver.institucional')) {
            $metrics['inscripciones_activas'] = $gestion ? (clone $enrollments)->count() : null;
        }
        if ($user->can('estudiantes.ver.institucional')) {
            $metrics['estudiantes_activos'] = $gestion ? (clone $enrollments)->whereHas('estudiante', fn ($q) => $q->where('est_est', 'ACTIVO'))->distinct()->count('cod_est') : null;
        }
        if ($user->can('cursos.ver.institucional')) {
            $courses = Curso::where('est_cur', 'ACTIVO');
            if ($actor === 'Regente') {
                $courses->whereHas('planesAsignatura', fn ($q) => app(RegencyAccessService::class)->constrain($q, $user, 'plan_asignatura')->when($gestion, fn ($p) => $p->where('cod_gea', $gestion->cod_gea)));
            }
            $metrics['cursos_activos'] = $courses->count();
        }
        if ($actor === 'Director') {
            if ($user->can('Docentes')) {
                $metrics['docentes_activos'] = Docente::where('est_doc', 'ACTIVO')->count();
            }
            if ($user->can('calificaciones.ver.institucional')) {
                $metrics['calificaciones_registradas'] = Calificacion::where('est_cal', 'ACTIVO')->count();
            }
        }
        if ($actor === 'Secretaria') {
            if ($user->can('Registro_Personas')) {
                $metrics['personas_registradas'] = Persona::count();
            }
            if ($user->can('usuarios.ver.institucional')) {
                $metrics['cuentas_activas'] = app(OperationalAccountService::class)->scope(User::where('est_usu', 'ACTIVO'))->count();
            }
        }

        return ['metrics' => $metrics, 'gestion' => $gestion?->ani_gea];
    }
}
