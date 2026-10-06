<?php

namespace App\Support\Academico;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** Límites de la propuesta; registrar o confirmar una recuperación es otra operación. */
final class RecuperacionCalendario
{
    public function limites(array $caso, array $gestion, ?array $periodos = null): array
    {
        $inicio = $caso['inicio'] ?? '';
        $fin = $caso['fin'] ?? '';
        if (Validator::make(['fecha'=>$inicio],['fecha'=>'date_format:Y-m-d'])->fails()) $inicio='';
        if (Validator::make(['fecha'=>$fin],['fecha'=>'date_format:Y-m-d'])->fails()) $fin='';
        $caso['grupos'] = is_array($caso['grupos'] ?? null) ? array_values(array_filter($caso['grupos'], 'is_string')) : [];
        $caso['turnos'] = is_array($caso['turnos'] ?? null) ? array_values(array_filter($caso['turnos'], 'is_string')) : [];
        $periodos ??= app(PanelGestionAcademica::class)->periodos($gestion['id']);
        $periodo = collect($periodos)->first(fn ($p) => $inicio && $fin && $p['fecha_inicio'] && $p['fecha_fin']
            && substr($p['fecha_inicio'],0,10) <= $inicio && substr($p['fecha_fin'],0,10) >= $fin);
        $hoy = CarbonImmutable::now('America/La_Paz')->toDateString();
        $minimo = $hoy;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fin)) {
            try { $minimo = max($hoy, ($caso['jornada'] ?? '') === 'PARCIAL' ? $fin : CarbonImmutable::parse($fin)->addDay()->toDateString()); } catch (\Throwable) {}
        }
        $maximo = min(substr($gestion['fecha_fin'],0,10), $periodo ? substr($periodo['fecha_fin'],0,10) : $minimo);
        $horas = DB::table('horario as h')->join('grupo_academico as g','g.cod_gac','=','h.cod_gac')
            ->join('horario_bloque as b','b.cod_pho','=','h.cod_pho')->where('g.cod_gea',$gestion['id'])
            ->where('h.est_hor','<>','ANULADO')->where('b.est_hbl','ACTIVO')->where('b.tip_hbl','CLASE')
            ->when(($caso['alcance'] ?? '')==='GRUPO', fn($q)=>$q->whereIn('g.cod_gac',$caso['grupos'] ?: array_filter([$caso['grupo'] ?? ''])))
            ->when(($caso['alcance'] ?? '')==='TURNO', fn($q)=>$q->whereIn('g.cod_tur',$caso['turnos'] ?: array_filter([$caso['turno'] ?? ''])))
            ->when($inicio, fn($q)=>$q->where('h.fii_hor','<=',$inicio)->where(fn($q)=>$q->whereNull('h.ffi_hor')->orWhere('h.ffi_hor','>=',$inicio)))
            ->selectRaw('min(b.hor_ini_hbl) as inicio, max(b.hor_fin_hbl) as fin')->first();
        return ['minimo'=>$minimo, 'maximo'=>$maximo, 'trimestre'=>$periodo['nombre'] ?? null,
            'hora_minima'=>$horas?->inicio ? substr($horas->inicio,0,5) : null, 'hora_maxima'=>$horas?->fin ? substr($horas->fin,0,5) : null,
            'disponible'=>$periodo && $minimo <= $maximo && $horas?->inicio && $horas?->fin];
    }

    public function revisar(array $caso, array $gestion, ?int $minutosPorGrupo = null): array
    {
        if (($caso['recuperacion'] ?? '')!=='PROPUESTA') return ['bloqueos'=>[], 'avisos'=>[], 'limites'=>$this->limites($caso,$gestion)];
        $l = $this->limites($caso,$gestion);
        $errores = []; $avisos = [];
        if (! $l['disponible']) $errores['fecha_recuperacion']='No hay un rango válido dentro del mismo trimestre. Revisa las fechas del caso y el horario del grupo antes de reprogramar.';
        $fecha = $caso['fecha_recuperacion'] ?? '';
        $a = $caso['hora_recuperacion_inicio'] ?? ''; $b = $caso['hora_recuperacion_fin'] ?? '';
        if ($fecha < $l['minimo'] || $fecha > $l['maximo']) $errores['fecha_recuperacion']='Elige una fecha que no haya pasado, posterior al caso y dentro de su mismo trimestre.';
        if ($fecha===CarbonImmutable::now('America/La_Paz')->toDateString() && $a <= CarbonImmutable::now('America/La_Paz')->format('H:i')) $errores['hora_recuperacion_inicio']='La hora de recuperación ya pasó. Elige una hora futura.';
        if ($fecha===($caso['fin'] ?? '') && (($caso['jornada'] ?? '')!=='PARCIAL' || $a < ($caso['hora_fin'] ?? ''))) $errores['hora_recuperacion_inicio']='La recuperación debe empezar después de que termine el caso.';
        if ($l['hora_minima'] && ($a < $l['hora_minima'] || $b > $l['hora_maxima'])) $errores['hora_recuperacion_inicio']='Usa horas dentro del horario escolar: '.$l['hora_minima'].' a '.$l['hora_maxima'].'.';
        if (preg_match('/^\d{2}:\d{2}$/',$a) && preg_match('/^\d{2}:\d{2}$/',$b)) {
            $minutos = $this->minutos($b)-$this->minutos($a);
            if ($minutos < 60) $errores['hora_recuperacion_fin']='La recuperación debe durar al menos una hora.';
            elseif ($minutosPorGrupo !== null && $minutos > $minutosPorGrupo) $errores['hora_recuperacion_fin']='Esta franja común admite como máximo '.intdiv($minutosPorGrupo,60).' h '.($minutosPorGrupo%60).' min, según el grupo con menos tiempo afectado. Reduce la franja o prepara la recuperación por separado para cada grupo.';
        }
        if ($fecha && ($caso['fin'] ?? '') && $fecha > CarbonImmutable::parse($caso['fin'])->addDays(7)->toDateString()) $avisos[]='La propuesta supera una semana desde el caso. Conviene recuperar antes; coordina con estudiantes y docentes y explica el motivo de la demora.';
        return ['bloqueos'=>$errores,'avisos'=>$avisos,'limites'=>$l];
    }

    private function minutos(string $hora): int
    {
        return (int) substr($hora,0,2)*60+(int) substr($hora,3,2);
    }
}
