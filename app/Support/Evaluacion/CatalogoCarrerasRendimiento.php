<?php

namespace App\Support\Evaluacion;

use Illuminate\Support\Facades\Validator;

class CatalogoCarrerasRendimiento
{
    /** Copia instalada del catálogo que usa Python; no inventa ofertas ni porcentajes. */
    public function leer(): array
    {
        $ruta = base_path('ai-service/data/catalog/careers.json');
        if (! is_readable($ruta)) {
            return ['version' => null, 'carreras' => []];
        }
        $documento = json_decode(file_get_contents($ruta), true);
        if (! is_array($documento) || Validator::make($documento, [
            'catalog_version' => 'required|string', 'universities' => 'required|array',
            'universities.*.university_id' => 'required|string|distinct', 'universities.*.name' => 'required|string',
            'careers' => 'required|array', 'careers.*.career_id' => 'required|string|distinct',
            'careers.*.name' => 'required|string', 'careers.*.university_id' => 'required|string',
            'careers.*.source_ids' => 'required|array|min:1',
        ])->fails()) {
            return ['version' => null, 'carreras' => []];
        }
        $universidades = collect($documento['universities'])->keyBy('university_id');
        $carreras = [];
        foreach ($documento['careers'] as $carrera) {
            $universidad = $universidades->get($carrera['university_id']);
            if (! $universidad) {
                continue;
            }
            $capa = $carrera['evidence_layer'] ?? 'CORPUS_VALIDADO';
            $elegible = ($carrera['recommendation_eligible'] ?? true) === true && $capa === 'CORPUS_VALIDADO';
            $carreras[$carrera['career_id']] = ['id' => $carrera['career_id'], 'nombre' => $carrera['name'], 'universidad' => $universidad['name'],
                'elegible' => $elegible, 'capa' => $capa, 'fuentes' => $carrera['source_ids'],
                'estudios' => 0, 'academica' => 0, 'tecnica' => 0, 'declarados' => 0];
        }

        return ['version' => $documento['catalog_version'], 'carreras' => $carreras];
    }
}
