<?php

namespace App\Support\Reportes;

use App\Models\Calificacion;
use App\Models\Asignatura;
use App\Models\Estudiante;
use App\Models\EspecialidadTecnica;
use App\Models\PeriodoEvaluacion;
use App\Support\Academico\AsignaturaInteligente;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use App\Support\Evaluacion\CalificacionInteligente;

class ReporteAcademicoInteligente
{
    public function clasificar(float $nota): string
    {
        return app(CalificacionInteligente::class)->clasificar($nota);
    }

    public function orientacionPorEspecialidad(?string $especialidad): array
    {
        if (!$especialidad) {
            return ['Formación general', ['Todas las carreras universitarias']];
        }

        // 1. Try to find in DB first if the columns exist
        if (\Illuminate\Support\Facades\Schema::hasColumn('especialidad_tecnica', 'area_bth_esp')) {
            $record = EspecialidadTecnica::where('nom_esp', $especialidad)->first();
            if ($record && $record->clas_bth_esp && $record->area_bth_esp && !empty($record->car_rel_esp)) {
                return [$record->area_bth_esp, (array) $record->car_rel_esp];
            }
        }

        // 2. Catalog fallback
        $catalog = EspecialidadTecnicaInteligente::catalogo();
        foreach ($catalog as $nombre => $item) {
            similar_text(mb_strtolower($especialidad), mb_strtolower($nombre), $sim);
            if ($sim >= 80) {
                return [$item['area_bth'], $item['carreras_relacionadas']];
            }
        }

        // 3. String match fallback
        $texto = mb_strtolower((string) $especialidad);
        return match (true) {
            str_contains($texto, 'sistemas') || str_contains($texto, 'comput') => ['Tecnología digital', ['Ingeniería de Sistemas', 'Informática', 'Ciencia de Datos']],
            str_contains($texto, 'electr') => ['Electrónica y telecomunicaciones', ['Ingeniería Electrónica', 'Telecomunicaciones', 'Mecatrónica']],
            str_contains($texto, 'contab') => ['Administración y finanzas', ['Contaduría Pública', 'Administración de Empresas', 'Economía']],
            str_contains($texto, 'gastronom') => ['Servicios y producción gastronómica', ['Gastronomía', 'Turismo', 'Administración de Servicios']],
            str_contains($texto, 'mec') => ['Industria y mantenimiento', ['Ingeniería Mecánica', 'Ingeniería Industrial', 'Mecatrónica']],
            str_contains($texto, 'textil') || str_contains($texto, 'cost') => ['Diseño y producción textil', ['Diseño Textil', 'Diseño de Modas', 'Ingeniería Industrial']],
            default => ['Formación técnica y producción', ['Área técnica relacionada', 'Emprendimiento productivo']],
        };
    }

