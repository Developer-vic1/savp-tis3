<?php

namespace Tests\Feature;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Perfil\ActualizarInformacion;
use App\Livewire\Perfil\RetirarAcceso;
use App\Models\Oficial\Sistema\User;
use App\Support\Seguridad\VerificadorIdentidadPerfil;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PerfilInstitucionalTest extends TestCase
{
    private const PASSWORD = 'SoloParaPrueba456!';

    protected function setUp(): void
    {
        parent::setUp();
        config(['seguridad-acceso.cache' => 'array']);
        Schema::create('users', function (Blueprint $table) {
            $table->string('cod_usu')->primary();
            $table->string('cod_per')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('est_usu');
            $table->string('remember_token')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
        });
        Schema::create('persona', function (Blueprint $table) {
            $table->string('cod_per')->primary();
            $table->string('tel_per')->nullable();
            $table->string('dir_per')->nullable();
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->string('tokenable_id');
        });
    }

    private function usuario(string $estado = 'ACTIVO', string $codigo = 'PRUEBA'): User
    {
        DB::table('persona')->insert(['cod_per' => $codigo, 'tel_per' => '76543210', 'dir_per' => 'Dirección de prueba']);
        DB::table('users')->insert(['cod_usu' => $codigo, 'cod_per' => $codigo, 'email' => strtolower($codigo).'@example.test',
            'password' => Hash::make(self::PASSWORD), 'est_usu' => $estado, 'remember_token' => 'token-prueba', 'profile_photo_path' => 'foto-prueba.jpg']);

        return User::findOrFail($codigo);
    }

    public function test_el_editor_carga_contacto_y_no_carga_secretos_del_usuario(): void
    {
        $this->actingAs($this->usuario());
        $formulario = new ActualizarInformacion;
        $formulario->mount();
        $this->assertSame('76543210', $formulario->state['tel_per']);
        $this->assertSame('Dirección de prueba', $formulario->state['dir_per']);
        $this->assertSame('', $formulario->state['current_password']);
        $this->assertArrayNotHasKey('password', $formulario->state);
        $this->assertArrayNotHasKey('remember_token', $formulario->state);
    }

    public function test_el_servidor_no_actualiza_el_perfil_sin_contrasena_actual(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        try {
            app(UpdateUserProfileInformation::class)->update($usuario, ['email' => 'otro@example.test', 'tel_per' => '70000000']);
            $this->fail('Debió pedir contraseña.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('current_password', $error->errors());
        }
        $this->assertSame('prueba@example.test', $usuario->fresh()->email);
        $this->assertSame('76543210', $usuario->persona->fresh()->tel_per);
    }

    public function test_identidad_confirmada_actualiza_solo_contacto_y_permite_vaciar_campos_opcionales(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        app(UpdateUserProfileInformation::class)->update($usuario, ['email' => 'actualizado@example.test', 'tel_per' => null,
            'dir_per' => 'Nueva dirección', 'current_password' => self::PASSWORD, 'est_usu' => 'INACTIVO', 'cod_per' => 'AJENO']);
        $this->assertSame('actualizado@example.test', $usuario->fresh()->email);
        $this->assertNull($usuario->persona->fresh()->tel_per);
        $this->assertSame('Nueva dirección', $usuario->persona->fresh()->dir_per);
        $this->assertSame('ACTIVO', $usuario->fresh()->est_usu);
        $this->assertSame('PRUEBA', $usuario->fresh()->cod_per);
    }

    public function test_no_se_puede_editar_el_usuario_ajeno(): void
    {
        $this->actingAs($this->usuario());
        $ajeno = $this->usuario(codigo: 'AJENO');
        $this->expectException(HttpException::class);
        app(UpdateUserProfileInformation::class)->update($ajeno, ['current_password' => self::PASSWORD]);
    }

    public function test_el_servidor_rechaza_un_telefono_invalido_sin_cambiar_contacto(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        try {
            app(UpdateUserProfileInformation::class)->update($usuario, ['email' => $usuario->email,
                'tel_per' => 'texto-no-telefono', 'current_password' => self::PASSWORD]);
            $this->fail('Debió rechazar el teléfono.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('tel_per', $error->errors());
        }
        $this->assertSame('76543210', $usuario->persona->fresh()->tel_per);
    }

    public function test_la_accion_directa_de_quitar_foto_exige_contrasena(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        Livewire::test(ActualizarInformacion::class)->call('deleteProfilePhoto')->assertHasErrors('current_password');
        $this->assertSame('foto-prueba.jpg', $usuario->fresh()->profile_photo_path);
    }

    public function test_tres_fallos_de_identidad_generan_una_pausa_progresiva(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        $verificador = app(VerificadorIdentidadPerfil::class);
        for ($i = 0; $i < 3; $i++) {
            try {
                $verificador->confirmar($usuario, 'incorrecta');
            } catch (ValidationException $error) {
            }
        }
        try {
            $verificador->confirmar($usuario, self::PASSWORD);
            $this->fail('La pausa debe mantenerse aunque la contraseña sea correcta.');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('15 segundos', $error->getMessage());
        }
        $this->travel(15)->seconds();
        $verificador->confirmar($usuario, self::PASSWORD);
        $this->assertSame('ACTIVO', $usuario->fresh()->est_usu);
    }

    public function test_retirar_acceso_exige_la_confirmacion_y_la_contrasena(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        Livewire::test(RetirarAcceso::class)->set('password', self::PASSWORD)->call('deleteUser')->assertHasErrors('confirmacion');
        Livewire::test(RetirarAcceso::class)->set('confirmacion', 'RETIRAR')->set('password', 'incorrecta')->call('deleteUser')->assertHasErrors('password');
        $this->assertSame('ACTIVO', $usuario->fresh()->est_usu);
    }

    public function test_la_cuenta_ficticia_se_desactiva_sin_borrar_persona_foto_y_con_logout(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario);
        DB::table('personal_access_tokens')->insert(['tokenable_type' => User::class, 'tokenable_id' => $usuario->getKey()]);
        Livewire::test(RetirarAcceso::class)->set('confirmacion', 'RETIRAR')->set('password', self::PASSWORD)->call('deleteUser')->assertRedirect('/');
        $this->assertGuest();
        $this->assertSame('INACTIVO', $usuario->fresh()->est_usu);
        // SessionGuard rota el token al cerrar sesión; el anterior queda invalidado.
        $this->assertNotSame('token-prueba', $usuario->fresh()->remember_token);
        $this->assertSame('foto-prueba.jpg', $usuario->fresh()->profile_photo_path);
        $this->assertSame('76543210', DB::table('persona')->value('tel_per'));
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_una_cuenta_inactiva_no_inicia_sesion_con_contrasena_correcta(): void
    {
        $usuario = $this->usuario('INACTIVO');
        $this->postJson('/login', ['email' => $usuario->email, 'password' => self::PASSWORD])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_el_middleware_interrumpe_una_sesion_existente_inactiva(): void
    {
        $this->actingAs($this->usuario('INACTIVO'));
        Route::get('/prueba-perfil-protegido', fn () => 'No debe mostrarse')->middleware(['web', 'auth']);
        $this->get('/prueba-perfil-protegido')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_google_rechaza_la_cuenta_inactiva_antes_de_modificarla(): void
    {
        $usuario = $this->usuario('INACTIVO');
        $identidad = Mockery::mock()->shouldReceive('getEmail')->andReturn($usuario->email)->getMock();
        Socialite::shouldReceive('driver')->with('google')->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($identidad)->getMock());
        $request = Request::create('/auth/google/callback');
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        $respuesta = app(GoogleAuthController::class)->callback();
        $this->assertSame(route('login'), $respuesta->getTargetUrl());
        $this->assertGuest();
        $this->assertSame('INACTIVO', $usuario->fresh()->est_usu);
    }

    public function test_avatar_por_defecto_es_local_y_no_envia_identidad_a_terceros(): void
    {
        $usuario = new User(['email' => 'privado@example.test']);
        $this->assertStringContainsString('/image/avatar-institucional.svg', $usuario->profile_photo_url);
        $this->assertStringNotContainsString('privado', $usuario->profile_photo_url);
    }
}
