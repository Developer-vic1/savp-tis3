<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Identificación principal
            |--------------------------------------------------------------------------
            */
            $table->string('cod_bit', 20)
                ->primary()
                ->comment('Código único de la bitácora. Ejemplo: BIT_0001');

            /*
            |--------------------------------------------------------------------------
            | Usuario responsable
            |--------------------------------------------------------------------------
            */
            $table->string('cod_usu', 20)
                ->nullable()
                ->comment('Código del usuario que ejecutó la acción. Puede ser nulo si fue una acción del sistema.');

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->nullOnDelete();

            $table->string('rol_bit', 100)
                ->nullable()
                ->comment('Rol del usuario al momento de ejecutar la acción. Ejemplo: Administrador, Director, Regente.');

            /*
            |--------------------------------------------------------------------------
            | Acción realizada
            |--------------------------------------------------------------------------
            */
            $table->string('acc_bit', 150)
                ->comment('Acción realizada dentro del sistema. Ejemplo: REGISTRAR_ESTUDIANTE, EDITAR_USUARIO.');

            $table->string('mod_bit', 150)
                ->nullable()
                ->comment('Módulo funcional donde ocurrió la acción. Ejemplo: Gestión de Estudiantes, Gestión de Usuarios.');

            $table->string('tab_bit', 100)
                ->comment('Tabla de base de datos afectada. Ejemplo: estudiante, users, inscripcion_estudiante.');

            $table->string('reg_bit', 50)
                ->nullable()
                ->comment('Código o identificador del registro afectado. Ejemplo: EST_0001, USU_0002.');

            $table->string('nom_reg_bit', 255)
                ->nullable()
                ->comment('Nombre visible o descripción corta del registro afectado. Ejemplo: María Choque Mamani.');

            /*
            |--------------------------------------------------------------------------
            | Descripción institucional
            |--------------------------------------------------------------------------
            */
            $table->text('des_bit')
                ->nullable()
                ->comment('Descripción humana de la acción realizada, entendible para administración o dirección.');

            /*
            |--------------------------------------------------------------------------
            | Clasificación del evento
            |--------------------------------------------------------------------------
            */
            $table->string('niv_bit', 30)
                ->default('INFO')
                ->comment('Nivel del evento: INFO, SUCCESS, WARNING, ERROR, SECURITY, CRITICAL.');

            $table->string('res_bit', 30)
                ->default('EXITOSO')
                ->comment('Resultado del evento: EXITOSO, FALLIDO, BLOQUEADO.');

            /*
            |--------------------------------------------------------------------------
            | Información técnica de auditoría
            |--------------------------------------------------------------------------
            */
            $table->string('ip_bit', 45)
                ->nullable()
                ->comment('Dirección IP desde donde se ejecutó la acción.');

            $table->text('age_bit')
                ->nullable()
                ->comment('User agent del navegador o dispositivo utilizado.');

            $table->string('rut_bit', 255)
                ->nullable()
                ->comment('Ruta del sistema donde ocurrió la acción.');

            $table->string('met_bit', 20)
                ->nullable()
                ->comment('Método HTTP utilizado. Ejemplo: GET, POST, PUT, DELETE.');

            /*
            |--------------------------------------------------------------------------
            | Valores antes y después
            |--------------------------------------------------------------------------
            | Estos campos permiten auditar cambios sensibles.
            | Ejemplo: antes = estado ACTIVO, después = estado OBSERVADO.
            |--------------------------------------------------------------------------
            */
            $table->json('val_ant_bit')
                ->nullable()
                ->comment('Valores anteriores del registro afectado en formato JSON.');

            $table->json('val_nue_bit')
                ->nullable()
                ->comment('Valores nuevos del registro afectado en formato JSON.');

            /*
            |--------------------------------------------------------------------------
            | Error o excepción
            |--------------------------------------------------------------------------
            */
            $table->text('err_bit')
                ->nullable()
                ->comment('Mensaje de error o excepción si la acción falló.');

            /*
            |--------------------------------------------------------------------------
            | Fecha del evento
            |--------------------------------------------------------------------------
            */
            $table->timestamp('fec_bit')
                ->useCurrent()
                ->comment('Fecha y hora exacta en la que ocurrió la acción.');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
