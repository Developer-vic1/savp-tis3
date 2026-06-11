<?php

namespace App\Support\Evaluacion;

use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Models\Asignatura;
use App\Support\Academico\AsignaturaInteligente;

class CalificacionInteligente
{
    public const ESTADO_FORTALEZA = 'FORTALEZA_ACADEMICA';
    public const ESTADO_RIESGO = 'RIESGO_ACADEMICO';
    public const ESTADO_COHERENTE = 'COHERENTE';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $estudianteId = $datos['cod_est'] ?? null;
        $asignaturaId = $datos['cod_asi'] ?? null;
        $periodoId = $datos['cod_pev'] ?? null;
        $nota = is_numeric($datos['not_cal'] ?? null) ? round((float) $datos['not_cal'], 2) : -1;
        $observacionOriginal = trim((string) ($datos['obs_cal'] ?? ''));
        $estado = $datos['est_cal'] ?? 'ACTIVO';

        $faltantes = [];
        if (!$estudianteId) $faltantes[] = 'cod_est';
        if (!$asignaturaId) $faltantes[] = 'cod_asi';
        if (!$periodoId) $faltantes[] = 'cod_pev';

        $bloqueos = [];
        if (!empty($faltantes)) {
            $bloqueos[] = 'Faltan relaciones académicas obligatorias (Estudiante, Asignatura o Periodo).';
        }
        if ($nota < 0 || $nota > 100) {
            $bloqueos[] = 'La nota de la calificación debe estar comprendida obligatoriamente entre 0 y 100.';
        }

        // Check duplicate
        $duplicado = false;
        if (empty($faltantes)) {
            $duplicado = Calificacion::query()
                ->when($ignorarCodigo, fn ($q) => $q->where('cod_cal', '!=', $ignorarCodigo))
                ->where('cod_est', $estudianteId)
                ->where('cod_asi', $asignaturaId)
                ->where('cod_pev', $periodoId)
                ->exists();
            if ($duplicado) {
                $bloqueos[] = 'Ya existe una calificación registrada para el mismo estudiante, asignatura y periodo de evaluación.';
            }
        }

        // Fetch related models
        $estudiante = $estudianteId ? Estudiante::with('especialidad.estudiantes')->find($estudianteId) : null;
        $asignatura = $asignaturaId ? Asignatura::find($asignaturaId) : null;

        // Subject details from AsignaturaInteligente
        $areaAcademica = 'No definida';
        $tipoAsignatura = 'No definido';
        $nivelAsignatura = 'No definido';
        $carrerasRelacionadas = [];
        if ($asignatura) {
            $analisisAsignatura = AsignaturaInteligente::interpretar($asignatura->nom_asi);
            $areaAcademica = $analisisAsignatura['area'] ?? 'No definida';
            $tipoAsignatura = $analisisAsignatura['tipo'] ?? 'No definido';
            $nivelAsignatura = $analisisAsignatura['nivel'] ?? 'No definido';
            $carrerasRelacionadas = $analisisAsignatura['carreras_relacionadas'] ?? [];
        }

        // Student specialty
        $especialidadEstudiante = $estudiante?->especialidad?->nom_esp ?? null;

        // Scale
        $desempeno = $this->clasificar($nota);
        $categoriaRiesgo = $this->categoriaRiesgo($nota);
        $nivelRiesgo = ($nota <= 50) ? 'Alto/Crítico' : (($nota < 70) ? 'Medio' : 'Bajo');

        // Dynamic academic performance messages & vocational compatibility
        $compatibilidadAcademica = 50;
        $impactoVocacional = 'Neutral';
        $fortalezaDetectada = 'Ninguna';
        $debilidadDetectada = 'Ninguna';
        $mensajeOrientacion = 'Se recomienda seguimiento regular del rendimiento académico.';
        $recomendacionDocente = 'Ofrecer retroalimentación oportuna sobre las tareas.';
        $recomendacionEstudiante = 'Asistir a las sesiones de consulta y tutoría.';
        $advertencias = [];

