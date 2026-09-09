<?php

namespace App\Services;

use App\Models\NovedadEstudiante;
use App\Models\SeguimientoAcademico;
use App\Models\User;
use App\Policies\SeguimientoAcademicoPolicy;
use App\Support\Academico\SeguimientoAcademicoInteligente;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeguimientoAcademicoService
{
    public function abrir(array $datos): SeguimientoAcademico
    {
        return $this->guardar(array_replace($datos, ['est_seg' => 'ABIERTO']));
    }

    public function actualizar(string $codigo, array $datos, ?string $version = null): SeguimientoAcademico
    {
        return $this->guardar($datos, $codigo, $version);
    }

    public function asignarResponsable(string $codigo, string $responsable): SeguimientoAcademico
    {
        return $this->guardar(['cod_usu_res' => $responsable], $codigo);
    }

    public function resolver(string $codigo, string $resultado): SeguimientoAcademico
    {
        return $this->guardar(['est_seg' => 'RESUELTO', 'res_seg' => $resultado, 'fec_cie_seg' => today()->toDateString()], $codigo);
    }

    public function cancelar(string $codigo, string $resultado): SeguimientoAcademico
    {
        return $this->guardar(['est_seg' => 'CANCELADO', 'res_seg' => $resultado, 'fec_cie_seg' => today()->toDateString()], $codigo);
    }

    private function guardar(array $datos, ?string $codigo = null, ?string $version = null): SeguimientoAcademico
    {
        return DB::transaction(function () use ($datos, $codigo, $version) {
            $registro = $codigo ? SeguimientoAcademico::whereKey($codigo)->lockForUpdate()->firstOrFail() : new SeguimientoAcademico;
            Gate::authorize($codigo ? 'update' : 'create', $codigo ? $registro : SeguimientoAcademico::class);
            if ($version !== null && $registro->getRawOriginal('updated_at') !== $version) {
                throw ValidationException::withMessages(['seguimiento' => 'El registro fue modificado por otro usuario. Actualice la información antes de continuar.']);
            }
            if ($codigo && in_array($registro->est_seg, ['RESUELTO', 'CANCELADO'], true)) {
                throw ValidationException::withMessages(['seguimiento' => 'El seguimiento ya se encuentra cerrado.']);
            }
            $antes = $registro->toArray();
            $datos = array_replace($registro->getAttributes(), $datos);
            if ($codigo && ($datos['cod_est'] !== $registro->cod_est || $datos['cod_gea'] !== $registro->cod_gea)) {
                throw ValidationException::withMessages(['seguimiento' => 'El estudiante y la gestión de un seguimiento no pueden cambiarse.']);
            }
            $datos['mot_seg'] = trim($datos['mot_seg'] ?? '');
            $datos['res_seg'] = trim($datos['res_seg'] ?? '') ?: null;
            $validos = Validator::make($datos, [
                'cod_est' => 'required|exists:estudiante,cod_est', 'cod_gea' => 'required|exists:gestion_academica,cod_gea',
                'tip_seg' => 'required|in:ASISTENCIA,RENDIMIENTO,ADMINISTRATIVO,INTEGRAL,OTRO', 'ori_seg' => 'required|string|max:100',
                'mot_seg' => 'required|string|max:4000', 'niv_ape_seg' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
                'est_seg' => 'required|in:ABIERTO,EN_SEGUIMIENTO,RESUELTO,CANCELADO', 'vis_seg' => 'required|in:NORMAL,RESTRINGIDO',
                'cod_usu_res' => 'required|exists:users,cod_usu', 'fec_ape_seg' => 'required|date_format:Y-m-d',
                'fec_pro_seg' => 'nullable|date_format:Y-m-d|after_or_equal:fec_ape_seg',
                'fec_cie_seg' => 'nullable|required_if:est_seg,RESUELTO,CANCELADO|date_format:Y-m-d|after_or_equal:fec_ape_seg',
                'res_seg' => 'nullable|required_if:est_seg,RESUELTO,CANCELADO|string|max:4000', 'pro_acc_seg' => 'nullable|string|max:4000', 'obs_seg' => 'nullable|string|max:4000',
            ])->validate();
            $responsable = User::findOrFail($validos['cod_usu_res']);
            if (! app(SeguimientoAcademicoPolicy::class)->create($responsable)) {
                throw ValidationException::withMessages(['cod_usu_res' => 'El responsable debe estar autorizado para gestionar seguimientos.']);
            }
            $registro->fill($validos);
            Gate::authorize('update', $registro);
            if (! $codigo) {
                $registro->cod_seg = 'SEG_'.bin2hex(random_bytes(8));
            }
            $registro->save();
            // La bitácora general conserva metadatos, sin copiar motivos o detalles restringidos.
            $campos = ['cod_est', 'cod_gea', 'est_seg', 'vis_seg', 'cod_usu_res', 'fec_ape_seg', 'fec_pro_seg', 'fec_cie_seg'];
            BitacoraService::registrar(accion: 'SEGUIMIENTO_'.match ($registro->est_seg) {
                'RESUELTO' => 'RESUELTO', 'CANCELADO' => 'CANCELADO', default => $codigo ? 'ACTUALIZADO' : 'ABIERTO'
            }, tabla: 'seguimiento_academico', registro: $registro->cod_seg, modulo: 'Seguimiento Académico', valoresAnteriores: array_intersect_key($antes, array_flip($campos)), valoresNuevos: $registro->only($campos));

            return $registro;
        }, 3);
    }

    public function registrarNovedad(array $datos, ?string $codigo = null, ?string $version = null, ?UploadedFile $respaldo = null): NovedadEstudiante
    {
        Gate::authorize('create', SeguimientoAcademico::class);

        return DB::transaction(function () use ($datos, $codigo, $version, $respaldo) {
            $registro = $codigo ? NovedadEstudiante::whereKey($codigo)->lockForUpdate()->firstOrFail() : new NovedadEstudiante;
            if ($version !== null && $version !== $registro->getRawOriginal('updated_at')) {
                throw ValidationException::withMessages(['novedad' => 'El registro fue modificado por otro usuario. Actualice la información antes de continuar.']);
            }
            $datos = array_replace($registro->getAttributes(), $datos);
            if ($codigo && ($datos['cod_est'] !== $registro->cod_est || $datos['cod_gea'] !== $registro->cod_gea)) {
                throw ValidationException::withMessages(['novedad' => 'No puede cambiarse el estudiante o gestión de una novedad.']);
            }
            $datos['mot_nes'] = trim($datos['mot_nes'] ?? '');
            $validos = Validator::make($datos, [
                'cod_est' => 'required|exists:estudiante,cod_est', 'cod_gea' => 'required|exists:gestion_academica,cod_gea',
                'tip_nes' => ['required', Rule::in(SeguimientoAcademicoInteligente::TIPOS_NOVEDAD)],
                'fii_nes' => 'required|date_format:Y-m-d', 'ffi_nes' => 'required|date_format:Y-m-d|after_or_equal:fii_nes',
                'est_nes' => 'required|in:ACTIVA,FINALIZADA,CANCELADA', 'mot_nes' => 'required|string|max:4000', 'obs_nes' => 'nullable|string|max:4000',
            ])->validate();
            $registro->fill($validos);
            if ($respaldo) {
                Validator::make(['respaldo' => $respaldo], ['respaldo' => 'file|mimes:pdf,jpg,jpeg,png|max:5120'])->validate();
                $registro->rut_res_nes = $respaldo->store('novedades', 'local');
            }
            if (! $codigo) {
                $registro->cod_nes = 'NES_'.bin2hex(random_bytes(8));
            }
            $registro->save();
            BitacoraService::registrar(accion: 'NOVEDAD_REGISTRADA', tabla: 'novedad_estudiante', registro: $registro->cod_nes, modulo: 'Estudiantes', valoresNuevos: $registro->only(['cod_est', 'cod_gea', 'tip_nes', 'est_nes', 'fii_nes', 'ffi_nes']));

            return $registro;
        }, 3);
    }
}
