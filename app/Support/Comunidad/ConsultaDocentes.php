<?php

namespace App\Support\Comunidad;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\Paralelo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/** Adaptador de lectura: conserva los registros y el contrato académico. */
class ConsultaDocentes
{
    public function gestiones(): Collection
    {
        return GestionAcademica::orderByDesc('ani_gea')->get();
    }

    public function consultar(?string $gestion, ?string $docente = null): Collection
    {
        $relaciones = ['personalInstitucional.persona.usuario.roles'];
        foreach (['planAsignaturas' => 'plan_asignatura', 'planEspecialidades' => 'plan_especialidad'] as $relacion => $tabla) {
            $canonico = Schema::hasColumn($tabla, 'cod_gac');
            $relaciones[$relacion] = function ($q) use ($gestion, $canonico) {
                if ($gestion) {
                    $canonico ? $q->whereHas('grupoAcademico', fn ($grupo) => $grupo->where('cod_gea', $gestion)) : $q->where('cod_gea', $gestion);
                }
            };
            $relaciones[] = $relacion.'.'.($relacion === 'planAsignaturas' ? 'asignatura' : 'especialidadTecnica');
            if ($canonico) {
                $relaciones[] = $relacion.'.grupoAcademico.curso';
                $relaciones[] = $relacion.'.grupoAcademico.paralelo';
            }
        }
        $docentes = Docente::with($relaciones)->when($docente, fn ($q) => $q->whereKey($docente))->get();
        $cursos = Curso::all()->keyBy('cod_cur');
        $paralelos = Paralelo::all()->keyBy('cod_par');
        foreach ($docentes as $registro) {
            foreach ($registro->planAsignaturas->concat($registro->planEspecialidades) as $plan) {
                $grupo = $plan->relationLoaded('grupoAcademico') ? $plan->grupoAcademico : null;
                // Alias de lectura que reutiliza el calendario compartido con ambos contratos.
                $plan->setRelation('curso', $grupo?->curso ?? $cursos->get($plan->getRawOriginal('cod_cur')));
                $plan->setRelation('paralelo', $grupo?->paralelo ?? $paralelos->get($plan->getRawOriginal('cod_par')));
                if ($plan->relationLoaded('especialidadTecnica')) {
                    $plan->setRelation('especialidad', $plan->especialidadTecnica);
                }
            }
        }
        return $docentes;
    }

    public function ficha($docente): array
    {
        $persona = $docente->personalInstitucional?->persona;
        $planes = [];
        foreach (['materia' => $docente->planAsignaturas, 'tecnica' => $docente->planEspecialidades] as $tipo => $asignaciones) {
            foreach ($asignaciones as $plan) {
                $esMateria = $tipo === 'materia';
                $planes[] = [
                    'nombre' => $esMateria ? ($plan->asignatura?->nom_asi ?: 'Materia por revisar') : ($plan->especialidadTecnica?->nom_esp ?: 'Especialidad por revisar'),
                    'grupo' => ($plan->curso?->nom_cur ?: 'Curso por revisar').' · '.($plan->paralelo?->nom_par ?: 'Paralelo por revisar'),
                    'tipo' => $tipo, 'horas' => (float) ($esMateria ? $plan->hor_pas : $plan->hor_pes),
                    'activo' => ($esMateria ? $plan->est_pas : $plan->est_pes) === 'ACTIVO',
                    'estado' => $esMateria ? $plan->est_pas : $plan->est_pes,
                ];
            }
        }
        $activos = collect($planes)->where('activo', true)->values();
        return [
            'clave' => (string) $docente->getKey(),
            'nombre' => mb_strtoupper(trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) ?: 'Identidad por revisar'),
            'ci' => $persona?->ci_per ?: 'Sin documento registrado', 'perfil' => $docente->esp_doc ?: 'Especialidad profesional por completar',
            'completo' => app(DocenteInteligente::class)->analizarEspecialidad($docente->esp_doc)['puede_guardar'],
            'acceso' => $persona?->usuario ? ($persona->usuario->est_usu === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO') : 'SIN_CUENTA',
            'usuario' => $persona?->usuario,
            'correo' => $persona?->ema_per ?: '', 'telefono' => $persona?->tel_per ?: '', 'cuenta' => $persona?->usuario?->email ?: '',
            'roles' => $persona?->usuario?->roles->pluck('name')->implode(', ') ?: '',
            'planes' => $planes, 'activos' => $activos->all(), 'horas' => (float) round($activos->sum('horas'), 2),
            'horas_materias' => round($activos->where('tipo', 'materia')->sum('horas'), 2),
            'horas_tecnicas' => round($activos->where('tipo', 'tecnica')->sum('horas'), 2),
            'cursos' => $activos->pluck('grupo')->unique()->values()->all(), 'materias' => $activos->pluck('nombre')->unique()->values()->all(),
        ];
    }
}
