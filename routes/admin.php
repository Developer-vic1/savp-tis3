<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\CalificacionController;
use App\Http\Controllers\Admin\EspecialidadesTecnicasController;
use App\Http\Controllers\Admin\GestionAcademicaController;
use App\Http\Controllers\Admin\GestionAsignaturaController;
use App\Http\Controllers\Admin\GestionCursosController;
use App\Http\Controllers\Admin\GestionDocenteController;
use App\Http\Controllers\Admin\GestionEstudiantesController;
use App\Http\Controllers\Admin\GestionInscripcionController;
use App\Http\Controllers\Admin\GestionParaleloController;
use App\Http\Controllers\Admin\GestionPersonaController;
use App\Http\Controllers\Admin\GestionPersonalInstitucional;
use App\Http\Controllers\Admin\GestionTurnoController;
use App\Http\Controllers\Admin\GestionUsuarioController;
use App\Http\Controllers\Admin\InstitucionProcedenciaController;
use App\Http\Controllers\Admin\PeriodoEvaluacionController;
use App\Http\Controllers\Admin\PlanesAsignaturaController;
use App\Http\Controllers\Admin\ReporteAcademicoController;
use App\Http\Controllers\Admin\ReporteAdministrativoController;
use App\Http\Controllers\Admin\ReportePdfController;
use App\Http\Controllers\Admin\RoleRequestDocumentController;
use App\Http\Controllers\Admin\TipoVinculacionEstudianteController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\InstitutionalQueryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('actor:Administrador')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/lms-supervision', [InstitutionalQueryController::class, 'index'])->defaults('area', 'lms')->middleware('can:cursos.ver.institucional')->name('consulta');
    Route::view('/roles-permisos', 'admin.roles-permisos')->middleware('can:roles-permisos.gestionar')->name('roles-permisos');
    Route::get('/roles-permisos/solicitudes/{roleRequest}/documento', RoleRequestDocumentController::class)
        ->middleware(['can:roles-permisos.gestionar', 'can:roles.documentos.ver'])->name('roles-permisos.documento');
    Route::view('/asignaciones-regencia', 'admin.asignaciones-regencia')->middleware('can:regencia.asignaciones.gestionar')->name('asignaciones-regencia');

    $modules = [
        ['gestion-usuarios', GestionUsuarioController::class, 'gestion-usuarios', 'Gestion_Usuarios'],
        ['gestion-personas', GestionPersonaController::class, 'gestion-personas', 'Registro_Personas'],
        ['personal-institucional', GestionPersonalInstitucional::class, 'personal-institucional', 'Personal_Institucional'],
        ['gestion-estudiantes', GestionEstudiantesController::class, 'gestion-estudiantes', 'Estudiantes'],
        ['bitacora', BitacoraController::class, 'bitacora', 'Bitacora'],
        ['gestion-academica', GestionAcademicaController::class, 'gestion-academica', 'Gestion_Academica'],
        ['gestion-cursos', GestionCursosController::class, 'gestion-cursos', 'Cursos'],
        ['gestion-asignaturas', GestionAsignaturaController::class, 'gestion-asignaturas', 'Asignaturas'],
        ['gestion-paralelos', GestionParaleloController::class, 'gestion-paralelos', 'Paralelos'],
        ['gestion-turnos', GestionTurnoController::class, 'gestion-turnos', 'Turnos'],
        ['gestion-inscripciones', GestionInscripcionController::class, 'gestion-inscripciones', 'Inscripciones'],
        ['especialidades-tecnicas', EspecialidadesTecnicasController::class, 'especialidades-tecnicas', 'Especialidades_Tecnicas'],
        ['periodo-evaluacion', PeriodoEvaluacionController::class, 'periodo-evaluacion', 'Periodo_Evaluacion'],
        ['planes-asignatura', PlanesAsignaturaController::class, 'planes-asignatura', 'Planes_Asignatura'],
        ['gestion-docentes', GestionDocenteController::class, 'gestion-docentes', 'Docentes'],
        ['institucion-procedencia', InstitucionProcedenciaController::class, 'institucion-procedencia', 'Institucion_Procedencia'],
        ['tipo-vinculacion-estudiante', TipoVinculacionEstudianteController::class, 'tipo-vinculacion-estudiante', 'Tipo_Vinculacion_Estudiante'],
        ['calificaciones', CalificacionController::class, 'calificaciones', 'Calificaciones'],
        ['reportes-academicos', ReporteAcademicoController::class, 'reportes-academicos', 'Reportes_Academicos'],
        ['reportes-administrativos', ReporteAdministrativoController::class, 'reportes-administrativos', 'Reportes_Administrativos'],
    ];

    foreach ($modules as [$uri, $controller, $name, $permission]) {
        Route::get('/'.$uri, [$controller, 'index'])->name($name)->middleware('can:'.$permission);
    }

    Route::prefix('reportes')->name('reportes.')->middleware('can:Gestion_Academica')->group(function () {
        Route::get('/academico-general/pdf', [ReportePdfController::class, 'academicoGeneral'])->name('academico-general.pdf');
        Route::get('/calificaciones/pdf', [ReportePdfController::class, 'calificaciones'])->name('calificaciones.pdf');
        Route::get('/estudiantes-riesgo/pdf', [ReportePdfController::class, 'estudiantesRiesgo'])->name('estudiantes-riesgo.pdf');
        Route::get('/administrativo/pdf', [ReportePdfController::class, 'administrativo'])->name('administrativo.pdf');
        Route::get('/bitacora/pdf', [ReportePdfController::class, 'bitacora'])->name('bitacora.pdf');
        Route::get('/vocacional-riasec/pdf', [ReportePdfController::class, 'vocacionalRiasec'])->name('vocacional-riasec.pdf');
        Route::get('/compatibilidad-carreras/pdf', [ReportePdfController::class, 'compatibilidadCarreras'])->name('compatibilidad-carreras.pdf');
        Route::get('/institucional-completo/pdf', [ReportePdfController::class, 'institucionalCompleto'])->name('institucional-completo.pdf');
        Route::get('/respaldo-academico/sql', [ReportePdfController::class, 'respaldoSql'])->name('respaldo-academico.sql');
        Route::get('/paquete/zip', [ReportePdfController::class, 'paqueteZip'])->name('paquete.zip');
    });
});
