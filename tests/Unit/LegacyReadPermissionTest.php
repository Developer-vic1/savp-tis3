<?php

namespace Tests\Unit;

use App\Models\Oficial\Sistema\User;
use App\Support\LegacyReadPermission;
use Mockery;
use Tests\TestCase;

class LegacyReadPermissionTest extends TestCase
{
    private function user(string $actor, array $permissions, bool $active = true, array $extraRoles = []): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = $active ? 'ACTIVO' : 'INACTIVO';
        $roles = [$actor, ...$extraRoles];
        $user->shouldReceive('hasRole')->andReturnUsing(fn (string $candidate) => in_array($candidate, $roles, true));
        $user->shouldReceive('getAllPermissions')->andReturn(collect(array_map(fn (string $name) => (object) ['name' => $name], $permissions)));

        return $user;
    }

    public function test_legacy_student_grade_permission_only_unlocks_own_read_scope(): void
    {
        $user = $this->user('Estudiante', ['Calificaciones']);
        $bridge = app(LegacyReadPermission::class);

        $this->assertTrue($bridge->allows($user, 'calificaciones.ver.propias'));
        $this->assertFalse($bridge->allows($user, 'calificaciones.ver.institucional'));
        $this->assertFalse($bridge->allows($user, 'usuarios.asignar_roles'));
    }

    public function test_legacy_director_permission_unlocks_only_mapped_read_scope(): void
    {
        $user = $this->user('Director', ['Estudiantes']);
        $bridge = app(LegacyReadPermission::class);

        $this->assertTrue($bridge->allows($user, 'estudiantes.ver.institucional'));
        $this->assertFalse($bridge->allows($user, 'cursos.ver.institucional'));
        $this->assertFalse($bridge->allows($user, 'asistencia.ver.institucional'));
    }

    public function test_inactive_or_multi_actor_account_cannot_use_a_legacy_bridge(): void
    {
        $bridge = app(LegacyReadPermission::class);

        $this->assertFalse($bridge->allows($this->user('Estudiante', ['Calificaciones'], false), 'calificaciones.ver.propias'));
        $this->assertFalse($bridge->allows($this->user('Estudiante', ['Calificaciones'], true, ['Docente']), 'calificaciones.ver.propias'));
    }

    public function test_original_window_catalog_preserves_all_ids_and_actor_totals(): void
    {
        $windows = collect(config('architecture_windows'));

        $this->assertCount(105, $windows);
        $this->assertSame(array_map(fn (int $n) => sprintf('V%03d', $n), range(1, 105)), $windows->pluck('id')->all());
        $this->assertSame([
            'Administrador' => 26, 'Director' => 17, 'Docente' => 17,
            'Estudiante' => 19, 'Regente' => 12, 'Secretaria' => 14,
        ], $windows->countBy('actor')->sortKeys()->all());
        $this->assertSame([
            'BLOCKED_EXTERNALLY_DB' => 10,
            'BLOCKED_EXTERNALLY_INSTITUTIONAL' => 4,
            'PARTIAL' => 91,
        ], $windows->countBy('status')->sortKeys()->all());
    }
}
