<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\Regente;
use App\Models\RegenteAsignacion;
use App\Models\User;
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
                ->join('regente as r', 'r.cod_reg', '=', 'ra.cod_reg')
                ->join('personal_institucional as pi', 'pi.cod_pin', '=', 'r.cod_pin')
                ->join('gestion_academica as scope_gea', 'scope_gea.cod_gea', '=', 'ra.cod_gea')
                ->where('pi.cod_per', $user->cod_per)->where('pi.est_pin', 'ACTIVO')->where('r.est_reg', 'ACTIVO')
                ->where('ra.activa', true)
                ->where('scope_gea.est_gea', 'ACTIVO')
                ->whereColumn('ra.cod_gea', $table.'.cod_gea')->whereColumn('ra.cod_cur', $table.'.cod_cur');
        });
    }

    public function assignments(User $user): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Regente' && $user->can('cursos.ver.institucional'), 403);
        if (! $this->available()) {
            return ['available' => false, 'rows' => null];
        }
        $rows = RegenteAsignacion::with('gestion', 'curso')->where('activa', true)->whereHas('gestion', fn ($q) => $q->where('est_gea', 'ACTIVO'))
            ->whereHas('regente', fn ($q) => $q->where('est_reg', 'ACTIVO')->whereHas('personalInstitucional', fn ($p) => $p->where('cod_per', $user->cod_per)->where('est_pin', 'ACTIVO')))
            ->orderBy('cod_cur')->paginate(15);

        return ['available' => true, 'rows' => $rows];
    }

    public function assign(User $actor, array $data): RegenteAsignacion
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador' && $actor->can('regencia.asignaciones.gestionar'), 403);
        abort_unless($this->available(), 409, 'La estructura de asignaciones está pendiente de aplicación autorizada.');
        $data = validator($data, [
            'cod_reg' => ['required', 'exists:regente,cod_reg'],
            'cod_gea' => ['required', 'exists:gestion_academica,cod_gea'],
            'cod_cur' => ['required', 'exists:curso,cod_cur'],
            'activa' => ['required', 'boolean'],
        ])->validate();

        return DB::transaction(function () use ($data) {
            $regent = Regente::query()->lockForUpdate()->findOrFail($data['cod_reg']);
            if ($data['activa']) {
                if ($regent->est_reg !== 'ACTIVO' || $regent->personalInstitucional?->est_pin !== 'ACTIVO') {
                    throw ValidationException::withMessages(['form.cod_reg' => 'El Regente y su perfil institucional deben estar activos.']);
                }
                if (! Curso::whereKey($data['cod_cur'])->where('est_cur', 'ACTIVO')->exists()) {
                    throw ValidationException::withMessages(['form.cod_cur' => 'El grado debe estar activo.']);
                }
            }
            $assignment = RegenteAsignacion::firstOrNew(collect($data)->except('activa')->all());
            $count = RegenteAsignacion::where('cod_reg', $data['cod_reg'])->where('cod_gea', $data['cod_gea'])->where('activa', true)
                ->when($assignment->exists, fn ($q) => $q->whereKeyNot($assignment->getKey()))->count();
            if ($data['activa'] && $count >= 2) {
                throw ValidationException::withMessages(['form.cod_cur' => 'El Regente ya tiene dos grados activos en esta gestión.']);
            }
            $before = $assignment->exists ? $assignment->only(['cod_reg', 'cod_gea', 'cod_cur', 'activa']) : [];
            if (! $data['activa'] && ! $assignment->exists) {
                throw ValidationException::withMessages(['form.cod_cur' => 'No existe una asignación que retirar.']);
            }
            $assignment->fill($data)->save();
            BitacoraService::registrar(accion: 'ASIGNAR_REGENTE', tabla: 'regente_asignaciones', registro: (string) $assignment->id,
                modulo: 'Regencia', descripcion: 'Asignación explícita de Regente por gestión y grado.', valoresAnteriores: $before, valoresNuevos: $data);

            return $assignment;
        });
    }
}
