<?php

namespace App\Services;

use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\InscripcionVigencia;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Inscripción anual única, trayectos históricos y membresías de aulas existentes. */
class InscripcionAcademicaService
{
    public function guardar(array $datos, ?InscripcionEstudiante $inscripcion = null, ?string $fechaContexto = null, ?string $grupoTecnico = null): InscripcionEstudiante
    {
        validator($datos, [
            'cod_est' => ['required', 'exists:estudiante,cod_est'],
            'cod_gea' => ['required', 'exists:gestion_academica,cod_gea'],
            'cod_cur' => ['required', 'exists:curso,cod_cur'],
            'cod_par' => ['required', 'exists:paralelo,cod_par'],
            'cod_tur' => ['required', 'exists:turno,cod_tur'],
            'fei_ins' => ['required', 'date_format:Y-m-d'],
            'est_ins' => ['required', 'in:ACTIVA,PENDIENTE,OBSERVADA,ANULADA,RETIRADA'],
        ])->validate();
        if ($fechaContexto) {
            validator(['fecha' => $fechaContexto], ['fecha' => ['date_format:Y-m-d']])->validate();
        }

        return DB::transaction(function () use ($datos, $inscripcion, $fechaContexto, $grupoTecnico) {
            $estudiante = DB::table('estudiante')->where('cod_est', $datos['cod_est'])->lockForUpdate()->first();
            if ($estudiante->est_est !== 'ACTIVO') {
                throw ValidationException::withMessages(['cod_est' => 'El estudiante debe estar activo para registrar o modificar su inscripción.']);
            }
            $gestion = GestionAcademica::whereKey($datos['cod_gea'])->where('est_gea', 'ACTIVO')->lockForUpdate()->firstOrFail();
            if ($inscripcion) {
                $inscripcion = InscripcionEstudiante::lockForUpdate()->findOrFail($inscripcion->cod_ins);
                if ($inscripcion->cod_est !== $datos['cod_est'] || $inscripcion->cod_gea !== $datos['cod_gea']) {
                    throw ValidationException::withMessages(['cod_ins' => 'La inscripción histórica conserva estudiante y gestión.']);
                }
            } elseif (InscripcionEstudiante::where('cod_est', $datos['cod_est'])->where('cod_gea', $datos['cod_gea'])->exists()) {
                throw ValidationException::withMessages(['cod_ins' => 'El estudiante ya tiene una inscripción anual. Edita esa inscripción.']);
            }
            $grupo = GrupoAcademico::where('cod_gea', $datos['cod_gea'])->where('cod_cur', $datos['cod_cur'])
                ->where('cod_par', $datos['cod_par'])->where('cod_tur', $datos['cod_tur'])->with('turno')->firstOrFail();
            if (mb_strtoupper($grupo->turno?->nom_tur ?? '') !== 'MAÑANA') {
                throw ValidationException::withMessages(['cod_tur' => 'La inscripción regular corresponde a MAÑANA; la técnica es un trayecto complementario.']);
            }
            $fecha = $fechaContexto ?? max($datos['fei_ins'], $gestion->fii_gea->format('Y-m-d'));
            if ($fecha < $gestion->fii_gea->format('Y-m-d') || $fecha > $gestion->ffi_gea->format('Y-m-d')) {
                throw ValidationException::withMessages(['fii_ivg' => 'La fecha efectiva debe estar dentro de la gestión académica.']);
            }
            $teniaContexto = $inscripcion?->exists && $inscripcion->inscripcionVigenciaRegistros()->exists();
            $estadoAnterior = $inscripcion?->est_ins;
            $inscripcion ??= new InscripcionEstudiante;
            $inscripcion->fill(array_intersect_key($datos, array_flip($inscripcion->getFillable())))->save();
            if ($inscripcion->est_ins === 'ACTIVA') {
                if ($teniaContexto && $estadoAnterior !== 'ACTIVA' && ! $fechaContexto) {
                    throw ValidationException::withMessages(['fii_ivg' => 'La reactivación requiere una fecha académica explícita; no reabre intervalos históricos.']);
                }
                $this->sincronizarTrayecto($inscripcion, $grupo, null, $fecha, $fechaContexto !== null);
                if ($inscripcion->cod_esp_tec) {
                    $grupos = PlanEspecialidad::where('cod_esp', $inscripcion->cod_esp_tec)->where('est_pes', 'ACTIVO')
                        ->deGestion($inscripcion->cod_gea)->deCurso($inscripcion->cod_cur)
                        ->when($grupoTecnico, fn ($q) => $q->where('cod_gac', $grupoTecnico))->distinct()->pluck('cod_gac');
                    if ($grupos->count() !== 1) {
                        throw ValidationException::withMessages(['cod_gac_tecnico' => 'Selecciona el grupo oficial de la especialidad; no se puede deducir una asignación ambigua.']);
                    }
                    $this->sincronizarTrayecto($inscripcion, GrupoAcademico::findOrFail($grupos->sole()), $inscripcion->cod_esp_tec, $fecha, $fechaContexto !== null);
                } elseif ($tecnica = InscripcionVigencia::where('cod_ins', $inscripcion->cod_ins)->whereNotNull('cod_esp_tec')->where('est_ivg', 'ACTIVO')->whereNull('ffi_ivg')->lockForUpdate()->first()) {
                    if (! $fechaContexto) {
                        throw ValidationException::withMessages(['fii_ivg' => 'Retirar la especialidad requiere una fecha efectiva explícita.']);
                    }
                    $this->cerrarTrayecto($tecnica, $fecha);
                }
            } elseif ($estadoAnterior === 'ACTIVA' && $teniaContexto) {
                if (! $fechaContexto) {
                    throw ValidationException::withMessages(['fii_ivg' => 'Cambiar el estado requiere la fecha del último día académico para cerrar los trayectos sin borrar historia.']);
                }
                $this->cerrarInscripcion($inscripcion, $fechaContexto);
            }

            return $inscripcion->refresh();
        });
    }

