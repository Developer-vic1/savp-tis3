<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Limpiar caché de permisos y roles
        |--------------------------------------------------------------------------
        */
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | ROLES DEL SISTEMA
        |--------------------------------------------------------------------------
        */
        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $director = Role::firstOrCreate([
            'name' => 'Director',
            'guard_name' => 'web',
        ]);

        $docente = Role::firstOrCreate([
            'name' => 'Docente',
            'guard_name' => 'web',
        ]);

        $estudiante = Role::firstOrCreate([
            'name' => 'Estudiante',
            'guard_name' => 'web',
        ]);

        $secretaria = Role::firstOrCreate([
            'name' => 'Secretaria',
            'guard_name' => 'web',
        ]);

        $regente = Role::firstOrCreate([
            'name' => 'Regente',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISOS POR MÓDULOS / VENTANAS
        |--------------------------------------------------------------------------
        | Estos permisos controlan:
        | - acceso a módulos
        | - visibilidad en sidebar
        | - acceso a pantallas principales
        |--------------------------------------------------------------------------
        */
        $permisos = [

            /*
            |--------------------------------------------------------------------------
            | PANELES PRINCIPALES
            |--------------------------------------------------------------------------
            */
            'Panel_Administrador' => [$admin],
            'Panel_Director' => [$director],
            'Panel_Docente' => [$docente],
            'Panel_Estudiante' => [$estudiante],
            'Panel_Secretaria' => [$secretaria],
            'Panel_Regente' => [$regente],

            /*
            |--------------------------------------------------------------------------
            | MÓDULOS BASE ADMINISTRATIVOS
            |--------------------------------------------------------------------------
            | Registro_Personas:
            | Módulo base de identidad institucional.
            | Aquí se registra una sola vez la información personal.
            |
            | Gestion_Usuarios:
            | Controla acceso al sistema, cuentas, roles y credenciales.
            |
            | Personal_Institucional:
            | Gestiona el vínculo funcional/laboral de una persona con
            | la institución.
            |
            | Estudiantes:
            | Gestiona la condición académica del estudiante, separada
            | del registro de identidad.
            |--------------------------------------------------------------------------
            */
            'Registro_Personas' => [$admin, $director, $secretaria],
            'Gestion_Usuarios' => [$admin, $director, $secretaria],
            'Personal_Institucional' => [$admin, $director, $secretaria],
            'Estudiantes' => [$admin, $director, $docente, $secretaria, $regente],

            /*
            |--------------------------------------------------------------------------
            | CATÁLOGOS RELACIONADOS CON ESTUDIANTES
            |--------------------------------------------------------------------------
            */
            'Institucion_Procedencia' => [$admin, $secretaria],
            'Tipo_Vinculacion_Estudiante' => [$admin, $secretaria],

            /*
            |--------------------------------------------------------------------------
            | MÓDULOS DOCENTES E INSCRIPCIONES
            |--------------------------------------------------------------------------
            */
            'Docentes' => [$admin, $director, $secretaria],
            'Inscripciones' => [$admin, $secretaria, $regente],

            /*
            |--------------------------------------------------------------------------
            | ESTRUCTURA ACADÉMICA
            |--------------------------------------------------------------------------
            */
            'Gestion_Academica' => [$admin, $director, $secretaria],
            'Cursos' => [$admin, $director, $secretaria, $docente, $regente],
            'Paralelos' => [$admin, $director, $secretaria, $docente, $regente],
            'Turnos' => [$admin, $director, $secretaria],
            'Asignaturas' => [$admin, $director, $docente, $secretaria],

            /*
            |--------------------------------------------------------------------------
            | ESPECIALIDADES Y PLANIFICACIÓN
            |--------------------------------------------------------------------------
            */
            'Especialidades_Tecnicas' => [$admin, $director, $secretaria, $docente],
            'Planes_Asignatura' => [$admin, $director, $docente],

            /*
            |--------------------------------------------------------------------------
            | EVALUACIÓN Y CALIFICACIÓN
            |--------------------------------------------------------------------------
            */
            'Periodo_Evaluacion' => [$admin, $director, $docente, $secretaria],
            'Calificaciones' => [$admin, $director, $docente, $estudiante, $secretaria, $regente],

            /*
            |--------------------------------------------------------------------------
            | REPORTES
            |--------------------------------------------------------------------------
            */
            'Reportes_Academicos' => [$admin, $director, $docente, $regente],
            'Reportes_Administrativos' => [$admin, $director, $secretaria],

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA
            |--------------------------------------------------------------------------
            */
            'Bitacora' => [$admin, $director],

            /*
            |--------------------------------------------------------------------------
            | PERFIL PROPIO
            |--------------------------------------------------------------------------
            */
            'Mi_Perfil' => [$admin, $director, $docente, $estudiante, $secretaria, $regente],

            /*
            |--------------------------------------------------------------------------
            | MÓDULOS FUTUROS
            |--------------------------------------------------------------------------
            */
            // 'Tareas_Academicas' => [$docente, $estudiante],
            // 'Evaluaciones' => [$docente, $estudiante],
            // 'Orientacion_Vocacional' => [$estudiante],
            // 'Resultados_Predictivos' => [$estudiante, $docente, $director],
        ];

        /*
        |--------------------------------------------------------------------------
        | CREACIÓN DE PERMISOS Y ASIGNACIÓN DE ROLES
        |--------------------------------------------------------------------------
        */
        foreach ($permisos as $nombre => $roles) {
            $permiso = Permission::firstOrCreate([
                'name' => $nombre,
                'guard_name' => 'web',
            ]);

            $permiso->syncRoles($roles);
        }

        /*
        |--------------------------------------------------------------------------
        | PERMISOS POR ACCIÓN Y ALCANCE
        |--------------------------------------------------------------------------
        | Conviven con los permisos legados durante la transición. No se eliminan
        | nombres antiguos para no interrumpir rutas y componentes existentes.
        */
        $permisosPorAccion = [
            'usuarios.ver.global' => [$admin],
            'usuarios.ver.institucional' => [$secretaria],
            'usuarios.crear' => [$admin, $secretaria],
            'usuarios.editar' => [$admin, $secretaria],
            'usuarios.activar' => [$admin, $secretaria],
            'usuarios.desactivar' => [$admin, $secretaria],
            'usuarios.reset_password' => [$admin, $secretaria],
            'usuarios.asignar_roles' => [$admin],
            'roles-permisos.gestionar' => [$admin],
            'regencia.asignaciones.gestionar' => [$admin],
            'calificaciones.rectificar' => [$admin],

            'personas.ver.institucional' => [$admin, $director, $secretaria],
            'personas.gestionar.institucional' => [$admin, $secretaria],
            'estudiantes.ver.global' => [$admin],
            'estudiantes.ver.institucional' => [$director, $secretaria, $regente],
            'estudiantes.ver.curso' => [$docente],
            'estudiantes.ver.propio' => [$estudiante],
            'estudiantes.gestionar.institucional' => [$admin, $secretaria],

            'cursos.ver.global' => [$admin],
            'cursos.ver.institucional' => [$director, $secretaria, $regente],
            'cursos.ver.asignados' => [$docente],
            'cursos.ver.propios' => [$estudiante],
            'cursos.gestionar.global' => [$admin],
            'inscripciones.ver.institucional' => [$admin, $director, $secretaria, $regente],
            'inscripciones.ver.curso' => [$docente],
            'inscripciones.ver.propias' => [$estudiante],
            'inscripciones.gestionar.institucional' => [$admin, $secretaria],

            'calificaciones.ver.global' => [$admin],
            'calificaciones.ver.institucional' => [$director, $regente],
            'calificaciones.ver.curso' => [$docente],
            'calificaciones.ver.propias' => [$estudiante],
            'calificaciones.gestionar.global' => [$admin],
            'calificaciones.gestionar.curso' => [$docente],
            'asistencia.ver.institucional' => [$admin, $director, $regente],
            'asistencia.ver.curso' => [$docente],
            'asistencia.ver.propia' => [$estudiante],
            'asistencia.gestionar.curso' => [$docente],

            'aula.materiales.gestionar.curso' => [$docente],
            'aula.tareas.gestionar.curso' => [$docente],
            'aula.entregas.gestionar.propias' => [$estudiante],
            'aula.entregas.revisar.curso' => [$docente],
            'orientacion.realizar' => [$estudiante],
            'orientacion.ver.propia' => [$estudiante],
            'orientacion.ver.curso' => [$docente],
            'orientacion.ver.institucional' => [$admin, $director, $regente],
            'orientacion.configurar' => [$admin],
            'reportes.ver.institucional' => [$admin, $director, $secretaria, $regente],
            'reportes.exportar.institucional' => [$admin, $director],
            'bitacora.ver.global' => [$admin],
            'integraciones.peter3.usar' => [$admin, $director, $docente, $estudiante],
        ];

        foreach ($permisosPorAccion as $nombre => $roles) {
            $permiso = Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            $permiso->syncRoles($roles);
        }

        /*
        |--------------------------------------------------------------------------
        | Limpiar caché nuevamente
        |--------------------------------------------------------------------------
        */
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
