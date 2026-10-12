<?php

namespace App\Support\Orientacion;

use App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2;

/** Presentación de evidencia existente; no calcula intereses ni recomienda carreras. */
final class ExperienciaIntereses
{
    public const AREAS = [
        'R' => ['nombre' => 'Realista', 'icono' => 'wrench', 'actividad' => 'Construir, reparar y trabajar con herramientas.', 'ejemplo' => 'Actividades prácticas para construir, reparar o trabajar con objetos.'],
        'I' => ['nombre' => 'Investigador', 'icono' => 'flask', 'actividad' => 'Investigar y comprender cómo funcionan las cosas.', 'ejemplo' => 'Actividades que requieren preguntas, análisis y búsqueda de explicaciones.'],
        'A' => ['nombre' => 'Artístico', 'icono' => 'palette', 'actividad' => 'Crear, diseñar y expresarte.', 'ejemplo' => 'Actividades de creación, imaginación y expresión de ideas.'],
        'S' => ['nombre' => 'Social', 'icono' => 'users-three', 'actividad' => 'Ayudar, enseñar y acompañar a otras personas.', 'ejemplo' => 'Actividades de colaboración, enseñanza y acompañamiento a otras personas.'],
        'E' => ['nombre' => 'Emprendedor', 'icono' => 'megaphone', 'actividad' => 'Proponer, liderar y negociar.', 'ejemplo' => 'Actividades para impulsar ideas, liderar o negociar.'],
        'C' => ['nombre' => 'Convencional', 'icono' => 'list-checks', 'actividad' => 'Organizar información y seguir procedimientos.', 'ejemplo' => 'Actividades con información ordenada, registros y procedimientos.'],
    ];

    public static function perfilVigente(?array $respuestas, ?array $resultado): bool
    {
        return $respuestas && $resultado
            && ContratoAporteIngenierilV2::riasecValido($respuestas)
            && ContratoAporteIngenierilV2::respuestaValida('riasec_score', $resultado)
            && $respuestas['instrument_version'] === $resultado['instrument_version'];
    }

    public static function presentar(array $contexto): array
    {
        $actividad = $contexto['activity'] ?? null;
        $vigente = self::perfilVigente($actividad?->riasec_public, $actividad?->riasec_score);
        $puntajes = $vigente ? $actividad->riasec_score['scores'] : [];
        $orden = array_keys(self::AREAS);
        usort($orden, fn ($a, $b) => ($puntajes[$b] ?? -1) <=> ($puntajes[$a] ?? -1));
        $destacados = $vigente ? array_values(array_filter($orden, fn ($area) => $puntajes[$area] === max($puntajes))) : [];
        $areas = [];
        foreach (self::AREAS as $codigo => $area) {
            $areas[$codigo] = $area + ['puntaje' => $puntajes[$codigo] ?? null, 'destacada' => in_array($codigo, $destacados, true)];
        }

        // Las notas se presentan por contexto, sin convertir promedios parciales en rendimiento anual.
        $periodos = [];
        foreach ($contexto['payload']['history'] ?? [] as $periodo) {
            $periodos[] = self::periodo($periodo['period'], $periodo['records'] ?? [], false, $contexto['gestiones'][$periodo['period']] ?? null);
        }
        if (isset($contexto['payload']['academic_period'])) {
            $periodos[] = self::periodo($contexto['payload']['academic_period'], $contexto['payload']['academic']['records'] ?? [], true, $contexto['gestiones'][$contexto['payload']['academic_period']] ?? null);
        }

        return [
            'vigente' => (bool) $vigente,
            'anterior' => (bool) $actividad?->riasec_score && ! $vigente,
            'areas' => $areas,
            'codigo' => $vigente ? $actividad->riasec_score['holland_code'] : null,
            'destacados' => $destacados,
            'empate' => count($destacados) > 1,
            'plano' => $vigente && count(array_unique($puntajes)) === 1,
            'periodos' => $periodos,
            'fecha' => $actividad?->finalizado_at,
            'bloqueos' => array_values(array_filter($contexto['precheck']['requirements'] ?? [], fn ($item) => $item['type'] === 'OBLIGATORIO' && ! $item['complete'])),
        ];
    }

    private static function periodo(string $codigo, array $registros, bool $actual, ?int $anio): array
    {
        return ['codigo' => $codigo, 'anio' => $anio, 'actual' => $actual, 'registros' => count($registros),
            'materias' => array_values(array_unique(array_column($registros, 'subject'))),
        ];
    }
}
