<?php

namespace App\Support\Orientacion;

/** Resume exclusivamente las notas del contexto autorizado, sin consultar ni escribir datos. */
final class TrayectoriaIntereses
{
    public static function presentar(array $contexto): array
    {
        $payload = $contexto['payload'] ?? [];
        $grupos = [];
        foreach ($payload['history'] ?? [] as $historia) {
            $codigo = (string) ($historia['period'] ?? '');
            if ($codigo !== '') {
                $grupos[$codigo] = ['codigo' => $codigo, 'actual' => false, 'registros' => $historia['records'] ?? []];
            }
        }
        if (! empty($payload['academic_period'])) {
            $codigo = (string) $payload['academic_period'];
            $grupos[$codigo] = ['codigo' => $codigo, 'actual' => true, 'registros' => $payload['academic']['records'] ?? []];
        }
        $gestiones = [];
        foreach ($grupos as $codigo => $grupo) {
            $anio = $contexto['gestiones'][$codigo] ?? null;
            $gestiones[] = self::gestion($grupo, is_numeric($anio) ? (int) $anio : null);
        }
        usort($gestiones, fn ($a, $b) => ($b['actual'] <=> $a['actual']) ?: (($b['anio'] ?? 0) <=> ($a['anio'] ?? 0)) ?: strnatcasecmp($a['codigo'], $b['codigo']));

        return [
            'gestiones' => $gestiones,
            'seleccionada' => $gestiones[0]['codigo'] ?? '',
            'opciones' => array_map(fn ($gestion) => ['valor' => $gestion['codigo'], 'etiqueta' => $gestion['etiqueta']], $gestiones),
        ];
    }

    private static function gestion(array $grupo, ?int $anio): array
    {
        $periodos = [];
        $materias = [];
        foreach ($grupo['registros'] as $registro) {
            $materia = trim((string) ($registro['subject'] ?? ''));
            if ($materia === '') {
                continue;
            }
            $periodo = trim((string) ($registro['period'] ?? ''));
            $clave = $periodo !== '' ? $periodo : '__sin_periodo__';
            $orden = is_numeric($registro['period_order'] ?? null) ? (float) $registro['period_order'] : null;
            $periodos[$clave] ??= ['clave' => $clave, 'nombre' => $periodo ?: 'Período no identificado', 'orden' => $orden];
            $materias[$materia] ??= ['nombre' => $materia, 'celdas' => [], 'notas' => [], 'escalas' => []];
            $nota = self::nota($registro);
            $materias[$materia]['celdas'][$clave][] = $nota;
            if ($nota !== null) {
                $materias[$materia]['notas'][] = $nota;
                $minimo = is_numeric($registro['scale_min'] ?? null) ? (float) $registro['scale_min'] : null;
                $maximo = is_numeric($registro['scale_max'] ?? null) ? (float) $registro['scale_max'] : null;
                $materias[$materia]['escalas'][] = [$minimo, $maximo];
            }
        }
        uasort($periodos, fn ($a, $b) => (($a['orden'] ?? PHP_FLOAT_MAX) <=> ($b['orden'] ?? PHP_FLOAT_MAX)) ?: strnatcasecmp($a['nombre'], $b['nombre']));
        $trimestres = count(array_filter($periodos, fn ($periodo) => preg_match('/trimestr/iu', $periodo['nombre'])));
        $registros = 0;
        foreach ($materias as &$materia) {
            $materia['cantidad'] = count($materia['notas']);
            $registros += $materia['cantidad'];
            $materia['suma'] = array_sum($materia['notas']);
            $escalas = array_unique(array_map(fn ($escala) => json_encode($escala), $materia['escalas']));
            $materia['escalas_distintas'] = count($escalas) > 1;
            $materia['promedio'] = $materia['cantidad'] && ! $materia['escalas_distintas'] ? $materia['suma'] / $materia['cantidad'] : null;
            $escala = $materia['escalas'][0] ?? [null, null];
            $materia['escala'] = $escala;
            $materia['barra'] = $materia['promedio'] !== null && $escala[0] !== null && $escala[1] !== null && $escala[1] > $escala[0]
                ? max(0, min(100, (($materia['promedio'] - $escala[0]) / ($escala[1] - $escala[0])) * 100)) : null;
            $materia['periodos_con_nota'] = count(array_filter($materia['celdas'], fn ($notas) => count(array_filter($notas, fn ($nota) => $nota !== null)) > 0));
            $materia['sin_nota'] = count($periodos) - $materia['periodos_con_nota'];
            $materia['varias_notas'] = count(array_filter($materia['celdas'], fn ($notas) => count($notas) > 1)) > 0;
        }
        unset($materia);
        uksort($materias, 'strnatcasecmp');

        return [
            'codigo' => $grupo['codigo'], 'anio' => $anio, 'actual' => $grupo['actual'],
            'etiqueta' => ($anio ? 'Gestión '.$anio : 'Gestión sin año verificado').' · '.($grupo['actual'] ? 'Actual' : 'Histórica'),
            'periodos' => array_values($periodos), 'materias' => array_values($materias),
            'registros' => $registros, 'trimestres' => $trimestres,
        ];
    }

    private static function nota(array $registro): ?float
    {
        $valor = $registro['score'] ?? null;
        if (! is_numeric($valor) || ! is_finite((float) $valor)) {
            return null;
        }
        $nota = (float) $valor;
        if ((is_numeric($registro['scale_min'] ?? null) && $nota < (float) $registro['scale_min'])
            || (is_numeric($registro['scale_max'] ?? null) && $nota > (float) $registro['scale_max'])) {
            return null;
        }

        return $nota;
    }
}
