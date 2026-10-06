<?php

namespace App\Support\Evaluacion;

use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\PlanAsignatura;
use App\Models\Oficial\Academico\PlanEspecialidad;

class CalificacionInteligente
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nota = is_numeric($datos['not_cal'] ?? null) ? round((float) $datos['not_cal'], 2) : -1;
        $desempeno = $this->clasificar($nota);
        $campoPlan = ! empty($datos['cod_pas']) ? 'cod_pas' : 'cod_pes';
        $plan = $campoPlan === 'cod_pas' ? PlanAsignatura::find($datos['cod_pas'] ?? '') : PlanEspecialidad::find($datos['cod_pes'] ?? '');
        $inscripcion = $datos['cod_ins'] ?? ($plan && ! empty($datos['cod_est'])
            ? InscripcionEstudiante::where('cod_est', $datos['cod_est'])->where('cod_gea', $plan->cod_gea)->value('cod_ins') : null);
        $duplicado = Calificacion::query()
            ->when($ignorarCodigo, fn ($q) => $q->where('cod_cal', '!=', $ignorarCodigo))
            ->where('cod_ins', $inscripcion)
            ->where($campoPlan, $datos[$campoPlan] ?? null)
            ->where('cod_pev', $datos['cod_pev'] ?? '')
            ->where('est_cal', '!=', 'ANULADA')
            ->exists();

        $faltantes = collect(['cod_est', $campoPlan, 'cod_pev', 'fea_cal'])
            ->filter(fn ($campo) => blank($datos[$campo] ?? null))->values()->all();
        $bloqueos = [];

        if ($faltantes !== []) {
            $bloqueos[] = 'Faltan estudiante, plan, periodo o fecha académica efectiva.';
        }
        if (empty($inscripcion)) {
            $bloqueos[] = 'El estudiante no tiene inscripción en la gestión del plan.';
        }
        if ($nota < 0 || $nota > 100) {
            $bloqueos[] = 'La nota debe estar entre 0 y 100.';
        }
        if ($duplicado) {
            $bloqueos[] = 'Ya existe una calificación para estudiante, asignación y periodo.';
        }

        return [
            'datos' => array_merge($datos, [
                'not_cal' => max(0, $nota),
                'obs_cal' => trim((string) ($datos['obs_cal'] ?? '')) ?: $this->observacion($nota),
                'est_cal' => $datos['est_cal'] ?? 'VIGENTE',
            ]),
            'desempeno' => $desempeno,
            'riesgo' => $nota >= 0 && $nota <= 50,
            'duplicado' => $duplicado,
            'completitud' => (int) round(((4 - count($faltantes) + ($nota >= 0 && $nota <= 100 ? 1 : 0)) / 5) * 100),
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
