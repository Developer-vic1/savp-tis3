<?php

namespace App\Support\Conocimiento;

use Illuminate\Support\Facades\Validator;

class ContratoColeccionUniversitaria
{
    public static function valido(array $datos): bool
    {
        $urlOficial = function ($atributo, $valor, $fallar) {
            $host = strtolower((string) parse_url($valor, PHP_URL_HOST));
            if (! filter_var($valor, FILTER_VALIDATE_URL) || parse_url($valor, PHP_URL_SCHEME) !== 'https'
                || ! ($host === 'unifranz.edu.bo' || str_ends_with($host, '.unifranz.edu.bo'))
                || parse_url($valor, PHP_URL_USER) !== null || parse_url($valor, PHP_URL_PASS) !== null
                || ! in_array(parse_url($valor, PHP_URL_PORT), [null, 443], true)) {
                $fallar('La URL no pertenece a la colección oficial.');
            }
        };

        return Validator::make($datos, [
            'institution' => ['required', 'in:UNIFRANZ'],
            'source_url' => ['required', 'in:https://unifranz.edu.bo/carreras/'],
            'status' => ['required', 'in:SIN_INICIAR,EN_PROCESO,COMPLETADA,PARCIAL,NO_DISPONIBLE'],
            'careers' => ['present', 'array', 'max:60'],
            'careers.*.name' => ['required', 'string', 'max:240'],
            'careers.*.url' => ['required', 'string', 'max:2000', $urlOficial],
            'careers.*.status' => ['required', 'string', 'max:100'],
            'careers.*.campuses' => ['present', 'array', 'max:20'],
            'careers.*.campuses.*' => ['string', 'max:240'],
            'careers.*.campus_availability' => ['sometimes', 'array', 'max:4'],
            'careers.*.campus_availability.*.campus' => ['required', 'in:La Paz,El Alto,Cochabamba,Santa Cruz'],
            'careers.*.campus_availability.*.status' => ['required', 'in:OFERTADA,NO_INDICADA,PENDIENTE'],
            'careers.*.campus_availability.*.source_url' => ['required', 'string', 'max:2000', $urlOficial],
            'careers.*.campus_availability.*.evidence_text' => ['required', 'string', 'max:500'],
            'careers.*.campus_evidence' => ['sometimes', 'array'],
            'careers.*.campus_evidence.status' => ['required_with:careers.*.campus_evidence', 'in:OFERTA_IDENTIFICADA,PENDIENTE'],
            'careers.*.campus_evidence.source_url' => ['required_with:careers.*.campus_evidence', 'string', 'max:2000', $urlOficial],
            'careers.*.campus_evidence.document_hash' => ['sometimes', 'nullable', 'regex:/^sha256:[a-f0-9]{64}$/'],
            'careers.*.campus_evidence.retrieved_at' => ['sometimes', 'nullable', 'date'],
            'careers.*.duration' => ['present', 'nullable', 'string', 'max:500'],
            'careers.*.summary' => ['sometimes', 'string', 'max:2000'],
            'careers.*.curricula' => ['present', 'array', 'max:30'],
            'careers.*.curricula.*.url' => ['required', 'string', 'max:2000', $urlOficial],
            'careers.*.curricula.*.title' => ['required', 'string', 'max:500'],
            'careers.*.curricula.*.status' => ['required', 'string', 'max:100'],
            'careers.*.curricula.*.pages' => ['required', 'integer', 'min:0'],
            'careers.*.curricula.*.digital_pages' => ['required', 'integer', 'min:0'],
            'careers.*.curricula.*.recognized_pages' => ['sometimes', 'integer', 'min:0', 'max:80'],
            'careers.*.curricula.*.readable_pages' => ['sometimes', 'integer', 'min:0', 'max:80'],
            'careers.*.curricula.*.page_methods' => ['sometimes', 'array', 'max:80'],
            'careers.*.curricula.*.page_methods.*.page' => ['required', 'integer', 'min:1', 'max:80'],
            'careers.*.curricula.*.page_methods.*.method' => ['required', 'in:DIGITAL,OCR'],
            'careers.*.curricula.*.page_methods.*.review_required' => ['required', 'boolean'],
            'careers.*.curricula.*.subjects_candidates' => ['sometimes', 'array', 'max:200'],
            'careers.*.curricula.*.subjects_candidates.*.name' => ['required', 'string', 'max:180'],
            'careers.*.curricula.*.subjects_candidates.*.semester' => ['required', 'integer', 'min:1', 'max:10'],
            'careers.*.curricula.*.subjects_candidates.*.page' => ['required', 'integer', 'min:1', 'max:80'],
            'careers.*.curricula.*.subjects_candidates.*.method' => ['required', 'in:DIGITAL,OCR'],
            'careers.*.curricula.*.subjects_candidates.*.status' => ['required', 'in:PENDIENTE_REVISION'],
            'careers.*.curricula.*.subjects_candidates.*.evidence_text' => ['required', 'string', 'max:2000'],
            'careers.*.curricula.*.subjects_candidates.*.source_url' => ['required', 'string', 'max:2000', $urlOficial],
            'careers.*.curricula.*.subjects_candidates.*.document_hash' => ['required', 'regex:/^sha256:[a-f0-9]{64}$/'],
            'careers.*.curricula.*.subject_review.status' => ['sometimes', 'in:PENDIENTE_REVISION,PENDIENTE_LECTURA'],
            'careers.*.curricula.*.subject_review.message' => ['sometimes', 'string', 'max:1000'],
            'careers.*.curricula.*.curricular_structure' => ['sometimes', 'array'],
            'careers.*.curricula.*.curricular_structure.version' => ['required_with:careers.*.curricula.*.curricular_structure', 'in:estructura-curricular-v1'],
            'careers.*.curricula.*.curricular_structure.status' => ['required_with:careers.*.curricula.*.curricular_structure', 'in:REQUIERE_REVISION,ORGANIZADA_PENDIENTE_REVISION'],
            'careers.*.curricula.*.curricular_structure.semester_count' => ['required_with:careers.*.curricula.*.curricular_structure', 'integer', 'min:0', 'max:10'],
            'careers.*.curricula.*.curricular_structure.subject_count' => ['required_with:careers.*.curricula.*.curricular_structure', 'integer', 'min:0', 'max:200'],
            'careers.*.curricula.*.curricular_structure.pending_regions' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'careers.*.curricula.*.curricular_structure.semesters' => ['required_with:careers.*.curricula.*.curricular_structure', 'array', 'max:10'],
            'careers.*.curricula.*.curricular_structure.semesters.*.number' => ['required', 'integer', 'min:1', 'max:10'],
            'careers.*.curricula.*.curricular_structure.semesters.*.subjects' => ['present', 'array', 'max:100'],
            'careers.*.curricula.*.curricular_structure.semesters.*.subjects.*.name' => ['required', 'string', 'max:180'],
            'careers.*.curricula.*.curricular_structure.message' => ['required_with:careers.*.curricula.*.curricular_structure', 'string', 'max:1000'],
            'careers.*.curricula.*.chunks' => ['required', 'integer', 'min:0'],
            'careers.*.curricula.*.excerpt' => ['sometimes', 'string', 'max:2000'],
            'careers.*.curricula.*.document_hash' => ['present', 'nullable', 'regex:/^sha256:[a-f0-9]{64}$/'],
            'careers.*.curricula.*.warnings' => ['present', 'array', 'max:100'],
            'careers.*.curricula.*.warnings.*' => ['string', 'max:2000'],
            'careers.*.warnings' => ['present', 'array', 'max:100'],
            'careers.*.warnings.*' => ['string', 'max:2000'],
            'progress.completed' => ['required', 'integer', 'min:0'],
            'progress.total' => ['required', 'integer', 'min:0'],
            'progress.current' => ['present', 'nullable', 'string', 'max:500'],
            'statistics.careers' => ['required', 'integer', 'min:0'],
            'statistics.curricula' => ['required', 'integer', 'min:0'],
            'statistics.extracted_documents' => ['required', 'integer', 'min:0'],
            'warnings' => ['present', 'array', 'max:100'],
            'warnings.*' => ['string', 'max:2000'],
            'updated_at' => ['present', 'nullable', 'date'],
            'processing_stage' => ['sometimes', 'in:RECONOCIMIENTO,ANALISIS,ORGANIZACION,FINALIZADO'],
            'processing_seconds' => ['sometimes', 'numeric', 'min:0'],
        ])->passes() && data_get($datos, 'progress.completed') <= data_get($datos, 'progress.total')
            && self::lecturaCoherente($datos['careers'] ?? []);
    }

