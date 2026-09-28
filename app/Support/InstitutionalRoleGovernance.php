<?php

namespace App\Support;

use Illuminate\Support\Str;

final class InstitutionalRoleGovernance
{
    public const KNOWLEDGE_VERSION = 'SAVP-INSTITUCIONAL-2026-1';

    private const INSTITUTIONAL = ['Administrador', 'Director', 'Secretaria', 'Regente', 'Docente', 'Estudiante'];
    private const RESERVED = ['root', 'system', 'god', 'superadmin', 'superusuario', 'super admin', 'administrador general'];
    private const ALIASES = [
        'Administrador' => ['administrador', 'admin', 'superadmin', 'super admin', 'administrador general'],
        'Director' => ['director', 'directora'],
        'Secretaria' => ['secretaria', 'secretario', 'oficinista', 'auxiliar de secretaria'],
        'Regente' => ['regente', 'regencia', 'jefe de asistencia', 'controlador de estudiantes'],
        'Docente' => ['docente', 'profesor', 'profesora', 'maestro', 'maestra'],
        'Estudiante' => ['estudiante', 'alumno', 'alumna'],
    ];
    private const FUNCTION_DOMAINS = [
        'Secretaria' => ['inscripcion', 'inscripciones', 'matricula', 'registrar estudiantes', 'informacion personal', 'secretaria'],
        'Regente' => ['asistencia', 'faltas', 'seguimiento de estudiantes', 'supervision de estudiantes', 'regencia'],
        'Docente' => ['ensenanza', 'clases', 'tareas', 'calificar', 'docencia'],
        'Director' => ['direccion academica', 'supervision institucional', 'director'],
    ];
    private const CRITICAL = [
        'Panel_Administrador', 'Gestion_Roles_Permisos', 'roles-permisos.gestionar',
        'usuarios.asignar_roles', 'bitacora.ver.global', 'calificaciones.gestionar.global',
        'calificaciones.rectificar', 'orientacion.configurar', 'roles.crear', 'roles.editar',
        'roles.desactivar', 'roles.permisos.asignar', 'roles.solicitudes.analizar',
    ];
    private const PERMISSION_CONTEXT = [
        'usuarios' => ['usuarios', 'cuentas', 'accesos'],
        'personas' => ['personas', 'datos personales'],
        'estudiantes' => ['estudiantes', 'alumnos', 'alumnas'],
        'cursos' => ['cursos', 'clases', 'grados'],
        'inscripciones' => ['inscripciones', 'matriculas'],
        'calificaciones' => ['calificaciones', 'notas', 'evaluacion'],
        'asistencia' => ['asistencia', 'faltas'],
        'aula' => ['aula', 'materiales', 'tareas', 'entregas'],
        'orientacion' => ['orientacion', 'vocacional'],
        'reportes' => ['reportes', 'informes', 'estadisticas'],
        'bitacora' => ['bitacora', 'auditoria'],
        'integraciones' => ['integraciones'],
    ];

    public function rolesInstitucionales(): array { return self::INSTITUTIONAL; }
    public function rolesReservados(): array { return self::RESERVED; }

