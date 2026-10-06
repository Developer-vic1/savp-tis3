<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Support\Seguridad\LimitadorIntentosAcceso;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

class ProteccionAccesoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['seguridad-acceso.cache' => 'array']);
        Event::fake([Login::class, Failed::class]);
        Fortify::authenticateUsing(fn () => null);
    }

    private function credenciales(): array
    {
        return ['email' => 'persona@ejemplo.test', 'password' => 'fallo-simulado', 'remember' => 0];
    }

    public function test_tres_fallos_bloquean_y_la_siguiente_ronda_duplica_la_pausa(): void
    {
        $consultas = 0;
        Fortify::authenticateUsing(function () use (&$consultas) {
            $consultas++;

            return null;
        });
        $this->postJson('/login', $this->credenciales())->assertStatus(422);
        $this->postJson('/login', $this->credenciales())->assertStatus(422);
        $this->postJson('/login', $this->credenciales())->assertStatus(429)->assertHeader('Retry-After', '15');
        $this->travel(3)->seconds();
        $this->postJson('/login', $this->credenciales())->assertStatus(429)->assertJsonPath('retry_after', 12);
        $this->assertSame(3, $consultas, 'Durante el bloqueo no se verifican credenciales.');
        $this->travel(12)->seconds();
        $this->postJson('/login', $this->credenciales())->assertStatus(422);
        $this->postJson('/login', $this->credenciales())->assertStatus(422);
        $this->postJson('/login', $this->credenciales())->assertStatus(429)->assertHeader('Retry-After', '30');
    }

    public function test_el_post_vacio_tambien_se_bloquea_sin_consultar_usuarios(): void
    {
        Fortify::authenticateUsing(function () {
            $this->fail('El POST vacío no debe autenticar.');
        });
        $this->postJson('/login', [])->assertStatus(422);
        $this->postJson('/login', [])->assertStatus(422);
        $this->postJson('/login', [])->assertStatus(429)->assertJsonPath('retry_after', 15);
    }

    public function test_un_identificador_con_tipo_incorrecto_no_rompe_la_vista(): void
    {
        $this->from('/login')->post('/login', ['email' => ['invalido'], 'password' => 'simulada'])
            ->assertRedirect('/login');
        $this->assertSame('', session('_old_input.email'));
        $this->get('/login')->assertOk();
    }

    public function test_la_proteccion_no_omite_el_segundo_factor(): void
    {
        $usuario = new User;
        $usuario->cod_usu = 'usuario-solo-prueba';
        $usuario->two_factor_secret = encrypt('secreto-solo-prueba');
        $usuario->two_factor_confirmed_at = now();
        Fortify::authenticateUsing(fn () => $usuario);
        $this->postJson('/login', $this->credenciales())->assertOk()->assertJsonPath('two_factor', true);
        $this->assertGuest();
        $this->assertSame('usuario-solo-prueba', session('login.id'));
    }

    public function test_html_conserva_el_correo_y_la_pausa_pero_no_la_contrasena(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $respuesta = $this->from('/login')->post('/login', $this->credenciales());
        }
        $respuesta->assertRedirect('/login')->assertHeader('Retry-After', '15');
        $this->assertSame('persona@ejemplo.test', session('_old_input.email'));
        $this->assertArrayNotHasKey('password', session('_old_input'));
        $this->assertGreaterThan(now()->timestamp, session('acceso_bloqueado_hasta'));
        $this->get('/login')->assertOk()->assertSee('loginCooldown', false);
    }

    private function usuarioSimulado(): GenericUser
    {
        return new GenericUser([
            'id' => 123, 'password' => 'hash-simulado', 'remember_token' => 'token-solo-prueba', 'est_usu' => 'ACTIVO',
            'two_factor_secret' => null, 'two_factor_confirmed_at' => null,
        ]);
    }

    public function test_acceso_correcto_reinicia_fallos_y_recordarme_crea_cookie(): void
    {
        $this->postJson('/login', $this->credenciales())->assertStatus(422);
        $antes = session()->getId();
        Fortify::authenticateUsing(fn () => $this->usuarioSimulado());
        $respuesta = $this->postJson('/login', [...$this->credenciales(), 'remember' => 1]);
        $respuesta->assertOk()->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertNotSame($antes, session()->getId());
        $limitador = app(LimitadorIntentosAcceso::class);
        $peticion = Request::create('/login', 'POST', $this->credenciales());
        $this->assertSame(0, $limitador->estado($limitador->clave($peticion))['fallos']);
    }

    public function test_sin_recordarme_no_se_crea_cookie_persistente(): void
    {
        Fortify::authenticateUsing(fn () => $this->usuarioSimulado());
        $this->postJson('/login', $this->credenciales())->assertOk()
            ->assertCookieMissing(Auth::guard('web')->getRecallerName());
    }

    public function test_recordarme_esta_marcado_inicialmente_y_respeta_la_desactivacion(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();
        preg_match('/<input[^>]*id="remember_me"[^>]*>/', $html, $control);
        $this->assertStringContainsString('checked', $control[0]);
        $this->from('/login')->post('/login', $this->credenciales())->assertRedirect('/login');
        $html = $this->get('/login')->assertOk()->getContent();
        preg_match('/<input[^>]*id="remember_me"[^>]*>/', $html, $control);
        $this->assertStringNotContainsString('checked', $control[0]);
    }

    public function test_el_limite_por_ip_sigue_aplicando_a_correos_distintos(): void
    {
        config(['seguridad-acceso.peticiones_por_minuto' => 3]);
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/login', ['email' => "persona{$i}@ejemplo.test"])->assertStatus(422);
        }
        $this->postJson('/login', ['email' => 'otra@ejemplo.test'])->assertStatus(429);
    }

    public function test_la_pausa_tiene_tope_y_se_reinicia_tras_inactividad(): void
    {
        $limitador = app(LimitadorIntentosAcceso::class);
        $clave = $limitador->clave(Request::create('/login', 'POST', $this->credenciales()));
        foreach ([15, 30, 60, 120, 240, 300, 300] as $segundos) {
            for ($i = 0; $i < 3; $i++) {
                $estado = $limitador->serializar($clave, fn () => $limitador->registrarFallo($clave));
            }
            $this->assertSame($segundos, $estado['restantes']);
            $this->travel($segundos)->seconds();
        }
        $this->travel(3601)->seconds();
        $this->assertSame(0, $limitador->estado($clave)['nivel']);
    }

    public function test_el_ambito_normaliza_correo_y_distingue_ip(): void
    {
        $limitador = app(LimitadorIntentosAcceso::class);
        $a = Request::create('/login', 'POST', ['email' => ' Persona@Ejemplo.test ']);
        $b = Request::create('/login', 'POST', ['email' => 'persona@ejemplo.test']);
        $this->assertSame($limitador->clave($a), $limitador->clave($b));
        $b->server->set('REMOTE_ADDR', '192.0.2.10');
        $this->assertNotSame($limitador->clave($a), $limitador->clave($b));
    }
}
