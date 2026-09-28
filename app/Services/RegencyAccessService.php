<?php

namespace App\Services;

use App\Models\Regente;
use App\Models\RegenteAsignacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RegencyAccessService
{
    public function available(): bool
    {
        return Schema::hasTable('regente_asignaciones');
    }

    /** Correlación por gestión Y grado; no basta la identidad del estudiante. */
    public function constrain(Builder $query, User $user, string $table): Builder
    {
        if (! $user->hasRole('Regente') || $user->est_usu !== 'ACTIVO' || ! $user->cod_per || ! $this->available()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereExists(function ($sub) use ($user, $table) {
            $sub->selectRaw('1')->from('regente_asignaciones as ra')
                ->join('regente as r', 'r.cod_reg', '=', 'ra.cod_reg')
                ->join('personal_institucional as pi', 'pi.cod_pin', '=', 'r.cod_pin')
                ->where('pi.cod_per', $user->cod_per)->where('pi.est_pin', 'ACTIVO')->where('r.est_reg', 'ACTIVO')
                ->where('ra.activa', true)
                ->whereColumn('ra.cod_gea', $table.'.cod_gea')->whereColumn('ra.cod_cur', $table.'.cod_cur');
        });
    }

    public function assign(User $actor, array $data): RegenteAsignacion
    {
        abort_unless($actor->est_usu === 'ACTIVO' && $actor->hasRole('Administrador') && $actor->can('regencia.asignaciones.gestionar'), 403);
        abort_unless($this->available(), 409, 'La estructura de asignaciones está pendiente de aplicación autorizada.');
        $data = validator($data, [
            'cod_reg' => ['required', 'exists:regente,cod_reg'],
            'cod_gea' => ['required', 'exists:gestion_academica,cod_gea'],
            'cod_cur' => ['required', 'exists:curso,cod_cur'],
            'activa' => ['required', 'boolean'],
        ])->validate();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $regent = Regente::query()->lockForUpdate()->findOrFail($data['cod_reg']);
            if ($data['activa']) {
                if ($regent->est_reg !== 'ACTIVO' || $regent->personalInstitucional?->est_pin !== 'ACTIVO') {
                    throw \Illuminate\Validation\ValidationException::withMessages(['form.cod_reg' => 'El Regente y su perfil institucional deben estar activos.']);
                }
                if (! \App\Models\Curso::whereKey($data['cod_cur'])->where('est_cur', 'ACTIVO')->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['form.cod_cur' => 'El grado debe estar activo.']);
                }
            }
            $assignment = RegenteAsignacion::firstOrNew(collect($data)->except('activa')->all());
            $count = RegenteAsignacion::where('cod_reg', $data['cod_reg'])->where('cod_gea', $data['cod_gea'])->where('activa', true)
                ->when($assignment->exists, fn ($q) => $q->whereKeyNot($assignment->getKey()))->count();
            if ($data['activa'] && $count >= 2) {
                throw \Illuminate\Validation\ValidationException::withMessages(['form.cod_cur' => 'El Regente ya tiene dos grados activos en esta gestión.']);
            }
            $before = $assignment->exists ? $assignment->only(['cod_reg', 'cod_gea', 'cod_cur', 'activa']) : [];
            if (! $data['activa'] && ! $assignment->exists) {
                throw \Illuminate\Validation\ValidationException::withMessages(['form.cod_cur' => 'No existe una asignación que retirar.']);
            }
            $assignment->fill($data)->save();
            BitacoraService::registrar(accion: 'ASIGNAR_REGENTE', tabla: 'regente_asignaciones', registro: (string) $assignment->id,
                modulo: 'Regencia', descripcion: 'Asignación explícita de Regente por gestión y grado.', valoresAnteriores: $before, valoresNuevos: $data);

            return $assignment;
        });
    }
}