        if ($nota >= 0 && $asignatura) {
            $nombreAsiLower = mb_strtolower($asignatura->nom_asi);
            $esMat = str_contains($nombreAsiLower, 'mat');
            $esFis = str_contains($nombreAsiLower, 'fis');
            $esQmc = str_contains($nombreAsiLower, 'quim');
            $esBio = str_contains($nombreAsiLower, 'biol');
            $esTec = str_contains($nombreAsiLower, 'tecn') || str_contains($nombreAsiLower, 'prog') || str_contains($nombreAsiLower, 'inf') || str_contains($nombreAsiLower, 'robot');

            if ($nota >= 90) {
                $estadoInteligente = self::ESTADO_FORTALEZA;
                $fortalezaDetectada = "Alto rendimiento cognitivo en {$asignatura->nom_asi}.";
                if ($esMat) {
                    $mensajeOrientacion = 'Presenta fortaleza lógico-matemática destacada, favorable para áreas de ingeniería, tecnología, economía, estadística y ciencias aplicadas.';
                    $recomendacionEstudiante = 'Considerar participar en olimpiadas científicas o clubes de robótica para potenciar el talento.';
                } elseif ($esQmc || $esBio) {
                    $mensajeOrientacion = 'Demuestra excelente aptitud científica en ciencias de la vida, ideal para áreas de salud, bioquímica, agronomía e ingeniería ambiental.';
                } else {
                    $mensajeOrientacion = "Desempeño destacado en el área de {$areaAcademica}, demostrando alta aptitud académica para carreras afines.";
                }
                $recomendacionDocente = 'Proponer retos de profundización investigativa y asignarle rol de tutor par en el aula.';
                $compatibilidadAcademica = 90;
                $impactoVocacional = 'Muy positivo';
            } elseif ($nota <= 50) {
                $estadoInteligente = self::ESTADO_RIESGO;
                $debilidadDetectada = "Dificultad de aprendizaje en {$asignatura->nom_asi}.";
                if ($esMat || $esFis) {
                    $mensajeOrientacion = 'Requiere refuerzo en razonamiento físico-matemático antes de considerar carreras de alta exigencia técnica como ingeniería, electrónica o construcción civil.';
                } else {
                    $mensajeOrientacion = "Rendimiento crítico en {$asignatura->nom_asi}. Se recomienda tutoría especializada para recuperar los aprendizajes.";
                }
                $recomendacionDocente = 'Diseñar plan de remediación académica y coordinar con el gabinete de orientación.';
                $recomendacionEstudiante = 'Establecer un cronograma diario de estudio enfocado y resolver guías de ejercicios básicos.';
                $compatibilidadAcademica = 25;
                $impactoVocacional = 'Desfavorable para áreas técnicas directas';
                $advertencias[] = 'Rendimiento académico en nivel de riesgo. Requiere acompañamiento.';
            } else {
                $estadoInteligente = self::ESTADO_COHERENTE;
                $mensajeOrientacion = "Rendimiento estable y aprobado en {$asignatura->nom_asi}. Mantiene competencias básicas esperadas.";
                if ($nota >= 80) {
                    $compatibilidadAcademica = 75;
                    $impactoVocacional = 'Favorable';
                } else {
                    $compatibilidadAcademica = 60;
                    $impactoVocacional = 'Estable';
                }
            }

            // Relationship between student's BTH specialty and performance
            if ($especialidadEstudiante) {
                $espLower = mb_strtolower($especialidadEstudiante);
                $relacionado = false;
                if (str_contains($espLower, 'sistemas') && ($esMat || $esTec)) $relacionado = true;
                if (str_contains($espLower, 'gastronom') && ($esQmc || str_contains($nombreAsiLower, 'gas') || str_contains($nombreAsiLower, 'nutr'))) $relacionado = true;
                if (str_contains($espLower, 'contab') && ($esMat || str_contains($nombreAsiLower, 'fin') || str_contains($nombreAsiLower, 'cont'))) $relacionado = true;
                if (str_contains($espLower, 'electr') && ($esFis || $esTec)) $relacionado = true;
                if (str_contains($espLower, 'construc') && ($esMat || $esFis || str_contains($nombreAsiLower, 'dib') || str_contains($nombreAsiLower, 'plan'))) $relacionado = true;

                if ($relacionado) {
                    if ($nota >= 80) {
                        $mensajeOrientacion .= " Compatibilidad académica alta con su especialidad técnica BTH ({$especialidadEstudiante}). Su rendimiento consolida su perfil vocacional profesional.";
                        $compatibilidadAcademica = min($compatibilidadAcademica + 15, 100);
                    } elseif ($nota <= 50) {
                        $mensajeOrientacion .= " Alerta vocacional: Presenta bajo rendimiento en una asignatura clave vinculada a su especialidad BTH ({$especialidadEstudiante}). Se sugiere revisar aptitudes e intereses.";
                        $compatibilidadAcademica = max($compatibilidadAcademica - 15, 10);
                        $advertencias[] = 'Rendimiento crítico en materia vinculada a su especialidad BTH.';
                    }
                }
            }
        } else {
            $estadoInteligente = self::ESTADO_INCOMPLETA;
        }

        $observacionSugerida = $observacionOriginal ?: $mensajeOrientacion;

        // Completeness calculation
        $totalCampos = 5;
        $camposCompletos = 0;
        if ($estudianteId) $camposCompletos++;
        if ($asignaturaId) $camposCompletos++;
        if ($periodoId) $camposCompletos++;
        if ($nota >= 0) $camposCompletos++;
        if ($observacionOriginal !== '') $camposCompletos++;
        $completitud = (int) round(($camposCompletos / $totalCampos) * 100);