    public function generarReporte(array $filtros): array
    {
        $calificacionesQuery = Calificacion::query()
            ->with(['estudiante.persona', 'estudiante.especialidad', 'asignatura', 'periodoEvaluacion'])
            ->where('est_cal', 'ACTIVO');

        // Apply filters
        if (!blank($filtros['cod_pev'] ?? null)) {
            $calificacionesQuery->where('cod_pev', $filtros['cod_pev']);
        }
        if (!blank($filtros['cod_asi'] ?? null)) {
            $calificacionesQuery->where('cod_asi', $filtros['cod_asi']);
        }
        if (!blank($filtros['cod_est'] ?? null)) {
            $calificacionesQuery->where('cod_est', $filtros['cod_est']);
        }
        if (!blank($filtros['cod_esp'] ?? null)) {
            $calificacionesQuery->whereHas('estudiante', fn ($q) => $q->where('cod_esp', $filtros['cod_esp']));
        }

        $calificaciones = $calificacionesQuery->get();

        // Perform qualitative classification
        foreach ($calificaciones as $cal) {
            $cal->setAttribute('desempeno_calculado', $this->clasificar((float) $cal->not_cal));
        }

        if (!blank($filtros['desempeno'] ?? null)) {
            $calificaciones = $calificaciones->where('desempeno_calculado', $filtros['desempeno'])->values();
        }

        $totalRegistros = $calificaciones->count();
        $promedioGeneral = $totalRegistros > 0 ? round((float) $calificaciones->avg('not_cal'), 2) : 0.0;

        // Promedios agrupados
        $promedioPorAsignatura = $calificaciones->groupBy('cod_asi')->map(function ($items) {
            return [
                'nombre' => $items->first()->asignatura?->nom_asi ?? 'Desconocida',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
                'riesgo' => $items->where('not_cal', '<=', 50)->count(),
            ];
        })->sortByDesc('promedio')->values()->toArray();

        $promedioPorPeriodo = $calificaciones->groupBy('cod_pev')->map(function ($items) {
            return [
                'nombre' => $items->first()->periodoEvaluacion?->nom_pev ?? 'Periodo',
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
            ];
        })->sortBy('nombre')->values()->toArray();

        $promedioPorEspecialidad = $calificaciones->filter(fn($c) => $c->estudiante?->especialidad)->groupBy(fn($c) => $c->estudiante->especialidad->cod_esp)->map(function ($items) {
            return [
                'nombre' => $items->first()->estudiante->especialidad->nom_esp,
                'promedio' => round((float) $items->avg('not_cal'), 2),
                'registros' => $items->count(),
            ];
        })->sortByDesc('promedio')->values()->toArray();

        // Areas analysis (Cosmos, Comunidad, Vida, Ciencia)
        $rendimientoAreas = [
            'Cosmos y Pensamiento' => ['sum' => 0, 'count' => 0],
            'Comunidad y Sociedad' => ['sum' => 0, 'count' => 0],
            'Vida, Tierra y Territorio' => ['sum' => 0, 'count' => 0],
            'Ciencia, Tecnología y Producción' => ['sum' => 0, 'count' => 0],
        ];

        foreach ($calificaciones as $cal) {
            if ($cal->asignatura) {
                $infoAsi = AsignaturaInteligente::interpretar($cal->asignatura->nom_asi);
                $area = $infoAsi['area'] ?? 'Ciencia, Tecnología y Producción';
                if (isset($rendimientoAreas[$area])) {
                    $rendimientoAreas[$area]['sum'] += $cal->not_cal;
                    $rendimientoAreas[$area]['count']++;
                }
            }
        }

        $areasAverages = [];
        $areasFuertes = [];
        $areasEnRiesgo = [];
        foreach ($rendimientoAreas as $area => $meta) {
            $avg = $meta['count'] > 0 ? round($meta['sum'] / $meta['count'], 2) : 0;
            $areasAverages[$area] = $avg;
            if ($avg >= 70) {
                $areasFuertes[] = $area;
            } elseif ($avg > 0 && $avg < 51) {
                $areasEnRiesgo[] = $area;
            }
        }

        // Estudiantes destacados & en riesgo
        $estudiantesDestacados = [];
        $estudiantesEnRiesgo = [];
        $estudiantesGroup = $calificaciones->groupBy('cod_est');
        foreach ($estudiantesGroup as $estId => $items) {
            $p = $items->first()->estudiante?->persona;
            $nombreComp = trim(($p?->nom_per ?? '') . ' ' . ($p?->ape_pat_per ?? '') . ' ' . ($p?->ape_mat_per ?? ''));
            $avg = round((float) $items->avg('not_cal'), 2);

            if ($avg >= 90) {
                $estudiantesDestacados[] = ['nombre' => $nombreComp, 'promedio' => $avg];
            } elseif ($avg <= 50) {
                $estudiantesEnRiesgo[] = ['nombre' => $nombreComp, 'promedio' => $avg];
            }
        }

        // Carreras sugeridas & familias profesionales & RIASEC profiles
        $carrerasFrec = [];
        $familiasFrec = [];
        $riasecCounts = [
            'Realista' => 0,
            'Investigativo' => 0,
            'Artístico' => 0,
            'Social' => 0,
            'Emprendedor' => 0,
            'Convencional' => 0,
        ];

        $catalogEsp = EspecialidadTecnicaInteligente::catalogo();
        foreach ($calificaciones as $cal) {
            if ($cal->asignatura) {
                $infoAsi = AsignaturaInteligente::interpretar($cal->asignatura->nom_asi);
                // If the student performs well, add suggested careers
                if ($cal->not_cal >= 70) {
                    foreach ($infoAsi['carreras_relacionadas'] ?? [] as $car) {
                        $carrerasFrec[$car] = ($carrerasFrec[$car] ?? 0) + 1;
                    }
                }
            }

            if ($cal->estudiante?->especialidad) {
                $esp = $cal->estudiante->especialidad;
                $hasExtended = \Illuminate\Support\Facades\Schema::hasColumn('especialidad_tecnica', 'fam_pro_esp');

                if ($hasExtended && $esp->clas_bth_esp && $esp->fam_pro_esp) {
                    $fam = $esp->fam_pro_esp;
                    $familiasFrec[$fam] = ($familiasFrec[$fam] ?? 0) + 1;

                    $riasecArr = is_array($esp->perfil_riasec_esp) ? $esp->perfil_riasec_esp : [];
                    foreach ($riasecArr as $part) {
                        $trimmed = trim($part);
                        if (isset($riasecCounts[$trimmed])) {
                            $riasecCounts[$trimmed]++;
                        }
                    }
                } else {
                    $espNom = $esp->nom_esp;
                    // Find matching catalog entry for RIASEC / Family
                    $match = null;
                    foreach ($catalogEsp as $nomFormal => $meta) {
                        similar_text(mb_strtolower($espNom), mb_strtolower($nomFormal), $sim);
                        if ($sim >= 80) {
                            $match = $meta;
                            break;
                        }
                    }

                    if ($match) {
                        $fam = $match['familia_profesional'] ?? 'General';
                        $familiasFrec[$fam] = ($familiasFrec[$fam] ?? 0) + 1;

                        // RIASEC split
                        $riasecStr = $match['perfil_riasec'] ?? 'Realista';
                        $parts = explode('/', $riasecStr);
                        foreach ($parts as $part) {
                            $trimmed = trim($part);
                            if (isset($riasecCounts[$trimmed])) {
                                $riasecCounts[$trimmed]++;
                            }
                        }
                    }
                }
            }
        }

        arsort($carrerasFrec);
        arsort($familiasFrec);
        $carrerasSugeridas = array_slice(array_keys($carrerasFrec), 0, 5);
        $familiasProfesionales = array_slice(array_keys($familiasFrec), 0, 3);

        // Tendencias
        $tendenciaAcademica = 'Estable';
        if (count($promedioPorPeriodo) >= 2) {
            $primera = end($promedioPorPeriodo)['promedio'];
            $ultima = reset($promedioPorPeriodo)['promedio'];
            $diff = $ultima - $primera;
            if ($diff > 3) {
                $tendenciaAcademica = 'Creciente positiva (+ ' . abs(round($diff, 1)) . ' pts)';
            } elseif ($diff < -3) {
                $tendenciaAcademica = 'Decreciente en riesgo (- ' . abs(round($diff, 1)) . ' pts)';
            } else {
                $tendenciaAcademica = 'Estable con variaciones mínimas';
            }
        }

        $tendenciaVocacional = 'Alta concentración en el área de ';
        if (!empty($familiasProfesionales)) {
            $tendenciaVocacional .= $familiasProfesionales[0] . '.';
        } else {
            $tendenciaVocacional = 'Inclinación general diversificada.';
        }

        // Warnings & Alerts
        $alertas = [];
        $recomendaciones = [];

        $riesgoCount = $calificaciones->where('not_cal', '<=', 50)->count();
        $riesgoPct = $totalRegistros > 0 ? round(($riesgoCount / $totalRegistros) * 100, 1) : 0;

        if ($riesgoPct > 25) {
            $alertas[] = "Alerta crítica: El {$riesgoPct}% de las calificaciones registradas están en nivel de riesgo (<= 50 pts).";
            $recomendaciones[] = 'Conformar grupos de apoyo escolar inmediato para estudiantes rezagados.';
        }

        foreach ($promedioPorAsignatura as $asiData) {
            if ($asiData['riesgo'] > 3) {
                $alertas[] = "Atención prioritaria: La asignatura '{$asiData['nombre']}' registra {$asiData['riesgo']} estudiantes en situación de riesgo.";
                $recomendaciones[] = "Coordinar con el docente de '{$asiData['nombre']}' estrategias de flexibilización o refuerzo pedagógico.";
            }
        }

        if (empty($alertas)) {
            $alertas[] = 'Rendimiento general dentro del estándar satisfactorio.';
        }
        if (empty($recomendaciones)) {
            $recomendaciones[] = 'Mantener el seguimiento y felicitar a los estudiantes destacados.';
            $recomendaciones[] = 'Fomentar la participación en ferias técnicas del bachillerato.';
        }

        // Interpretative summary text
        $lecturaInterpretativa = "El análisis académico-vocacional consolidado para los filtros seleccionados muestra un promedio general institucional de {$promedioGeneral} puntos sobre 100, con un total de {$totalRegistros} calificaciones evaluadas. ";
        if ($riesgoCount > 0) {
            $lecturaInterpretativa .= "Se detectan {$riesgoCount} registros en nivel de riesgo, concentrados en asignaturas de alta exigencia cognitiva. ";
        } else {
            $lecturaInterpretativa .= "No se registran estudiantes en riesgo crítico de reprobación. ";
        }
        if (!empty($carrerasSugeridas)) {
            $lecturaInterpretativa .= "A nivel vocacional, el rendimiento destaca una fuerte aptitud orientada hacia " . implode(', ', array_slice($carrerasSugeridas, 0, 3)) . ".";
        }

        // Visualizaciones structured details
        $visualizaciones = [
            'barras_areas' => $areasAverages,
            'barras_carreras' => array_slice($carrerasFrec, 0, 6),
            'distribucion_desempeno' => [
                'Destacado' => $calificaciones->where('not_cal', '>=', 90)->count(),
                'Muy bueno' => $calificaciones->where('not_cal', '>=', 80)->where('not_cal', '<', 90)->count(),
                'Aprobado sólido' => $calificaciones->where('not_cal', '>=', 70)->where('not_cal', '<', 80)->count(),
                'En seguimiento' => $calificaciones->where('not_cal', '>=', 51)->where('not_cal', '<', 70)->count(),
                'En riesgo' => $calificaciones->where('not_cal', '<=', 50)->count(),
            ],
            'matriz_especialidad_area' => $this->generarMatrizEspecialidadArea($calificaciones),
        ];

        return [
            'promedio_general' => $promedioGeneral,
            'promedio_por_asignatura' => $promedioPorAsignatura,
            'promedio_por_periodo' => $promedioPorPeriodo,
            'promedio_por_especialidad' => $promedioPorEspecialidad,
            'areas_fuertes' => $areasFuertes,
            'areas_en_riesgo' => $areasEnRiesgo,
            'estudiantes_destacados' => $estudiantesDestacados,
            'estudiantes_en_riesgo' => $estudiantesEnRiesgo,
            'carreras_sugeridas' => $carrerasSugeridas,
            'familias_profesionales' => $familiasProfesionales,
            'perfil_institucional_riasec' => $riasecCounts,
            'tendencia_academica' => $tendenciaAcademica,
            'tendencia_vocacional' => $tendenciaVocacional,
            'alertas_institucionales' => $alertas,
            'recomendaciones_institucionales' => $recomendaciones,
            'lectura_interpretativa' => $lecturaInterpretativa,
            'visualizaciones' => $visualizaciones,
        ];
    }

