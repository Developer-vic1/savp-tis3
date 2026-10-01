<?php

namespace App\Services;

use App\Models\User;
use App\Support\WorkspaceNavigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function available(): bool
    {
        return config('features.notifications', false) && Schema::hasTable('notifications');
    }

    public function snapshot(User $user): array
    {
        $this->authorize($user);
        if (! $this->available()) {
            return ['available' => false, 'unread' => null, 'rows' => null];
        }

        return ['available' => true, 'unread' => $user->unreadNotifications()->count(), 'rows' => $user->notifications()->latest()->paginate(5, ['*'], 'notificationsPage')];
    }

    public function mark(User $user, string $id, bool $read): void
    {
        $this->authorize($user);
        abort_unless($this->available(), 409, 'Las notificaciones institucionales no están habilitadas.');
        DB::transaction(function () use ($user, $id, $read) {
            $notification = $user->notifications()->whereKey($id)->lockForUpdate()->firstOrFail();
            $notification->update(['read_at' => $read ? now() : null]);
            BitacoraService::registrar(accion: $read ? 'LEER_NOTIFICACION' : 'MARCAR_NOTIFICACION_NO_LEIDA', tabla: 'notifications', registro: $id, modulo: 'Notificaciones');
        });
    }

    public function link(User $user, array $data): ?string
    {
        foreach (app(WorkspaceNavigation::class)->for($user) as $item) {
            if (($data['route'] ?? null) === $item['route'] && ($data['params'] ?? []) === $item['params']) {
                return route($item['route'], $item['params']);
            }
        }

        return null;
    }

    private function authorize(User $user): void
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user), 403);
    }
}
