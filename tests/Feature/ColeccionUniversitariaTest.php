<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use Mockery;
use Tests\TestCase;

class ColeccionUniversitariaTest extends TestCase
{
    public function test_solo_administrador_con_permiso_puede_iniciar_y_consultar(): void
    {
        foreach (['Director', 'Estudiante', 'Docente'] as $rol) {
            $this->actingAs($this->actor($rol));
            $this->get('/conocimiento/universidades/unifranz')->assertForbidden();
            $this->getJson('/conocimiento/colecciones/unifranz')->assertForbidden();
            $this->postJson('/conocimiento/colecciones/unifranz', [])->assertForbidden();
            $this->postJson('/conocimiento/colecciones/unifranz/analizar', [])->assertForbidden();
            $this->get('/conocimiento/colecciones/unifranz/documento/'.str_repeat('a', 64).'/descargar')->assertForbidden();
        }
        $this->actingAs($this->actor('Administrador', false));
        $this->getJson('/conocimiento/colecciones/unifranz')->assertForbidden();
        $this->postJson('/conocimiento/colecciones/unifranz', [])->assertForbidden();
    }

    public function test_consulta_no_inicia_descarga_y_el_inicio_no_admite_otra_url(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('unifranzCollection')->once()->withNoArgs()->andReturn(new AporteResponse(true, ['status' => 'PARCIAL']));
        $cliente->shouldReceive('unifranzCollection')->once()->with(true)->andReturn(new AporteResponse(true, ['status' => 'EN_PROCESO']));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->postJson('/conocimiento/colecciones/unifranz', ['url' => 'https://otra.test/'])->assertUnprocessable();
        $this->getJson('/conocimiento/colecciones/unifranz')->assertOk()->assertJsonPath('status', 'PARCIAL');
        $this->postJson('/conocimiento/colecciones/unifranz', [])->assertOk()->assertJsonPath('status', 'EN_PROCESO');
    }

    public function test_fallo_no_se_presenta_como_coleccion_completada(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('unifranzCollection')->once()->andReturn(AporteResponse::unavailable());
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'))->getJson('/conocimiento/colecciones/unifranz')
            ->assertStatus(503)->assertJsonMissing(['status' => 'COMPLETADA']);
    }

    public function test_descarga_entrega_adjunto_con_nombre_y_no_inicia_extraccion(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $hash = str_repeat('a', 64);
        $cliente->shouldReceive('documentoUnifranz')->once()->with($hash)->andReturn(new AporteResponse(true, ['content' => '%PDF-test', 'filename' => 'UNIFRANZ - Medicina - Malla.pdf']));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->get('/conocimiento/colecciones/unifranz/documento/invalido/descargar')->assertNotFound();
        $this->get('/conocimiento/colecciones/unifranz/documento/'.$hash.'/descargar')->assertOk()->assertDownload('UNIFRANZ - Medicina - Malla.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_analisis_usa_solo_mallas_guardadas_y_rechaza_contenido_del_navegador(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('analizarColeccionUnifranz')->once()->withNoArgs()->andReturn(new AporteResponse(true, ['status' => 'EN_PROCESO', 'processing_stage' => 'RECONOCIMIENTO']));
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->actor('Administrador'));
        $this->postJson('/conocimiento/colecciones/unifranz/analizar', ['text' => 'texto inventado'])->assertUnprocessable();
        $this->postJson('/conocimiento/colecciones/unifranz/analizar', [])->assertOk()->assertJsonPath('processing_stage', 'RECONOCIMIENTO');
    }

    private function actor(string $rol, bool $permiso = true): User
    {
        $usuario = Mockery::mock(User::class)->makePartial();
        $usuario->forceFill(['cod_usu' => 'TEST_COLECCION_'.$rol, 'email' => strtolower($rol).'@example.test', 'email_verified_at' => now(), 'est_usu' => 'ACTIVO']);
        $usuario->shouldReceive('hasRole')->andReturnUsing(fn ($nombre) => $nombre === $rol);
        $usuario->shouldReceive('can')->andReturn($permiso);
        $usuario->shouldReceive('checkPermissionTo')->andReturn($permiso);

        return $usuario;
    }
}
