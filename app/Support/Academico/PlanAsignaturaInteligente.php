<?php

namespace App\Support\Academico;

use App\Models\PlanAsignatura;
use App\Models\Docente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Paralelo;
use App\Models\Turno;
use App\Models\GestionAcademica;

class PlanAsignaturaInteligente
{
    public const ESTADO_COHERENTE = 'COHERENTE';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_DUPLICADA = 'DUPLICADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $relaciones = ['cod_asi', 'cod_doc', 'cod_cur', 'cod_par', 'cod_tur', 'cod_gea'];
        $faltantes = collect($relaciones)->filter(fn ($campo) => blank($datos[$campo] ?? null))->values()->all();
        $horas = is_numeric($datos['hor_pas'] ?? null) ? (int) $datos['hor_pas'] : 0;
        $estado = $datos['est_pas'] ?? 'ACTIVO';

        $bloqueos = [];
        $advertencias = [];
        $accionesRecomendadas = [];

        if ($faltantes !== []) {
            $bloqueos[] = 'Faltan relaciones académicas obligatorias para el plan curricular.';
        }
        if ($horas < 1 || $horas > 40) {
            $bloqueos[] = 'Las horas semanales asignadas al plan de asignatura deben estar entre 1 y 40 horas.';
        }

        // Check duplicate
        $duplicado = false;
        if ($faltantes === []) {
            $duplicado = PlanAsignatura::query()
                ->when($ignorarCodigo, fn ($q) => $q->where('cod_pas', '!=', $ignorarCodigo))
                ->where(function ($q) use ($datos, $relaciones) {
                    foreach ($relaciones as $campo) {
                        $q->where($campo, $datos[$campo]);
                    }
                })
                ->exists();
            if ($duplicado) {
                $bloqueos[] = 'Ya existe un plan registrado para la misma asignatura, docente, curso, paralelo, turno y gestión.';
            }
        }

        // Fetch models to do coherence analysis
        $docente = !blank($datos['cod_doc'] ?? null) ? Docente::with('personalInstitucional.persona')->find($datos['cod_doc']) : null;
        $asignatura = !blank($datos['cod_asi'] ?? null) ? Asignatura::find($datos['cod_asi']) : null;
        $curso = !blank($datos['cod_cur'] ?? null) ? Curso::find($datos['cod_cur']) : null;
        $gestion = !blank($datos['cod_gea'] ?? null) ? GestionAcademica::find($datos['cod_gea']) : null;

        // Overload check (teacher hours in this gestion)
        $riesgoSobrecarga = 'Bajo';
        $horasAcumuladas = 0;
        if ($docente && $gestion) {
            $horasAcumuladas = PlanAsignatura::where('cod_doc', $docente->cod_doc)
                ->where('cod_gea', $gestion->cod_gea)
                ->when($ignorarCodigo, fn ($q) => $q->where('cod_pas', '!=', $ignorarCodigo))
                ->sum('hor_pas');

            $totalHorasConEsta = $horasAcumuladas + $horas;
            if ($totalHorasConEsta > 40) {
                $bloqueos[] = "Sobrecarga de carga horaria crítica: El docente superará las 40 horas semanales permitidas (Total: {$totalHorasConEsta} horas).";
                $riesgoSobrecarga = 'Crítico';
            } elseif ($totalHorasConEsta > 30) {
                $advertencias[] = "Riesgo de sobrecarga horaria: El docente acumulará {$totalHorasConEsta} horas en esta gestión.";
                $riesgoSobrecarga = 'Alto';
                $accionesRecomendadas[] = 'Revisar la carga lectiva del docente antes de aprobar el plan.';
            } elseif ($totalHorasConEsta > 20) {
                $riesgoSobrecarga = 'Medio';
            }
        }

        // Coherence: Docente specialty vs Asignatura
        $coherenciaDocente = 'No evaluable';
        if ($docente && $asignatura) {
            $espDoc = mb_strtolower($docente->esp_doc);
            $nomAsi = mb_strtolower($asignatura->nom_asi);

            $coherenciaDocente = 'Apta';
            $mismatch = false;

            // Check mismatches
            if (str_contains($nomAsi, 'programacion') || str_contains($nomAsi, 'informatica') || str_contains($nomAsi, 'base de datos') || str_contains($nomAsi, 'robotica')) {
                if (!str_contains($espDoc, 'sistemas') && !str_contains($espDoc, 'informat') && !str_contains($espDoc, 'comput')) {
                    $mismatch = true;
                }
            } elseif (str_contains($nomAsi, 'gastronom') || str_contains($nomAsi, 'nutri')) {
                if (!str_contains($espDoc, 'gastron') && !str_contains($espDoc, 'cocin') && !str_contains($espDoc, 'alim')) {
                    $mismatch = true;
                }
            } elseif (str_contains($nomAsi, 'contab') || str_contains($nomAsi, 'financ')) {
                if (!str_contains($espDoc, 'contab') && !str_contains($espDoc, 'admin') && !str_contains($espDoc, 'audit') && !str_contains($espDoc, 'econ')) {
                    $mismatch = true;
                }
            }

            if ($mismatch) {
                $advertencias[] = "Incoherencia de perfil docente: El docente con especialidad '{$docente->esp_doc}' no posee el perfil formal óptimo para dictar '{$asignatura->nom_asi}'.";
                $coherenciaDocente = 'Requiere revisión de perfil';
                $accionesRecomendadas[] = 'Verificar certificaciones técnicas adicionales del docente en el área.';
            }
        }

