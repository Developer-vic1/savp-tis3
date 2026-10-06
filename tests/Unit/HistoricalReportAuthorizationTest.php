<?php

namespace Tests\Unit;

use App\Models\Oficial\Academico\ReporteGenerado;
use App\Models\Oficial\Sistema\User;
use App\Services\HistoricalReportAccessService;
use Mockery;
use Tests\TestCase;

class HistoricalReportAuthorizationTest extends TestCase
{
    private function actor(string $role, bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($candidate) => $candidate === $role);
        $user->shouldReceive('can')->andReturn($allowed);

        return $user;
    }

    private function report(array $changes = []): ReporteGenerado
    {
        return new ReporteGenerado(array_replace(['tipo_reporte' => 'Reporte Administrativo', 'formato' => 'pdf',
            'estado' => 'generado', 'ruta_archivo' => 'reportes/administrativos/report.pdf'], $changes));
    }

    public function test_secretary_can_read_administrative_pdf_but_not_academic_pdf(): void
    {
        $access = new HistoricalReportAccessService;
        $user = $this->actor('Secretaria');
        $this->assertTrue($access->canRead($user, $this->report()));
        $this->assertFalse($access->canRead($user, $this->report(['tipo_reporte' => 'Reporte de Calificaciones', 'ruta_archivo' => 'reportes/academicos/grades.pdf'])));
    }

    public function test_broad_permission_never_exposes_sql_zip_or_unknown_family(): void
    {
        $access = new HistoricalReportAccessService;
        foreach (['sql', 'zip'] as $format) {
            $this->assertFalse($access->canRead($this->actor('Director'), $this->report(['formato' => $format])));
        }
        $this->assertFalse($access->canRead($this->actor('Director'), $this->report(['tipo_reporte' => 'Reporte Institucional Completo'])));
    }

    public function test_path_traversal_and_cross_family_path_are_rejected(): void
    {
        $access = new HistoricalReportAccessService;
        foreach (['reportes/administrativos/../academicos/private.pdf', 'reportes/academicos/private.pdf', 'reportes/administrativos/file.sql'] as $path) {
            $this->assertFalse($access->canRead($this->actor('Director'), $this->report(['ruta_archivo' => $path])));
        }
    }

    public function test_revoked_permission_and_regency_cannot_read_global_history(): void
    {
        $access = new HistoricalReportAccessService;
        $this->assertFalse($access->canRead($this->actor('Director', false), $this->report()));
        $this->assertFalse($access->canRead($this->actor('Regente'), $this->report()));
        $this->assertStringContainsString('1 = 0', $access->query($this->actor('Regente'))->toSql());
    }
}
