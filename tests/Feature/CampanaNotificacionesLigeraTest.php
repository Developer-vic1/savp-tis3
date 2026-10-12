<?php

namespace Tests\Feature;

use App\Livewire\Shared\NotificationCenter;
use App\Models\Oficial\Sistema\User;
use App\Services\AccesoProgramadoService;
use App\Services\NotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CampanaNotificacionesLigeraTest extends TestCase
{
    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['features.notifications' => true]);

        Schema::create('notificacion', function (Blueprint $tabla) {
            $tabla->string('cod_not')->primary();
            foreach (['titulo', 'mensaje', 'tipo', 'est_not', 'origen', 'clave_evento', 'permiso', 'ruta'] as $campo) {
                $tabla->string($campo)->nullable();
            }
            $tabla->dateTime('publicada_en');
            $tabla->dateTime('vence_en')->nullable();
            $tabla->timestamps();
        });
        Schema::create('notificacion_usuario', function (Blueprint $tabla) {
            $tabla->string('cod_nus')->primary();
            $tabla->string('cod_not');
            $tabla->string('cod_usu');
            $tabla->dateTime('archivada_en')->nullable();
            $tabla->dateTime('leida_en')->nullable();
            $tabla->timestamps();
        });

        $this->usuario = Mockery::mock(User::class)->makePartial();
        $this->usuario->cod_usu = 'U';
        $this->usuario->est_usu = 'ACTIVO';
        $this->usuario->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Estudiante');
        $this->usuario->shouldReceive('can')->andReturnFalse();
        $this->actingAs($this->usuario);
        $accesos = Mockery::mock(AccesoProgramadoService::class);
        $accesos->shouldReceive('disponible')->andReturnFalse();
        $this->app->instance(AccesoProgramadoService::class, $accesos);

        foreach (['PROPIO', 'AJENO', 'RESTRINGIDO', 'LEIDO', 'ARCHIVADO', 'VENCIDO'] as $indice => $codigo) {
            DB::table('notificacion')->insert([
                'cod_not' => $codigo, 'titulo' => 'Aviso '.$codigo, 'mensaje' => 'Mensaje '.$codigo,
                'tipo' => 'INFORMACION', 'est_not' => 'ACTIVA', 'origen' => 'SISTEMA',
                'clave_evento' => $codigo, 'permiso' => $codigo === 'RESTRINGIDO' ? 'Calificaciones' : null,
                'publicada_en' => now()->subDay(), 'vence_en' => $codigo === 'VENCIDO' ? now()->subHour() : null,
            ]);
            DB::table('notificacion_usuario')->insert([
                'cod_nus' => 'NUS_'.str_pad((string) $indice, 6, '0', STR_PAD_LEFT), 'cod_not' => $codigo,
                'cod_usu' => $codigo === 'AJENO' ? 'OTRO' : 'U',
                'leida_en' => $codigo === 'LEIDO' ? now() : null,
                'archivada_en' => $codigo === 'ARCHIVADO' ? now() : null, 'created_at' => now(),
            ]);
        }
    }

    public function test_cerrada_cuenta_solo_avisos_autorizados_y_evitar_cargar_bandeja_ahorra_tres_consultas(): void
    {
        $servicio = new NotificationService;
        DB::enableQueryLog();
        DB::flushQueryLog();
        $cerrada = $servicio->snapshot($this->usuario, incluirBandeja: false);
        $consultasCerrada = count(DB::getQueryLog());
        $this->assertSame(3, $consultasCerrada);
        $this->assertSame(1, $cerrada['unread']);
        $this->assertNull($cerrada['rows']);
        $this->assertSame([], $cerrada['vigencias']);

        DB::flushQueryLog();
        $abierta = $servicio->snapshot($this->usuario);
        $consultasAbierta = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame(1, $abierta['unread']);
        $this->assertEqualsCanonicalizing(['PROPIO', 'LEIDO'], $abierta['rows']->pluck('cod_not')->all());
        $this->assertSame($consultasCerrada + 3, $consultasAbierta);
    }

    public function test_abrir_la_campana_carga_la_bandeja_y_cerrar_retira_los_mensajes(): void
    {
        Livewire::test(NotificationCenter::class)
            ->assertDontSee('Aviso PROPIO')
            ->set('abierta', true)
            ->assertSee('Aviso PROPIO')->assertSee('Aviso LEIDO')
            ->assertDontSee('Aviso AJENO')->assertDontSee('Aviso RESTRINGIDO')->assertDontSee('Aviso VENCIDO')
            ->set('abierta', false)->assertDontSee('Aviso PROPIO');
    }

    public function test_la_lectura_ligera_tambien_rechaza_la_cuenta_inactiva(): void
    {
        $this->usuario->est_usu = 'INACTIVO';
        try {
            (new NotificationService)->snapshot($this->usuario, incluirBandeja: false);
            $this->fail('Una cuenta inactiva tampoco puede consultar el contador.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
    }
}