    private function sincronizarTrayecto(InscripcionEstudiante $inscripcion, GrupoAcademico $grupo, ?string $especialidad, string $fecha, bool $fechaExplicita): void
    {
        $actual = InscripcionVigencia::where('cod_ins', $inscripcion->cod_ins)->where('est_ivg', 'ACTIVO')
            ->when($especialidad === null, fn ($q) => $q->whereNull('cod_esp_tec'), fn ($q) => $q->whereNotNull('cod_esp_tec'))
            ->whereNull('ffi_ivg')->lockForUpdate()->first();
        if ($actual && $actual->cod_gac === $grupo->cod_gac && $actual->cod_esp_tec === $especialidad) {
            return;
        }
        if ($actual) {
            if (! $fechaExplicita || $fecha <= $actual->fii_ivg->format('Y-m-d')) {
                throw ValidationException::withMessages(['fii_ivg' => 'El cambio de grupo o especialidad requiere una fecha efectiva explícita posterior al inicio anterior.']);
            }
            $this->cerrarTrayecto($actual, $fecha);
        } elseif (! $fechaExplicita && InscripcionVigencia::where('cod_ins', $inscripcion->cod_ins)
            ->when($especialidad === null, fn ($q) => $q->whereNull('cod_esp_tec'), fn ($q) => $q->whereNotNull('cod_esp_tec'))->exists()) {
            throw ValidationException::withMessages(['fii_ivg' => 'Abrir otro intervalo histórico requiere una fecha académica explícita.']);
        }
        InscripcionVigencia::create(['cod_ins' => $inscripcion->cod_ins, 'cod_gac' => $grupo->cod_gac, 'cod_esp_tec' => $especialidad,
            'fii_ivg' => $fecha, 'tip_ivg' => $especialidad ? 'FORMACION_TECNICA_COMPLEMENTARIA' : 'INSCRIPCION', 'est_ivg' => 'ACTIVO']);
        foreach ($this->aulasDelContexto($grupo->cod_gac, $especialidad)->where('est_cla', 'ACTIVA')->with('planAsignatura', 'planEspecialidad')->get() as $aula) {
            $plan = $aula->planAsignatura ?? $aula->planEspecialidad;
            $sufijo = $aula->cod_pas ? 'pas' : 'pes';
            $inicio = max($fecha, $plan->{'fii_'.$sufijo}->format('Y-m-d'), $aula->fec_ini_cla?->format('Y-m-d') ?? $fecha);
            if (($plan->{'ffi_'.$sufijo} && $inicio > $plan->{'ffi_'.$sufijo}->format('Y-m-d')) || ($aula->fec_fin_cla && $inicio > $aula->fec_fin_cla->format('Y-m-d'))) {
                continue;
            }
            $aula->estudiantes()->create(['cod_est' => $inscripcion->cod_est, 'fec_inc_cla_est' => $inicio, 'est_cla_est' => 'ACTIVO']);
        }
    }

    private function aulasDelContexto(string $grupo, ?string $especialidad)
    {
        return ClaseVirtual::query()->when($especialidad === null,
            fn ($q) => $q->whereHas('planAsignatura', fn ($p) => $p->where('cod_gac', $grupo)),
            fn ($q) => $q->whereHas('planEspecialidad', fn ($p) => $p->where('cod_gac', $grupo)->where('cod_esp', $especialidad)));
    }

    private function cerrarTrayecto(InscripcionVigencia $vigencia, string $primerDiaFuera): void
    {
        if ($primerDiaFuera <= $vigencia->fii_ivg->format('Y-m-d')) {
            throw ValidationException::withMessages(['fii_ivg' => 'El cierre debe ser posterior al inicio académico y conservar los hechos anteriores.']);
        }
        foreach ($this->aulasDelContexto($vigencia->cod_gac, $vigencia->cod_esp_tec)->get() as $aula) {
            foreach ($aula->estudiantes()->where('cod_est', $vigencia->inscripcionEstudiante->cod_est)->whereNull('fec_ret_cla_est')->lockForUpdate()->get() as $membresia) {
                $membresia->update(['fec_ret_cla_est' => $primerDiaFuera, 'est_cla_est' => 'TRANSFERIDO']);
            }
        }
        $vigencia->update(['ffi_ivg' => Carbon::parse($primerDiaFuera)->subDay()->toDateString(), 'est_ivg' => 'CERRADO', 'cie_ivg' => 'CIERRE DE CONTEXTO']);
    }

    public function cerrarInscripcion(InscripcionEstudiante $inscripcion, string $ultimoDiaAcademico): void
    {
        validator(['fecha' => $ultimoDiaAcademico], ['fecha' => ['required', 'date_format:Y-m-d']])->validate();
        DB::transaction(function () use ($inscripcion, $ultimoDiaAcademico) {
            InscripcionEstudiante::whereKey($inscripcion->cod_ins)->lockForUpdate()->firstOrFail();
            $vigencias = InscripcionVigencia::where('cod_ins', $inscripcion->cod_ins)->where('est_ivg', 'ACTIVO')->whereNull('ffi_ivg')->lockForUpdate()->get();
            // Cerrar la técnica primero; su cobertura regular anterior aún es válida.
            foreach ($vigencias->sortByDesc(fn ($v) => $v->cod_esp_tec !== null) as $vigencia) {
                $this->cerrarTrayecto($vigencia, Carbon::parse($ultimoDiaAcademico)->addDay()->toDateString());
            }
        });
    }
}
