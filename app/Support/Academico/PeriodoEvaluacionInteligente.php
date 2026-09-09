<?php

namespace App\Support\Academico;

use App\Models\PeriodoEvaluacion;
use App\Support\CatalogoInteligenteBase;
use Illuminate\Support\Facades\DB;

class PeriodoEvaluacionInteligente extends CatalogoInteligenteBase
{
    public const TRANSICIONES = [
        'PLANIFICADO' => ['ACTIVO'], 'ACTIVO' => ['EN_CIERRE'],
        'EN_CIERRE' => ['CERRADO'], 'CERRADO' => ['REABIERTO'], 'REABIERTO' => ['EN_CIERRE'],
    ];

    public function analizarTransicion(PeriodoEvaluacion $periodo, string $destino): array
    {
        $bloqueos = [];
        if (! in_array($destino, self::TRANSICIONES[$periodo->est_pev] ?? [], true)) {
            $bloqueos[] = 'La transición no corresponde al estado actual del periodo.';
        }
        if (! $periodo->cod_gea || ! $periodo->fii_pev || ! $periodo->ffi_pev) {
            $bloqueos[] = 'Debe definir gestión y fechas del periodo antes de cambiar su estado.';
        }
        if ($destino === 'CERRADO' && $periodo->cod_gea) {
            $pendientes = $this->calificacionesPendientes($periodo);
            if ($pendientes > 0) {
                $bloqueos[] = "Existen {$pendientes} calificaciones pendientes.";
            }
        }

        return ['puede_continuar' => $bloqueos === [], 'bloqueos' => $bloqueos, 'advertencias' => [], 'sugerencias' => []];
    }

    public function calificacionesPendientes(PeriodoEvaluacion $periodo): int
    {
        if (! $periodo->cod_gea || ! $periodo->fii_pev || ! $periodo->ffi_pev) {
            return 0;
        }

        return DB::table('inscripcion_vigencia as v')
            ->join('inscripcion_estudiante as i', 'i.cod_ins', '=', 'v.cod_ins')
            ->join('plan_asignatura as p', function ($join) {
                $join->on('p.cod_gea', '=', 'i.cod_gea')->on('p.cod_cur', '=', 'v.cod_cur')->on('p.cod_par', '=', 'v.cod_par')->on('p.cod_tur', '=', 'v.cod_tur');
            })->where('i.cod_gea', $periodo->cod_gea)->where('v.est_ivg', '<>', 'ANULADA')->where('p.est_pas', 'ACTIVO')
            ->whereDate('v.fii_ivg', '<=', $periodo->ffi_pev)->where(fn ($q) => $q->whereNull('v.ffi_ivg')->orWhereDate('v.ffi_ivg', '>=', $periodo->fii_pev))
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('calificacion as c')->whereColumn('c.cod_est', 'i.cod_est')->whereColumn('c.cod_pas', 'p.cod_pas')->where('c.cod_pev', $periodo->cod_pev)->where('c.est_cal', 'ACTIVO'))
            ->distinct()->count(DB::raw('(i.cod_est,p.cod_pas)'));
    }

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_pev'] ?? '');
        $orden = is_numeric($datos['ord_pev'] ?? null) ? (int) $datos['ord_pev'] : 0;
        $duplicidad = $this->analizarDuplicidad($nombre, PeriodoEvaluacion::where('cod_gea', $datos['cod_gea'] ?? null)->get(), $ignorarCodigo);
        $ordenDuplicado = PeriodoEvaluacion::query()
            ->where('cod_gea', $datos['cod_gea'] ?? null)
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
            'datos' => array_merge($datos, ['nom_pev' => $nombre, 'ord_pev' => $orden, 'est_pev' => $datos['est_pev'] ?? 'ACTIVO']),
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
