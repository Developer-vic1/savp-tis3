<?php

namespace App\Services;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;
use App\Support\InstitutionalRoleGovernance;
use App\Support\PermissionLabel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionService
{
    public function lockRoles(): Collection
    {
        return Role::where('guard_name', 'web')->orderBy('id')->lockForUpdate()->get();
    }

    /** Un único escritor operacional; reemplaza solo el actor y conserva roles complementarios. */
    public function assignActor(User $target, string $name, User $actor): void
    {
        $institutional = app(InstitutionalRoleGovernance::class)->rolesInstitucionales();
        if (! in_array($name, $institutional, true)) {
            throw ValidationException::withMessages(['role' => 'Selecciona uno de los seis actores institucionales.']);
        }

        DB::transaction(function () use ($target, $name, $actor, $institutional) {
            $roles = $this->lockRoles();
            $role = $roles->firstWhere('name', $name);
            abort_unless($role, 409, 'El actor institucional debe existir en el catálogo aprobado.');
            $users = User::whereIn('cod_usu', [$actor->cod_usu, $target->cod_usu])->orderBy('cod_usu')->lockForUpdate()->get();
            $operator = $users->firstWhere('cod_usu', $actor->cod_usu);
            $user = $users->firstWhere('cod_usu', $target->cod_usu);
            abort_unless($operator && $user && $operator->est_usu === 'ACTIVO', 403);
            $operator->unsetRelation('roles');
            $operatorActor = app(RoleDashboardResolver::class)->roleFor($operator);
            $before = $user->roles()->where('guard_name', 'web')->pluck('name')->all();
            $current = array_values(array_intersect($before, $institutional));
            $admin = $operatorActor === 'Administrador' && $operator->can('usuarios.asignar_roles');
            $operational = $operatorActor === 'Secretaria' && $operator->can('usuarios.crear')
                && $operator->can('usuarios.ver.institucional') && in_array($name, OperationalAccountService::ROLES, true)
                && $before === [] && ! $user->permissions()->exists();
            abort_unless($admin || $operational, 403);
            if ($operational) {
                $eligible = $name === 'Estudiante'
                    ? Estudiante::where('cod_per', $user->cod_per)->where('est_est', 'ACTIVO')->exists()
                    : Docente::where('est_doc', 'ACTIVO')->whereHas('personalInstitucional', fn ($q) => $q->where('cod_per', $user->cod_per)->where('est_pin', 'ACTIVO'))->exists();
                abort_unless($user->cod_per && $eligible, 403);
            }
            abort_if($user->is($operator) && $current !== [$name], 403, 'No puedes cambiar tu propio actor institucional.');
            if (in_array('Administrador', $current, true) && ($name !== 'Administrador' || $user->est_usu !== 'ACTIVO')) {
                $others = User::where('cod_usu', '!=', $user->cod_usu)->where('est_usu', 'ACTIVO')
                    ->whereHas('roles', fn ($q) => $q->where('guard_name', 'web')->where('name', 'Administrador'))
                    ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', array_diff($institutional, ['Administrador'])))->exists();
                if (! $others) {
                    throw ValidationException::withMessages(['role' => 'No puedes retirar el último Administrador válido.']);
                }
            }
            $legacy = ['Admin', 'Super Admin', 'Secretaria Académica', 'Secretaria Academica'];
            $complementary = array_values(array_diff($before, $institutional, $legacy));
            $user->syncRoles([...$complementary, $name]);
            $target->unsetRelation('roles');
            BitacoraService::registrar(accion: 'ASIGNAR_ACTOR_INSTITUCIONAL', tabla: 'users', registro: $user->cod_usu,
                modulo: 'Seguridad', valoresAnteriores: ['roles' => $before], valoresNuevos: ['roles' => [...$complementary, $name]]);
        });
    }

    private const ADMIN_CRITICAL = [
        'Panel_Administrador',
        'roles-permisos.gestionar',
        'usuarios.asignar_roles',
        'roles.permisos.asignar',
        'roles.crear',
    ];

    public function huella(Role $role): string
    {
        return hash('sha256',json_encode([$role->getKey(),$role->guard_name,$role->permissions()->pluck('name')->sort()->values()->all()]));
    }

    public function sync(Role $role, array $permissionNames, User $actor, string $motivo = '', string $tipoMotivo = 'OTRO', ?string $huella = null): void
    {
        $motivo=\App\Support\SupportRolesInstitucionales::motivo('cambio',$tipoMotivo,$motivo);
        validator(compact('motivo'), ['motivo'=>'required|string|min:20|max:2000'])->validate();
        abort_unless(\Illuminate\Support\Facades\Schema::hasTable('bitacora'),409,'No podemos guardar cambios sin bitácora.');
        DB::transaction(function () use ($role, $permissionNames, $actor, $motivo, $huella) {
            $roles = $this->lockRoles();
            $lockedRole = $roles->firstWhere('id', $role->getKey());
            $lockedActor = User::lockForUpdate()->findOrFail($actor->cod_usu);
            abort_unless($lockedRole && app(RoleDashboardResolver::class)->roleFor($lockedActor) === 'Administrador', 403);
            if ($huella !== null && !hash_equals($this->huella($lockedRole),$huella)) {
                throw ValidationException::withMessages(['permissions'=>'Los permisos del rol cambiaron mientras editabas. Sal y vuelve a cargar el rol antes de guardar.']);
            }
            $antes = $lockedRole->permissions()->pluck('name')->all();
            $this->synchronize($lockedRole, $permissionNames, $lockedActor);
            BitacoraService::registrar(accion:'JUSTIFICAR_PERMISOS_ROL',tabla:'roles',registro:(string)$role->getKey(),modulo:'Roles y Permisos',descripcion:$motivo);
            $agregados = array_values(array_diff($permissionNames, $antes));
            $retirados = array_values(array_diff($antes, $permissionNames));
            if (($agregados || $retirados) && app(NotificationService::class)->available()) {
                $etiquetas = fn(array $nombres) => mb_strimwidth(collect($nombres)->map(fn($p)=>PermissionLabel::describe($p)['label'])->implode(', '),0,550,'…');
                $mensaje = "El rol {$lockedRole->name} cambió: ".count($agregados).' permisos agregados y '.count($retirados).' retirados.';
                if ($agregados) $mensaje .= ' Nuevas tareas: '.$etiquetas($agregados).'.';
                if ($retirados) $mensaje .= ' Tareas retiradas: '.$etiquetas($retirados).'.';
                $mensaje .= ' Motivo: '.mb_strimwidth($motivo,0,500,'…');
                $destinatarios = User::where('est_usu','ACTIVO')->where(fn($q)=>$q->whereHas('roles',fn($r)=>$r->whereKey($lockedRole->getKey()))->orWhereKey($lockedActor->getKey()))->get();
                $aviso = app(NotificationService::class)->publicar([
                    'clave_evento'=>'rol-permisos:'.\Illuminate\Support\Str::uuid(), 'origen'=>'SISTEMA','tipo'=>'INFORMACION',
                    'titulo'=>'Se actualizaron los permisos de un rol','mensaje'=>$mensaje,'cod_usu_emisor'=>$lockedActor->getKey(),
                ],$destinatarios);
                abort_unless($aviso,409,'No se pudo guardar el aviso; los permisos no se modificaron.');
            }
        });
    }

    public function createApprovedRole(string $name, array $permissions, User $actor): Role
    {
        return DB::transaction(function () use ($name, $permissions, $actor) {
            $this->lockRoles();
            $operator = User::lockForUpdate()->findOrFail($actor->cod_usu);
            abort_unless(app(RoleDashboardResolver::class)->roleFor($operator) === 'Administrador'
                && $operator->can('roles.crear') && $operator->can('roles.permisos.asignar'), 403);
            $role = Role::create(['name' => trim($name), 'guard_name' => 'web']);
            $this->synchronize($role, $permissions, $operator);

            return $role;
        });
    }

    private function synchronize(Role $role, array $permissionNames, User $actor): void
    {
        if (! $actor->hasRole('Administrador') || ! $actor->can('roles-permisos.gestionar') || ! $actor->can('roles.permisos.asignar') || $actor->est_usu !== 'ACTIVO' || $role->guard_name !== 'web') {
            throw new AuthorizationException('No tienes autorización para administrar roles y permisos.');
        }

        if (array_filter($permissionNames, fn ($name) => ! is_string($name)) !== []) {
            throw ValidationException::withMessages(['permissions' => 'La selección contiene permisos inválidos.']);
        }

        $valid = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_values(array_unique($permissionNames)))
            ->pluck('name')
            ->all();

        if (count($valid) !== count(array_unique($permissionNames))) {
            throw ValidationException::withMessages([
                'permissions' => 'La selección contiene permisos inexistentes o no autorizados.',
            ]);
        }

        $before = $role->permissions()->pluck('name')->sort()->values()->all();
        $added = array_diff($valid, $before);
        foreach ($added as $permission) {
            if (! $actor->can($permission) || (PermissionLabel::describe($permission)['critical'] && $role->name !== 'Administrador')
                || (str_ends_with($permission, '.global') && $role->name !== 'Administrador')) {
                throw ValidationException::withMessages(['permissions' => 'No se puede conceder un permiso superior al propio ni ampliar permisos críticos a otro rol.']);
            }
        }
        if ($actor->hasRole($role->name) && array_diff($before, $valid) !== []) {
            throw ValidationException::withMessages(['permissions' => 'No puede retirar permisos de un rol asignado a su propia cuenta.']);
        }

        if ($role->name === 'Administrador') {
            $existingCritical = Permission::query()->whereIn('name', self::ADMIN_CRITICAL)->pluck('name')->all();
            $missing = array_diff($existingCritical, $valid);

            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'permissions' => 'El rol Administrador debe conservar sus permisos críticos.',
                ]);
            }
        }

        DB::transaction(function () use ($role, $valid, $actor, $before): void {
            $role->syncPermissions($valid);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            BitacoraService::registrar(
                accion: 'ACTUALIZAR_PERMISOS_ROL',
                tabla: 'roles',
                registro: (string) $role->getKey(),
                modulo: 'Roles y Permisos',
                nombreRegistro: $role->name,
                descripcion: 'Se actualizaron los permisos del rol por un Administrador autorizado.',
                valoresAnteriores: ['permisos' => $before, 'actor' => $actor->cod_usu],
                valoresNuevos: ['permisos' => collect($valid)->sort()->values()->all()]
            );
        });
    }
}
