<?php

namespace App\Services\AporteIngenieril;

final class OrientationReadiness
{
    public function evaluate(bool $linked, array $payload, string $technicalStatus = 'NO_APLICA'): array
    {
        $requirements = [
            ['label' => 'Identidad y perfil vinculados', 'type' => 'OBLIGATORIO', 'complete' => $linked],
            ['label' => 'Datos académicos con contexto', 'type' => 'OBLIGATORIO', 'complete' => ! empty($payload['academic']['records'])],
            ['label' => 'Cuestionario RIASEC oficial', 'type' => 'OBLIGATORIO', 'complete' => ! empty($payload['riasec_public'])],
            ['label' => 'Formación técnica BTH', 'type' => $technicalStatus === 'NO_APLICA' ? 'NO_APLICA' : 'OBLIGATORIO', 'complete' => $technicalStatus === 'NO_APLICA' || ! empty($payload['technical']['specialty'])],
            ['label' => 'Asistencia', 'type' => 'RECOMENDADO', 'complete' => isset($payload['attendance'])],
            ['label' => 'Historia académica', 'type' => 'RECOMENDADO', 'complete' => ! empty($payload['history'])],
            ['label' => 'Actividad de aprendizaje', 'type' => 'OPCIONAL', 'complete' => isset($payload['learning_activity'])],
            ['label' => 'Intereses declarados', 'type' => 'OPCIONAL', 'complete' => ! empty($payload['declared_interests'])],
        ];
        $blocking = array_filter($requirements, fn ($item) => $item['type'] === 'OBLIGATORIO' && ! $item['complete']);
        return ['ready' => $blocking === [], 'requirements' => $requirements];
    }
}
