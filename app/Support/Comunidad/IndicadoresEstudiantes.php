<?php

namespace App\Support\Comunidad;

use Illuminate\Support\Collection;

final class IndicadoresEstudiantes
{
    /** Consulta visual: un estudiante se cuenta una vez, tomando su última inscripción en la gestión. */
    public static function desde(Collection $estudiantes, Collection $cursos, Collection $paralelos, Collection $especialidades): array
    {
        $registros = $estudiantes->map(function ($estudiante) {
            $inscripcion = $estudiante->inscripciones->sortByDesc('created_at')->sortByDesc('fei_ins')->first();

            return [
                'curso' => $inscripcion?->cod_cur,
                'paralelo' => $inscripcion?->cod_par,
                'especialidad' => $estudiante->cod_esp,
                'inscrito' => $inscripcion && in_array($inscripcion->est_ins, ['ACTIVA', 'ACTIVO'], true),
                'sin_inscripcion' => ! $inscripcion,
                'fecha' => $inscripcion?->fei_ins?->format('Y-m'),
            ];
        });
        $gruposCursos = $cursos->map(fn ($curso) => [
            'valor' => $curso->getKey(), 'nombre' => $curso->nom_cur,
            'total' => $registros->where('curso', $curso->getKey())->count(),
            'paralelos' => $paralelos->map(fn ($paralelo) => ['valor' => $paralelo->getKey(), 'nombre' => $paralelo->nom_par, 'total' => $registros->where('curso', $curso->getKey())->where('paralelo', $paralelo->getKey())->count()])->values()->all(),
        ])->values()->all();
        $gruposEspecialidades = $especialidades->map(fn ($especialidad) => [
            'valor' => $especialidad->getKey(), 'nombre' => $especialidad->nom_esp,
            'total' => $registros->where('especialidad', $especialidad->getKey())->count(),
        ])->values()->all();
        $sinEspecialidad = $registros->filter(fn ($r) => ! $r['especialidad'])->count();
        $meses = $registros->filter(fn ($r) => $r['fecha'])->groupBy('fecha')->sortKeys()->map(fn ($grupo, $mes) => ['mes' => $mes, 'total' => $grupo->count()])->values()->all();

        return [
            'total' => $registros->count(), 'inscritos' => $registros->where('inscrito', true)->count(),
            'sin_inscripcion' => $registros->where('sin_inscripcion', true)->count(),
            'con_especialidad' => $registros->count() - $sinEspecialidad,
            'sin_especialidad' => $sinEspecialidad,
            'cursos' => $gruposCursos, 'especialidades' => $gruposEspecialidades, 'meses' => $meses,
        ];
    }
}
