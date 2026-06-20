<?php

use App\Http\Controllers\AulaVirtual\AuthAulaVirtualController;
use App\Http\Controllers\AulaVirtual\AsistenciaController;
use App\Http\Controllers\AulaVirtual\AulaVirtualController;
use App\Http\Controllers\AulaVirtual\CursoVirtualController;
use App\Http\Controllers\AulaVirtual\EntregaController;
use App\Http\Controllers\AulaVirtual\MaterialController;
use App\Http\Controllers\AulaVirtual\OrientacionController;
use App\Http\Controllers\AulaVirtual\ReporteAulaVirtualController;
use App\Http\Controllers\AulaVirtual\TareaController;
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

            Route::middleware('can:Aula_Virtual_Estudiante')->name('estudiante.')->group(function () {
                Route::get('/mis-asignaturas', [CursoVirtualController::class, 'indexEstudiante'])
                    ->name('asignaturas');
                Route::get('/mis-asignaturas/{curso}', [CursoVirtualController::class, 'showEstudiante'])
                    ->name('curso');
                Route::get('/mi-asistencia', [AsistenciaController::class, 'miAsistencia'])
                    ->name('asistencia');
                Route::get('/orientacion', [OrientacionController::class, 'estudiante'])
                    ->name('orientacion');
                Route::get('/orientacion/explorador', [OrientacionController::class, 'explorador'])
                    ->name('orientacion.explorador');
                Route::get('/orientacion/resultados', [OrientacionController::class, 'resultados'])
                    ->name('orientacion.resultados');
                Route::get('/tareas/{tarea}/entregar', [TareaController::class, 'entregar'])
                    ->name('tareas.entregar');
                Route::post('/tareas/{tarea}/entregas', [EntregaController::class, 'store'])
                    ->name('tareas.entregas.store');
            });

            Route::middleware('can:Aula_Virtual_Docente')->name('docente.')->group(function () {
                Route::get('/mis-cursos', [CursoVirtualController::class, 'indexDocente'])
                    ->name('cursos');
                Route::get('/mis-cursos/{curso}', [CursoVirtualController::class, 'showDocente'])
                    ->name('curso');
                Route::post('/mis-cursos/{curso}/materiales', [MaterialController::class, 'store'])
                    ->name('materiales.store');
                Route::post('/mis-cursos/{curso}/tareas', [TareaController::class, 'store'])
                    ->name('tareas.store');
                Route::get('/mis-cursos/{curso}/asistencia/registrar', [AsistenciaController::class, 'registrar'])
                    ->name('asistencia.registrar');
                Route::post('/mis-cursos/{curso}/asistencia', [AsistenciaController::class, 'guardar'])
                    ->name('asistencia.guardar');
                Route::get('/tareas/{tarea}/revisar', [TareaController::class, 'revisar'])
                    ->name('tareas.revisar');
                Route::post('/entregas/{entrega}/calificar', [EntregaController::class, 'calificar'])
                    ->name('entregas.calificar');
                Route::post('/entregas/{entrega}/devolver', [EntregaController::class, 'devolver'])
                    ->name('entregas.devolver');
                Route::get('/orientacion/seguimiento', [OrientacionController::class, 'seguimientoDocente'])
                    ->name('orientacion.seguimiento');
                Route::get('/reportes', [ReporteAulaVirtualController::class, 'index'])
                    ->name('reportes');
            });

            Route::get('/cursos/{curso}/materiales', [MaterialController::class, 'index'])
                ->name('materiales.index');
            Route::get('/materiales/{material}/descargar', [MaterialController::class, 'descargar'])
                ->name('materiales.descargar');
            Route::post('/materiales/{material}/publicar', [MaterialController::class, 'publicar'])
                ->middleware('can:Aula_Virtual_Docente')
                ->name('materiales.publicar');
            Route::post('/materiales/{material}/ocultar', [MaterialController::class, 'ocultar'])
                ->middleware('can:Aula_Virtual_Docente')
                ->name('materiales.ocultar');
            Route::get('/entregas/archivos/{archivo}/descargar', [EntregaController::class, 'descargar'])
                ->name('entregas.archivos.descargar');
        });
    });
