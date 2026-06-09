<?php

namespace App\Support\Academico;

use App\Models\PeriodoEvaluacion;
use App\Support\CatalogoInteligenteBase;

class PeriodoEvaluacionInteligente extends CatalogoInteligenteBase
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_pev'] ?? '');
        $orden = is_numeric($datos['ord_pev'] ?? null) ? (int) $datos['ord_pev'] : 0;
        $duplicidad = $this->analizarDuplicidad($nombre, PeriodoEvaluacion::all(), $ignorarCodigo);
        $ordenDuplicado = PeriodoEvaluacion::query()
            ->when($ignorarCodigo, fn ($q) => $q->where('cod_pev', '!=', $ignorarCodigo))
            ->where('ord_pev', $orden)->exists();
        $bloqueos = [];

        if (mb_strlen($nombre) < 4) {
            $bloqueos[] = 'El nombre del periodo es incompleto.';
        }
        if ($orden < 1 || $orden > 20) {
            $bloqueos[] = 'El orden debe estar entre 1 y 20.';
        }
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe un periodo igual o críticamente similar.';
        }
        if ($ordenDuplicado) {
            $bloqueos[] = 'El orden seleccionado ya está asignado a otro periodo.';
        }

        return [
            'datos' => ['nom_pev' => $nombre, 'ord_pev' => $orden, 'est_pev' => $datos['est_pev'] ?? 'ACTIVO'],
            'duplicidad' => $duplicidad,
            'completitud' => $this->completitud(['nombre' => $nombre, 'orden' => $orden], ['nombre', 'orden']),
            'bloqueos' => $bloqueos,
            'sugerencias' => ['Primer Trimestre', 'Segundo Trimestre', 'Tercer Trimestre'],
            'puede_guardar' => $bloqueos === [],
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_pev;
    }
}
