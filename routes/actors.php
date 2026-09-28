<?php

use App\Http\Controllers\Admin\GestionAcademicaController;
use App\Http\Controllers\Admin\GestionCursosController;
use App\Http\Controllers\Admin\GestionEstudiantesController;
use App\Http\Controllers\Admin\GestionInscripcionController;
use App\Http\Controllers\Admin\GestionParaleloController;
use App\Http\Controllers\Admin\GestionPersonaController;
use App\Http\Controllers\Admin\GestionTurnoController;
use App\Http\Controllers\Docente\CalificacionCursoController;
use App\Http\Controllers\Estudiante\ExperienceController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('direccion')->name('direccion.')->middleware('actor:Director')->group(function () {
    Route::get('/', [WorkspaceController::class, 'director'])->name('dashboard');
    Route::get('/consultas/{area}', [\App\Http\Controllers\InstitutionalQueryController::class, 'index'])
        ->whereIn('area', array_keys(\App\Http\Controllers\InstitutionalQueryController::AREAS))->name('consulta');
});

Route::prefix('secretaria')->name('secretaria.')->middleware('actor:Secretaria')->group(function () {
    Route::get('/', [WorkspaceController::class, 'secretaria'])->name('dashboard');
    Route::view('/cuentas', 'secretaria.cuentas')->middleware('can:usuarios.ver.institucional')->name('cuentas');
    Route::get('/personas', [GestionPersonaController::class, 'index'])->middleware('can:Registro_Personas')->name('personas');
    Route::get('/estudiantes', [GestionEstudiantesController::class, 'index'])->middleware('can:Estudiantes')->name('estudiantes');
    Route::get('/inscripciones', [GestionInscripcionController::class, 'index'])->middleware('can:Inscripciones')->name('inscripciones');
    Route::get('/gestion-academica', [GestionAcademicaController::class, 'index'])->middleware('can:Gestion_Academica')->name('gestion-academica');
    Route::get('/cursos', [GestionCursosController::class, 'index'])->middleware('can:Cursos')->name('cursos');
    Route::get('/paralelos', [GestionParaleloController::class, 'index'])->middleware('can:Paralelos')->name('paralelos');
    Route::get('/turnos', [GestionTurnoController::class, 'index'])->middleware('can:Turnos')->name('turnos');
});

Route::prefix('regencia')->name('regencia.')->middleware('actor:Regente')->group(function () {
    Route::get('/', [WorkspaceController::class, 'regencia'])->name('dashboard');
    Route::get('/consultas/{area}', [\App\Http\Controllers\InstitutionalQueryController::class, 'index'])
        ->whereIn('area', ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia'])->name('consulta');
});

Route::prefix('docente')->name('docente.')->middleware('actor:Docente')->group(function () {
    Route::get('/', [WorkspaceController::class, 'docente'])->name('dashboard');
    Route::redirect('/cursos', '/aula-virtual/mis-cursos')->name('cursos');
    Route::get('/cursos/{curso}/calificaciones', [CalificacionCursoController::class, 'index'])->name('cursos.calificaciones');
    Route::post('/cursos/{curso}/calificaciones', [CalificacionCursoController::class, 'store'])->name('cursos.calificaciones.store');
    Route::put('/cursos/{curso}/calificaciones/{calificacion}', [CalificacionCursoController::class, 'update'])->name('cursos.calificaciones.update');
});

Route::prefix('estudiante')->name('estudiante.')->middleware('actor:Estudiante')->group(function () {
    Route::get('/', [WorkspaceController::class, 'estudiante'])->name('dashboard');
    Route::redirect('/materias', '/aula-virtual/mis-asignaturas')->name('materias');
    Route::redirect('/asistencia', '/aula-virtual/mi-asistencia')->name('asistencia');
    Route::redirect('/intereses', '/aula-virtual/orientacion')->name('intereses');
    Route::get('/{area}', [ExperienceController::class, 'show'])
        ->whereIn('area', ['progreso', 'futuro', 'preparacion', 'plan', 'fuentes', 'asistente'])
        ->name('area');
});
