<?php

namespace App\Support\Bitacora;

use App\Models\Oficial\Academico\Bitacora;
use Illuminate\Support\Str;

/** Lectura determinista de eventos. No altera evidencias ni interpreta valores privados. */
class BitacoraInteligente
{
    public function accionInstitucional(?string $accion): string
    {
        return match (mb_strtoupper($accion ?? '')) {
            /*
            |--------------------------------------------------------------------------
            | Usuarios
            |--------------------------------------------------------------------------
            */
            'CREAR_USUARIO' => 'Registro de cuenta institucional',
            'ACTUALIZAR_USUARIO' => 'Actualización de cuenta institucional',
            'DESACTIVAR_USUARIO' => 'Desactivación de cuenta institucional',
            'REACTIVAR_USUARIO' => 'Reactivación de cuenta institucional',
            'ACTIVAR_USUARIOS_LOTE' => 'Reactivación masiva de cuentas',
            'DESACTIVAR_USUARIOS_LOTE' => 'Desactivación masiva de cuentas',
            'SINCRONIZAR_DATOS_USUARIOS' => 'Sincronización de usuarios ejecutada',
            'SINCRONIZAR_PERFILES_USUARIO' => 'Sincronización de perfiles institucionales',
            'REVISAR_SINCRONIZACION_USUARIOS' => 'Revisión de sincronización institucional',
            'ERROR_SINCRONIZAR_DATOS_USUARIOS' => 'Error durante sincronización institucional',
            'ERROR_CREAR_USUARIO' => 'Error al registrar cuenta institucional',
            'ERROR_ACTUALIZAR_USUARIO' => 'Error al actualizar cuenta institucional',

            /*
            |--------------------------------------------------------------------------
            | Personas
            |--------------------------------------------------------------------------
            */
            'CREAR_PERSONA' => 'Registro de persona',
            'ACTUALIZAR_PERSONA' => 'Actualización de datos personales',
            'DESACTIVAR_PERSONA' => 'Desactivación de persona',
            'REACTIVAR_PERSONA' => 'Reactivación de persona',
            'ELIMINAR_FOTO_PERSONA' => 'Eliminación de fotografía personal',

            /*
            |--------------------------------------------------------------------------
            | Estudiantes
            |--------------------------------------------------------------------------
            */
            'REGISTRAR_ESTUDIANTE' => 'Registro académico de estudiante',
            'EDITAR_ESTUDIANTE' => 'Actualización académica de estudiante',
            'DESACTIVAR_ESTUDIANTE' => 'Desactivación de estudiante',
            'REACTIVAR_ESTUDIANTE' => 'Reactivación de estudiante',
            'MARCAR_ESTUDIANTE_RETIRADO' => 'Cambio de estado a retirado',
            'MARCAR_ESTUDIANTE_OBSERVADO' => 'Cambio de estado a observado',
            'MARCAR_ESTUDIANTE_EGRESADO' => 'Cambio de estado a egresado',
            'MARCAR_ESTUDIANTE_TRASLADADO' => 'Cambio de estado a trasladado',

            /*
            |--------------------------------------------------------------------------
            | Inscripciones
            |--------------------------------------------------------------------------
            */
            'INSCRIBIR_ESTUDIANTE' => 'Inscripción académica registrada',
            'ACTUALIZAR_INSCRIPCION_ESTUDIANTE' => 'Actualización de inscripción académica',

            /*
            |--------------------------------------------------------------------------
            | Personal institucional
            |--------------------------------------------------------------------------
            */
            'ASIGNAR_MATERIA_MANANA' => 'Asignación de materia curricular',
            'ASIGNAR_ESPECIALIDAD_TARDE' => 'Asignación de especialidad técnica',
            'EDITAR_DOCENTE' => 'Actualización de información docente',
            'DESACTIVAR_DOCENTE' => 'Desactivación de docente',
            'REACTIVAR_DOCENTE' => 'Reactivación de docente',

            'LOGIN_GOOGLE_EXITOSO' => 'Acceso con Google',
            'LOGIN_EXITOSO' => 'Inicio de sesión',
            'LOGIN_FALLIDO', 'LOGIN_GOOGLE_FALLIDO' => 'Intento de acceso',
            'LOGOUT', 'CERRAR_SESION' => 'Cierre de sesión',
            'CALENDARIO_EVENTO_CONFIRMADO' => 'Confirmación de evento del calendario',
            'CALENDARIO_EVENTO_CANCELADO' => 'Cancelación de evento del calendario',
            'CALENDARIO_EVENTO_CREADO' => 'Registro de evento del calendario',
            'CALENDARIO_EVENTO_ACTUALIZADO' => 'Actualización de evento del calendario',
            default => $this->formatearAccion($accion),
        };
    }

