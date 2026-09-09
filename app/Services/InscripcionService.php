<?php

namespace App\Services;

use App\Models\InscripcionEstudiante;
use App\Models\InscripcionVigencia;
use App\Support\Academico\InscripcionAcademica;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InscripcionService
{
    public function __construct(private InscripcionAcademica $soporte) {}

    public function confirmar(string $codigo): InscripcionEstudiante
    {
        return DB::transaction(function () use ($codigo) {
            $registro = InscripcionEstudiante::whereKey($codigo)->lockForUpdate()->firstOrFail();
            Gate::authorize('gestionar', $registro);
            if (! $this->soporte->transicionPermitida($registro->est_ins, 'CONFIRMADA')) {
                $this->bloquear('La inscripción no puede confirmarse desde su estado actual.');
            }
            $analisis = $this->soporte->evaluarConfirmacionFinal($registro->toArray(), $registro->documentos->toArray(), $codigo);
            if (! $analisis['puede_confirmar']) {
                $this->bloquear($analisis['mensaje']);
            }
            $antes = $registro->toArray();
            $resultado = $this->guardar(array_replace($antes, ['est_ins' => 'CONFIRMADA', 'fec_con_ins' => now()]), $codigo);
            BitacoraService::registrar(accion: 'INSCRIPCION_CONFIRMADA', tabla: 'inscripcion_estudiante', registro: $codigo, modulo: 'Inscripciones', valoresAnteriores: $antes, valoresNuevos: $resultado->toArray());

            return $resultado;
        }, 3);
    }

    public function cambiarCurso(string $codigo, string $destino, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'CAMBIO', $motivo, $fecha, ['cod_cur' => $destino], $version);
    }

    public function cambiarParalelo(string $codigo, string $destino, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'CAMBIO', $motivo, $fecha, ['cod_par' => $destino], $version);
    }

    public function cambiarTurno(string $codigo, string $destino, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'CAMBIO', $motivo, $fecha, ['cod_tur' => $destino], $version);
    }

    public function cambiarEspecialidad(string $codigo, ?string $destino, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'CAMBIO', $motivo, $fecha, ['cod_esp_tec' => $destino], $version);
    }

    public function guardar(array $datos, ?string $codigo = null): InscripcionEstudiante
    {
        Gate::authorize('Inscripciones');
        Validator::make($datos, [
            'cod_est' => 'required|exists:estudiante,cod_est', 'cod_gea' => 'required|exists:gestion_academica,cod_gea',
            'cod_cur' => 'required|exists:curso,cod_cur', 'cod_par' => 'required|exists:paralelo,cod_par', 'cod_tur' => 'required|exists:turno,cod_tur',
            'est_ins' => 'required|in:PENDIENTE,CONFIRMADA,ACTIVA,OBSERVADA', 'fei_ins' => 'required|date',
        ])->validate();

        return DB::transaction(function () use ($datos, $codigo) {
            $gestion = DB::table('gestion_academica')->where('cod_gea', $datos['cod_gea'])->lockForUpdate()->first();
            if (! $gestion || ! in_array($gestion->est_gea, ['ACTIVA', 'ACTIVO', 'PLANIFICADA', 'PLANIFICADO'], true)) {
                $this->bloquear('La gestión no permite inscripciones.');
            }
            $registro = $codigo ? InscripcionEstudiante::whereKey($codigo)->lockForUpdate()->firstOrFail() : new InscripcionEstudiante;
            $antes = $registro->exists ? $registro->toArray() : null;
            if ($codigo) {
                foreach (['cod_est', 'cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'cod_esp_tec'] as $campo) {
                    if (($datos[$campo] ?? null) != $registro->{$campo}) {
                        $this->bloquear('Utilice Gestionar cambio para modificar la identidad o el destino de una inscripción existente.');
                    }
                }
                if (in_array($registro->est_ins, ['RETIRADA', 'ANULADA', 'ARCHIVADA'], true)) {
                    $this->bloquear('Utilice Gestionar cambio para el reingreso o la restitución.');
                }
            }
            $cupo = $this->soporte->calcularCupo($datos['cod_gea'], $datos['cod_cur'], $datos['cod_par'], $datos['cod_tur'], $codigo);
            if ($cupo['disponibles'] < 1 && ! ($datos['sob_aut_ins'] ?? false)) {
                $this->bloquear('El último cupo ya fue ocupado. Actualice la información.');
            }
            if ($datos['sob_aut_ins'] ?? false) {
                Gate::authorize('corregir', $registro);
            }
            $registro->fill($datos)->save();
            if (in_array($registro->est_ins, ['ACTIVA', 'CONFIRMADA', 'OBSERVADA'], true) && ! $registro->vigencias()->exists()) {
                InscripcionVigencia::create($registro->only(['cod_ins', 'cod_cur', 'cod_par', 'cod_tur', 'cod_esp_tec']) + [
                    'cod_ivg' => 'IVG_'.bin2hex(random_bytes(8)), 'fii_ivg' => $registro->fei_ins,
                    'tip_ivg' => 'INICIAL', 'est_ivg' => 'ACTIVA',
                ]);
            }
            BitacoraService::registrar(accion: $codigo ? 'INSCRIPCION_ACTUALIZADA' : 'INSCRIPCION_REGISTRADA', tabla: 'inscripcion_estudiante', registro: $registro->cod_ins, modulo: 'Inscripciones', valoresAnteriores: $antes, valoresNuevos: $registro->toArray());

            return $registro;
        }, 3);
    }

    public function retirar(string $codigo, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'RETIRO', $motivo, $fecha, [], $version);
    }

    public function anular(string $codigo, string $motivo, string $fecha, ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'ANULACION', $motivo, $fecha, [], $version);
    }

    public function reingresar(string $codigo, string $motivo, string $fecha, array $destino = [], ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'REINGRESO', $motivo, $fecha, $destino, $version);
    }

    public function restituir(string $codigo, string $motivo, string $fecha, array $destino = [], ?string $version = null): InscripcionEstudiante
    {
        return $this->gestionar($codigo, 'RESTITUCION', $motivo, $fecha, $destino, $version);
    }

    public function gestionar(string $codigo, string $accion, string $motivo, string $fecha, array $destino = [], ?string $version = null): InscripcionEstudiante
    {
        $motivo = trim($motivo);
        Validator::make(compact('motivo', 'fecha', 'accion'), [
            'motivo' => ['required', 'string', 'max:2000'],
            'fecha' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'accion' => ['required', 'in:RETIRO,ANULACION,CAMBIO,REINGRESO,RESTITUCION'],
        ])->validate();

        return DB::transaction(function () use ($codigo, $accion, $motivo, $fecha, $destino, $version) {
            $registro = InscripcionEstudiante::findOrFail($codigo);
            Gate::authorize('gestionar', $registro);
            // Un bloqueo común de gestión evita competir por el último cupo entre destinos.
            $gestion = DB::table('gestion_academica')->where('cod_gea', $registro->cod_gea)->lockForUpdate()->first();
            $registro = InscripcionEstudiante::whereKey($codigo)->lockForUpdate()->firstOrFail();
            if ($version !== null && $registro->getRawOriginal('updated_at') !== $version) {
                $this->bloquear('El registro fue modificado por otro usuario. Actualice la información antes de continuar.');
            }
            if (! $gestion || ! in_array($gestion->est_gea, ['ACTIVA', 'ACTIVO'], true)) {
                $this->bloquear('La gestión no está activa.');
            }
            if ($fecha < $registro->fei_ins->toDateString() || ($gestion->fii_gea && $fecha < $gestion->fii_gea) || ($gestion->ffi_gea && $fecha > $gestion->ffi_gea)) {
                $this->bloquear('La fecha efectiva debe pertenecer a la inscripción y a su gestión.');
            }
            if ($fecha < today()->toDateString() || $accion === 'RESTITUCION') {
                Gate::authorize('corregir', $registro);
            }
            $antes = $registro->toArray();
            $vigencia = $registro->vigencias()->where('est_ivg', 'ACTIVA')->lockForUpdate()->first();
            if (in_array($accion, ['RETIRO', 'ANULACION'], true)) {
                $evaluacion = $accion === 'RETIRO'
                    ? $this->soporte->evaluarRetiro($codigo, $motivo)
                    : $this->soporte->evaluarAnulacion($codigo, $motivo);
                if (! $evaluacion['puede_continuar']) {
                    $this->bloquear(implode(' ', $evaluacion['bloqueos']));
                }
                if ($accion === 'RETIRO' && ! $vigencia) {
                    $this->bloquear('No existe vigencia activa. Revise la inconsistencia histórica antes de retirar.');
                }
                if ($vigencia) {
                    if ($fecha < $vigencia->fii_ivg->toDateString()) {
                        $this->bloquear('La fecha de cierre es anterior a la vigencia activa.');
                    }
                    $vigencia->update(['ffi_ivg' => $fecha, 'cie_ivg' => $accion === 'RETIRO' ? 'RETIRO' : 'CORRECCION', 'mot_ivg' => $motivo, 'est_ivg' => $accion === 'RETIRO' ? 'CERRADA' : 'ANULADA']);
                }
                $datos = $accion === 'RETIRO' ? $this->soporte->prepararDatosRetiro($motivo) : $this->soporte->prepararDatosAnulacion($motivo);
                $datos[$accion === 'RETIRO' ? 'fec_ret_ins' : 'fec_anu_ins'] = $fecha;
                $registro->fill($datos)->save();
            } else {
                $permitidos = match ($accion) {
                    'CAMBIO' => ['ACTIVA', 'CONFIRMADA', 'OBSERVADA'],
                    'REINGRESO' => ['RETIRADA'],
                    'RESTITUCION' => ['ANULADA', 'RETIRADA'],
                };
                if (! in_array($registro->est_ins, $permitidos, true)) {
                    $this->bloquear('La acción no corresponde al estado actual de la inscripción.');
                }
                $datos = array_replace($registro->only(['cod_cur', 'cod_par', 'cod_tur', 'cod_esp_tec']), array_intersect_key($destino, array_flip(['cod_cur', 'cod_par', 'cod_tur', 'cod_esp_tec'])));
                $datos['cod_esp_tec'] = $datos['cod_esp_tec'] ?: null;
                Validator::make($datos, [
                    'cod_cur' => 'required|exists:curso,cod_cur', 'cod_par' => 'required|exists:paralelo,cod_par',
                    'cod_tur' => 'required|exists:turno,cod_tur', 'cod_esp_tec' => 'nullable|exists:especialidad_tecnica,cod_esp',
                ])->validate();
                $analisis = $this->soporte->validarCombinacionAcademica($registro->cod_gea, $datos['cod_cur'], $datos['cod_par'], $datos['cod_tur']);
                if (! $analisis['puede_continuar']) {
                    $this->bloquear(implode(' ', $analisis['bloqueos']));
                }
                $especialidad = $this->soporte->validarEspecialidadTecnica(array_replace($registro->toArray(), $datos));
                if ($especialidad['bloquea'] ?? false) {
                    $this->bloquear($especialidad['mensaje']);
                }
                $cupo = $this->soporte->calcularCupo($registro->cod_gea, $datos['cod_cur'], $datos['cod_par'], $datos['cod_tur'], $codigo);
                if ($cupo['disponibles'] < 1) {
                    $this->bloquear('No existen cupos disponibles para el destino.');
                }
                if ($accion === 'CAMBIO') {
                    if (! $vigencia || $fecha <= $vigencia->fii_ivg->toDateString()) {
                        $this->bloquear('El cambio requiere una vigencia anterior y una fecha posterior a su inicio.');
                    }
                    if ($datos === $registro->only(array_keys($datos))) {
                        $this->bloquear('Seleccione un destino diferente al actual.');
                    }
                    $vigencia->update(['ffi_ivg' => Carbon::parse($fecha)->subDay()->toDateString(), 'cie_ivg' => 'CAMBIO', 'mot_ivg' => $motivo, 'est_ivg' => 'CERRADA']);
                } elseif ($vigencia) {
                    $this->bloquear('La inscripción ya tiene una vigencia activa.');
                }
                if ($registro->vigencias()->where('est_ivg', '<>', 'ANULADA')->where(fn ($q) => $q->whereNull('ffi_ivg')->orWhereDate('ffi_ivg', '>=', $fecha))->exists()) {
                    $this->bloquear('La nueva vigencia se solapa con la historia existente.');
                }
                $registro->fill($datos + ['est_ins' => 'ACTIVA'])->save();
                InscripcionVigencia::create($datos + [
                    'cod_ivg' => 'IVG_'.bin2hex(random_bytes(8)), 'cod_ins' => $codigo,
                    'fii_ivg' => $fecha, 'tip_ivg' => $accion, 'mot_ivg' => $motivo, 'est_ivg' => 'ACTIVA',
                ]);
            }
            $this->sincronizarMembresia($registro, $fecha);
            BitacoraService::registrar(
                accion: match ($accion) {
                    'RETIRO' => 'INSCRIPCION_RETIRADA', 'ANULACION' => 'INSCRIPCION_ANULADA', 'RESTITUCION' => 'INSCRIPCION_RESTITUIDA',
                    'CAMBIO' => $antes['cod_cur'] !== $registro->cod_cur ? 'CAMBIO_CURSO' : ($antes['cod_par'] !== $registro->cod_par ? 'CAMBIO_PARALELO' : ($antes['cod_tur'] !== $registro->cod_tur ? 'CAMBIO_TURNO' : 'CAMBIO_ESPECIALIDAD')),
                    default => $accion
                },
                tabla: 'inscripcion_estudiante', registro: $codigo, modulo: 'Inscripciones',
                descripcion: $motivo.' (fecha efectiva: '.$fecha.')', valoresAnteriores: $antes, valoresNuevos: $registro->toArray(),
            );

            return $registro;
        }, 3);
    }

    private function sincronizarMembresia(InscripcionEstudiante $inscripcion, string $fecha): void
    {
        $clases = DB::table('clase_virtual as c')
            ->leftJoin('plan_asignatura as p', 'p.cod_pas', '=', 'c.cod_pas')
            ->leftJoin('plan_especialidad as e', 'e.cod_pes', '=', 'c.cod_pes')
            ->where(fn ($q) => $q->where('p.cod_gea', $inscripcion->cod_gea)->orWhere('e.cod_gea', $inscripcion->cod_gea))
            ->select('c.cod_cla', 'c.est_cla')->selectRaw('COALESCE(p.cod_cur,e.cod_cur) as cod_cur, COALESCE(p.cod_par,e.cod_par) as cod_par, COALESCE(p.cod_tur,e.cod_tur) as cod_tur, e.cod_esp')->get();
        foreach ($clases as $clase) {
            $valida = in_array($inscripcion->est_ins, ['ACTIVA', 'CONFIRMADA', 'OBSERVADA'], true)
                && $clase->est_cla === 'ACTIVA' && $clase->cod_cur === $inscripcion->cod_cur
                && $clase->cod_par === $inscripcion->cod_par && $clase->cod_tur === $inscripcion->cod_tur
                && (! $clase->cod_esp || $clase->cod_esp === $inscripcion->cod_esp_tec);
            $query = DB::table('clase_estudiante')->where('cod_cla', $clase->cod_cla)->where('cod_est', $inscripcion->cod_est);
            if ($valida) {
                if ($query->exists()) {
                    $query->update(['est_cla_est' => 'ACTIVO', 'fec_ret_cla_est' => null, 'updated_at' => now()]);
                } else {
                    DB::table('clase_estudiante')->insert(['cod_cla_est' => 'CLE_'.bin2hex(random_bytes(8)), 'cod_cla' => $clase->cod_cla, 'cod_est' => $inscripcion->cod_est, 'fec_inc_cla_est' => $fecha, 'est_cla_est' => 'ACTIVO', 'created_at' => now(), 'updated_at' => now()]);
                }
            } else {
                $query->where('est_cla_est', 'ACTIVO')->update(['est_cla_est' => 'RETIRADO', 'fec_ret_cla_est' => $fecha, 'updated_at' => now()]);
            }
        }
    }

    private function bloquear(string $mensaje): never
    {
        throw ValidationException::withMessages(['cambioInscripcion' => $mensaje]);
    }
}
