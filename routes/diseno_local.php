<?php

use App\Http\Controllers\DisenoLocalController;
use App\Http\Middleware\RestringirDisenoLocal;
use App\Http\Middleware\VerificarCuentaActiva;
use Illuminate\Support\Facades\Route;

Route::prefix('diseno')->name('diseno.')->withoutMiddleware(VerificarCuentaActiva::class)
    ->middleware(RestringirDisenoLocal::class)->group(function (): void {
        Route::get('/', [DisenoLocalController::class, 'acceso'])->name('acceso');
        Route::post('/acceso', [DisenoLocalController::class, 'entrar'])->middleware('throttle:5,1')->name('entrar');
        Route::get('/vistas', [DisenoLocalController::class, 'vistas'])->name('vistas');
        Route::get('/capturas/{captura}', [DisenoLocalController::class, 'imagen'])->name('imagen');
        Route::post('/salir', [DisenoLocalController::class, 'salir'])->name('salir');
    });
