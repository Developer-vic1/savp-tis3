<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\GestionPersonaController;
use App\Http\Controllers\Admin\GestionPersonalInstitucional;
use App\Http\Controllers\Admin\GestionUsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->get('/dashboard', function () {
    $user = Auth::user();

    if ($user->can('Panel_Administrador')) {
        return app(AdminDashboardController::class)->index();
    }

    abort(403, 'No tienes un panel asignado.');
})->name('dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/gestion-usuarios', [GestionUsuarioController::class, 'index'])
        ->name('gestion-usuarios')
        ->middleware('can:Gestion_Usuarios');

    Route::get('/gestion-personas', [GestionPersonaController::class, 'index'])
        ->name('gestion-personas')
        ->middleware('can:Registro_Personas');

    Route::get('/personal-institucional', [GestionPersonalInstitucional::class, 'index'])
        ->name('personal-institucional')
        ->middleware('can:Personal_Institucional');
});