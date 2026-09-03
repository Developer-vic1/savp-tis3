<?php

namespace App\Support\Evaluacion;

use App\Models\Calificacion;
use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalificacionInteligente extends SoporteInteligenteBase
{
    public const NOTA_MINIMA = 0;
    public const NOTA_MAXIMA = 100;
    public const NOTA_APROBATORIA = 51;
    public const UMBRAL_VARIACION_ATIPICA = 35.0;

    /**
     * Analiza una calificación respetando la escala boliviana de secundaria (0 a 100).
     */
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nota = is_numeric($datos['not_cal'] ?? null) ? round((float) $datos['not_cal'], 2) : -1;
        $desempeno = $this->clasificar($nota);

        $codEst = $datos['cod_est'] ?? '';
        $codAsi = $datos['cod_asi'] ?? '';
        $codPev = $datos['cod_pev'] ?? '';

        $duplicado = false;
        if ($codEst && $codAsi && $codPev) {
            $duplicado = Calificacion::query()
                ->when($ignorarCodigo, fn ($q) => $q->where('cod_cal', '!=', $ignorarCodigo))
                ->where('cod_est', $codEst)
                ->where('cod_asi', $codAsi)
                ->where('cod_pev', $codPev)
                ->exists();
        }

        $faltantes = collect(['cod_est', 'cod_asi', 'cod_pev'])
            ->filter(fn ($campo) => blank($datos[$campo] ?? null))->values()->all();

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $fuentes = ['RM 0190/2024 Reglamento de Evaluación', 'Escala Cuantitativa 1-100 Secundaria MinEdu'];

        if ($faltantes !== []) {
            $msg = 'Faltan estudiante, asignatura o periodo.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'CAL_CAMPOS_FALTANTES', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        if ($nota < self::NOTA_MINIMA || $nota > self::NOTA_MAXIMA) {
            $msg = 'La calificación debe estar entre ' . self::NOTA_MINIMA . ' y ' . self::NOTA_MAXIMA . ' puntos.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'CAL_FUERA_RANGO', self::TIPO_NORMATIVA, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
        }

        if ($duplicado) {
            $msg = 'Ya existe una calificación registrada para el mismo estudiante, asignatura y periodo.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'CAL_DUPLICADA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
        }

        // Análisis de Desempeño y Variación Histórica
        if ($codEst && $codAsi && $nota >= 0 && $nota <= 100 && Schema::hasTable('calificacion')) {
            $historico = Calificacion::where('cod_est', $codEst)
                ->where('cod_asi', $codAsi)
                ->when($ignorarCodigo, fn ($q) => $q->where('cod_cal', '!=', $ignorarCodigo))
                ->pluck('not_cal');

            if ($historico->isNotEmpty()) {
                $promedioHistorico = round($historico->avg(), 1);
                $datosCalculados['promedio_historico_area'] = $promedioHistorico;
                $diferencia = abs($nota - $promedioHistorico);

                if ($diferencia >= self::UMBRAL_VARIACION_ATIPICA) {
                    $adv = "La nota ({$nota}) presenta una variación atípica de {$diferencia} pts respecto al promedio histórico del estudiante en esta asignatura ({$promedioHistorico} pts). Verifique si fue digitada correctamente.";
                    $advertencias[] = $adv;
                    $this->registrarHallazgo($hallazgos, 'CAL_VARIACION_ATIPICA', self::TIPO_ESTADISTICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, [
                        'nota_ingresada' => $nota,
                        'promedio_historico' => $promedioHistorico,
                        'diferencia' => $diferencia,
                    ]);
                }
            }
        }

        if ($nota >= 0 && $nota < self::NOTA_APROBATORIA) {
            $sug = 'La nota es menor a 51 (en riesgo de reprobación). Se recomienda planificar actividades de reforzamiento pedagógico continuo.';
            $sugerencias[] = $sug;
            $this->registrarHallazgo($hallazgos, 'CAL_EN_RIESGO_PEDAGOGICO', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
        }

        $puedeGuardar = $bloqueos === [];

        return [
            // Retrocompatibilidad con vistas
            'datos' => array_merge($datos, [
                'not_cal' => max(0, $nota),
                'obs_cal' => trim((string) ($datos['obs_cal'] ?? '')) ?: $this->observacion($nota),
                'est_cal' => $datos['est_cal'] ?? 'ACTIVO',
            ]),
            'desempeno' => $desempeno,
            'riesgo' => $nota >= 0 && $nota < self::NOTA_APROBATORIA,
            'duplicado' => $duplicado,
            'completitud' => (int) round(((3 - count($faltantes) + ($nota >= 0 && $nota <= 100 ? 1 : 0)) / 4) * 100),
            'bloqueos' => $bloqueos,
            'puede_guardar' => $puedeGuardar,
            'puede_continuar' => $puedeGuardar,
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
            'hallazgos' => $hallazgos,
            'datos_calculados' => $datosCalculados,
            'fuentes_regla' => $fuentes,
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
