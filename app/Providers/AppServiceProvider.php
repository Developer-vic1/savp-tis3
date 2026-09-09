<?php

namespace App\Providers;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\Tarea;
use App\Models\CalendarioEvento;
use App\Models\InscripcionEstudiante;
use App\Policies\AsistenciaClasePolicy;
use App\Policies\CalendarioEventoPolicy;
use App\Policies\ClaseVirtualPolicy;
use App\Policies\EntregaTareaPolicy;
use App\Policies\InscripcionEstudiantePolicy;
use App\Policies\MaterialClasePolicy;
use App\Policies\TareaPolicy;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Migraciones del Aula Virtual
        |--------------------------------------------------------------------------
        */

        $this->loadMigrationsFrom(
            database_path('migrations/aula_virtual')
        );

        /*
        |--------------------------------------------------------------------------
        | Policies
        |--------------------------------------------------------------------------
        */

        Gate::policy(
            ClaseVirtual::class,
            ClaseVirtualPolicy::class
        );

        Gate::policy(
            Tarea::class,
            TareaPolicy::class
        );

        Gate::policy(
            EntregaTarea::class,
            EntregaTareaPolicy::class
        );

        Gate::policy(
            MaterialClase::class,
            MaterialClasePolicy::class
        );

        Gate::policy(
            AsistenciaClase::class,
            AsistenciaClasePolicy::class
        );

        Gate::policy(
            CalendarioEvento::class,
            CalendarioEventoPolicy::class
        );

        Gate::policy(
            InscripcionEstudiante::class,
            InscripcionEstudiantePolicy::class
        );

        /*
        |--------------------------------------------------------------------------
        | Datos del buscador del Topbar
        |--------------------------------------------------------------------------
        |
        | Solamente se ejecuta cuando se renderiza el topbar del Aula Virtual.
        | Los cursos se filtran previamente mediante CursoVirtualService.
        |
        */

        View::composer(
            'aula-virtual.layouts.topbar',
            function ($view): void {
                $user = auth()->user();

                if (! $user) {
                    $view->with(
                        'cursosBusqueda',
                        collect()
                    );

                    return;
                }

                try {
                    $servicio = app(
                        CursoVirtualService::class
                    );

                    $cursos = $servicio
                        ->cursosParaBuscador($user)
                        ->map(function (array $curso) {
                            /*
                            |------------------------------------------
                            | Ruta según contexto
                            |------------------------------------------
                            */

                            if ($curso['contexto'] === 'Docente') {
                                $routeName =
                                    'aula-virtual.docente.curso';
                            } else {
                                $routeName =
                                    'aula-virtual.estudiante.curso';
                            }

                            return [
                                ...$curso,

                                'url' => route(
                                    $routeName,
                                    [
                                        'curso' => $curso['cod_cla'],
                                    ]
                                ),
                            ];
                        });

                    $view->with(
                        'cursosBusqueda',
                        $cursos
                    );
                } catch (Throwable $exception) {
                    /*
                    |--------------------------------------------------------------------------
                    | El buscador nunca debe romper todo el Aula Virtual
                    |--------------------------------------------------------------------------
                    */

                    Log::warning(
                        'No fue posible preparar los cursos del buscador del Aula Virtual.',
                        [
                            'cod_usu' => $user->cod_usu ?? null,
                            'exception' => $exception,
                        ]
                    );

                    $view->with(
                        'cursosBusqueda',
                        collect()
                    );
                }
            }
        );
    }
}
