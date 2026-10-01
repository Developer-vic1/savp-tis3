<?php

namespace Tests\Feature;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\ReporteAulaVirtualService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/** PDF real en disco temporal de pruebas, con conteos simulados y sin consultas de BD. */
class TeacherReportPdfTest extends TestCase
{
    private function teacher(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Docente');
        $user->shouldReceive('can')->andReturnTrue();
        $user->shouldReceive('checkPermissionTo')->andReturnTrue();

        return $user;
    }

    public function test_scoped_pdf_is_generated_with_private_storage_and_download_headers(): void
    {
        $user = $this->teacher();
        $class = (new ClaseVirtual(['cod_cla' => 'OWN', 'nom_cla' => 'Curso de prueba aislada']))->setRelation('planAsignatura', null);
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'OWN')->once()->andReturn($class);
        $reports = Mockery::mock(ReporteAulaVirtualService::class);
        $reports->shouldReceive('consolidadoCurso')->with($class)->once()->andReturn(['curso' => $class,
            'estudiantes' => 2, 'materiales' => 3, 'tareas' => 4, 'asistencias' => 1]);
        $this->app->instance(CursoVirtualService::class, $courses);
        $this->app->instance(ReporteAulaVirtualService::class, $reports);
        Storage::fake('local');
        Schema::shouldReceive('hasTable')->with('bitacora')->once()->andReturnFalse();
        $response = $this->actingAs($user)->get('/aula-virtual/mis-cursos/OWN/reporte.pdf');
        $response->assertOk()->assertDownload('Consolidado-curso.pdf')->assertHeader('Content-Type', 'application/pdf');
        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertStringStartsWith('%PDF-', file_get_contents($path));
        $this->assertStringContainsString('reportes/aula', str_replace('\\', '/', $path));
    }

    public function test_foreign_course_cannot_generate_a_pdf(): void
    {
        $user = $this->teacher();
        $courses = Mockery::mock(CursoVirtualService::class);
        $courses->shouldReceive('cursoParaDocente')->with($user, 'FOREIGN')->once()->andReturnNull();
        $reports = Mockery::mock(ReporteAulaVirtualService::class);
        $reports->shouldNotReceive('consolidadoCurso');
        $this->app->instance(CursoVirtualService::class, $courses);
        $this->app->instance(ReporteAulaVirtualService::class, $reports);
        $this->actingAs($user)->get('/aula-virtual/mis-cursos/FOREIGN/reporte.pdf')->assertForbidden();
    }
}
