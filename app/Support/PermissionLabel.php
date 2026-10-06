<?php

namespace App\Support;

final class PermissionLabel
{
    public static function describe(string $name): array
    {
        $parts = explode('.', $name);
        $domain = str_replace(['_', '-'], ' ', $parts[0]);
        $domain = match($parts[0]) {
            'Gestion_Roles_Permisos','roles-permisos','roles'=>'Roles y permisos',
            'Gestion_Usuarios','usuarios'=>'Usuarios', 'Gestion_Personas','personas'=>'Personas',
            'Panel_Administrador'=>'Panel administrativo','Aula_Virtual_Docente','Aula_Virtual_Estudiante','Acceso_Aula_Virtual'=>'Aula virtual',
            'Reportes_Academicos','reportes'=>'Reportes académicos',
            'Orientacion_Academica_Profesional','orientacion'=>'Orientación vocacional',
            'Personal_Institucional'=>'Personal institucional','Plan_Asignatura'=>'Planes de asignatura',
            'Especialidades_Tecnicas'=>'Especialidades técnicas','asistencia'=>'Asistencia',
            default=>ucfirst($domain),
        };
        $action = $parts[1] ?? 'acceder';
        $scope = $parts[2] ?? '';
        if($parts[0]==='aula'&&count($parts)>=3){$domain=match($parts[1]){'tareas'=>'Tareas del aula','materiales'=>'Materiales del aula','entregas'=>'Entregas del aula',default=>'Aula virtual'};$action=$parts[2];$scope=$parts[3]??'';}

        return [
            'espacio'=>preg_match('/aula|tarea|material|entrega|cuestionario|^mis_/i',$name)===1?'aula':'administrativo',
            'capacidad'=>count($parts)===1?'entrada':(in_array($action,['ver','acceder','descargar'],true)?'consulta':(in_array($action,['gestionar','crear','editar','asignar','activar','desactivar','eliminar','revisar','realizar'],true)?'edicion':'operacion')),
            'consultas_compatibles'=>LegacyReadPermission::FALLBACKS[$name]??[],
            'tipo_aula'=>match(true){
                preg_match('/tarea/i',$name)===1=>'Tareas',preg_match('/material/i',$name)===1=>'Materiales',
                preg_match('/entrega/i',$name)===1=>'Entregas',preg_match('/calificacion|evaluacion|cuestionario/i',$name)===1=>'Evaluación',
                preg_match('/reporte/i',$name)===1=>'Reportes del aula',default=>'Clases y acceso al aula',
            },
            'area' => match(true){
                preg_match('/roles|usuarios|bitacora|seguridad/i',$name)===1=>'Seguridad y cuentas',
                preg_match('/reporte/i',$name)===1=>'Reportes e informes',
                preg_match('/orientacion|conocimiento|integracion|peter/i',$name)===1=>'Orientación y conocimiento',
                preg_match('/calificacion|asistencia|regencia/i',$name)===1=>'Evaluación y asistencia',
                preg_match('/persona|docente|estudiante|inscripcion|vinculacion|procedencia/i',$name)===1&&!str_contains($name,'Aula_')=>'Comunidad educativa',
                preg_match('/curso|asignatura|paralelo|turno|periodo|gestion_academica|especialidad/i',$name)===1&&!preg_match('/Mis_|Aula_/',$name)=>'Organización académica',
                preg_match('/aula|tarea|material|entrega|cuestionario|mis_/i',$name)===1=>'Enseñanza y aula virtual',
                default=>'Espacio personal e institución',
            },
            'domain' => ucfirst($domain),
            'action' => $action,
            'scope' => $scope,
            'label' => match($name){
                'Aula_Virtual_Estudiante'=>'Entrar a las materias del estudiante',
                'Aula_Virtual_Docente'=>'Entrar a los cursos del docente',
                'Acceso_Aula_Virtual'=>'Entrar al Aula virtual',
                'Gestion_Roles_Permisos'=>'Entrar a roles y permisos',
                default=>match($action){
                'ver'=>'Consultar '.mb_strtolower($domain),'acceder'=>'Acceder a '.mb_strtolower($domain),
                'reset_password'=>'Restablecer acceso de usuarios',
                'asignar_roles'=>'Asignar el rol principal a usuarios',
                'solicitudes'=>match($scope){'crear'=>'Solicitar un rol complementario','ver'=>'Consultar solicitudes de roles','analizar'=>'Revisar cartas de autorización','cancelar'=>'Cancelar una solicitud de rol',default=>'Administrar solicitudes de roles'},
                'permisos'=>'Asignar permisos a roles','documentos'=>'Consultar cartas de autorización',
                default=>ucfirst(str_replace('_',' ',$action)).' '.mb_strtolower($domain),
                },
            },
            'scope_label' => match ($scope) {
                'global' => 'Toda la plataforma',
                'institucional' => 'Institución',
                'curso', 'asignados' => 'Cursos asignados',
                'propio', 'propia', 'propios', 'propias' => 'Información propia',
                default => count($parts) === 1 ? 'Compatibilidad con módulo existente' : 'Según autorización de la operación',
            },
            'critical' => in_array($name, ['cursos.gestionar.global', 'Panel_Administrador', 'Gestion_Roles_Permisos', 'Gestion_Usuarios', 'Bitacora', 'roles-permisos.gestionar', 'usuarios.asignar_roles', 'bitacora.ver.global', 'calificaciones.gestionar.global', 'calificaciones.rectificar', 'orientacion.configurar', 'conocimiento.revisar'], true)
                || str_starts_with($name, 'roles.')
                || in_array($action, ['desactivar', 'reset_password', 'eliminar'], true),
        ];
    }
}
