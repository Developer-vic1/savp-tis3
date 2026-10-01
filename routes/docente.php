<?php

use App\Http\Controllers\AulaVirtual\CursoVirtualController;
use App\Http\Controllers\Docente\CalificacionCursoController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('docente')->name('docente.')->middleware('actor:Docente')->group(function () {
    Route::get('/', [WorkspaceController::class, 'docente'])->name('dashboard');
    Route::redirect('/cursos', '/aula-virtual/mis-cursos')->middleware('can:Aula_Virtual_Docente')->name('cursos');
    Route::get('/cursos/{curso}', [CursoVirtualController::class, 'showDocente'])
        ->middleware(['can:Acceso_Aula_Virtual', 'can:Aula_Virtual_Docente'])->name('curso');
    Route::get('/cursos/{curso}/calificaciones', [CalificacionCursoController::class, 'index'])->name('cursos.calificaciones');
    Route::post('/cursos/{curso}/calificaciones', [CalificacionCursoController::class, 'store'])->name('cursos.calificaciones.store');
    Route::put('/cursos/{curso}/calificaciones/{calificacion}', [CalificacionCursoController::class, 'update'])->name('cursos.calificaciones.update');
});
