<?php

namespace Tests\Feature;

use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\AporteIngenieril\StudentOrientationService;
use Mockery;
use Tests\TestCase;
use Tests\Unit\DirectorioNacionalTest;

class AccesoDirectorioNacionalTest extends TestCase
{
    public function test_rutas_conservan_actor_y_permiso(): void
    {
        $this->getJson('/conocimiento/directorio-nacional')->assertUnauthorized();
        $this->getJson('/estudiante/directorio-nacional')->assertUnauthorized();
        $this->actingAs($this->actor('Docente'))->getJson('/conocimiento/directorio-nacional')->assertForbidden();
        $this->getJson('/estudiante/directorio-nacional')->assertForbidden();
        $this->actingAs($this->actor('Administrador', false))->getJson('/conocimiento/directorio-nacional')->assertForbidden();
        $this->actingAs($this->actor('Estudiante', false))->getJson('/estudiante/directorio-nacional')->assertForbidden();
    }

    public function test_administrador_ve_el_directorio_y_valida_filtros_sin_consultar_bd(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('nationalDirectory')->once()->andReturn(new AporteResponse(true, DirectorioNacionalTest::directorio()));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->getJson('/conocimiento/directorio-nacional?duracion[]=8')->assertUnprocessable();
        $respuesta = $this->get('/conocimiento/directorio-nacional?departamento=La%20Paz')->assertOk()
            ->assertViewHas('instituciones', fn ($items) => count($items) === 1)
            ->assertViewHas('todasInstituciones', fn ($items) => count($items) === 2);
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
    }

    public function test_estudiante_debe_tener_perfil_vinculado_y_no_necesita_snapshot(): void
    {
        $orientacion = Mockery::mock(StudentOrientationService::class);
        $orientacion->shouldReceive('student')->once()->andReturn(new Estudiante);
        $orientacion->shouldNotReceive('context');
        $this->app->instance(StudentOrientationService::class, $orientacion);
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('nationalDirectory')->once()->andReturn(new AporteResponse(true, DirectorioNacionalTest::directorio()));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Estudiante'))->get('/estudiante/directorio-nacional')->assertOk()->assertViewHas('estudiante', true);
    }

    public function test_estudiante_sin_vinculo_se_bloquea_antes_de_consultar_fuentes(): void
    {
        $orientacion = Mockery::mock(StudentOrientationService::class);
        $orientacion->shouldReceive('student')->once()->andReturnUsing(fn () => abort(403));
        $this->app->instance(StudentOrientationService::class, $orientacion);
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldNotReceive('nationalDirectory');
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Estudiante'))->getJson('/estudiante/directorio-nacional')->assertForbidden();
    }

    public function test_fallo_del_servicio_muestra_recuperacion_y_conserva_filtros(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('nationalDirectory')->once()->andReturn(AporteResponse::unavailable());
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'))->get('/conocimiento/directorio-nacional?buscar=medicina')
            ->assertOk()->assertSee('El directorio no está disponible temporalmente')->assertSee('medicina');
    }

    private function actor(string $role, bool $permiso = true): User
    {
        $usuario = Mockery::mock(User::class)->makePartial();
        $usuario->forceFill(['cod_usu' => 'TEST_DIRECTORIO_'.$role, 'email' => strtolower($role).'@example.test', 'email_verified_at' => now(), 'est_usu' => 'ACTIVO']);
        $usuario->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $usuario->shouldReceive('can')->andReturn($permiso);
        $usuario->shouldReceive('checkPermissionTo')->andReturn($permiso);

        return $usuario;
    }
}
