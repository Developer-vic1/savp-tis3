<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Fortify\Features;
use Tests\TestCase;

/** Render HTTP real; no certifica credenciales, tokens válidos ni escritura. */
class FortifyScreenTest extends TestCase
{
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }
        $this->get('/forgot-password')->assertOk()->assertSee('email', false);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }
        // El controlador muestra el formulario; validar el token corresponde al POST persistente.
        $this->get('/reset-password/SCREEN_ONLY?email=test%40example.com')
            ->assertOk()->assertSee('SCREEN_ONLY')->assertSee('test@example.com');
    }

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = new User;
        $user->forceFill(['cod_usu' => 'SCREEN_ONLY', 'est_usu' => 'ACTIVO', 'email_verified_at' => now()]);
        $this->actingAs($user)->get('/user/confirm-password')->assertOk()->assertSee('password', false);
    }
}
