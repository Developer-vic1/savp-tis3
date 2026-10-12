<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\ActualizacionesFuentes;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ActualizacionesFuentesTest extends TestCase
{
    private string $ruta;

    private $cliente;

    private ActualizacionesFuentes $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruta = sys_get_temp_dir().'/savp-revisiones-'.bin2hex(random_bytes(6)).'.json';
        config()->set('services.aporte_ingenieril.actualizaciones_path', $this->ruta);
        $this->cliente = Mockery::mock(AporteIngenierilClient::class);
        $this->cliente->shouldReceive('knowledgeGovernance')->andReturn(new AporteResponse(true, [
            'trusted_universities' => [['domain' => 'upb.edu']],
            'sources' => [
                ['source_id' => 'BO-UPB-SIS', 'title' => 'Malla Sistemas', 'institution' => 'UPB', 'url' => 'https://www.upb.edu/malla.pdf', 'verification_status' => 'VALID'],
                ['source_id' => 'BO-MIN', 'title' => 'Currículo base', 'institution' => 'Ministerio', 'url' => 'https://minedu.gob.bo/base.pdf', 'verification_status' => 'VALID'],
            ],
        ]));
        $this->servicio = new class($this->cliente) extends ActualizacionesFuentes
        {
            public array $avisos = [];

            protected function avisar(array $fuente, string $evento): bool
            {
                $this->avisos[] = $evento;

                return true;
            }
        };
        $this->travelTo(Carbon::create(2026, 1, 31, 0, 0, 0, 'America/La_Paz'));
    }

    protected function tearDown(): void
    {
        File::delete([$this->ruta, $this->ruta.'.tmp']);
        parent::tearDown();
    }

    public function test_separa_ministerio_y_no_acepta_url_o_fuente_inventada(): void
    {
        $this->assertCount(1, $this->servicio->snapshot()['sources']);
        $this->cliente->shouldNotReceive('revisarActualizacionFuente');
        $this->expectException(ValidationException::class);
        $this->servicio->revisar('BO-MIN');
    }

    public function test_baseline_cambios_reintento_y_notificacion_sin_duplicado(): void
    {
        $this->cliente->shouldReceive('revisarActualizacionFuente')->with('BO-UPB-SIS')->andReturn(
            new AporteResponse(true, ['source_id' => 'BO-UPB-SIS', 'status' => 'VERIFICADA', 'fingerprint' => str_repeat('a', 64)]),
            new AporteResponse(true, ['source_id' => 'BO-UPB-SIS', 'status' => 'VERIFICADA', 'fingerprint' => str_repeat('b', 64)]),
            new AporteResponse(false),
            new AporteResponse(true, ['source_id' => 'BO-UPB-SIS', 'status' => 'VERIFICADA', 'fingerprint' => str_repeat('b', 64)])
        );
        $this->servicio->configurar('BO-UPB-SIS', 'MENSUAL');
        $primero = $this->servicio->revisar('BO-UPB-SIS')['sources'][0];
        $this->assertSame('SIN_CAMBIOS', $primero['status']);
        $this->assertStringStartsWith('2026-02-28', $primero['next_check_at']);
        $this->assertArrayNotHasKey('fingerprint', $primero);
        $this->assertSame('CAMBIO_DETECTADO', $this->servicio->revisar('BO-UPB-SIS')['sources'][0]['status']);
        $this->assertSame('NO_DISPONIBLE', $this->servicio->revisar('BO-UPB-SIS')['sources'][0]['status']);
        $this->assertSame('CAMBIO_DETECTADO', $this->servicio->revisar('BO-UPB-SIS')['sources'][0]['status']);
        $this->assertCount(1, $this->servicio->avisos);
    }

    public function test_frecuencias_semestre_gestion_y_manual(): void
    {
        $this->cliente->shouldReceive('revisarActualizacionFuente')->andReturn(new AporteResponse(true, [
            'source_id' => 'BO-UPB-SIS', 'status' => 'VERIFICADA', 'fingerprint' => str_repeat('a', 64),
        ]));
        $this->servicio->revisar('BO-UPB-SIS');
        $this->assertStringStartsWith('2026-07-31', $this->servicio->configurar('BO-UPB-SIS', 'SEMESTRAL')['sources'][0]['next_check_at']);
        $this->assertStringStartsWith('2027-01-31', $this->servicio->configurar('BO-UPB-SIS', 'ANUAL')['sources'][0]['next_check_at']);
        $this->assertNull($this->servicio->configurar('BO-UPB-SIS', 'MANUAL')['sources'][0]['next_check_at']);
        $this->assertSame(0, $this->servicio->procesarPendientes());
    }
}
