<?php

namespace App\Providers;

use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\Tarea;
use App\Policies\AsistenciaClasePolicy;
use App\Policies\ClaseVirtualPolicy;
use App\Policies\EntregaTareaPolicy;
use App\Policies\MaterialClasePolicy;
use App\Policies\TareaPolicy;
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
        $this->loadMigrationsFrom(database_path('migrations/aula_virtual'));

        // Registro explícito de Policies del Aula Virtual
        Gate::policy(ClaseVirtual::class, ClaseVirtualPolicy::class);
        Gate::policy(Tarea::class, TareaPolicy::class);
        Gate::policy(EntregaTarea::class, EntregaTareaPolicy::class);
        Gate::policy(MaterialClase::class, MaterialClasePolicy::class);
        Gate::policy(AsistenciaClase::class, AsistenciaClasePolicy::class);
    }
}
