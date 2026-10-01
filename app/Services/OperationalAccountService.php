<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Secretaría opera cuentas de estudiantes/docentes, nunca la matriz de privilegios. */
class OperationalAccountService
{
    public const ROLES = ['Estudiante', 'Docente'];

    public function authorize(User $actor, string $permission): void
    {
        abort_unless($actor->est_usu === 'ACTIVO' && $actor->hasRole('Secretaria')
            && $actor->can('usuarios.ver.institucional') && $actor->can($permission), 403);
    }

    public function scope(Builder $query): Builder
    {
        return $query->whereHas('roles', fn ($q) => $q->whereIn('name', self::ROLES)->where('guard_name', 'web'))
            ->whereDoesntHave('roles', fn ($q) => $q->whereNotIn('name', self::ROLES)->orWhere('guard_name', '!=', 'web'))
            ->whereDoesntHave('permissions');
    }

    public function save(User $actor, array $input, ?string $id): User
    {
        $this->authorize($actor, $id ? 'usuarios.editar' : 'usuarios.crear');
        $data = validator($input, [
            'cod_per' => [$id ? 'nullable' : 'required', 'string', 'exists:persona,cod_per'],
            'role' => [$id ? 'nullable' : 'required', Rule::in(self::ROLES)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id, 'cod_usu')],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8', 'max:128', 'confirmed'],
        ])->validate();
        if ($id && filled($data['password'] ?? null)) {
            $this->authorize($actor, 'usuarios.reset_password');
        }

        return DB::transaction(function () use ($actor, $data, $id) {
            app(RolePermissionService::class)->lockRoles();
            if ($id) {
                $user = $this->scope(User::query())->lockForUpdate()->findOrFail($id);
                abort_if($user->is($actor), 403);
            } else {
                $person = Persona::lockForUpdate()->findOrFail($data['cod_per']);
                if (! $person->est_per || User::where('cod_per', $person->cod_per)->exists()) {
                    throw ValidationException::withMessages(['form.cod_per' => 'La persona debe estar activa y no tener otra cuenta.']);
                }
                $eligible = $data['role'] === 'Estudiante'
                    ? Estudiante::where('cod_per', $person->cod_per)->where('est_est', 'ACTIVO')->exists()
                    : Docente::where('est_doc', 'ACTIVO')->whereHas('personalInstitucional', fn ($q) => $q->where('cod_per', $person->cod_per)->where('est_pin', 'ACTIVO'))->exists();
                if (! $eligible) {
                    throw ValidationException::withMessages(['form.role' => 'La persona no tiene un perfil activo para esa cuenta.']);
                }
                $role = Role::where('name', $data['role'])->where('guard_name', 'web')->firstOrFail();
                $user = new User(['cod_usu' => 'USU_'.Str::upper(Str::random(16)), 'cod_per' => $person->cod_per, 'est_usu' => 'ACTIVO']);
            }
            $before = $user->exists ? $user->only(['email', 'est_usu']) : [];
            $user->email = $data['email'];
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            if (filled($data['password'] ?? null)) {
                $user->password = $data['password'];
                $user->remember_token = Str::random(60);
            }
            $user->save();
            if (! $id) {
                app(RolePermissionService::class)->assignActor($user, $role->name, $actor);
            }
            BitacoraService::registrar(accion: $id ? 'EDITAR_CUENTA_OPERATIVA' : 'CREAR_CUENTA_OPERATIVA', tabla: 'users', registro: $user->cod_usu,
                modulo: 'Secretaría', valoresAnteriores: $before, valoresNuevos: $user->only(['email', 'est_usu']));

            return $user;
        });
    }

    public function changeState(User $actor, string $id, bool $active): void
    {
        $this->authorize($actor, $active ? 'usuarios.activar' : 'usuarios.desactivar');
        DB::transaction(function () use ($actor, $id, $active) {
            $user = $this->scope(User::query())->lockForUpdate()->findOrFail($id);
            abort_if($user->is($actor), 403);
            $before = $user->only('est_usu');
            $user->update(['est_usu' => $active ? 'ACTIVO' : 'INACTIVO']);
            BitacoraService::registrar(accion: 'ESTADO_CUENTA_OPERATIVA', tabla: 'users', registro: $id, modulo: 'Secretaría',
                valoresAnteriores: $before, valoresNuevos: $user->only('est_usu'));
        });
    }
}
