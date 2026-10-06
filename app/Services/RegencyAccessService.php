<?php

namespace App\Services;

use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\RegenteAsignacion;
use App\Models\Oficial\Academico\VinculoPersonal;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RegencyAccessService
{
    public function available(): bool
    {
        return Schema::hasTable('regente_asignaciones');
    }

    /** Correlación por gestión Y grado; no basta la identidad del estudiante. */
    public function constrain(Builder $query, User $user, string $table): Builder
    {
        if (app(RoleDashboardResolver::class)->roleFor($user) !== 'Regente' || ! $user->cod_per || ! $this->available()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereExists(function ($sub) use ($user, $table) {
            $sub->selectRaw('1')->from('regente_asignaciones as ra')
                ->join('vinculo_personal as vinculo_regencia', 'vinculo_regencia.cod_vpe', '=', 'ra.cod_vpe')
                ->join('personal_institucional as pi', 'pi.cod_pin', '=', 'vinculo_regencia.cod_pin')
                ->join('gestion_academica as scope_gea', 'scope_gea.cod_gea', '=', 'ra.cod_gea')
                ->where('pi.cod_per', $user->cod_per)->where('pi.est_pin', 'ACTIVO')->where('vinculo_regencia.est_vpe', 'ACTIVO')
                ->where('vinculo_regencia.fii_vpe', '<=', now()->toDateString())
                ->where(fn ($fin) => $fin->whereNull('vinculo_regencia.ffi_vpe')->orWhere('vinculo_regencia.ffi_vpe', '>=', now()->toDateString()))
                ->where('ra.est_ras', 'ACTIVO')
                ->where('ra.fii_ras', '<=', now()->toDateString())
                ->where(fn ($fin) => $fin->whereNull('ra.ffi_ras')->orWhere('ra.ffi_ras', '>=', now()->toDateString()))
                ->where('scope_gea.est_gea', 'ACTIVO')
                ->when(in_array($table, ['plan_asignatura', 'plan_especialidad', 'horario'], true), function ($contexto) use ($table) {
                    $contexto->join('grupo_academico as alcance_grupo', 'alcance_grupo.cod_gac', '=', $table.'.cod_gac')
                        ->whereColumn('ra.cod_gea', 'alcance_grupo.cod_gea')->whereColumn('ra.cod_cur', 'alcance_grupo.cod_cur');
                }, fn ($contexto) => $contexto->whereColumn('ra.cod_gea', $table.'.cod_gea')->whereColumn('ra.cod_cur', $table.'.cod_cur'));
        });
    }

    public function assignments(User $user): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Regente' && $user->can('cursos.ver.institucional'), 403);
        if (! $this->available()) {
            return ['available' => false, 'rows' => null];
        }
        $rows = RegenteAsignacion::with('gestion', 'curso')->where('est_ras', 'ACTIVO')->whereHas('gestion', fn ($q) => $q->where('est_gea', 'ACTIVO'))
            ->whereHas('vinculoPersonal', fn ($q) => $q->where('est_vpe', 'ACTIVO')->whereHas('personalInstitucional', fn ($p) => $p->where('cod_per', $user->cod_per)->where('est_pin', 'ACTIVO')))
            ->orderBy('cod_cur')->paginate(15);

        return ['available' => true, 'rows' => $rows];
    }

    public function assign(User $actor, array $data): RegenteAsignacion
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador' && $actor->can('regencia.asignaciones.gestionar'), 403);
        abort_unless($this->available(), 409, 'La estructura de asignaciones está pendiente de aplicación autorizada.');
        $data = validator($data, [
            'cod_vpe' => ['required', 'exists:vinculo_personal,cod_vpe'],
            'cod_gea' => ['required', 'exists:gestion_academica,cod_gea'],
            'cod_cur' => ['required', 'exists:curso,cod_cur'],
            'fii_ras' => ['required', 'date_format:Y-m-d'],
            'ffi_ras' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fii_ras'],
            'obs_ras' => ['required', 'string', 'min:20', 'max:1000'],
        ])->validate();

        return DB::transaction(function () use ($data) {
            $vinculo = VinculoPersonal::with('cargoInstitucional', 'personalInstitucional.persona.usuario.roles')->lockForUpdate()->findOrFail($data['cod_vpe']);
            $revision = app(\App\Support\Academico\AsignacionRegenciaInteligente::class)->revisar($data);
            if (!$revision['puede_guardar']) throw ValidationException::withMessages(collect($revision['errores'])->mapWithKeys(fn($mensajes,$campo)=>['form.'.$campo=>$mensajes])->all());
            if ($vinculo->est_vpe !== 'ACTIVO' || $vinculo->cargoInstitucional?->cla_cai !== 'REGENTE'
                || $vinculo->personalInstitucional?->est_pin !== 'ACTIVO'
                || $vinculo->personalInstitucional?->persona?->usuario?->est_usu !== 'ACTIVO'
                || ! $vinculo->personalInstitucional?->persona?->usuario?->hasRole('Regente')) {
                throw ValidationException::withMessages(['form.cod_vpe' => 'Se requiere un vínculo institucional activo de regencia y su cuenta autorizada.']);
            }
            if ($data['fii_ras'] < $vinculo->fii_vpe->format('Y-m-d') || ($vinculo->ffi_vpe && (empty($data['ffi_ras']) || $data['ffi_ras'] > $vinculo->ffi_vpe->format('Y-m-d')))) {
                throw ValidationException::withMessages(['form.fii_ras' => 'Las fechas de la asignación deben quedar dentro del vínculo institucional.']);
            }
            if (! Curso::whereKey($data['cod_cur'])->where('est_cur', 'ACTIVO')->exists()) {
                throw ValidationException::withMessages(['form.cod_cur' => 'El grado debe estar activo.']);
            }
            $simultaneas = RegenteAsignacion::where('cod_vpe', $data['cod_vpe'])->where('cod_gea', $data['cod_gea'])->where('est_ras', 'ACTIVO')
                ->when(! empty($data['ffi_ras']), fn ($q) => $q->where('fii_ras', '<=', $data['ffi_ras']))
                ->where(fn ($q) => $q->whereNull('ffi_ras')->orWhere('ffi_ras', '>=', $data['fii_ras']))->get();
            if ($simultaneas->contains('cod_cur', $data['cod_cur'])) {
                throw ValidationException::withMessages(['form.cod_cur' => 'Ya existe una asignación solapada para ese grado. Se conserva el historial anterior.']);
            }
            foreach ($simultaneas as $indice => $primera) {
                foreach ($simultaneas->take($indice) as $segunda) {
                    $inicio = max($data['fii_ras'], $primera->fii_ras->toDateString(), $segunda->fii_ras->toDateString());
                    $fin = min($data['ffi_ras'] ?? '9999-12-31', $primera->ffi_ras?->toDateString() ?? '9999-12-31', $segunda->ffi_ras?->toDateString() ?? '9999-12-31');
                    if ($inicio <= $fin) {
                        throw ValidationException::withMessages(['form.cod_cur' => 'El Regente ya tiene dos grados simultáneos durante el intervalo solicitado.']);
                    }
                }
            }
            $assignment = RegenteAsignacion::create($data + ['est_ras' => 'ACTIVO']);
            BitacoraService::registrar(accion: 'ASIGNAR_REGENTE', tabla: 'regente_asignaciones', registro: $assignment->cod_ras,
                modulo: 'Regencia', descripcion: 'Asignación histórica por vínculo, gestión, grado e intervalo.', valoresNuevos: $data);

            return $assignment;
        });
    }

    public function retirar(User $actor, string $codigo, string $fecha): void
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador' && $actor->can('regencia.asignaciones.gestionar'), 403);
        validator(['fecha' => $fecha], ['fecha' => ['required', 'date_format:Y-m-d']])->validate();
        DB::transaction(function () use ($codigo, $fecha) {
            $asignacion = RegenteAsignacion::lockForUpdate()->findOrFail($codigo);
            if ($fecha < $asignacion->fii_ras->format('Y-m-d')) {
                throw ValidationException::withMessages(['form.fii_ras' => 'El cierre no puede preceder al inicio de la asignación.']);
            }
            $antes = $asignacion->only(['ffi_ras', 'est_ras']);
            $asignacion->update(['ffi_ras' => $fecha, 'est_ras' => 'INACTIVO']);
            BitacoraService::registrar(accion: 'RETIRAR_ASIGNACION_REGENTE', tabla: 'regente_asignaciones', registro: $codigo,
                modulo: 'Regencia', valoresAnteriores: $antes, valoresNuevos: ['ffi_ras' => $fecha, 'est_ras' => 'INACTIVO']);
        });
    }
}