    public function tablaInstitucional(?string $tabla): string
    {
        return match ($tabla) {
            'users' => 'Usuarios del sistema',
            'persona' => 'Personas registradas',
            'estudiante' => 'Estudiantes',
            'inscripcion_estudiante' => 'Inscripciones académicas',
            'docente' => 'Docentes',
            'personal_institucional' => 'Personal institucional',
            'plan_asignatura' => 'Planificación de materias',
            'plan_especialidad' => 'Planificación de especialidades',
            'documento_inscripcion_estudiante' => 'Documentación de inscripción',
            'calendario_evento' => 'Calendario institucional',
            'gestion_academica' => 'Gestión académica',
            'curso' => 'Cursos',
            'turno' => 'Turnos',
            'asignatura' => 'Asignaturas',
            'bitacora' => 'Bitácora institucional',
            'roles' => 'Roles del sistema',
            'permissions' => 'Permisos del sistema',
            'model_has_roles' => 'Asignación de roles',
            'model_has_permissions' => 'Asignación de permisos',
            default => 'Área institucional',
        };
    }

    private function formatearAccion(?string $accion): string
    {
        // Las descripciones legibles existentes se conservan; códigos nuevos quedan en el detalle técnico.
        if (filled($accion) && ! str_contains($accion, '_') && str_contains(trim($accion), ' ')) {
            if (str_starts_with($accion, 'Se aplicó la sugerencia de curso')) {
                return 'Sugerencia de curso aplicada';
            }
            if (str_starts_with($accion, 'Se registró una actualización en el módulo de inscripciones')) {
                return 'Actualización de inscripción';
            }

            return Str::limit(trim($accion), 150);
        }

        return 'Actividad institucional registrada';
    }

    public function presentar(Bitacora $evento): array
    {
        $resultado = match ($evento->res_bit) {
            'EXITOSO' => 'Completado',
            'FALLIDO' => 'No se completó',
            'BLOQUEADO' => 'Bloqueado',
            default => 'Resultado no informado',
        };
        $color = match ($evento->res_bit) {
            'FALLIDO' => 'ui-badge-danger',
            'BLOQUEADO' => 'ui-badge-warning',
            'EXITOSO' => 'ui-badge-success',
            default => 'ui-badge-info',
        };
        $usuario = $evento->usuario;
        $persona = $usuario?->persona;
        $actor = trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? ''));
        $actor = $actor ?: ($usuario ? 'Usuario institucional' : 'Autor no informado');
        $area = $this->tablaInstitucional($evento->tab_bit);
        // No mostrar IP, correos, errores internos ni los JSON anteriores/nuevos en el tablero.
        $detalle = $actor.' · '.$area;
        if (filled($evento->des_bit) && $evento->des_bit !== $this->accionInstitucional($evento->acc_bit)
            && ! preg_match('/(SQLSTATE|password|token|secret|\bSELECT\b)/i', $evento->des_bit)) {
            $descripcion = str_replace('utilizando Google OAuth', 'con la cuenta de Google', strip_tags($evento->des_bit));
            $detalle .= '. '.Str::limit($descripcion, 220);
        }
        $fecha = $evento->fec_bit?->copy()->timezone('America/La_Paz');

        return [
            'titulo' => $this->accionInstitucional($evento->acc_bit),
            'detalle' => $detalle,
            'fecha' => $fecha?->locale('es')->diffForHumans() ?? 'Sin fecha registrada',
            'fecha_completa' => $fecha?->format('d/m/Y H:i') ?? 'Sin fecha registrada',
            'resultado' => $resultado,
            'color' => $color,
            'icono' => match ($evento->res_bit) {
                'EXITOSO' => 'ph-check-circle', 'FALLIDO' => 'ph-warning-circle',
                'BLOQUEADO' => 'ph-lock-key', default => 'ph-clock-counter-clockwise',
            },
        ];
    }
}
