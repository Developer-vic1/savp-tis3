<?php

namespace App\Providers;

use App\Contracts\KardexRepository;
use App\Contracts\SpecializedAcademicClient;
use App\Livewire\InstitutionalAuthorization;
use App\Models\Oficial\Academico\AsistenciaClase;
use App\Models\Oficial\AulaVirtual\ClaseVirtual;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\AulaVirtual\MaterialClase;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionResultado;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\DocumentoInscripcionEstudiante;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\ReporteGenerado;
use App\Models\Oficial\Sistema\User;
use App\Policies\AulaVirtualAsistenciaPolicy;
use App\Policies\AulaVirtualCursoPolicy;
use App\Policies\AulaVirtualEntregaPolicy;
use App\Policies\AulaVirtualMaterialPolicy;
use App\Policies\AulaVirtualTareaPolicy;
use App\Policies\CalificacionPolicy;
use App\Policies\CursoPolicy;
use App\Policies\DocumentoInscripcionEstudiantePolicy;
use App\Policies\EstudiantePolicy;
use App\Policies\InscripcionEstudiantePolicy;
use App\Policies\OrientacionResultadoPolicy;
use App\Policies\PersonaPolicy;
use App\Policies\ReporteGeneradoPolicy;
use App\Policies\UserPolicy;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\Kardex\ScopedKardexRepository;
use App\Support\LegacyReadPermission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\AccesoProgramadoService::class);
        $this->app->bind(SpecializedAcademicClient::class, AporteIngenierilClient::class);
        $this->app->bind(KardexRepository::class, ScopedKardexRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\App\Models\Oficial\Sistema\PersonalAccessToken::class);
        Livewire::componentHook(InstitutionalAuthorization::class);
        $this->loadMigrationsFrom([
            database_path('migrations/Sistema'),
            database_path('migrations/Academico'),
            database_path('migrations/AulaVirtual'),
            database_path('migrations/AporteAcademicoVocacional'),
        ]);

        // El catálogo histórico permite consultar la matriz aunque los permisos
        // de gobernanza nuevos todavía no se hayan aplicado a la base de datos.
        Gate::define('roles-permisos.ver', fn (User $user): bool =>
            $user->est_usu === 'ACTIVO'
            && $user->hasRole('Administrador')
            && ($user->can('roles-permisos.gestionar') || $user->can('Gestion_Roles_Permisos'))
        );

        foreach (array_keys(LegacyReadPermission::FALLBACKS) as $permission) {
            Gate::define($permission, fn (User $user): bool => app(LegacyReadPermission::class)->allows($user, $permission));
        }

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Persona::class, PersonaPolicy::class);
        Gate::policy(Estudiante::class, EstudiantePolicy::class);
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'App\\Models\\User' => \App\Models\Oficial\Sistema\User::class,
        ]);
        Gate::policy(Curso::class, CursoPolicy::class);
        Gate::policy(InscripcionEstudiante::class, InscripcionEstudiantePolicy::class);
        Gate::policy(DocumentoInscripcionEstudiante::class, DocumentoInscripcionEstudiantePolicy::class);
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
