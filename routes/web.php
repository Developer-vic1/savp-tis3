<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ColeccionUniversitariaController;
use App\Http\Controllers\ConocimientoUniversitarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorioNacionalController;
use App\Http\Controllers\HistoricalReportController;
use App\Http\Controllers\ReferenciaDocumentalController;
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
        Route::get('/documentacion-institucional/referencias/{id}', [ReferenciaDocumentalController::class, 'imagen'])->whereUuid('id')->name('documentacion.referencia');
    });
    Route::get('/reportes/historicos/{report}/descargar', [HistoricalReportController::class, 'download'])->middleware('actor:Administrador,Director,Secretaria')->name('reportes.historicos.descargar');
    Route::prefix('conocimiento')->name('conocimiento.')->middleware(['actor:Administrador,Director', 'can:conocimiento.ver'])->group(function () {
        Route::get('/directorio-nacional', [DirectorioNacionalController::class, 'index'])->middleware('throttle:30,1,conocimiento-directorio:')->name('directorio-nacional');
        Route::get('/directorio-nacional/resumen', [ConocimientoUniversitarioController::class, 'resumenDirectorio'])->middleware('throttle:30,1,conocimiento-directorio:')->name('directorio-resumen');
        Route::get('/conexion', [ConocimientoUniversitarioController::class, 'conexion'])->middleware('throttle:10,1,conocimiento-conexion:')->name('conexion');
        Route::post('/tutor/probar', [ConocimientoUniversitarioController::class, 'probarTutor'])->middleware('throttle:10,1,conocimiento-tutor:')->name('tutor.probar');
        Route::get('/fuentes', [ConocimientoUniversitarioController::class, 'index'])->middleware('throttle:60,1,conocimiento-fuentes-ver:')->name('fuentes.index');
        Route::middleware(['actor:Administrador', 'can:conocimiento.revisar'])->group(function () {
            Route::get('/actualizaciones', [ConocimientoUniversitarioController::class, 'actualizaciones'])->middleware('throttle:30,1,conocimiento-actualizaciones:')->name('actualizaciones.index');
            Route::post('/actualizaciones/configurar', [ConocimientoUniversitarioController::class, 'configurarActualizaciones'])->middleware('throttle:10,1,conocimiento-actualizaciones-configurar:')->name('actualizaciones.configurar');
            Route::post('/actualizaciones/revisar', [ConocimientoUniversitarioController::class, 'revisarActualizaciones'])->middleware('throttle:3,1,conocimiento-actualizaciones-revisar:')->name('actualizaciones.revisar');
        });
        Route::get('/universidades/unifranz', [ColeccionUniversitariaController::class, 'mostrar'])
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar'])->name('unifranz.index');
        Route::get('/colecciones/unifranz', [ColeccionUniversitariaController::class, 'consultar'])
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:30,1,conocimiento-coleccion-consulta:'])->name('unifranz.coleccion');
        Route::post('/colecciones/unifranz', [ColeccionUniversitariaController::class, 'iniciar'])
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:3,1,conocimiento-coleccion-iniciar:'])->name('unifranz.iniciar');
        Route::get('/colecciones/unifranz/documento/{hash}/descargar', [ColeccionUniversitariaController::class, 'descargar'])
            ->where('hash', '[a-f0-9]{64}')
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:20,1,conocimiento-coleccion-descargar:'])->name('unifranz.descargar');
        Route::post('/colecciones/unifranz/analizar', [ColeccionUniversitariaController::class, 'analizar'])
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:3,1,conocimiento-coleccion-analizar:'])->name('unifranz.analizar');
        Route::post('/fuentes/comprobar-url', [ConocimientoUniversitarioController::class, 'comprobarUrl'])->middleware(['can:conocimiento.proponer', 'throttle:6,1,conocimiento-fuentes-comprobar:'])->block(40, 5)->name('fuentes.comprobar');
        Route::post('/fuentes/analizar', [ConocimientoUniversitarioController::class, 'analizar'])->middleware(['can:conocimiento.proponer', 'throttle:10,1,conocimiento-fuentes-analizar:'])->block(40, 5)->name('fuentes.analizar');
        Route::post('/fuentes', [ConocimientoUniversitarioController::class, 'guardar'])->middleware(['can:conocimiento.proponer', 'throttle:5,1,conocimiento-fuentes-guardar:'])->block(40, 5)->name('fuentes.guardar');
        Route::post('/fuentes/{proposalId}/revision', [ConocimientoUniversitarioController::class, 'revisar'])
            ->where('proposalId', 'KGI-[A-F0-9]{12}')
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:10,1,conocimiento-fuentes-revisar:'])->name('fuentes.revisar');
        Route::post('/fuentes/{proposalId}/extraccion', [ConocimientoUniversitarioController::class, 'extraer'])
            ->where('proposalId', 'KGI-[A-F0-9]{12}')
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:3,1,conocimiento-fuentes-extraer:'])
            ->block(90, 5)->name('fuentes.extraer');
        Route::get('/fuentes/{proposalId}/extraccion', [ConocimientoUniversitarioController::class, 'extraer'])
            ->where('proposalId', 'KGI-[A-F0-9]{12}')
            ->middleware(['actor:Administrador', 'can:conocimiento.revisar', 'throttle:30,1,conocimiento-fuentes-extraccion-guardada:'])
            ->name('fuentes.extraccion-guardada');
    });
    require __DIR__.'/admin.php';
    require __DIR__.'/actors.php';
});
