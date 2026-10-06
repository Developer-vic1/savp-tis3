<?php

namespace Tests\Unit;

use App\Models\Oficial\Sistema\User;
use App\Services\NotificationService;
use Mockery;
use Tests\TestCase;

class NotificationBoundaryTest extends TestCase
{
    private function student(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Estudiante');
        $user->shouldReceive('can')->andReturnTrue();

        return $user;
    }

    public function test_disabled_notifications_do_not_query_storage_or_invent_zero_count(): void
    {
        config(['features.notifications' => false]);
        $user = $this->student();
        $user->shouldNotReceive('notifications');
        $user->shouldNotReceive('unreadNotifications');
        $this->assertSame(['available' => false, 'unread' => null, 'rows' => null], (new NotificationService)->snapshot($user));
    }

    public function test_forged_external_or_foreign_record_links_are_rejected(): void
    {
        $service = new NotificationService;
        $user = $this->student();
        $this->assertNull($service->link($user, ['route' => 'https://foreign.example/private']));
        $this->assertNull($service->link($user, ['route' => 'admin.dashboard']));
        $this->assertNull($service->link($user, ['route' => 'estudiante.materias', 'params' => ['curso' => 'FOREIGN']]));
        $this->assertSame(route('estudiante.materias'), $service->link($user, ['route' => 'estudiante.materias']));
    }
}
