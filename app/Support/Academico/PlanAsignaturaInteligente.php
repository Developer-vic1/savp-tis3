<?php

namespace App\Support\Academico;

use App\Models\PlanAsignatura;

class PlanAsignaturaInteligente
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $relaciones = ['cod_asi', 'cod_doc', 'cod_cur', 'cod_par', 'cod_tur', 'cod_gea'];
        $faltantes = collect($relaciones)->filter(fn ($campo) => blank($datos[$campo] ?? null))->values()->all();
        $horas = is_numeric($datos['hor_pas'] ?? null) ? (int) $datos['hor_pas'] : 0;

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
        }

        $bloqueos = [];
        if ($faltantes !== []) {
            $bloqueos[] = 'Faltan relaciones académicas obligatorias.';
        }
        if ($horas < 1 || $horas > 40) {
            $bloqueos[] = 'Las horas asignadas deben estar entre 1 y 40.';
        }
        if ($duplicado) {
            $bloqueos[] = 'Ya existe un plan con la misma combinación académica.';
        }

        return [
            'datos' => array_merge($datos, ['hor_pas' => $horas, 'est_pas' => $datos['est_pas'] ?? 'ACTIVO']),
            'faltantes' => $faltantes,
            'duplicado' => $duplicado,
            'completitud' => (int) round(((count($relaciones) - count($faltantes) + ($horas > 0 ? 1 : 0)) / 7) * 100),
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }
}
