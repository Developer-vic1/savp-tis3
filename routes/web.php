<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Autenticación complementaria (OAuth)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('google.callback');
});

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

    if ($user->canAny([
        'Panel_Administrador',
        'Panel_Director',
        'Panel_Docente',
        'Panel_Estudiante',
        'Panel_Secretaria',
        'Panel_Regente',
    ])) {
        return app(AdminDashboardController::class)->index();
    }

    abort(403, 'No tienes un panel asignado.');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Módulos del Sistema
|--------------------------------------------------------------------------
*/
require __DIR__.'/admin.php';
require __DIR__.'/aula_virtual.php';