    private function generarMatrizEspecialidadArea($calificaciones): array
    {
        $especialidades = EspecialidadTecnica::orderBy('nom_esp')->get();
        $areas = [
            'Cosmos y Pensamiento',
            'Comunidad y Sociedad',
            'Vida, Tierra y Territorio',
            'Ciencia, Tecnología y Producción'
        ];

        $matriz = [];
        foreach ($especialidades as $esp) {
            $fila = ['especialidad' => $esp->nom_esp, 'celdas' => []];
            foreach ($areas as $area) {
                $calsFiltradas = $calificaciones->filter(function ($cal) use ($esp, $area) {
                    if (!$cal->estudiante || !$cal->estudiante->especialidad || !$cal->asignatura) return false;
                    if ($cal->estudiante->especialidad->cod_esp !== $esp->cod_esp) return false;
                    $infoAsi = AsignaturaInteligente::interpretar($cal->asignatura->nom_asi);
                    return ($infoAsi['area'] ?? '') === $area;
                });

                $fila['celdas'][$area] = [
                    'promedio' => $calsFiltradas->count() > 0 ? round((float) $calsFiltradas->avg('not_cal'), 1) : null,
                    'cantidad' => $calsFiltradas->count()
                ];
            }
            $matriz[] = $fila;
        }

        return $matriz;
    }
}
