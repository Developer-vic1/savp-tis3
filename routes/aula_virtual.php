<?php

use App\Http\Controllers\AulaVirtual\AuthAulaVirtualController;
use App\Http\Controllers\AulaVirtual\AulaVirtualController;
use Illuminate\Support\Facades\Route;

Route::prefix('aula-virtual')
    ->name('aula-virtual.')
    ->middleware(['web'])
    ->group(function () {
        Route::get('/login', [AuthAulaVirtualController::class, 'login'])
            ->middleware('guest')
            ->name('login');

        Route::get('/google/redirect', [AuthAulaVirtualController::class, 'redirectToGoogle'])
            ->middleware('guest')
            ->name('google.redirect');

        Route::middleware([
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified',
            'can:Acceso_Aula_Virtual',
        ])->group(function () {
            Route::get('/', [AulaVirtualController::class, 'index'])
                ->name('inicio');
        });
    });
