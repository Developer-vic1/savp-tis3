<?php

namespace Tests\Feature;

use App\Livewire\Shared\InstitutionalRecordDrawer;
use App\Models\User;
use App\Services\InstitutionalQueryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SecretaryCatalogDetailTest extends TestCase
{
    private function actor(string $role, bool $allowed = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_CATALOG', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->andReturn($allowed);

        return $user;
    }

    public function test_drawer_opens_closes_and_rechecks_permission_after_revocation(): void
    {
        $allowed = true;
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['cod_usu' => 'TEST_CATALOG', 'est_usu' => 'ACTIVO']);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === 'Secretaria');
        $user->shouldReceive('can')->andReturnUsing(function () use (&$allowed) {
            return $allowed;
        });
        $this->actingAs($user);
        $service = Mockery::mock(InstitutionalQueryService::class)->makePartial();
        $service->shouldReceive('secretaryDetail')->with($user, 'cursos', 'CUR1', 'GEA1')->andReturn([
            'title' => 'Curso autorizado', 'fields' => ['Código' => 'CUR1', 'Estado' => 'ACTIVO'],
            'items' => collect(), 'itemsTitle' => 'Oferta registrada en planes', 'truncated' => false,
        ]);
        $this->app->instance(InstitutionalQueryService::class, $service);
        $drawer = Livewire::test(InstitutionalRecordDrawer::class, ['area' => 'cursos'])
            ->call('open', 'CUR1', 'GEA1')->assertSet('recordId', 'CUR1')
            ->assertSee('Curso autorizado')->assertSee('No hay registros para este contexto.')
            ->assertDontSee('Editar')->call('close')->assertSet('recordId', null)
            ->assertDontSee('Curso autorizado');
        $allowed = false;
        $drawer->call('open', 'CUR1', 'GEA1')->assertForbidden();
    }

    public function test_foreign_actor_cannot_load_minimal_details_even_with_module_permission(): void
    {
        $this->expectException(HttpException::class);
        (new InstitutionalQueryService)->secretaryDetail($this->actor('Regente'), 'cursos', 'CUR1');
    }

    public function test_secretary_cannot_request_pedagogical_detail_through_catalog_drawer(): void
    {
        $this->expectException(HttpException::class);
        (new InstitutionalQueryService)->secretaryDetail($this->actor('Secretaria'), 'rendimiento', 'CAL1');
    }

    public static function invalidFilters(): array
    {
        return [['estado', 'INVALIDO'], ['desde', '25:70'], ['hasta', '06:00']];
    }

    #[DataProvider('invalidFilters')]
    public function test_invalid_shift_filters_stop_before_reading_models(string $field, string $value): void
    {
        $request = Request::create('/secretaria/turnos', 'GET', [$field => $value, 'desde' => $field === 'desde' ? $value : '08:00']);
        $request->setUserResolver(fn () => $this->actor('Secretaria'));
        $this->expectException(ValidationException::class);
        (new InstitutionalQueryService)->search($request,'turnos','secretaria');
    }
}
