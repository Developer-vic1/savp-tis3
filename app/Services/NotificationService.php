<?php

namespace App\Services;

use App\Models\Oficial\Sistema\User;
use App\Models\Oficial\Sistema\Notificacion;
use App\Models\Oficial\Sistema\NotificacionUsuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Support\WorkspaceNavigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function available(): bool
    {
        return config('features.notifications', false) && Schema::hasTable('notificacion') && Schema::hasTable('notificacion_usuario');
    }

    private function bandeja(User $user): Builder
    {
        $permisos = collect(app(WorkspaceNavigation::class)->for($user))->pluck('permission')->filter()->unique()->all();
        $ahora = DB::getDriverName() === 'pgsql' ? now()->toIso8601String() : now();
        return $user->avisos()->getQuery()->whereHas('notificacion', fn (Builder $q) => $q
            ->where('est_not', 'ACTIVA')->where('publicada_en', '<=', $ahora)
            ->where(fn ($n) => $n->whereNull('vence_en')->orWhere('vence_en', '>', $ahora))
            ->where(fn ($n) => $n->whereNull('permiso')->orWhereIn('permiso', $permisos)));
    }

    public function snapshot(User $user, string $filtro = 'TODAS', string $origen = 'TODOS'): array
    {
        $this->authorize($user);
        validator(compact('filtro', 'origen'), [
            'filtro' => [Rule::in(['TODAS', 'NO_LEIDAS', 'LEIDAS', 'ARCHIVADAS'])],
            'origen' => [Rule::in(['TODOS', 'ADMINISTRATIVO', 'AULA_VIRTUAL', 'APORTE', 'SISTEMA'])],
        ])->validate();
        if (! $this->available()) {
            return ['available' => false, 'unread' => 0, 'rows' => null];
        }

        $base = $this->bandeja($user);
        $unread = (clone $base)->whereNull('archivada_en')->whereNull('leida_en')->count();
        $base->when($filtro === 'ARCHIVADAS', fn ($q) => $q->whereNotNull('archivada_en'), fn ($q) => $q->whereNull('archivada_en'))
            ->when($filtro === 'LEIDAS', fn ($q) => $q->whereNotNull('leida_en'))
            ->when($filtro === 'NO_LEIDAS', fn ($q) => $q->whereNull('leida_en'))
            ->when($origen !== 'TODOS', fn ($q) => $q->whereHas('notificacion', fn ($n) => $n->where('origen', $origen)));
        $rows = $base->with('notificacion')->orderByDesc('created_at')->orderByDesc('cod_nus')->paginate(5, ['*'], 'notificationsPage');
        return ['available' => true, 'unread' => $unread, 'rows' => $rows, 'vigencias' => $this->vigencias($user, $rows->getCollection())];
    }

    private function vigencias(User $user, iterable $rows): array
    {
        if (!app(AccesoProgramadoService::class)->disponible()) return [];
        $claves = collect($rows)->mapWithKeys(function ($item) {
            if (!preg_match('/^acceso:(CAC_[0-9]{6,16}):(programado|vigente)(:operador)?$/D', $item->notificacion->clave_evento, $m)) return [];
            return [$item->getKey() => $m[1]];
        });
        if ($claves->isEmpty()) return [];
        $concesiones = \App\Models\Oficial\Sistema\ConcesionAcceso::whereIn('cod_cac', $claves->values())
            ->where(fn ($q) => $q->where('cod_usu_autorizador', $user->getKey())
                ->orWhereHas('usuarios', fn ($u) => $u->where('users.cod_usu', $user->getKey())))
            ->get()->keyBy('cod_cac');
        $reloj = now()->toIso8601String();
        return $claves->filter(fn ($id) => $concesiones->has($id))->map(fn ($id) => [
            'inicio' => $concesiones[$id]->inicio->toIso8601String(), 'fin' => $concesiones[$id]->fin->toIso8601String(),
            'estado' => $concesiones[$id]->estado, 'reloj' => $reloj,
        ])->all();
    }

    public function mark(User $user, string $id, bool $read): void
    {
        $this->cambiar($user, $id, 'leida_en', $read, $read ? 'LEER_NOTIFICACION' : 'AVISO_NO_LEIDO');
    }

    public function archive(User $user, string $id, bool $archivar): void
    {
        $this->cambiar($user, $id, 'archivada_en', $archivar, $archivar ? 'ARCHIVAR_AVISO' : 'RESTAURAR_AVISO');
    }

    private function cambiar(User $user, string $id, string $campo, bool $activar, string $accion): void
    {
        $this->authorize($user);
        validator(['id' => $id], ['id' => ['required', 'regex:/^NUS_[0-9]{6,16}$/']])->validate();
        abort_unless($this->available(), 409, 'Las notificaciones institucionales no están habilitadas.');
        DB::transaction(function () use ($user, $id, $campo, $activar, $accion) {
            $aviso = $this->bandeja($user)->whereKey($id)->lockForUpdate()->firstOrFail();
            if (($aviso->{$campo} !== null) === $activar) return;
            $aviso->update([$campo => $activar ? now() : null]);
            BitacoraService::registrar(accion: $accion, tabla: 'notificacion_usuario', registro: $id, modulo: 'Notificaciones', descripcion: 'El usuario actualizó un aviso de su propia bandeja.');
        });
    }

    /** Productores internos solamente. Ninguna petición pública acepta destinatarios. */
    public function publicar(array $datos, iterable $destinatarios): ?Notificacion
    {
        if (! $this->available()) return null;
        $datos['publicada_en'] ??= now()->toIso8601String();
        $datos = validator($datos, [
            'clave_evento' => 'required|string|max:160',
            'origen' => ['required', Rule::in(['ADMINISTRATIVO', 'AULA_VIRTUAL', 'APORTE', 'SISTEMA'])],
            'tipo' => ['required', Rule::in(['INFORMACION', 'ADVERTENCIA', 'ACCION', 'RECORDATORIO'])],
            'titulo' => 'required|string|max:150', 'mensaje' => 'required|string|max:2000',
            'permiso' => 'nullable|string|max:125|required_with:ruta', 'ruta' => 'nullable|string|max:150',
            'cod_usu_emisor' => 'nullable|string|exists:users,cod_usu',
            'cod_gea' => 'nullable|string|exists:gestion_academica,cod_gea',
            'publicada_en' => 'required|date', 'vence_en' => 'nullable|date|after:publicada_en',
        ])->validate();
        $codigos = collect($destinatarios)->map(fn ($u) => $u instanceof User ? $u->getKey() : (string) $u)->unique()->all();
        $usuarios = User::whereIn('cod_usu', $codigos)->where('est_usu', 'ACTIVO')->with(['roles.permissions', 'permissions'])->get()
            ->filter(function (User $u) use ($datos) {
                if (! app(RoleDashboardResolver::class)->roleFor($u)) return false;
                $items = collect(app(WorkspaceNavigation::class)->for($u));
                if (! empty($datos['ruta'])) {
                    return $items->contains(fn ($i) => $i['route'] === $datos['ruta'] && $i['permission'] === ($datos['permiso'] ?? null) && $i['params'] === []);
                }
                return empty($datos['permiso']) || $items->contains('permission', $datos['permiso']);
            });
        if ($usuarios->isEmpty()) return null;
        return DB::transaction(function () use ($datos, $usuarios) {
            if (DB::getDriverName() === 'pgsql') {
                DB::select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['aviso:'.$datos['origen'].':'.$datos['clave_evento']]);
            }
            $aviso = Notificacion::firstOrCreate(['origen' => $datos['origen'], 'clave_evento' => $datos['clave_evento']], $datos);
            foreach (['titulo', 'mensaje', 'tipo', 'permiso', 'ruta', 'cod_gea'] as $campo) {
                abort_unless($aviso->{$campo} === ($datos[$campo] ?? null), 409, 'Este hecho ya tiene otro aviso registrado.');
            }
            foreach ($usuarios as $usuario) {
                NotificacionUsuario::firstOrCreate(['cod_not' => $aviso->getKey(), 'cod_usu' => $usuario->getKey()]);
            }
            return $aviso;
        });
    }

    public function link(User $user, Notificacion $aviso): ?string
    {
        foreach (app(WorkspaceNavigation::class)->for($user) as $item) {
            if ($aviso->ruta === $item['route'] && $aviso->permiso === $item['permission'] && $item['params'] === []) {
                return route($item['route']);
            }
        }

        return null;
    }

    private function authorize(User $user): void
    {
        abort_unless(auth()->id() === $user->getKey() && app(RoleDashboardResolver::class)->roleFor($user), 403);
    }
}
