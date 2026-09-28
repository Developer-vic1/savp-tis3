<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WorkspaceRoutesTest extends TestCase
{
    public function test_workspace_links_reference_registered_routes(): void
    {
        $views = [
            'components/actor-menu', 'docente/calificaciones', 'estudiante/area',
            'livewire/admin/asignaciones-regencia', 'livewire/secretaria/cuentas',
            'aula-virtual/layouts/sidebar', 'components/menu',
        ];
        foreach ($views as $view) {
            preg_match_all("/route\('([^']+)'/", file_get_contents(resource_path('views/'.$view.'.blade.php')), $matches);
            foreach (array_unique($matches[1]) as $name) { $this->assertTrue(Route::has($name), $view.': '.$name); }
        }
    }

    public function test_account_and_grade_routes_keep_actor_boundaries(): void
    {
        foreach (['secretaria.cuentas' => 'Secretaria', 'docente.cursos.calificaciones.store' => 'Docente', 'admin.roles-permisos' => 'Administrador'] as $name => $role) {
            $this->assertContains('actor:'.$role, Route::getRoutes()->getByName($name)->gatherMiddleware());
        }
    }
}