        // Subject suggested hours vs assigned hours
        $coherenciaAsignatura = 'Coherente';
        $nivelExigencia = 'Medio';
        $areaFormativa = 'Ciencia, Tecnología y Producción';
        if ($asignatura) {
            $analisisAsignatura = AsignaturaInteligente::interpretar($asignatura->nom_asi);
            $sugeridas = $analisisAsignatura['horas'] ?? 2;
            $areaFormativa = $analisisAsignatura['area'] ?? 'Ciencia, Tecnología y Producción';
            $nivelExigencia = ($analisisAsignatura['nivel'] === 'Especialización técnica') ? 'Alto' : 'Medio';

            if (abs($horas - $sugeridas) >= 2) {
                $advertencias[] = "Las horas asignadas ({$horas}h) difieren del estándar sugerido institucionalmente ({$sugeridas}h) para '{$asignatura->nom_asi}'.";
                $coherenciaAsignatura = 'Desviación de carga horaria estándar';
                $accionesRecomendadas[] = 'Ajustar la carga horaria para adecuarse a la malla oficial.';
            }
        }

        // State detection
        if (!empty($bloqueos)) {
            $estadoInteligente = $duplicado ? self::ESTADO_DUPLICADA : self::ESTADO_BLOQUEADA;
        } elseif (!empty($advertencias)) {
            $estadoInteligente = self::ESTADO_REVISION;
        } elseif (empty($faltantes)) {
            $estadoInteligente = self::ESTADO_COHERENTE;
        } else {
            $estadoInteligente = self::ESTADO_INCOMPLETA;
        }

        $explicacion = empty($advertencias) && empty($bloqueos)
            ? 'Planificación académica consolidada y validada preventivamente sin observaciones.'
            : 'Planificación con observaciones preventivas que requieren validación de la coordinación de BTH.';

        // Completeness
        $totalCampos = 8;
        $camposCompletos = count($relaciones) - count($faltantes);
        if ($horas > 0) $camposCompletos++;
        if ($estado !== '') $camposCompletos++;
        $completitud = (int) round(($camposCompletos / $totalCampos) * 100);

        // Visualizacion metrics
        $visualizacion = [
            'riesgo_sobrecarga' => $riesgoSobrecarga,
            'horas_acumuladas' => $horasAcumuladas,
            'coherencia_docente' => $coherenciaDocente,
            'coherencia_asignatura' => $coherenciaAsignatura,
            'nivel_exigencia' => $nivelExigencia,
            'area_formativa' => $areaFormativa,
            'color_hex' => $this->colorRiesgo($riesgoSobrecarga),
        ];

        return [
            'datos' => [
                'cod_asi' => $datos['cod_asi'] ?? null,
                'cod_doc' => $datos['cod_doc'] ?? null,
                'cod_cur' => $datos['cod_cur'] ?? null,
                'cod_par' => $datos['cod_par'] ?? null,
                'cod_tur' => $datos['cod_tur'] ?? null,
                'cod_gea' => $datos['cod_gea'] ?? null,
                'hor_pas' => $horas,
                'est_pas' => $estado,
            ],
            'estado_inteligente' => $estadoInteligente,
            'confianza' => 100,
            'completitud' => $completitud,
            'duplicidad' => [
                'exacto' => $duplicado,
                'aproximado_critico' => false,
                'aproximado_leve' => false,
                'similitud' => $duplicado ? 100.0 : 0.0,
                'registro' => null,
            ],
            'coincidencias' => $duplicado ? [['tipo' => 'ALTA', 'mensaje' => 'Combinación idéntica ya en sistema']] : [],
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => ['Verificar horas acumuladas semanales', 'Comprobar especialidad del docente contra el área de la materia'],
            'explicacion' => $explicacion,
            'acciones_recomendadas' => $accionesRecomendadas ?: ['Planificación validada. Puede ser consolidada.'],
            'visualizacion' => $visualizacion,
            'puede_guardar' => empty($bloqueos),
        ];
    }

    private function colorRiesgo(string $riesgo): string
    {
        return match ($riesgo) {
            'Crítico' => '#ef4444',
            'Alto' => '#f97316',
            'Medio' => '#f59e0b',
            default => '#10b981',
        };
    }
}
