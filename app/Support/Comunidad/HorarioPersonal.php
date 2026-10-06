<?php

namespace App\Support\Comunidad;

use App\Models\Oficial\Academico\HorarioDetalle;
use App\Models\Oficial\Academico\PersonalInstitucional;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HorarioPersonal
{
    public function consultar(PersonalInstitucional $personal, ?string $gestion): array
    {
        $docente = $personal->docente;
        if (! $docente || ! Schema::hasTable('horario_detalle')) {
            return [];
        }
        $materias = $docente->planAsignaturas->where('est_pas', 'ACTIVO')->keyBy('cod_pas');
        $tecnicas = $docente->planEspecialidades->where('est_pes', 'ACTIVO')->keyBy('cod_pes');
        if ($materias->isEmpty() && $tecnicas->isEmpty()) {
            return [];
        }
        $consulta = HorarioDetalle::query()->where('est_hde', 'ACTIVO')
            ->where(fn ($q) => $q->whereIn('cod_pas', $materias->keys())->orWhereIn('cod_pes', $tecnicas->keys()))
            ->whereHas('horario', fn ($q) => $q->where('est_hor', 'ACTIVO'))
            ->with(['bloque.plantilla.turno', 'bloque.plantilla.bloques', 'horario']);
        if ($gestion && Schema::hasColumn('horario', 'cod_gea')) {
            $consulta->whereHas('horario', fn ($q) => $q->where('cod_gea', $gestion));
        } elseif ($gestion && Schema::hasColumn('horario', 'cod_gac')) {
            $consulta->whereHas('horario.grupoAcademico', fn ($q) => $q->where('cod_gea', $gestion));
        }
        $dias = ['LUNES' => 'Lunes', 'MARTES' => 'Martes', 'MIERCOLES' => 'Miércoles', 'JUEVES' => 'Jueves', 'VIERNES' => 'Viernes', 'SABADO' => 'Sábado', 'DOMINGO' => 'Domingo'];
        $eventos = $consulta->get()->map(function ($detalle) use ($materias, $tecnicas, $dias) {
            $esMateria = filled($detalle->cod_pas);
            $plan = $esMateria ? $materias->get($detalle->cod_pas) : $tecnicas->get($detalle->cod_pes);
            $bloque = $detalle->bloque;

            return ['id' => (string) $detalle->getKey(), 'dia' => $dias[Str::upper(Str::ascii(trim($detalle->dia_hde)))] ?? (string) $detalle->dia_hde,
                'inicio' => $bloque?->hor_ini_hbl ? substr((string) $bloque->hor_ini_hbl, 0, 5) : '',
                'fin' => $bloque?->hor_fin_hbl ? substr((string) $bloque->hor_fin_hbl, 0, 5) : '',
                'bloque' => $bloque?->nom_hbl ?: 'Bloque académico',
                'turno' => $bloque?->plantilla?->turno?->nom_tur ?: 'Turno por revisar',
                'plantilla_id' => (string) $bloque?->plantilla?->getKey(),
                'plantilla' => $bloque?->plantilla?->nom_pho ?: 'Horario por revisar',
                'aplicada' => (bool) $bloque?->plantilla?->act_pho,
                'periodo' => $bloque?->plantilla?->fec_ini_pho?->format('d/m/Y').' — '.($bloque?->plantilla?->fec_fin_pho?->format('d/m/Y') ?: 'Sin cierre'),
                'asignacion_id' => (string) $plan?->getKey(),
                'recreos' => $bloque?->plantilla?->bloques?->where('tip_hbl', 'RECREO')->where('est_hbl', 'ACTIVO')->map(fn ($recreo) => ['inicio' => substr((string) $recreo->hor_ini_hbl, 0, 5), 'fin' => substr((string) $recreo->hor_fin_hbl, 0, 5)])->values()->all() ?? [],
                'nombre' => $esMateria ? ($plan?->asignatura?->nom_asi ?: 'Materia por revisar') : ($plan?->especialidad?->nom_esp ?: 'Especialidad por revisar'),
                'curso' => $plan?->curso?->nom_cur ?: 'Curso por revisar', 'paralelo' => $plan?->paralelo?->nom_par ?: 'Paralelo por revisar',
                'tipo' => $esMateria ? 'Materia curricular' : 'Especialidad técnica', 'aula' => $detalle->aul_hde ?: '', 'coincide' => false];
        })->values()->all();
        foreach ($eventos as $indice => $evento) {
            if (! $evento['inicio'] || ! $evento['fin']) {
                continue;
            }
            foreach ($eventos as $otroIndice => $otro) {
                if ($indice !== $otroIndice && $evento['plantilla_id'] === $otro['plantilla_id'] && $evento['dia'] === $otro['dia'] && $otro['inicio'] && $otro['fin'] && $evento['inicio'] < $otro['fin'] && $otro['inicio'] < $evento['fin']) {
                    $eventos[$indice]['coincide'] = true;
                    break;
                }
            }
        }

        return $eventos;
    }
}
