<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\GestionEstudiantesController;
use App\Http\Controllers\Admin\GestionPersonaController;
use App\Http\Controllers\Admin\GestionPersonalInstitucional;
use App\Http\Controllers\Admin\GestionUsuarioController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\GestionAcademicaController;
use App\Http\Controllers\Admin\GestionCursosController;
use App\Http\Controllers\Admin\GestionAsignaturaController;
use App\Http\Controllers\Admin\GestionParaleloController;
use App\Http\Controllers\Admin\GestionTurnoController;
use App\Http\Controllers\Admin\GestionInscripcionController;
use App\Http\Controllers\Admin\EspecialidadesTecnicasController;
use App\Http\Controllers\Admin\PeriodoEvaluacionController;
use App\Http\Controllers\Admin\PlanesAsignaturaController;
use App\Http\Controllers\Admin\GestionDocenteController;
use App\Http\Controllers\Admin\InstitucionProcedenciaController;
use App\Http\Controllers\Admin\TipoVinculacionEstudianteController;
use App\Http\Controllers\Admin\CalificacionController;
use App\Http\Controllers\Admin\ReporteAcademicoController;
use App\Http\Controllers\Admin\ReporteAdministrativoController;
use App\Http\Controllers\Admin\ReportePdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Dashboard principal
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->get('/dashboard', function () {
    $user = Auth::user();

    if ($user->canAny([
        'Panel_Administrador',
        'Panel_Director',
        'Panel_Docente',
        'Panel_Estudiante',
        'Panel_Secretaria',
        'Panel_Regente',
    ])) {
        return app(AdminDashboardController::class)->index();
    }

    abort(403, 'No tienes un panel asignado.');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas de administración
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/gestion-usuarios', [GestionUsuarioController::class, 'index'])
        ->name('gestion-usuarios')
        ->middleware('can:Gestion_Usuarios');

    Route::get('/gestion-personas', [GestionPersonaController::class, 'index'])
        ->name('gestion-personas')
        ->middleware('can:Registro_Personas');

    Route::get('/personal-institucional', [GestionPersonalInstitucional::class, 'index'])
        ->name('personal-institucional')
        ->middleware('can:Personal_Institucional');

    Route::get('/gestion-estudiantes', [GestionEstudiantesController::class, 'index'])
        ->name('gestion-estudiantes')
        ->middleware('can:Estudiantes');

    Route::get('/bitacora', [BitacoraController::class, 'index'])
        ->name('bitacora')
        ->middleware('can:Bitacora');

    Route::get('/gestion-academica', [GestionAcademicaController::class, 'index'])
        ->name('gestion-academica')
        ->middleware('can:Gestion_Academica');

    Route::get('/gestion-cursos', [GestionCursosController::class, 'index'])
        ->name('gestion-cursos')
        ->middleware('can:Cursos');

    Route::get('/gestion-asignaturas', [GestionAsignaturaController::class, 'index'])
        ->name('gestion-asignaturas')
        ->middleware('can:Asignaturas');

    Route::get('/gestion-paralelos', [GestionParaleloController::class, 'index'])
        ->name('gestion-paralelos')
        ->middleware('can:Paralelos');

    Route::get('/gestion-turnos', [GestionTurnoController::class, 'index'])
        ->name('gestion-turnos')
        ->middleware('can:Turnos');

    Route::get('/gestion-inscripciones', [GestionInscripcionController::class, 'index'])
        ->name('gestion-inscripciones')
        ->middleware('can:Inscripciones');

    Route::get('/especialidades-tecnicas', [EspecialidadesTecnicasController::class, 'index'])
        ->name('especialidades-tecnicas')
        ->middleware('can:Especialidades_Tecnicas');

    Route::get('/periodo-evaluacion', [PeriodoEvaluacionController::class, 'index'])
        ->name('periodo-evaluacion')
        ->middleware('can:Periodo_Evaluacion');

    Route::get('/planes-asignatura', [PlanesAsignaturaController::class, 'index'])
        ->name('planes-asignatura')
        ->middleware('can:Planes_Asignatura');

    Route::get('/gestion-docentes', [GestionDocenteController::class, 'index'])
        ->name('gestion-docentes')
        ->middleware('can:Docentes');

    Route::get('/institucion-procedencia', [InstitucionProcedenciaController::class, 'index'])
        ->name('institucion-procedencia')
        ->middleware('can:Institucion_Procedencia');

    Route::get('/tipo-vinculacion-estudiante', [TipoVinculacionEstudianteController::class, 'index'])
        ->name('tipo-vinculacion-estudiante')
        ->middleware('can:Tipo_Vinculacion_Estudiante');

    Route::get('/calificaciones', [CalificacionController::class, 'index'])
        ->name('calificaciones')
        ->middleware('can:Calificaciones');

    Route::get('/reportes-academicos', [ReporteAcademicoController::class, 'index'])
        ->name('reportes-academicos')
        ->middleware('can:Reportes_Academicos');

    Route::get('/reportes-administrativos', [ReporteAdministrativoController::class, 'index'])
        ->name('reportes-administrativos')
        ->middleware('can:Reportes_Administrativos');

    // ──────────────────────────────────────────────────────────────────────
    // MÓDULO DE REPORTES PDF / SQL / ZIP
    // ──────────────────────────────────────────────────────────────────────
    Route::prefix('reportes')->name('reportes.')->middleware('can:Gestion_Academica')->group(function () {

        // Reportes académicos
        Route::get('/academico-general/pdf',    [ReportePdfController::class, 'academicoGeneral'])->name('academico-general.pdf');
        Route::get('/calificaciones/pdf',        [ReportePdfController::class, 'calificaciones'])->name('calificaciones.pdf');
        Route::get('/estudiantes-riesgo/pdf',    [ReportePdfController::class, 'estudiantesRiesgo'])->name('estudiantes-riesgo.pdf');

        // Reportes administrativos
        Route::get('/administrativo/pdf',        [ReportePdfController::class, 'administrativo'])->name('administrativo.pdf');
        Route::get('/bitacora/pdf',              [ReportePdfController::class, 'bitacora'])->name('bitacora.pdf');

        // Reportes vocacionales
        Route::get('/vocacional-riasec/pdf',     [ReportePdfController::class, 'vocacionalRiasec'])->name('vocacional-riasec.pdf');
        Route::get('/compatibilidad-carreras/pdf', [ReportePdfController::class, 'compatibilidadCarreras'])->name('compatibilidad-carreras.pdf');

        // Reporte institucional completo
        Route::get('/institucional-completo/pdf', [ReportePdfController::class, 'institucionalCompleto'])->name('institucional-completo.pdf');

        // Respaldo SQL y paquete ZIP
        Route::get('/respaldo-academico/sql',    [ReportePdfController::class, 'respaldoSql'])->name('respaldo-academico.sql');
        Route::get('/paquete/zip',               [ReportePdfController::class, 'paqueteZip'])->name('paquete.zip');
    });
});
