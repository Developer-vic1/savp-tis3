<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DomainBoundaryController;
use App\Http\Controllers\EnrollmentDocumentController;
use App\Http\Controllers\RegencyAssignmentsController;
use App\Services\CalendarService;
use Illuminate\Support\Facades\Route;

// Ventanas del catálogo original que comparten dominios; siempre bajo auth + actor.
foreach (['admin' => 'Administrador', 'direccion' => 'Director', 'secretaria' => 'Secretaria', 'regencia' => 'Regente', 'docente' => 'Docente', 'estudiante' => 'Estudiante'] as $prefix => $actor) {
    Route::prefix($prefix)->name($prefix.'.')->middleware('actor:'.$actor)->group(function () use ($prefix, $actor) {
        Route::get('/calendario', CalendarController::class)->middleware('can:'.CalendarService::PERMISSIONS[$actor])->name('calendario');
        $domains = match ($actor) {
            'Administrador' => ['kardex' => 'kardex-parametros', 'lms-configuracion' => 'lms-configuracion', 'configuracion' => 'configuracion'],
            'Director' => ['kardex' => 'kardex', 'prevencion' => 'prevencion', 'seguimientos' => 'seguimientos'], 'Secretaria' => ['kardex' => 'kardex'],
            'Regente' => ['kardex' => 'kardex', 'seguimientos' => 'seguimientos', 'alertas' => 'alertas'], 'Docente' => ['kardex' => 'kardex', 'seguimientos' => 'seguimientos'], 'Estudiante' => ['seguimientos' => 'seguimientos']
        };
        foreach ($domains as $domain => $path) {
            Route::get('/'.$path, [DomainBoundaryController::class, 'show'])->defaults('domain', $domain)->name($domain);
        }
        Route::redirect('/perfil', '/user/profile')->name('perfil');
        if ($prefix === 'regencia') {
            Route::get('/mis-grados', RegencyAssignmentsController::class)->middleware('can:cursos.ver.institucional')->name('grados');
        }
        if (in_array($prefix, ['admin', 'secretaria'], true)) {
            Route::get('/documentacion', [EnrollmentDocumentController::class, 'index'])->middleware('can:Inscripciones')->name('documentacion');
        }
    });
}
Route::get('/documentos/inscripciones/{document}', [EnrollmentDocumentController::class, 'download'])->middleware('can:Inscripciones')->name('documentos.inscripciones.descargar');
