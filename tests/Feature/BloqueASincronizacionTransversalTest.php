<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BloqueASincronizacionTransversalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_timezone_bolivia_y_frontera_horaria_utc(): void
    {
        $timezoneConfig = config('app.timezone');
        $this->assertSame('America/La_Paz', $timezoneConfig);

        // En La Paz son las 23:30 del 2026-09-03 -> en UTC son las 03:30 del 2026-09-04
        $horaBolivia = Carbon::create(2026, 9, 3, 23, 30, 0, 'America/La_Paz');
        $horaUtc = $horaBolivia->copy()->setTimezone('UTC');

        $this->assertSame('2026-09-03', $horaBolivia->toDateString());
        $this->assertSame('2026-09-04', $horaUtc->toDateString());
        $this->assertSame('America/La_Paz', $horaBolivia->timezoneName);
    }

    public function test_autoregistro_publico_desactivado_retorna_404(): void
    {
        $this->assertFalse(Route::has('register'));
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    public function test_rutas_administrativas_requieren_autenticacion_y_permiso(): void
    {
        // Invitado es redirigido a login
        $response = $this->get(route('admin.gestion-usuarios'));
        $response->assertRedirect(route('login'));

        // Usuario sin permiso recibe 403
        $usuarioSinPermiso = User::factory()->create([
            'cod_usu' => 'USU_TEST_NOPOS',
            'email' => 'sinpermiso@savp.edu.bo',
            'email_verified_at' => now(),
        ]);

        $responseAuthed = $this->actingAs($usuarioSinPermiso)->get(route('admin.gestion-usuarios'));
        $responseAuthed->assertStatus(403);
    }
}
