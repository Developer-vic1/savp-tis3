<?php

use App\Http\Controllers\AulaVirtual\CursoVirtualController;
use App\Http\Controllers\Estudiante\AsistenteEstudioController;
use App\Http\Controllers\Estudiante\ExperienciaEstudianteController;
use App\Http\Controllers\Estudiante\FuturoAcademicoController;
use App\Http\Controllers\Estudiante\InteresesController;
use App\Http\Controllers\Estudiante\PlanAcademicoController;
use App\Http\Controllers\Estudiante\PreparacionAcademicaController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('estudiante')->name('estudiante.')->middleware('actor:Estudiante')->group(function () {
    Route::get('/', [WorkspaceController::class, 'estudiante'])->name('dashboard');
    Route::redirect('/materias', '/aula-virtual/mis-asignaturas')->middleware('can:Aula_Virtual_Estudiante')->name('materias');
    Route::get('/materias/{curso}', [CursoVirtualController::class, 'showEstudiante'])
        ->middleware(['can:Acceso_Aula_Virtual', 'can:Aula_Virtual_Estudiante'])->name('materia');
    Route::redirect('/asistencia', '/aula-virtual/mi-asistencia')->name('asistencia');
    Route::get('/intereses', [InteresesController::class, 'index'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('intereses');
    Route::post('/intereses', [InteresesController::class, 'guardar'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('intereses.guardar');
    Route::post('/intereses/analizar', [InteresesController::class, 'analizar'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('intereses.analizar');
    Route::get('/futuro', [FuturoAcademicoController::class, 'index'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('futuro');
    Route::get('/preparacion', [PreparacionAcademicaController::class, 'index'])
        ->middleware('can:Perfil_Academico')->name('preparacion');
    Route::get('/plan', [PlanAcademicoController::class, 'index'])
        ->middleware('can:Perfil_Academico')->name('plan');
    Route::get('/asistente', [AsistenteEstudioController::class, 'index'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('asistente');
    Route::post('/asistente', [AsistenteEstudioController::class, 'consultar'])
        ->middleware(['can:Orientacion_Academica_Profesional', 'throttle:20,1'])->name('asistente.query');
    Route::post('/asistente/reiniciar', [AsistenteEstudioController::class, 'reiniciar'])
        ->middleware('can:Orientacion_Academica_Profesional')->name('asistente.reset');
    Route::get('/{area}', [ExperienciaEstudianteController::class, 'mostrar'])
        ->whereIn('area', ['progreso', 'fuentes'])->name('area');
});
