<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\HistoricalReportController;
use App\Http\Controllers\InstitutionalQueryController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('secretaria')->name('secretaria.')->middleware('actor:Secretaria')->group(function () {
    Route::get('/', [WorkspaceController::class, 'secretaria'])->name('dashboard');
    Route::view('/cuentas', 'secretaria.cuentas')->middleware('can:usuarios.ver.institucional')->name('cuentas');
    Route::get('/reportes', [HistoricalReportController::class, 'secretary'])->middleware('can:Reportes_Administrativos')->name('reportes');
    foreach (['cursos' => ['cursos', 'Cursos'], 'turnos' => ['turnos', 'Turnos'], 'gestion-academica' => ['gestion', 'Gestion_Academica']] as $path => [$area,$permission]) {
        Route::get('/'.$path, [InstitutionalQueryController::class, 'index'])->defaults('area', $area)->middleware('can:'.$permission)->name($path);
    }
    foreach ([
        ['personas', Admin\GestionPersonaController::class, 'Registro_Personas'],
        ['estudiantes', Admin\GestionEstudiantesController::class, 'Estudiantes'],
        ['inscripciones', Admin\GestionInscripcionController::class, 'Inscripciones'],
        ['paralelos', Admin\GestionParaleloController::class, 'Paralelos'],
        ['procedencia', Admin\InstitucionProcedenciaController::class, 'Institucion_Procedencia'],
        ['vinculacion', Admin\TipoVinculacionEstudianteController::class, 'Tipo_Vinculacion_Estudiante'],
    ] as [$path, $controller, $permission]) {
        Route::get('/'.$path, [$controller, 'index'])->middleware('can:'.$permission)->name($path);
    }
});
