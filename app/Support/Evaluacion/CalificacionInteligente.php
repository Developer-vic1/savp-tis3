<?php

namespace App\Support\Evaluacion;

use App\Models\Calificacion;

class CalificacionInteligente
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nota = is_numeric($datos['not_cal'] ?? null) ? round((float) $datos['not_cal'], 2) : -1;
        $desempeno = $this->clasificar($nota);
        $duplicado = Calificacion::query()
            ->when($ignorarCodigo, fn ($q) => $q->where('cod_cal', '!=', $ignorarCodigo))
            ->where('cod_est', $datos['cod_est'] ?? '')
            ->where('cod_asi', $datos['cod_asi'] ?? '')
            ->where('cod_pev', $datos['cod_pev'] ?? '')
            ->exists();

        $faltantes = collect(['cod_est', 'cod_asi', 'cod_pev'])
            ->filter(fn ($campo) => blank($datos[$campo] ?? null))->values()->all();
        $bloqueos = [];

        if ($faltantes !== []) {
            $bloqueos[] = 'Faltan estudiante, asignatura o periodo.';
        }
        if ($nota < 0 || $nota > 100) {
            $bloqueos[] = 'La nota debe estar entre 0 y 100.';
        }
        if ($duplicado) {
            $bloqueos[] = 'Ya existe una calificación para estudiante, asignatura y periodo.';
        }

        return [
            'datos' => array_merge($datos, [
                'not_cal' => max(0, $nota),
                'obs_cal' => trim((string) ($datos['obs_cal'] ?? '')) ?: $this->observacion($nota),
                'est_cal' => $datos['est_cal'] ?? 'ACTIVO',
            ]),
            'desempeno' => $desempeno,
            'riesgo' => $nota >= 0 && $nota <= 50,
            'duplicado' => $duplicado,
            'completitud' => (int) round(((3 - count($faltantes) + ($nota >= 0 && $nota <= 100 ? 1 : 0)) / 4) * 100),
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }

    public function clasificar(float $nota): string
    {
        return match (true) {
            $nota >= 90 => 'Destacado',
            $nota >= 70 => 'Aprobado',
            $nota >= 51 => 'En seguimiento',
            default => 'En riesgo',
        };
    }

    public function observacion(float $nota): string
    {
        return match ($this->clasificar($nota)) {
            'Destacado' => 'Demuestra dominio destacado y fortalezas académicas consolidadas.',
            'Aprobado' => 'Alcanza los aprendizajes previstos para el periodo.',
            'En seguimiento' => 'Requiere seguimiento pedagógico para consolidar aprendizajes.',
            default => 'Requiere intervención y acompañamiento académico prioritario.',
        };
    }
}
