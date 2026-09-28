<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\Persona;
use App\Models\User;

class InstitutionalDashboardService
{
    public function for(string $actor): array
    {
        if ($actor === 'Regente') {
            $user = auth()->user();
            if (! $user || ! app(RegencyAccessService::class)->available()) {
                return ['metrics' => [], 'gestion' => null];
            }
            $gestion = GestionAcademica::whereIn('est_gea', ['ACTIVO', 'ACTIVA'])->orderByDesc('ani_gea')->first();
            if (! $gestion) { return ['metrics' => [], 'gestion' => null]; }
            $enrollments = app(RegencyAccessService::class)->constrain(
                InscripcionEstudiante::where('cod_gea', $gestion->cod_gea)->where('est_ins', 'ACTIVA'), $user, 'inscripcion_estudiante');
            $metrics = [];
            if ($user->can('inscripciones.ver.institucional')) { $metrics['inscripciones_activas'] = (clone $enrollments)->count(); }
            if ($user->can('estudiantes.ver.institucional')) { $metrics['estudiantes_activos'] = (clone $enrollments)->whereHas('estudiante', fn ($q) => $q->where('est_est', 'ACTIVO'))->distinct()->count('cod_est'); }
            return ['metrics' => $metrics, 'gestion' => $gestion->ani_gea];
        }

        $common = [
            'estudiantes_activos' => Estudiante::query()->where('est_est', 'ACTIVO')->count(),
            'cursos_activos' => Curso::query()->where('est_cur', 'ACTIVO')->count(),
            'inscripciones_activas' => InscripcionEstudiante::query()->where('est_ins', 'ACTIVA')->count(),
        ];

        $metrics = match ($actor) {
            'Director' => $common + [
                'docentes_activos' => Docente::query()->where('est_doc', 'ACTIVO')->count(),
                'calificaciones_registradas' => Calificacion::query()->count(),
            ],
            'Secretaria' => $common + [
                'personas_registradas' => Persona::query()->count(),
                'cuentas_activas' => User::query()->where('est_usu', 'ACTIVO')->count(),
            ],
            default => $common,
        };

        $gestion = GestionAcademica::query()->orderByDesc('created_at')->first();

        return [
            'metrics' => $metrics,
            'gestion' => $gestion?->ani_gea ?? $gestion?->nom_gea ?? null,
        ];
    }
}
