<?php

namespace App\Support\Academico;

use App\Models\Calificacion;
use App\Models\GestionAcademica;
use App\Models\PlanAsignatura;
use App\Support\Core\SoporteInteligenteBase;

class PlanAsignaturaInteligente extends SoporteInteligenteBase
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
        $advertencias = [];
        if (! empty($datos['cod_gea'])) {
            $gestion = GestionAcademica::find($datos['cod_gea']);
            if ($gestion && in_array($gestion->est_gea, ['CERRADA', 'CERRADO', 'ARCHIVADA'], true)) {
                $bloqueos[] = 'La gestión está cerrada; no se permite modificar la planificación.';
            }
        }
        if ($ignorarCodigo && Calificacion::where('cod_pas', $ignorarCodigo)->exists()) {
            $actual = PlanAsignatura::find($ignorarCodigo);
            foreach ($relaciones as $campo) {
                if ($actual && ($datos[$campo] ?? null) !== $actual->{$campo}) {
                    $bloqueos[] = 'El plan tiene calificaciones; no puede cambiarse su identidad académica.';
                    break;
                }
            }
        }
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
            'advertencias' => $advertencias,
            'puede_guardar' => $bloqueos === [],
        ];
    }
}
