<?php

namespace App\Services;

use App\Models\Oficial\Sistema\{Permission, User};
use App\Support\{PermissionLabel, SupportRolesInstitucionales};
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PermisosPersonalesService
{
    public function autorizar(User $actor): void
    {
        $propios = $actor->getAllPermissions()->pluck('name')->all();
        abort_unless(app(RoleDashboardResolver::class)->roleFor($actor) === 'Administrador'
            && in_array('roles-permisos.gestionar', $propios, true)
            && in_array('roles.permisos.asignar', $propios, true), 403);
    }

    public function huella(User $usuario): string
    {
        return hash('sha256', json_encode([
            $usuario->est_usu,
            $usuario->roles->pluck('id')->sort()->values()->all(),
            $usuario->getAllPermissions()->pluck('name')->sort()->values()->all(),
            $usuario->permissions->pluck('name')->sort()->values()->all(),
        ]));
    }

    public function delegable(string $nombre, User $actor): bool
    {
        return str_contains($nombre, '.') && !str_ends_with($nombre, '.global')
            && !PermissionLabel::describe($nombre)['critical']
            && $actor->getAllPermissions()->contains('name', $nombre);
    }

    public function guardar(User $actor, string $destinatario, array $nombres, string $huella, string $tipoMotivo, string $detalle): void
    {
        $this->autorizar($actor);
        validator(compact('nombres', 'huella'), [
            'nombres' => 'array|max:300', 'nombres.*' => 'required|string|distinct|max:125',
            'huella' => 'required|string|size:64',
        ])->validate();
        validator(compact('detalle'), ['detalle' => 'string|max:2000'])->validate();
        $motivo = SupportRolesInstitucionales::motivo('personal', $tipoMotivo, $detalle);
        abort_unless(Schema::hasTable('bitacora') && app(NotificationService::class)->available(), 409,
            'Necesitamos bitácora y notificaciones para respaldar el cambio.');

        DB::transaction(function () use ($actor, $destinatario, $nombres, $huella, $motivo) {
            app(RolePermissionService::class)->lockRoles();
            $usuarios = User::whereIn('cod_usu', [$actor->getKey(), $destinatario])->orderBy('cod_usu')->lockForUpdate()->get();
            $operador = $usuarios->firstWhere('cod_usu', $actor->getKey());
            $usuario = $usuarios->firstWhere('cod_usu', $destinatario);
            abort_unless($operador && $usuario, 404);
            $this->autorizar($operador);
            abort_if($usuario->is($operador), 403, 'Tu propio acceso se consulta; los cambios requieren otra autoridad.');
            abort_unless(app(RoleDashboardResolver::class)->roleFor($usuario), 422, 'La cuenta debe estar vigente y tener un único actor institucional.');
            if (!hash_equals($this->huella($usuario), $huella)) {
                throw ValidationException::withMessages(['permisosUsuario' => 'Los accesos cambiaron mientras editabas. Vuelve a cargar la cuenta antes de guardar.']);
            }
            $validos = Permission::where('guard_name', 'web')->whereIn('name', $nombres)->pluck('name')->all();
            if (count($validos) !== count($nombres)) {
                throw ValidationException::withMessages(['permisosUsuario' => 'Selecciona tareas existentes del catálogo institucional.']);
            }
            $antes = $usuario->permissions->pluck('name')->all();
            $agregados = array_values(array_diff($validos, $antes));
            $retirados = array_values(array_diff($antes, $validos));
            $heredados = $usuario->getPermissionsViaRoles()->pluck('name')->all();
            foreach (array_merge($agregados, $retirados) as $nombre) {
                if (!$this->delegable($nombre, $operador) || in_array($nombre, $agregados, true) && in_array($nombre, $heredados, true)) {
                    throw ValidationException::withMessages(['permisosUsuario' => 'No puedes modificar accesos protegidos, superiores al propio o duplicar tareas heredadas del rol.']);
                }
            }
            if (!$agregados && !$retirados) return;
            $usuario->syncPermissions($validos);
            BitacoraService::registrar(accion: 'ACTUALIZAR_PERMISOS_PERSONALES', tabla: 'users', registro: $destinatario,
                modulo: 'Roles y Permisos', descripcion: $motivo,
                valoresAnteriores: ['permisos_directos' => $antes], valoresNuevos: ['permisos_directos' => $validos, 'actor' => $operador->getKey()]);
            $hecho = 'permisos-personales:'.Str::uuid();
            $resumen = implode(' · ', array_filter([
                $agregados ? 'Concedidos: '.\App\Support\ResumenAvisoAcceso::tareas($agregados).'. Sin fecha de fin.' : null,
                $retirados ? 'Retirados: '.\App\Support\ResumenAvisoAcceso::tareas($retirados).'.' : null,
            ]));
            $aviso = app(NotificationService::class)->publicar([
                'clave_evento' => $hecho, 'origen' => 'ADMINISTRATIVO', 'tipo' => 'INFORMACION',
                'titulo' => 'Tus permisos fueron actualizados', 'mensaje' => $resumen,
                'cod_usu_emisor' => $operador->getKey(),
            ], [$usuario]);
            abort_unless($aviso, 409, 'No pudimos registrar el aviso personal. El cambio no se guardó.');
            $usuario->loadMissing('persona');
            $nombre = trim(implode(' ', array_filter([$usuario->persona?->nom_per, $usuario->persona?->ape_pat_per, $usuario->persona?->ape_mat_per]))) ?: 'Cuenta institucional';
            $confirmacion = app(NotificationService::class)->publicar([
                'clave_evento' => $hecho.':operador', 'origen' => 'ADMINISTRATIVO', 'tipo' => 'INFORMACION',
                'titulo' => 'Permisos personales actualizados', 'mensaje' => mb_strimwidth($nombre, 0, 120, '…').': '.$resumen,
                'cod_usu_emisor' => $operador->getKey(),
            ], [$operador]);
            abort_unless($confirmacion, 409, 'No pudimos registrar tu confirmación. El cambio no se guardó.');
        });
    }
}
