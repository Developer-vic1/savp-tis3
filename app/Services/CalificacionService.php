<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\InscripcionEstudiante;
use App\Models\PeriodoEvaluacion;
use App\Models\PlanAsignatura;
use App\Policies\CalificacionPolicy;
use App\Support\Academico\PeriodoEvaluacionInteligente;
use App\Support\Evaluacion\CalificacionInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CalificacionService
{
    public function previsualizarImportacion(array $filas): array
    {
        Gate::authorize('Calificaciones');
        $resultado = app(CalificacionInteligente::class)->previsualizarImportacion($filas);
        foreach ($resultado['validos'] as $fila) {
            $plan = PlanAsignatura::findOrFail($fila['cod_pas']);
            if (! app(CalificacionPolicy::class)->registrar(auth()->user(), $plan)) {
                $resultado['errores'][] = 'No tiene permiso para calificar el plan '.$plan->cod_pas;
            }
        }
        $resultado['puede_continuar'] = $resultado['errores'] === [];

        return $resultado;
    }

    public function importar(array $filas): array
    {
        return DB::transaction(function () use ($filas) {
            $preview = $this->previsualizarImportacion($filas);
            if (! $preview['puede_continuar']) {
                throw ValidationException::withMessages(['importacion' => $preview['errores']]);
            }
            foreach ($preview['validos'] as $fila) {
                $this->registrar($fila);
            }

            return ['registradas' => count($preview['validos']), 'errores' => 0];
        }, 3);
    }

    public function cambiarEstadoPeriodo(string $codigo, string $destino, string $motivo, ?string $version = null): PeriodoEvaluacion
    {
        Gate::authorize('Gestion_Academica');
        abort_unless(auth()->user()?->hasAnyRole(['Administrador', 'Director']), 403);
        if (trim($motivo) === '') {
            throw ValidationException::withMessages(['motivoPeriodo' => 'El cambio de estado requiere motivo.']);
        }

        return DB::transaction(function () use ($codigo, $destino, $motivo, $version) {
            $periodo = PeriodoEvaluacion::whereKey($codigo)->lockForUpdate()->firstOrFail();
            if ($version !== null && $version !== $periodo->getRawOriginal('updated_at')) {
                throw ValidationException::withMessages(['motivoPeriodo' => 'El registro fue modificado por otro usuario. Actualice la información antes de continuar.']);
            }
            $analisis = app(PeriodoEvaluacionInteligente::class)->analizarTransicion($periodo, $destino);
            if (! $analisis['puede_continuar']) {
                throw ValidationException::withMessages(['motivoPeriodo' => implode(' ', $analisis['bloqueos'])]);
            }
            $antes = $periodo->toArray();
            $periodo->est_pev = $destino;
            if ($destino === 'CERRADO') {
                $periodo->fec_cie_pev = now();
            }
            $periodo->save();
            BitacoraService::registrar(accion: 'PERIODO_'.$destino, tabla: 'periodo_evaluacion', registro: $codigo, modulo: 'Periodo de Evaluación', descripcion: trim($motivo), valoresAnteriores: $antes, valoresNuevos: $periodo->toArray());

            return $periodo;
        }, 3);
    }

    public function registrar(array $datos, ?string $codigo = null, ?string $version = null, ?string $motivo = null): Calificacion
    {
        return DB::transaction(function () use ($datos, $codigo, $version, $motivo) {
            Validator::make($datos, ['cod_est' => 'required|exists:estudiante,cod_est', 'cod_pas' => 'required|exists:plan_asignatura,cod_pas', 'cod_pev' => 'required|exists:periodo_evaluacion,cod_pev', 'not_cal' => 'required|numeric|between:0,100', 'obs_cal' => 'nullable|string|max:255', 'est_cal' => 'required|in:ACTIVO,INACTIVO,ANULADO'])->validate();
            $plan = PlanAsignatura::whereKey($datos['cod_pas'])->lockForUpdate()->firstOrFail();
            $gestion = DB::table('gestion_academica')->where('cod_gea', $plan->cod_gea)->lockForUpdate()->first();
            if (! $gestion || ! in_array($gestion->est_gea, ['ACTIVO', 'ACTIVA', 'EN_CIERRE'], true) || $plan->est_pas !== 'ACTIVO') {
                throw ValidationException::withMessages(['calificacion' => 'La gestión o el plan no permiten registrar calificaciones.']);
            }
            $periodo = PeriodoEvaluacion::whereKey($datos['cod_pev'])->lockForUpdate()->firstOrFail();
            $usuario = auth()->user();
            abort_unless($usuario && app(CalificacionPolicy::class)->registrar($usuario, $plan), 403);
            if (! $periodo->cod_gea || $periodo->cod_gea !== $plan->cod_gea || ! $periodo->fii_pev || ! $periodo->ffi_pev) {
                throw ValidationException::withMessages(['form.cod_pev' => 'El periodo debe tener gestión y fechas compatibles con el plan.']);
            }
            if ($periodo->est_pev === 'PLANIFICADO' || $periodo->est_pev === 'INACTIVO') {
                throw ValidationException::withMessages(['form.cod_pev' => 'El periodo aún no permite calificar.']);
            }
            if (! in_array($periodo->est_pev, ['ACTIVO', 'EN_CIERRE', 'REABIERTO'], true)) {
                abort_unless($usuario && app(CalificacionPolicy::class)->rectificar($usuario, $plan), 403);
                if (trim($motivo ?? '') === '') {
                    throw ValidationException::withMessages(['motivo' => 'La rectificación excepcional requiere motivo.']);
                }
            }
            $inscripcion = InscripcionEstudiante::where('cod_est', $datos['cod_est'])->where('cod_gea', $plan->cod_gea)->first();
            if (! $inscripcion || ! $inscripcion->vigencias()->where('cod_cur', $plan->cod_cur)->where('cod_par', $plan->cod_par)->where('cod_tur', $plan->cod_tur)->where('est_ivg', '<>', 'ANULADA')->whereDate('fii_ivg', '<=', $periodo->ffi_pev)->where(fn ($q) => $q->whereNull('ffi_ivg')->orWhereDate('ffi_ivg', '>=', $periodo->fii_pev))->exists()) {
                throw ValidationException::withMessages(['form.cod_est' => 'El estudiante no tiene vigencia compatible con el plan y periodo.']);
            }
            $registro = $codigo ? Calificacion::whereKey($codigo)->lockForUpdate()->firstOrFail() : new Calificacion;
            if ($version !== null && $registro->getRawOriginal('updated_at') !== $version) {
                throw ValidationException::withMessages(['calificacion' => 'El registro fue modificado por otro usuario. Actualice la información antes de continuar.']);
            }
            if ($codigo && ($registro->cod_est !== $datos['cod_est'] || $registro->cod_pev !== $datos['cod_pev'] || ($registro->cod_pas && $registro->cod_pas !== $datos['cod_pas']))) {
                throw ValidationException::withMessages(['calificacion' => 'La identidad de una calificación existente no puede cambiarse.']);
            }
            $datos['cod_asi'] = $plan->cod_asi;
            $analisis = app(CalificacionInteligente::class)->analizar($datos, $codigo);
            if (! $analisis['puede_guardar']) {
                throw ValidationException::withMessages(['calificacion' => implode(' ', $analisis['bloqueos'])]);
            }
            $antes = $registro->toArray();
            if (! $codigo) {
                $registro->cod_cal = 'CAL_'.bin2hex(random_bytes(8));
            }
            $registro->fill(array_intersect_key($datos, array_flip(['cod_est', 'cod_pas', 'cod_asi', 'cod_pev', 'not_cal', 'obs_cal', 'est_cal'])))->save();
            BitacoraService::registrar(accion: $codigo ? 'CALIFICACION_RECTIFICADA' : 'CALIFICACION_REGISTRADA', tabla: 'calificacion', registro: $registro->cod_cal, modulo: 'Calificaciones', descripcion: $motivo, valoresAnteriores: $antes, valoresNuevos: $registro->toArray());

            return $registro;
        }, 3);
    }

    public function corregir(string $codigo, array $datos, string $motivo, ?string $version = null): Calificacion
    {
        if (trim($motivo) === '') {
            throw ValidationException::withMessages(['motivo' => 'Debe indicar el motivo de rectificación.']);
        }

        return $this->registrar($datos, $codigo, $version, $motivo);
    }
}
