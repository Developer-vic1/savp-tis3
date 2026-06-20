<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AulaVirtualPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $estudiante = Role::firstOrCreate([
            'name' => 'Estudiante',
            'guard_name' => 'web',
        ]);

        $docente = Role::firstOrCreate([
            'name' => 'Docente',
            'guard_name' => 'web',
        ]);

        $administrador = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $permisosEstudiante = [
            'Acceso_Aula_Virtual',
            'Aula_Virtual_Estudiante',
            'Perfil_Academico',
            'Mis_Asignaturas',
            'Mis_Cursos',
            'Materiales_Aula',
            'Actividades_Aula',
            'Tareas_Aula',
            'Entregas_Aula',
            'Cuestionarios_Aula',
            'Mis_Archivos',
            'Calificaciones_Aula',
            'Asistencia_Aula',
            'Calendario_Aula',
            'Notificaciones_Aula',
            'Orientacion_Academica_Profesional',
            'Seguridad_Cuenta',
        ];

        $permisosDocente = [
            'Acceso_Aula_Virtual',
            'Aula_Virtual_Docente',
            'Mis_Cursos',
            'Estudiantes_Curso',
            'Materiales_Aula',
            'Actividades_Aula',
            'Tareas_Aula',
            'Entregas_Aula',
            'Cuestionarios_Aula',
            'Asistencia_Aula',
            'Calificaciones_Aula',
            'Calendario_Aula',
            'Notificaciones_Aula',
            'Reportes_Aula',
            'Orientacion_Academica_Profesional',
            'Seguridad_Cuenta',
        ];

        $permisosAdministrativos = [
            'Acceso_Aula_Virtual',
            'Aula_Virtual_Admin',
            'Mis_Asignaturas',
            'Mis_Cursos',
            'Materiales_Aula',
            'Actividades_Aula',
            'Tareas_Aula',
            'Entregas_Aula',
            'Calificaciones_Aula',
            'Asistencia_Aula',
            'Reportes_Aula',
            'Orientacion_Academica_Profesional',
            'Gestion_Roles_Permisos',
        ];

        $todosLosPermisos = array_unique([
            ...$permisosEstudiante,
            ...$permisosDocente,
            ...$permisosAdministrativos,
        ]);

        foreach ($todosLosPermisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }

        $estudiante->givePermissionTo($permisosEstudiante);
        $docente->givePermissionTo($permisosDocente);
        $administrador->givePermissionTo($permisosAdministrativos);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
