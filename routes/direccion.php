<?php

use App\Http\Controllers\InstitutionalQueryController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('direccion')->name('direccion.')->middleware('actor:Director')->group(function () {
    Route::get('/', [WorkspaceController::class, 'director'])->name('dashboard');
    Route::get('/consultas/{area}', [InstitutionalQueryController::class, 'index'])
        ->whereIn('area', array_keys(InstitutionalQueryController::AREAS))->name('consulta');
});