    public function analyze(string $name, string $justification, string $functions, array $permissions, array $existingRoles, array $availablePermissions): array
    {
        $reasons = []; $warnings = []; $blocked = []; $suggested = null;
        $normal = $this->normalize($name);
        if (! preg_match('/^[\pL\pM\pN ]{4,80}$/u', trim($name)) || preg_match('/\d/u', $name)) {
            $reasons[] = 'El nombre debe tener entre 4 y 80 caracteres, solo letras y espacios, y no incluir números.';
        }
        if (in_array($normal, self::RESERVED, true)) $reasons[] = 'El nombre está reservado para la administración del sistema.';
        foreach ($existingRoles as $existing) {
            if ($normal === $this->normalize((string) $existing)) {
                $suggested = (string) $existing;
                $reasons[] = 'Ya existe un rol con este nombre.';
                break;
            }
        }
        foreach (self::ALIASES as $role => $aliases) {
            if (in_array($normal, $aliases, true) || preg_match('/^(?:'.implode('|', array_map(fn ($a) => preg_quote($a, '/'), $aliases)).')\s*\d+$/u', $normal)) {
                $suggested = $role;
                $reasons[] = "El nombre propuesto corresponde al rol institucional {$role}.";
                break;
            }
        }
        $description = $this->normalize($name.' '.$functions.' '.$justification);
        if (mb_strlen(trim($justification)) < 30 || mb_strlen(trim($functions)) < 30 || in_array($description, ['porque se necesita', 'otro rol', 'para trabajar'], true)) {
            $reasons[] = 'Describa una necesidad y funciones concretas con al menos 30 caracteres cada una.';
        }
        foreach (self::FUNCTION_DOMAINS as $role => $terms) {
            $matches = 0;
            foreach ($terms as $term) if (str_contains($description, $term)) $matches++;
            if ($matches >= 2) {
                $suggested = $role;
                $reasons[] = "Las funciones declaradas corresponden principalmente al rol {$role}. Revise si requiere una función realmente distinta.";
                break;
            }
        }
        $permissions = array_values(array_unique($permissions));
        if ($permissions === []) $reasons[] = 'Seleccione los permisos mínimos necesarios.';
        foreach ($permissions as $permission) {
            if (! is_string($permission) || ! in_array($permission, $availablePermissions, true)) {
                $blocked[] = (string) $permission;
                $reasons[] = 'Se solicitó un permiso inexistente.';
                continue;
            }
            if (in_array($permission, self::CRITICAL, true) || str_starts_with($permission, 'roles.') || str_ends_with($permission, '.global') || ! str_contains($permission, '.')) {
                $blocked[] = $permission;
                $reasons[] = "El permiso {$permission} exige autoridad elevada y no puede concederse a un rol nuevo por este flujo.";
            }
            $domain = explode('.', $permission)[0];
            $domainMatches = false;
            foreach (self::PERMISSION_CONTEXT[$domain] ?? [] as $term) {
                if (str_contains($description, $term)) { $domainMatches = true; break; }
            }
            if (isset(self::PERMISSION_CONTEXT[$domain]) && ! $domainMatches) {
                $blocked[] = $permission;
                $reasons[] = "Las funciones declaradas no justifican el permiso {$permission}.";
            }
            if (str_ends_with($permission, '.global')) {
                $lower = substr($permission, 0, -7).'.institucional';
                if (in_array($lower, $availablePermissions, true)) $warnings[] = "Considere el menor alcance disponible: {$lower}.";
            }
        }
        if (str_contains($description, 'infraestructura') || str_contains($description, 'biblioteca') || str_contains($description, 'mantenimiento')) {
            $warnings[] = 'Las funciones declaradas no tienen un módulo dedicado en SAVP; requiere revisión institucional.';
        }
        $status = $blocked ? 'PERMISOS_INCOMPATIBLES' : ($suggested ? 'DUPLICA_ROL_EXISTENTE' : ($reasons ? 'RECHAZADO' : ($warnings ? 'REQUIERE_REVISION' : 'APTO')));
        return [
            'status' => $status, 'code' => $status, 'severity' => $status === 'APTO' ? 'success' : 'warning',
            'title' => match ($status) { 'APTO' => 'Solicitud coherente', 'DUPLICA_ROL_EXISTENTE' => 'Rol existente sugerido', 'PERMISOS_INCOMPATIBLES' => 'Permisos bloqueados', default => 'Revisión necesaria' },
            'summary' => $reasons[0] ?? ($warnings[0] ?? 'La necesidad y los permisos son coherentes con las reglas conocidas.'),
            'reasons' => array_values(array_unique($reasons)), 'suggested_role' => $suggested,
            'allowed_permissions' => array_values(array_diff($permissions, $blocked)), 'blocked_permissions' => array_values(array_unique($blocked)),
            'warnings' => $warnings, 'requirements' => ['Autorización documental revisada por un segundo administrador.'],
            'document_requirements' => ['Documento privado legible', 'Autoridad y rol comprobados', 'Firma y sello revisados'],
            'knowledge_version' => self::KNOWLEDGE_VERSION,
        ];
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower(Str::ascii($value))));
    }
}
