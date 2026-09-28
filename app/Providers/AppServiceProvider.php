<?php

namespace App\Providers;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\OrientacionResultado;
use App\Models\AulaVirtual\Tarea;
use App\Models\Calificacion;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\ReporteGenerado;
use App\Models\User;
use App\Policies\AulaVirtualAsistenciaPolicy;
use App\Policies\AulaVirtualCursoPolicy;
use App\Policies\AulaVirtualEntregaPolicy;
use App\Policies\AulaVirtualMaterialPolicy;
use App\Policies\AulaVirtualTareaPolicy;
use App\Policies\CalificacionPolicy;
use App\Policies\CursoPolicy;
use App\Policies\EstudiantePolicy;
use App\Policies\InscripcionEstudiantePolicy;
use App\Policies\OrientacionResultadoPolicy;
use App\Policies\ReporteGeneradoPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        \Livewire\Livewire::componentHook(\App\Livewire\InstitutionalAuthorization::class);
        $this->loadMigrationsFrom(database_path('migrations/aula_virtual'));

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Estudiante::class, EstudiantePolicy::class);
        Gate::policy(Curso::class, CursoPolicy::class);
        Gate::policy(InscripcionEstudiante::class, InscripcionEstudiantePolicy::class);
        Gate::policy(Calificacion::class, CalificacionPolicy::class);
        Gate::policy(ReporteGenerado::class, ReporteGeneradoPolicy::class);
        Gate::policy(ClaseVirtual::class, AulaVirtualCursoPolicy::class);
        Gate::policy(AsistenciaClase::class, AulaVirtualAsistenciaPolicy::class);
        Gate::policy(MaterialClase::class, AulaVirtualMaterialPolicy::class);
        Gate::policy(Tarea::class, AulaVirtualTareaPolicy::class);
        Gate::policy(EntregaTarea::class, AulaVirtualEntregaPolicy::class);
        Gate::policy(OrientacionResultado::class, OrientacionResultadoPolicy::class);
    }
}
