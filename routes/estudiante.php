<?php

use App\Http\Controllers\AulaVirtual\CursoVirtualController;
use App\Http\Controllers\Estudiante\ExperienceController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('estudiante')->name('estudiante.')->middleware('actor:Estudiante')->group(function () {
    Route::get('/', [WorkspaceController::class, 'estudiante'])->name('dashboard');
    Route::redirect('/materias', '/aula-virtual/mis-asignaturas')->middleware('can:Aula_Virtual_Estudiante')->name('materias');
    Route::get('/materias/{curso}', [CursoVirtualController::class, 'showEstudiante'])
        ->middleware(['can:Acceso_Aula_Virtual', 'can:Aula_Virtual_Estudiante'])->name('materia');
    Route::redirect('/asistencia', '/aula-virtual/mi-asistencia')->name('asistencia');
    Route::redirect('/intereses', '/aula-virtual/orientacion')->name('intereses');
    Route::get('/{area}', [ExperienceController::class, 'show'])
        ->whereIn('area', ['progreso', 'futuro', 'preparacion', 'plan', 'fuentes', 'asistente'])->name('area');
});
