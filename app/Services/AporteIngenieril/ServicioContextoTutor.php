<?php

namespace App\Services\AporteIngenieril;

class ServicioContextoTutor
{
    public function construir(array $contexto): array
    {
        $analisis = data_get($contexto, 'activity.analysis_snapshot', []);
        $perfiles = collect(data_get($analisis, 'career_evidence_profiles', []));
        $fortalezas = collect([
            data_get($analisis, 'student_snapshot.academic_evidence.best_observed_subject.statement'),
            data_get($analisis, 'student_snapshot.academic_evidence.best_observed_area.statement'),
        ])->filter()->values()->all();
        $areasPorReforzar = $perfiles->flatMap(
            fn ($perfil) => collect(data_get($perfil, 'preparation.reinforcement_areas', []))->pluck('competency')
        )->filter()->unique()->take(12)->values()->all();
        $rutaPreparacion = $perfiles->flatMap(
            fn ($perfil) => collect(data_get($perfil, 'preparation.reinforcement_areas', []))->pluck('initial_subject')
        )->filter()->unique()->take(12)->values()->all();

        return [[
            'academic_period' => data_get($contexto, 'payload.academic_period'),
            'course' => data_get($contexto, 'payload.course'),
            'strengths' => $fortalezas,
            'areas_to_reinforce' => $areasPorReforzar,
            'preparation_route' => $rutaPreparacion,
        ], [
            'riasec_code' => data_get($analisis, 'student_snapshot.vocational_interest_evidence.riasec.holland_code'),
            'technical_specialty' => data_get($contexto, 'payload.technical.specialty'),
        ]];
    }
}
