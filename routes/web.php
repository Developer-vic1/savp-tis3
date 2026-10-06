<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ConocimientoUniversitarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoricalReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
require __DIR__.'/diseno_local.php';

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/aula_virtual.php';

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::middleware('actor:Administrador,Director')->group(function () {
        Route::view('/documentacion-institucional', 'workspaces.documentacion-institucional')->name('documentacion.institucional');
        Route::get('/documentacion-institucional/referencias/{id}', [\App\Http\Controllers\ReferenciaDocumentalController::class, 'imagen'])->whereUuid('id')->name('documentacion.referencia');
    });
    Route::get('/reportes/historicos/{report}/descargar', [HistoricalReportController::class, 'download'])->middleware('actor:Administrador,Director,Secretaria')->name('reportes.historicos.descargar');
    Route::prefix('conocimiento')->name('conocimiento.')->middleware(['actor:Administrador,Director', 'can:conocimiento.ver'])->group(function () {
        Route::get('/conexion', [ConocimientoUniversitarioController::class, 'conexion'])->middleware('throttle:10,1')->name('conexion');
        Route::post('/tutor/probar', [ConocimientoUniversitarioController::class, 'probarTutor'])->middleware('throttle:10,1')->name('tutor.probar');
        Route::get('/fuentes', [ConocimientoUniversitarioController::class, 'index'])->middleware('throttle:60,1')->name('fuentes.index');
        Route::post('/fuentes/comprobar-url', [ConocimientoUniversitarioController::class, 'comprobarUrl'])->middleware(['can:conocimiento.proponer', 'throttle:6,1'])->block(40, 5)->name('fuentes.comprobar');
        Route::post('/fuentes/analizar', [ConocimientoUniversitarioController::class, 'analizar'])->middleware(['can:conocimiento.proponer', 'throttle:10,1'])->block(40, 5)->name('fuentes.analizar');
        Route::post('/fuentes', [ConocimientoUniversitarioController::class, 'guardar'])->middleware(['can:conocimiento.proponer', 'throttle:5,1'])->block(40, 5)->name('fuentes.guardar');
        Route::post('/fuentes/{proposalId}/revision', [ConocimientoUniversitarioController::class, 'revisar'])
            ->where('proposalId', 'KGI-[A-F0-9]{12}')
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:10,1'])->name('fuentes.revisar');
    });
    require __DIR__.'/admin.php';
    require __DIR__.'/actors.php';
});
