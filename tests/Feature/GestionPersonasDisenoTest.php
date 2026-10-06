<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionPersonas;
use App\Livewire\Admin\GestionUsuarios;
use App\Livewire\Admin\PersonalInstitucional;
use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use App\Notifications\InvitacionAccesoUsuario;
use App\Services\EnvioAccesoUsuario;
use App\Support\Personas\IndicadoresPersonas;
use App\Support\Personas\PersonaInteligente;
use App\Support\Usuarios\CorreoInstitucional;
use App\Support\Usuarios\IndicadoresUsuarios;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GestionPersonasDisenoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 3)->startOfDay());
        Schema::create('persona', function (Blueprint $tabla) {
            $tabla->string('cod_per')->primary();
            foreach (['nom_per', 'ape_pat_per', 'ape_mat_per', 'ci_per', 'com_per', 'exp_per', 'gen_per', 'tel_per', 'ema_per', 'dir_per', 'fot_per', 'zona_per', 'ave_per', 'cal_per', 'num_per', 'ref_per', 'ciu_per', 'mun_per', 'dep_per'] as $campo) {
                $tabla->string($campo)->nullable();
            }
            $tabla->date('fec_nac_per')->nullable();
            $tabla->boolean('est_per')->default(true);
            $tabla->timestamps();
        });
        Schema::create('users', function (Blueprint $tabla) {
            $tabla->string('cod_usu')->primary();
            $tabla->string('cod_per')->nullable();
            $tabla->string('email')->nullable();
            $tabla->string('est_usu')->default('ACTIVO');
            $tabla->timestamps();
        });
        Schema::create('permissions', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('name');
            $tabla->string('guard_name');
        });
        Schema::create('personal_institucional', function (Blueprint $tabla) {
            $tabla->string('cod_pin')->primary();
            $tabla->string('cod_per');
            $tabla->string('car_pin');
            $tabla->string('est_pin');
        });
        Schema::create('roles', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('name');
            $tabla->string('guard_name');
        });
        Schema::create('model_has_roles', function (Blueprint $tabla) {
            $tabla->unsignedBigInteger('role_id');
            $tabla->string('model_type');
            $tabla->string('cod_usu');
        });
        DB::table('users')->insert(['cod_usu' => 'PRUEBA_DISENO', 'email' => 'prueba@example.test', 'est_usu' => 'ACTIVO']);
        DB::table('roles')->insert(['id' => 1, 'name' => 'Administrador', 'guard_name' => 'web']);
        foreach (['Estudiante', 'Docente', 'Director', 'Secretaria', 'Regente'] as $indice => $nombre) {
            DB::table('roles')->insert(['id' => $indice + 2, 'name' => $nombre, 'guard_name' => 'web']);
        }
        DB::table('model_has_roles')->insert(['role_id' => 1, 'model_type' => User::class, 'cod_usu' => 'PRUEBA_DISENO']);
        $this->actingAs(User::findOrFail('PRUEBA_DISENO'));
        Gate::before(fn () => true);
        Livewire::setUpdateRoute(fn ($controlador) => Route::post('/livewire/update/prueba-personas', $controlador)->middleware('web'));
    }

    private function persona(array $datos = []): Persona
    {
        return Persona::create(array_merge(['nom_per' => 'Ana', 'ape_pat_per' => 'Prueba', 'ape_mat_per' => 'Ejemplo', 'ci_per' => (string) random_int(1000000, 9999999), 'exp_per' => 'LP', 'fec_nac_per' => '2010-06-11', 'gen_per' => 'F', 'tel_per' => '76543210', 'ema_per' => null, 'dir_per' => 'Zona Centro, Calle Bolívar Nro. 15', 'est_per' => 1], $datos));
    }

    public function test_editar_recupera_fecha_iso_genero_y_partes_de_direccion(): void
    {
        $persona = $this->persona();
        Livewire::test(GestionPersonas::class)->call('abrirModalEditar', $persona->cod_per)
            ->assertSet('formEditar.fec_nac_per', '2010-06-11')->assertSet('formEditar.gen_per', 'F')
            ->assertSet('formEditar.cal_per', 'Bolívar')->assertSee('Editar persona')->assertSee('11/06/2010');
    }

    public function test_generos_del_formulario_no_producen_advertencias_falsas(): void
    {
        $soporte = new PersonaInteligente;
        foreach (['M' => 'MASCULINO', 'F' => 'FEMENINO'] as $valor => $normalizado) {
            $datos = $soporte->normalizarDatos(['nom_per' => 'Ana', 'ape_pat_per' => 'Prueba', 'ci_per' => '12345678', 'exp_per' => 'LP', 'fec_nac_per' => '2010-06-11', 'gen_per' => $valor]);
            $this->assertSame($normalizado, $datos['gen_per']);
            $this->assertNotContains('El género ingresado no coincide con los valores sugeridos del sistema.', $soporte->analizarIdentidad($datos)['advertencias']);
        }
    }

    public function test_nueva_persona_siempre_activa_y_direccion_estructurada_persistida(): void
    {
        $componente = Livewire::test(GestionPersonas::class)->call('abrirModalCrear')->call('cambiarModoDireccionCrear', 'manual');
        foreach (['nom_per' => 'Elena', 'ape_pat_per' => 'Prueba', 'ci_per' => '7654321', 'exp_per' => 'LP', 'fec_nac_per' => '2010-06-11', 'gen_per' => 'F', 'tel_per' => '76543210', 'zona_per' => 'Centro', 'cal_per' => 'Bolívar', 'num_per' => '15', 'est_per' => 0] as $campo => $valor) {
            $componente->set('form.'.$campo, $valor)->assertStatus(200);
        }
        $componente->assertSet('form.zona_per', 'Centro')->assertSet('form.cal_per', 'Bolívar');
        $componente->call('guardarPersona')->assertStatus(200)->assertHasNoErrors();
        $componente->assertDispatched('persona-creada');
        $this->assertDatabaseHas('persona', ['ci_per' => '7654321', 'est_per' => 1, 'zona_per' => 'Centro', 'cal_per' => 'Bolívar']);
    }

    public function test_editar_no_reactiva_ni_desactiva_el_registro(): void
    {
        $persona = $this->persona(['est_per' => 0]);
        Livewire::test(GestionPersonas::class)->call('abrirModalEditar', $persona->cod_per)
            ->set('formEditar.est_per', 1)->set('formEditar.tel_per', '76543211')->call('actualizarPersona')
            ->assertHasNoErrors()->assertDispatched('persona-actualizada');
        $this->assertDatabaseHas('persona', ['cod_per' => $persona->cod_per, 'est_per' => 0, 'tel_per' => '76543211']);
    }

    public function test_pendientes_combinados_filtran_indicadores_y_paginacion(): void
    {
        $this->persona(['tel_per' => null, 'ema_per' => null]);
        $this->persona(['tel_per' => '76543211', 'ema_per' => null]);
        $this->persona(['tel_per' => null, 'ema_per' => 'otra@example.test']);
        $componente = Livewire::test(GestionPersonas::class)->set('pendientes', ['telefono', 'correo']);
        $this->assertSame(1, $componente->instance()->datosGraficos['total']);
        $componente->assertSee('persona encontrada')->assertSee('3 personas sin cuenta vinculada')->call('limpiarFiltros');
        $this->assertSame(3, $componente->instance()->datosGraficos['total']);
    }

    public function test_indicadores_respetan_limites_de_edad_y_consulta_filtrada(): void
    {
        foreach (['2013-10-04', '2013-10-03', '2008-10-03', '2000-10-03', null, '2027-01-01'] as $indice => $fecha) {
            $persona = $this->persona(['fec_nac_per' => $fecha]);
            if ($indice === 0) {
                DB::table('users')->insert(['cod_usu' => 'CUENTA_PRUEBA', 'cod_per' => $persona->cod_per]);
            }
        }
        $indicadores = app(IndicadoresPersonas::class)->analizar(Persona::query());
        $this->assertSame([1, 1, 1, 1, 2], $indicadores['edades']['data']);
        $this->assertSame([1, 5], $indicadores['cuentas']['data']);
        $vacio = app(IndicadoresPersonas::class)->analizar(Persona::where('cod_per', 'INEXISTENTE'));
        $this->assertSame(0, $vacio['total']);
        $this->assertSame([0, 0, 0, 0, 0], $vacio['edades']['data']);
    }

    public function test_cambiar_cantidad_reinicia_pagina_y_rechaza_tamanos_arbitrarios(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $this->persona();
        }
        Livewire::test(GestionPersonas::class)->call('gotoPage', 2)->assertSet('paginators.page', 2)
            ->set('perPage', 20)->assertSet('paginators.page', 1)->assertSee('Mostrar 50 personas por página')
            ->set('perPage', 999)->assertSet('perPage', 10)->assertHasErrors(['perPage']);
    }

    public function test_servidor_impide_fecha_futura_y_duplicar_identidad(): void
    {
        $persona = $this->persona();
        $datos = array_merge((new GestionPersonas)->form, $persona->toArray());
        $datos['fec_nac_per'] = '2027-01-01';
        $datos['ci_per'] = '98765432';
        Livewire::test(GestionPersonas::class)->call('abrirModalCrear')->set('form', $datos)
            ->call('guardarPersona')->assertDispatched('error-general');
        $this->assertDatabaseCount('persona', 1);
        $datos['fec_nac_per'] = '2010-06-11';
        $datos['ci_per'] = $persona->ci_per;
        Livewire::test(GestionPersonas::class)->call('abrirModalCrear')->set('form', $datos)
            ->call('guardarPersona')->assertDispatched('error-general');
        $this->assertDatabaseCount('persona', 1);
    }

    public function test_personas_no_expone_acciones_para_cambiar_estado_y_alerta_cuentas_pendientes(): void
    {
        $this->persona();
        $componente = Livewire::test(GestionPersonas::class)->assertSee('1 persona sin cuenta vinculada')
            ->assertSee('Distribución por género')->assertDontSee('Desactivar a');
        $this->assertFalse(method_exists($componente->instance(), 'desactivarPersona'));
        $this->assertFalse(method_exists($componente->instance(), 'reactivarPersona'));
        $componente->set('pendientes', ['campo_arbitrario'])->assertSet('pendientes', [])->assertHasErrors(['pendientes']);
    }

    public function test_support_rechaza_numeros_en_nombres_y_letras_en_ci_sin_ocultarlos(): void
    {
        $soporte = new PersonaInteligente;
        foreach (['nom_per' => 'Ana123', 'ape_pat_per' => 'Prueba7', 'ape_mat_per' => 'Ejemplo5', 'ci_per' => '1234abc'] as $campo => $valor) {
            $datos = $soporte->normalizarDatos(['nom_per' => 'Ana María', 'ape_pat_per' => 'O’Connor', 'ape_mat_per' => 'Pérez-Soria', 'ci_per' => '12345678', 'exp_per' => 'LP', 'fec_nac_per' => '2010-06-11', 'gen_per' => 'F', $campo => $valor]);
            $this->assertNotEmpty($soporte->analizarIdentidad($datos)['bloqueos']);
            $this->assertSame($valor, $datos[$campo]);
        }
        $this->assertSame([], $soporte->analizarIdentidad($soporte->normalizarDatos(['nom_per' => 'Ana María', 'ape_pat_per' => 'O’Connor', 'ape_mat_per' => 'Pérez-Soria', 'ci_per' => '12345678', 'exp_per' => 'LP', 'gen_per' => 'F']))['bloqueos']);
    }

    public function test_ci_duplicado_se_detecta_al_escribir_incluso_con_otro_complemento(): void
    {
        $primera = $this->persona(['ci_per' => '12345678', 'com_per' => '1A']);
        $segunda = $this->persona(['ci_per' => '87654321']);
        $crear = Livewire::test(GestionPersonas::class)->call('abrirModalCrear')->set('form.com_per', '2B')->set('form.ci_per', $primera->ci_per);
        $this->assertTrue($crear->instance()->analisisPersona['coincidencias']['duplicado_ci']);
        $this->assertFalse($crear->instance()->puedeGuardarPersona());
        $editar = Livewire::test(GestionPersonas::class)->call('abrirModalEditar', $segunda->cod_per)->set('formEditar.ci_per', $primera->ci_per);
        $this->assertTrue($editar->instance()->analisisPersonaEditar['coincidencias']['duplicado_ci']);
        $this->assertFalse($editar->instance()->puedeActualizarPersona());
        $editar->set('formEditar.ci_per', $segunda->ci_per);
        $this->assertFalse($editar->instance()->analisisPersonaEditar['coincidencias']['duplicado_ci']);
        $this->assertTrue($editar->instance()->puedeActualizarPersona());
        $this->assertDatabaseCount('persona', 2);
    }

    public function test_support_no_convierte_fechas_inexistentes_en_otra_fecha(): void
    {
        $soporte = new PersonaInteligente;
        $this->assertSame('2026-02-30', $soporte->normalizarFecha('2026-02-30'));
        $this->assertNotEmpty($soporte->analizarEdad('2026-02-30')['bloqueos']);
        $this->assertSame([], $soporte->analizarEdad('2012-02-29')['bloqueos']);
    }

    public function test_usuarios_pagina_filtra_por_nombre_y_limita_la_cantidad(): void
    {
        $persona = $this->persona(['nom_per' => 'Elena', 'ape_pat_per' => 'Vargas']);
        DB::table('users')->insert(['cod_usu' => 'CUENTA_ELENA', 'cod_per' => $persona->cod_per, 'email' => 'elena@example.test', 'est_usu' => 'ACTIVO']);
        Livewire::test(GestionUsuarios::class)->assertSee('Gestión de usuarios')->assertSee('Mostrar 50 usuarios por página')
            ->set('search', 'Elena Vargas')->assertSee('Elena Vargas')->assertDontSee('prueba@example.test')
            ->set('perPage', 999)->assertSet('perPage', 10)->assertHasErrors(['perPage']);
    }

    public function test_cuenta_sin_persona_no_oculta_personas_disponibles(): void
    {
        $persona = $this->persona();
        $componente = Livewire::test(GestionUsuarios::class);
        $this->assertTrue($componente->instance()->personasDisponibles->contains('cod_per', $persona->cod_per));
    }

    public function test_docente_conserva_relaciones_de_planes_del_esquema_en_uso(): void
    {
        $docente = new Docente;
        $asignaturas = $docente->planAsignaturas()->getRelated();
        $especialidades = $docente->planEspecialidades()->getRelated();
        foreach (['curso', 'paralelo', 'turno', 'gestionAcademica'] as $relacion) {
            $this->assertInstanceOf(BelongsTo::class, $asignaturas->{$relacion}());
            $this->assertInstanceOf(BelongsTo::class, $especialidades->{$relacion}());
        }
        $this->assertInstanceOf(BelongsTo::class, $especialidades->especialidad());
    }

    public function test_personal_detecta_gestion_activa_sin_elegir_entre_dos_activas(): void
    {
        Schema::create('gestion_academica', function (Blueprint $tabla) {
            $tabla->string('cod_gea')->primary();
            $tabla->string('est_gea');
        });
        $metodo = new \ReflectionMethod(PersonalInstitucional::class, 'obtenerGestionAcademicaPorDefecto');
        $componente = new PersonalInstitucional;
        $this->assertNull($metodo->invoke($componente));
        DB::table('gestion_academica')->insert(['cod_gea' => 'GESTION_PRUEBA', 'est_gea' => 'ACTIVO']);
        $this->assertSame('GESTION_PRUEBA', $metodo->invoke($componente)?->cod_gea);
        DB::table('gestion_academica')->where('cod_gea', 'GESTION_PRUEBA')->update(['est_gea' => 'ACTIVA']);
        $this->assertSame('GESTION_PRUEBA', $metodo->invoke($componente)?->cod_gea);
        DB::table('gestion_academica')->insert(['cod_gea' => 'SEGUNDA_PRUEBA', 'est_gea' => 'ACTIVO']);
        $this->assertNull($metodo->invoke($componente));
    }

    public function test_indicadores_usuarios_respetan_filtros_y_no_exponen_credenciales(): void
    {
        Schema::table('users', fn (Blueprint $tabla) => $tabla->timestamp('email_verified_at')->nullable());
        $persona = $this->persona(['nom_per' => 'Elena']);
        DB::table('users')->insert(['cod_usu' => 'ELENA', 'cod_per' => $persona->cod_per, 'email' => 'elena@example.test', 'est_usu' => 'INACTIVO']);
        $datos = app(IndicadoresUsuarios::class)->analizar(User::where('cod_usu', 'ELENA'));
        $this->assertSame(1, $datos['total']);
        $this->assertSame([0, 1, 0], $datos['estados']['data']);
        $this->assertSame([0, 1], $datos['correos']['data']);
        $this->assertStringNotContainsString('password', json_encode($datos));
        $vacio = app(IndicadoresUsuarios::class)->analizar(User::where('cod_usu', 'NO_EXISTE'));
        $this->assertSame(0, $vacio['total']);
        $this->assertSame([0, 0, 0], $vacio['estados']['data']);
    }

    private function prepararCorreoPrueba(): User
    {
        Schema::table('users', fn (Blueprint $tabla) => $tabla->string('password')->nullable());
        Schema::create('password_reset_tokens', function (Blueprint $tabla) {
            $tabla->string('email')->primary();
            $tabla->string('token');
            $tabla->timestamp('created_at')->nullable();
        });
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.username' => 'institucion@example.test', 'mail.mailers.smtp.password' => 'credencial-prueba',
            'acceso-usuarios.url_publica' => 'https://savp.example.test']);
        Notification::fake();
        $usuario = User::findOrFail('PRUEBA_DISENO');
        $usuario->update(['password' => 'ClaveOriginal#123']);

        return $usuario->fresh();
    }

    public function test_enlace_se_envia_sin_cambiar_password_y_limita_reintentos(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $hash = $usuario->password;
        $servicio = app(EnvioAccesoUsuario::class);
        $this->assertTrue($servicio->enviar($usuario)['enviado']);
        Notification::assertSentTo($usuario, InvitacionAccesoUsuario::class, function ($aviso) use ($usuario) {
            $this->assertTrue(Password::broker()->tokenExists($usuario, $aviso->token));
            $correo = $aviso->toMail($usuario);
            $this->assertStringStartsWith('https://savp.example.test/', $correo->actionUrl);
            $this->assertStringNotContainsString('ClaveOriginal', json_encode($correo->toArray()));

            return true;
        });
        $this->assertSame($hash, $usuario->fresh()->password);
        $this->assertFalse($servicio->enviar($usuario)['enviado']);
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_enlace_expira_y_se_utiliza_una_sola_vez(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $broker = Password::broker();
        $token = $broker->createToken($usuario);
        $this->travel(61)->minutes();
        $this->assertFalse($broker->tokenExists($usuario, $token));
        $token = $broker->createToken($usuario);
        $estado = $broker->reset(['email' => $usuario->email, 'token' => $token, 'password' => 'NuevaClave#123', 'password_confirmation' => 'NuevaClave#123'], fn ($destino, $password) => $destino->update(['password' => $password]));
        $this->assertSame(Password::PASSWORD_RESET, $estado);
        $this->assertFalse($broker->tokenExists($usuario, $token));
        $this->assertTrue(Hash::check('NuevaClave#123', $usuario->fresh()->password));
    }

    public function test_correo_log_y_url_local_no_simulan_envio_real(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $servicio = app(EnvioAccesoUsuario::class);
        config(['mail.default' => 'log']);
        $this->assertFalse($servicio->enviar($usuario)['enviado']);
        config(['mail.default' => 'smtp', 'acceso-usuarios.url_publica' => 'http://localhost:9000']);
        $this->assertFalse($servicio->enviar($usuario)['enviado']);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        Notification::assertNothingSent();
    }

    public function test_envio_fallido_conserva_cuenta_y_permita_reintento(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $hash = $usuario->password;
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Error SMTP'));
        $resultado = app(EnvioAccesoUsuario::class)->enviar($usuario);
        $this->assertFalse($resultado['enviado']);
        $this->assertStringNotContainsString('SMTP', $resultado['mensaje']);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertSame($hash, $usuario->fresh()->password);
    }

    public function test_cuenta_inactiva_no_recibe_enlace_y_creacion_bloqueada_no_escribe(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $usuario->update(['est_usu' => 'INACTIVO']);
        $this->assertFalse(app(EnvioAccesoUsuario::class)->enviar($usuario)['enviado']);
        $usuario->update(['est_usu' => 'ACTIVO']);
        config(['mail.default' => 'log']);
        $persona = $this->persona();
        Livewire::test(GestionUsuarios::class)->call('abrirModalCrear')
            ->set('form.cod_per', $persona->cod_per)->set('form.email', 'nueva@example.test')->set('form.role', 'Estudiante')
            ->set('correoEntrega', 'personal@example.test')->set('entregaAutorizada', true)->set('motivoCorreo', 'La persona autorizó otro correo de entrega.')
            ->call('guardarUsuario')->assertHasErrors(['general']);
        $this->assertDatabaseCount('users', 1);
        Notification::assertNothingSent();
    }

    public function test_enviar_invitacion_requiere_actor_administrador(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        DB::table('model_has_roles')->where('cod_usu', $usuario->cod_usu)->update(['role_id' => 2]);
        $usuario->unsetRelation('roles');
        $this->actingAs($usuario);
        $componente = new GestionUsuarios;
        $componente->cuentaInvitar = $usuario->cod_usu;
        try {
            $componente->enviarInvitacion();
            $this->fail('Una cuenta sin actor administrador no debe enviar enlaces.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        Notification::assertNothingSent();
    }

    public function test_correo_institucional_normaliza_nombres_y_detecta_gmail_equivalente(): void
    {
        $persona = $this->persona(['nom_per' => 'Ana María', 'ape_pat_per' => 'Pérez', 'ape_mat_per' => 'Ñúñez', 'ema_per' => 'ana.personal@example.test']);
        $soporte = app(CorreoInstitucional::class);
        $this->assertSame('uft3.anamaria.perez.nu@gmail.com', $soporte->sugerir($persona));
        Livewire::test(GestionUsuarios::class)->call('abrirModalCrear')->set('form.cod_per', $persona->cod_per)
            ->assertSet('form.email', 'uft3.anamaria.perez.nu@gmail.com')->assertSet('correoEntrega', 'ana.personal@example.test')
            ->assertSet('entregaAutorizada', false);
        DB::table('users')->insert(['cod_usu' => 'COLISION', 'email' => 'uft3anamariapereznu@gmail.com', 'est_usu' => 'ACTIVO']);
        $this->assertTrue($soporte->ocupado('uft3.anamaria.perez.nu@gmail.com'));
        $this->assertFalse($soporte->ocupado('uft3.anamaria.perez.nu@gmail.com', 'COLISION'));
        $persona->ape_mat_per = null;
        $this->assertSame('', $soporte->sugerir($persona));
        DB::table('users')->insert(['cod_usu' => 'FIJO', 'email' => 'uft3.ana.perez.nu@gmail.com', 'est_usu' => 'ACTIVO']);
        try {
            User::find('FIJO')->update(['email' => 'otro@example.test']);
            $this->fail('El correo institucional debe permanecer fijo también fuera del formulario.');
        } catch (ValidationException) {
            $this->assertSame('uft3.ana.perez.nu@gmail.com', User::find('FIJO')->email);
        }
    }

    public function test_destinatario_alternativo_exige_permiso_y_motivo_antes_de_enviar(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $persona = $this->persona(['ema_per' => 'personal@example.test']);
        $usuario->update(['cod_per' => $persona->cod_per]);
        $componente = Livewire::test(GestionUsuarios::class)->call('prepararInvitacion', $usuario->cod_usu)
            ->set('motivoPassword', 'La administración solicita recuperar el acceso de la persona.')->set('correoEntrega', 'alternativo@example.test')->call('enviarInvitacion')
            ->assertHasErrors(['entregaAutorizada', 'motivoCorreo']);
        $componente->set('entregaAutorizada', true)->set('motivoCorreo', 'corto')->call('enviarInvitacion')->assertHasErrors(['motivoCorreo']);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        Notification::assertNothingSent();
    }

    public function test_entrega_alternativa_conserva_identidad_de_acceso_y_bitacora_del_motivo(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        $persona = $this->persona(['nom_per' => 'Ana María', 'ema_per' => 'personal@example.test']);
        $usuario->update(['cod_per' => $persona->cod_per]);
        Schema::create('bitacora', function (Blueprint $tabla) {
            $tabla->string('cod_bit')->primary();
            foreach (['cod_usu', 'rol_bit', 'acc_bit', 'mod_bit', 'tab_bit', 'reg_bit', 'nom_reg_bit', 'des_bit', 'niv_bit', 'res_bit', 'ip_bit', 'age_bit', 'rut_bit', 'met_bit', 'val_ant_bit', 'val_nue_bit', 'err_bit'] as $campo) {
                $tabla->text($campo)->nullable();
            }
            $tabla->timestamp('fec_bit')->nullable();
        });
        $motivo = 'La persona autorizó su correo alternativo porque perdió el acceso al personal.';
        Livewire::test(GestionUsuarios::class)->call('prepararInvitacion', $usuario->cod_usu)
            ->set('motivoPassword', 'La administración solicita recuperar el acceso de la persona.')->set('correoEntrega', 'alternativo@example.test')->set('entregaAutorizada', true)->set('motivoCorreo', $motivo)
            ->call('enviarInvitacion')->assertHasNoErrors()->assertSet('cuentaInvitar', null);
        Notification::assertSentOnDemand(InvitacionAccesoUsuario::class, function ($aviso, $canales, $destino) use ($usuario) {
            $this->assertSame('alternativo@example.test', $destino->routes['mail']);
            $this->assertTrue(Password::broker()->tokenExists($usuario, $aviso->token));
            $correo = $aviso->toMail($destino);
            $this->assertStringContainsString(urlencode($usuario->email), $correo->actionUrl);
            $html = (string) app(Markdown::class)->render($correo->markdown, $correo->viewData);
            $this->assertStringContainsString('Ana María', $html);
            $this->assertStringContainsString($usuario->email, $html);
            $this->assertStringContainsString('alternativo@example.test', $html);
            $this->assertStringNotContainsString('ClaveOriginal', $html);

            return true;
        });
        $bitacora = Bitacora::where('acc_bit', 'SOLICITAR_ENLACE_ACCESO')->firstOrFail();
        $this->assertStringContainsString($motivo, $bitacora->des_bit);
        $this->assertSame('alternativo@example.test', $bitacora->val_nue_bit['correo_entrega']);
        $this->assertStringNotContainsString('token', json_encode($bitacora->toArray()));
        $this->assertSame($usuario->email, $usuario->fresh()->email);
    }

    public function test_cambiar_identificador_de_acceso_sin_motivo_no_escribe(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        Livewire::test(GestionUsuarios::class)->call('abrirModalEditar', $usuario->cod_usu)
            ->set('formEditar.email', 'otra@example.test')->call('guardarEdicionUsuario')->assertHasErrors(['formEditar.email']);
        $this->assertSame($usuario->email, $usuario->fresh()->email);
    }

    public function test_url_local_autorizada_aparece_en_la_bienvenida(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        config(['acceso-usuarios.url_publica' => 'http://localhost:8000']);
        $this->assertNull(app(EnvioAccesoUsuario::class)->bloqueoConfiguracion());
        $aviso = new InvitacionAccesoUsuario('token-prueba', $usuario->email, 'Ana', 'personal@example.test');
        $correo = $aviso->toMail($usuario);
        $this->assertStringStartsWith('http://localhost:8000/', $correo->actionUrl);
        $html = (string) app(Markdown::class)->render($correo->markdown, $correo->viewData);
        $this->assertStringContainsString('Elegir mi contraseña', $html);
        $this->assertStringContainsString('http://localhost:8000/login', $html);
    }

    private function prepararBitacora(): void
    {
        Schema::create('bitacora', function (Blueprint $tabla) {
            $tabla->string('cod_bit')->primary();
            foreach (['cod_usu', 'rol_bit', 'acc_bit', 'mod_bit', 'tab_bit', 'reg_bit', 'nom_reg_bit', 'des_bit', 'niv_bit', 'res_bit', 'ip_bit', 'age_bit', 'rut_bit', 'met_bit', 'val_ant_bit', 'val_nue_bit', 'err_bit'] as $campo) {
                $tabla->text($campo)->nullable();
            }
            $tabla->timestamp('fec_bit')->nullable();
        });
    }

    public function test_no_se_autodesactiva_por_edicion_y_password_exige_motivo(): void
    {
        $usuario = $this->prepararCorreoPrueba();
        Livewire::test(GestionUsuarios::class)->call('abrirModalEditar', $usuario->cod_usu)
            ->set('formEditar.est_usu', 'INACTIVO')->set('motivoEstado', 'No debe poder desactivarse por esta vía.')
            ->call('guardarEdicionUsuario')->assertHasErrors(['formEditar.est_usu']);
        $this->assertSame('ACTIVO', $usuario->fresh()->est_usu);
        Livewire::test(GestionUsuarios::class)->call('abrirModalEditar', $usuario->cod_usu)
            ->set('formEditar.password', 'SeguraNueva#123')->set('formEditar.password_confirmation', 'SeguraNueva#123')
            ->call('guardarEdicionUsuario')->assertHasErrors(['motivoPassword']);
        $this->assertTrue(Hash::check('ClaveOriginal#123', $usuario->fresh()->password));
    }

    public function test_desactivacion_masiva_exige_motivo_y_audita_nombres_o_cantidad_real(): void
    {
        $this->prepararBitacora();
        $codigos = [];
        foreach (['Elena', 'Mario', 'Juana', 'Pedro'] as $i => $nombre) {
            $persona = $this->persona(['nom_per' => $nombre]);
            $codigo = 'LOTE_'.$i;
            DB::table('users')->insert(['cod_usu' => $codigo, 'cod_per' => $persona->cod_per, 'email' => $nombre.'@example.test', 'est_usu' => 'ACTIVO']);
            DB::table('model_has_roles')->insert(['role_id' => 2, 'model_type' => User::class, 'cod_usu' => $codigo]);
            $codigos[] = $codigo;
        }
        $componente = new GestionUsuarios;
        $componente->selected = array_slice($codigos, 0, 2);
        $componente->accionLote = 'inactivar';
        try {
            $componente->aplicarAccionLote();
            $this->fail('Sin motivo no se debe desactivar.');
        } catch (ValidationException) {
            $this->assertSame(0, User::where('est_usu', 'INACTIVO')->count());
        }
        $motivo = 'Incorporación suspendida por decisión de la dirección.';
        $componente->aplicarAccionLote($motivo);
        $descripcion = Bitacora::where('acc_bit', 'DESACTIVAR_USUARIOS_LOTE')->firstOrFail()->des_bit;
        $this->assertStringContainsString('Elena', $descripcion);
        $this->assertStringContainsString('Mario', $descripcion);
        $this->assertStringContainsString($motivo, $descripcion);
        DB::table('users')->whereIn('cod_usu', $codigos)->update(['est_usu' => 'ACTIVO']);
        $componente->selected = array_merge($codigos, ['PRUEBA_DISENO']);
        $componente->accionLote = 'inactivar';
        $componente->aplicarAccionLote($motivo);
        $bitacora = Bitacora::where('acc_bit', 'DESACTIVAR_USUARIOS_LOTE')->orderByDesc('cod_bit')->firstOrFail();
        $this->assertStringContainsString('4 cuentas', $bitacora->des_bit);
        $this->assertStringNotContainsString('Elena', $bitacora->des_bit);
        $this->assertSame(4, $bitacora->val_nue_bit['total_afectados']);
        $this->assertSame('ACTIVO', User::find('PRUEBA_DISENO')->est_usu);
    }
}
