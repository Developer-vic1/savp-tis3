<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\GestionEstudiantesController;
use App\Http\Controllers\Admin\GestionPersonaController;
use App\Http\Controllers\Admin\GestionPersonalInstitucional;
use App\Http\Controllers\Admin\GestionUsuarioController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\GestionAcademicaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Dashboard principal
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| Rutas de administración
|--------------------------------------------------------------------------
*/
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

    Route::get('/gestion-estudiantes', [GestionEstudiantesController::class, 'index'])
        ->name('gestion-estudiantes')
        ->middleware('can:Estudiantes');

    Route::get('/bitacora', [BitacoraController::class, 'index'])
        ->name('bitacora')
        ->middleware('can:Bitacora');

    Route::get('/gestion-academica', [GestionAcademicaController::class, 'index'])
        ->name('gestion-academica')
        ->middleware('can:Gestion_Academica');
});