    private static function lecturaCoherente(array $carreras): bool
    {
        foreach ($carreras as $carrera) {
            if (isset($carrera['campus_evidence']) && $carrera['campus_evidence']['source_url'] !== ($carrera['final_url'] ?? $carrera['url'])) {
                return false;
            }
            if (isset($carrera['campus_availability'])) {
                $sedes = array_column($carrera['campus_availability'], 'campus');
                $ofertadas = array_column(array_filter($carrera['campus_availability'], fn ($sede) => $sede['status'] === 'OFERTADA'), 'campus');
                $guardadas = $carrera['campuses'];
                sort($ofertadas);
                sort($guardadas);
                if (count(array_unique($sedes)) !== count($sedes) || $ofertadas !== $guardadas) {
                    return false;
                }
                if (($carrera['campus_evidence']['status'] ?? null) === 'OFERTA_IDENTIFICADA'
                    && (count($sedes) !== 4 || in_array('PENDIENTE', array_column($carrera['campus_availability'], 'status'), true))) {
                    return false;
                }
                foreach ($carrera['campus_availability'] as $sede) {
                    if ($sede['source_url'] !== ($carrera['final_url'] ?? $carrera['url'])) {
                        return false;
                    }
                }
            }
            foreach ($carrera['curricula'] as $malla) {
                $leidas = $malla['digital_pages'] + ($malla['recognized_pages'] ?? 0);
                if ($leidas > $malla['pages'] || (isset($malla['readable_pages']) && $malla['readable_pages'] !== $leidas)) {
                    return false;
                }
                $paginas = [];
                foreach ($malla['page_methods'] ?? [] as $pagina) {
                    if ($pagina['page'] > $malla['pages'] || in_array($pagina['page'], $paginas, true)
                        || ($pagina['method'] === 'OCR' && ! $pagina['review_required'])) {
                        return false;
                    }
                    $paginas[] = $pagina['page'];
                }
                foreach ($malla['subjects_candidates'] ?? [] as $materia) {
                    if ($materia['page'] > $malla['pages'] || $materia['document_hash'] !== $malla['document_hash']
                        || $materia['source_url'] !== ($malla['final_url'] ?? $malla['url'])) {
                        return false;
                    }
                }
                if (isset($malla['curricular_structure'])) {
                    $estructura = $malla['curricular_structure'];
                    $numeros = array_column($estructura['semesters'], 'number');
                    $materias = array_merge([], ...array_column($estructura['semesters'], 'subjects'));
                    if ($estructura['semester_count'] !== count($numeros) || count(array_unique($numeros)) !== count($numeros)
                        || $estructura['subject_count'] !== count($materias) || count($materias) !== count($malla['subjects_candidates'] ?? [])) {
                        return false;
                    }
                    foreach ($estructura['semesters'] as $semestre) {
                        foreach ($semestre['subjects'] as $materia) {
                            if (($materia['semester'] ?? null) !== $semestre['number'] || ! in_array($materia, $malla['subjects_candidates'], true)) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
}
