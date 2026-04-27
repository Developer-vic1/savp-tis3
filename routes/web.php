<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\GestionUsuarioController;

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
])->group(function () {
    Route::get('/admin/gestion-usuarios', [GestionUsuarioController::class, 'index'])
        ->name('admin.gestion-usuarios')
        ->middleware('can:Gestion_Usuarios');
});