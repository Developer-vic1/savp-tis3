<?php

use App\Http\Controllers\InstitutionalQueryController;
use App\Http\Controllers\RegencyReportController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('regencia')->name('regencia.')->middleware('actor:Regente')->group(function () {
    Route::get('/', [WorkspaceController::class, 'regencia'])->name('dashboard');
    Route::get('/reportes', [RegencyReportController::class, 'index'])->name('reportes');
    Route::get('/reportes/{plan}.pdf', [RegencyReportController::class, 'pdf'])->name('reportes.pdf');
    Route::get('/consultas/{area}', [InstitutionalQueryController::class, 'index'])
        ->whereIn('area', ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia', 'lms'])->name('consulta');
});
