<?php

namespace Tests\Unit;

use App\Models\Oficial\Sistema\User;
use App\Services\GradeService;
use App\Services\RegencyAccessService;
use App\Services\RegencyReportService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RegencyReportScopeTest extends TestCase
{
    private function user(string $actor, bool $grades = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['est_usu' => 'ACTIVO', 'cod_per' => 'SELF']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === $actor);
        $user->shouldReceive('can')->andReturnUsing(fn ($permission) => $permission !== 'calificaciones.ver.institucional' || $grades);

        return $user;
    }

    public function test_report_correlates_assignment_and_all_enrollment_dimensions(): void
    {
        $access = Mockery::mock(RegencyAccessService::class)->makePartial();
        $access->shouldReceive('available')->andReturnTrue();
        $this->app->instance(RegencyAccessService::class, $access);
        $grades = Mockery::mock(GradeService::class);
        $grades->shouldReceive('available')->andReturnTrue();
        $this->app->instance(GradeService::class, $grades);
        $query = (new RegencyReportService)->query($this->user('Regente'));
        $sql = $query->toSql();
        foreach (['cod_gea', 'cod_cur'] as $field) {
            $this->assertStringContainsString('"ra"."'.$field.'" = "alcance_grupo"."'.$field.'"', $sql);
        }
        $this->assertStringContainsString('"alcance_grupo"."cod_gac" = "plan_asignatura"."cod_gac"', $sql);
        $this->assertStringContainsString('"inscripcion_vigencia"."cod_gac" = "plan_asignatura"."cod_gac"', $sql);
        $this->assertStringContainsString('"cod_esp_tec" is null', $sql);
        $this->assertStringContainsString('"calificacion"."cod_pas" = "plan_asignatura"."cod_pas"', $sql);
        $this->assertContains('SELF', $query->getBindings());
    }

    public function test_grade_permission_is_checked_before_schema_and_grade_query(): void
    {
        $access = Mockery::mock(RegencyAccessService::class)->makePartial();
        $access->shouldReceive('available')->andReturnFalse();
        $this->app->instance(RegencyAccessService::class, $access);
        $grades = Mockery::mock(GradeService::class);
        $grades->shouldNotReceive('available');
        $this->app->instance(GradeService::class, $grades);
        $sql = (new RegencyReportService)->query($this->user('Regente', false))->toSql();
        $this->assertStringContainsString('1 = 0', $sql);
        $this->assertStringNotContainsString('calificacion', $sql);
    }

    public function test_other_actor_cannot_use_regency_report_even_with_broad_permissions(): void
    {
        $this->expectException(HttpException::class);
        (new RegencyReportService)->query($this->user('Director'));
    }
}