        // Visualizacion array
        $visualizacion = [
            'badge_estado' => $this->badgeEstado($nota),
            'color_estado' => $this->colorEstado($nota),
            'porcentaje_rendimiento' => $nota >= 0 ? (int)$nota : 0,
            'nivel_riesgo' => $nivelRiesgo,
            'categoria_riesgo' => $categoriaRiesgo,
            'area_academica' => $areaAcademica,
            'tipo_asignatura' => $tipoAsignatura,
            'nivel_asignatura' => $nivelAsignatura,
            'fortaleza_detectada' => $fortalezaDetectada,
            'debilidad_detectada' => $debilidadDetectada,
            'carreras_relacionadas' => $carrerasRelacionadas,
            'perfil_riasec_asociado' => $this->sugerirRiasecAsignatura($asignatura?->nom_asi ?? ''),
            'compatibilidad_academica' => $compatibilidadAcademica,
            'impacto_vocacional' => $impactoVocacional,
            'mensaje_orientacion' => $mensajeOrientacion,
            'recomendacion_docente' => $recomendacionDocente,
            'recomendacion_estudiante' => $recomendacionEstudiante,
        ];

        return [
            'datos' => [
                'cod_est' => $estudianteId,
                'cod_asi' => $asignaturaId,
                'cod_pev' => $periodoId,
                'not_cal' => $nota >= 0 ? $nota : 0.00,
                'obs_cal' => $observacionSugerida,
                'est_cal' => $estado,
            ],
            'estado_inteligente' => $estadoInteligente,
            'confianza' => 100,
            'completitud' => $completitud,
            'duplicidad' => [
                'exacto' => $duplicado,
                'aproximado_critico' => false,
                'aproximado_leve' => false,
                'similitud' => $duplicado ? 100.0 : 0.0,
                'registro' => null
            ],
            'coincidencias' => $duplicado ? [['tipo' => 'ALTA', 'mensaje' => 'Ya existe la calificación en el sistema']] : [],
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => [$mensajeOrientacion],
            'explicacion' => $mensajeOrientacion,
            'acciones_recomendadas' => [$recomendacionDocente, $recomendacionEstudiante],
            'visualizacion' => $visualizacion,
            'puede_guardar' => empty($bloqueos),
        ];
    }

    public function clasificar(float $nota): string
    {
        return match (true) {
            $nota >= 90 => 'Destacado',
            $nota >= 80 => 'Muy bueno',
            $nota >= 70 => 'Aprobado sólido',
            $nota >= 51 => 'En seguimiento',
            default => 'En riesgo',
        };
    }

    public function categoriaRiesgo(float $nota): string
    {
        return match (true) {
            $nota >= 70 => 'Sin riesgo académico inmediato',
            $nota >= 51 => 'Riesgo medio',
            $nota >= 36 => 'Riesgo alto',
            default => 'Riesgo crítico',
        };
    }

    private function badgeEstado(float $nota): string
    {
        return match (true) {
            $nota >= 90 => 'ui-badge-success',
            $nota >= 70 => 'ui-badge-info',
            $nota >= 51 => 'ui-badge-warning',
            default => 'ui-badge-danger',
        };
    }

    private function colorEstado(float $nota): string
    {
        return match (true) {
            $nota >= 90 => 'var(--ui-success)',
            $nota >= 70 => 'var(--ui-info)',
            $nota >= 51 => 'var(--ui-warning)',
            default => 'var(--ui-danger)',
        };
    }

    private function sugerirRiasecAsignatura(string $nombre): string
    {
        $nom = mb_strtolower($nombre);
        if (str_contains($nom, 'mat') || str_contains($nom, 'fis') || str_contains($nom, 'quim') || str_contains($nom, 'biol')) {
            return 'Investigativo (I)';
        }
        if (str_contains($nom, 'prog') || str_contains($nom, 'inf') || str_contains($nom, 'tec') || str_contains($nom, 'robot')) {
            return 'Realista / Investigativo (R/I)';
        }
        if (str_contains($nom, 'art') || str_contains($nom, 'mus') || str_contains($nom, 'dis')) {
            return 'Artístico (A)';
        }
        if (str_contains($nom, 'soc') || str_contains($nom, 'psic') || str_contains($nom, 'val') || str_contains($nom, 'rel')) {
            return 'Social (S)';
        }
        if (str_contains($nom, 'emp') || str_contains($nom, 'fin') || str_contains($nom, 'cont')) {
            return 'Emprendedor / Convencional (E/C)';
        }
        return 'General (G)';
    }
}
