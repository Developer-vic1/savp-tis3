<?php

namespace App\Support\Usuarios;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\User;
use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsuarioInteligente extends SoporteInteligenteBase
{
    public const ROLES_ADMINISTRATIVOS = [
        'Administrador',
        'Director',
        'Secretaria',
        'Secretaria Académica',
        'Regente',
    ];

    /**
     * Analiza en tiempo real los datos para la creación de un nuevo usuario.
     */
    public function analizarCreacion(array $datos): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $resumen = [];
        $fuentes = ['Manual de Funciones y Roles SAVP', 'RBAC / Spatie Permission'];

        $codPer = trim((string) ($datos['cod_per'] ?? ''));
        $email = trim((string) ($datos['email'] ?? ''));
        $rol = trim((string) ($datos['role'] ?? $datos['rol'] ?? ''));
        $estado = trim((string) ($datos['est_usu'] ?? 'ACTIVO'));

        // 1. Verificación de la Persona asociada
        if ($codPer === '') {
            $msg = 'Debe seleccionar una persona existente para crear un usuario.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'USR_PERSONA_REQUERIDA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $persona = Persona::find($codPer);
            if (! $persona) {
                $msg = "La persona seleccionada ({$codPer}) no existe en el sistema.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'USR_PERSONA_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            } else {
                $datosCalculados['persona'] = [
                    'cod_per' => $persona->cod_per,
                    'nombre_completo' => trim("{$persona->nom_per} {$persona->ape_pat_per} {$persona->ape_mat_per}"),
                    'ci' => $persona->ci_per,
                    'estado' => $persona->est_per,
                ];

                $estaActiva = $persona->est_per === true || $persona->est_per === 1 || Str::upper((string) $persona->est_per) === 'ACTIVO' || Str::upper((string) $persona->est_per) === '1';
                if (! $estaActiva) {
                    $msg = 'La persona seleccionada se encuentra en estado INACTIVO. Debe reactivar a la persona antes de crearle un usuario.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'USR_PERSONA_INACTIVA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO, ['cod_per' => $codPer]);
                }

                // Ya tiene usuario asignado
                $usuarioExistente = User::where('cod_per', $codPer)->first();
                if ($usuarioExistente) {
                    $msg = "La persona seleccionada ya tiene una cuenta de usuario asignada ({$usuarioExistente->email} / {$usuarioExistente->cod_usu}).";
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'USR_DUPLICADO_PERSONA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['cod_usu' => $usuarioExistente->cod_usu]);
                }

                // Sugerencia sobre el correo si difiere del correo de persona
                if ($email !== '' && $persona->ema_per && Str::lower($email) !== Str::lower($persona->ema_per)) {
                    $sug = "El correo ingresado ({$email}) difiere del correo registrado en la ficha de la persona ({$persona->ema_per}).";
                    $sugerencias[] = $sug;
                    $this->registrarHallazgo($hallazgos, 'USR_CORREO_DIFERENTE_PERSONA', self::TIPO_RECOMENDACION, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
                }
            }
        }

        // 2. Verificación de Correo Único
        if ($email !== '') {
            $emailExiste = User::where('email', Str::lower($email))->exists();
            if ($emailExiste) {
                $msg = "El correo electrónico '{$email}' ya se encuentra registrado por otro usuario.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'USR_CORREO_DUPLICADO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
            }
        }

        // 3. Rol asignado
        if ($rol === '') {
            $msg = 'Debe seleccionar un rol para el usuario.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'USR_SIN_ROL', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } elseif ($codPer !== '') {
            $compatibilidad = $this->verificarCompatibilidadRol($codPer, $rol);
            if (! empty($compatibilidad['advertencias'])) {
                $advertencias = array_merge($advertencias, $compatibilidad['advertencias']);
                foreach ($compatibilidad['hallazgos'] as $h) {
                    $hallazgos[] = $h;
                }
            }
        }

        $resumen = [
            'total_bloqueos' => count($bloqueos),
            'total_advertencias' => count($advertencias),
            'total_sugerencias' => count($sugerencias),
        ];

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            resumen: $resumen,
            fuentesRegla: $fuentes
        );
    }

    /**
     * Analiza la edición de un usuario existente.
     */
    public function analizarEdicion(string $codUsu, array $datos): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $resumen = [];
        $fuentes = ['Manual de Funciones y Roles SAVP', 'Seguridad y Accesos'];

        $usuario = User::with('roles')->find($codUsu);
        if (! $usuario) {
            $msg = "El usuario '{$codUsu}' no existe en el sistema.";
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'USR_NO_EXISTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);

            return $this->construirResultado(
                puedeContinuar: false,
                puedeGuardar: false,
                bloqueos: $bloqueos,
                hallazgos: $hallazgos,
                fuentesRegla: $fuentes
            );
        }

        $emailNuevo = trim((string) ($datos['email'] ?? $usuario->email));
        $nuevoRol = trim((string) ($datos['role'] ?? $datos['rol'] ?? ''));
        $nuevoEstado = trim((string) ($datos['est_usu'] ?? $usuario->est_usu));

        $datosCalculados['usuario'] = [
            'cod_usu' => $usuario->cod_usu,
            'cod_per' => $usuario->cod_per,
            'email_actual' => $usuario->email,
            'rol_actual' => $usuario->roles->pluck('name')->first() ?? 'Sin Rol',
            'estado_actual' => $usuario->est_usu,
            'google_vinculado' => ! empty($usuario->google_id),
        ];

        // Validar correo duplicado en otro usuario
        if ($emailNuevo !== '' && Str::lower($emailNuevo) !== Str::lower($usuario->email)) {
            $duplicado = User::where('email', Str::lower($emailNuevo))
                ->where('cod_usu', '!=', $codUsu)
                ->exists();
            if ($duplicado) {
                $msg = "El correo electrónico '{$emailNuevo}' ya está en uso por otro usuario.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'USR_CORREO_DUPLICADO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
            }
            if (! empty($usuario->google_id)) {
                $adv = 'El usuario tiene vinculada una cuenta de Google OAuth. Cambiar su correo puede afectar el inicio de sesión con Google.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'USR_GOOGLE_OAUTH_ACTIVO', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO);
            }
        }

        // Validar cambio de rol e impacto
        $rolActual = $usuario->roles->pluck('name')->first() ?? '';
        if ($nuevoRol !== '' && $nuevoRol !== $rolActual) {
            $impactoRol = $this->analizarCambioRol($codUsu, $nuevoRol);
            $advertencias = array_merge($advertencias, $impactoRol['advertencias']);
            $sugerencias = array_merge($sugerencias, $impactoRol['sugerencias']);
            foreach ($impactoRol['hallazgos'] as $h) {
                $hallazgos[] = $h;
            }
            $impacto['cambio_rol'] = [
                'anterior' => $rolActual,
                'nuevo' => $nuevoRol,
            ];
        }

        // Validar cambio a INACTIVO
        if ($nuevoEstado === 'INACTIVO' && $usuario->est_usu === 'ACTIVO') {
            $desactivacion = $this->analizarDesactivacion($codUsu);
            $advertencias = array_merge($advertencias, $desactivacion['advertencias']);
            $bloqueos = array_merge($bloqueos, $desactivacion['bloqueos']);
            foreach ($desactivacion['hallazgos'] as $h) {
                $hallazgos[] = $h;
            }
            $impacto['desactivacion'] = $desactivacion['impacto'];
        }

        $resumen = [
            'total_bloqueos' => count($bloqueos),
            'total_advertencias' => count($advertencias),
            'total_sugerencias' => count($sugerencias),
        ];

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            resumen: $resumen,
            fuentesRegla: $fuentes
        );
    }

    /**
     * Analiza el impacto de desactivar un usuario.
     */
    public function analizarDesactivacion(string $codUsu): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $impacto = [];

        $usuarioActual = Auth::user();
        if ($usuarioActual && $usuarioActual->cod_usu === $codUsu) {
            $msg = 'No puede desactivar su propia cuenta de usuario en sesión activa.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'USR_PROPIA_CUENTA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
        }

        $usuario = User::find($codUsu);
        if ($usuario) {
            // Verificar si es Docente con materias activas
            if (Schema::hasTable('docente') && Schema::hasTable('plan_asignatura')) {
                $docente = Docente::whereHas('personalInstitucional', fn($q) => $q->where('cod_per', $usuario->cod_per))->first();
                if ($docente) {
                    $clasesAsignadas = DB::table('plan_asignatura')
                        ->where('cod_doc', $docente->cod_doc)
                        ->where('est_pas', 'ACTIVO')
                        ->count();

                    if ($clasesAsignadas > 0) {
                        $adv = "El usuario corresponde a un Docente con {$clasesAsignadas} asignación(es) académica(s) activa(s). Al desactivarlo no podrá ingresar al aula virtual.";
                        $advertencias[] = $adv;
                        $impacto['clases_activas'] = $clasesAsignadas;
                        $this->registrarHallazgo($hallazgos, 'USR_DESACTIVACION_DOCENTE_ACTIVO', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['clases' => $clasesAsignadas]);
                    }
                }
            }

            // Verificar si es el único Administrador activo
            if ($usuario->hasRole('Administrador')) {
                $otrosAdmins = User::role('Administrador')
                    ->where('cod_usu', '!=', $codUsu)
                    ->where('est_usu', 'ACTIVO')
                    ->count();

                if ($otrosAdmins === 0) {
                    $msg = 'No se puede desactivar al único Administrador activo del sistema.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'USR_ULTIMO_ADMIN', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
                }
            }
        }

        return [
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
            'hallazgos' => $hallazgos,
            'impacto' => $impacto,
        ];
    }

    /**
     * Analiza la compatibilidad de roles con la base de datos institucional.
     */
    private function verificarCompatibilidadRol(string $codPer, string $rol): array
    {
        $advertencias = [];
        $hallazgos = [];

        if ($rol === 'Docente' && Schema::hasTable('docente')) {
            $esDocente = Docente::whereHas('personalInstitucional', fn($q) => $q->where('cod_per', $codPer))->exists();
            if (! $esDocente) {
                $adv = "La persona no está registrada en el plantel docente (tabla 'docente'). Se creará el usuario, pero requerirá registro docente para impartir clases.";
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'USR_ROL_DOCENTE_SIN_REGISTRO', self::TIPO_NORMATIVA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO);
            }
        }

        if ($rol === 'Estudiante' && Schema::hasTable('estudiante')) {
            $esEstudiante = Estudiante::where('cod_per', $codPer)->exists();
            if (! $esEstudiante) {
                $adv = "La persona no está registrada en el padrón de estudiantes (tabla 'estudiante').";
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'USR_ROL_ESTUDIANTE_SIN_REGISTRO', self::TIPO_NORMATIVA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO);
            }
        }

        return [
            'advertencias' => $advertencias,
            'hallazgos' => $hallazgos,
        ];
    }

    /**
     * Analiza el impacto de cambiar el rol de un usuario.
     */
    public function analizarCambioRol(string $codUsu, string $nuevoRol): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];

        $usuario = User::find($codUsu);
        if ($usuario) {
            $rolActual = $usuario->roles->pluck('name')->first();
            if ($rolActual && $rolActual !== $nuevoRol) {
                $adv = "El usuario cambiará de rol de '{$rolActual}' a '{$nuevoRol}'. Se actualizarán sus permisos de acceso.";
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'USR_CAMBIO_ROL', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, [
                    'rol_anterior' => $rolActual,
                    'rol_nuevo' => $nuevoRol,
                ]);

                if ($rolActual === 'Docente' && $nuevoRol !== 'Docente') {
                    $sug = 'Verifique si el docente tiene clases asignadas en el período activo para reasignarlas antes de cambiar su rol.';
                    $sugerencias[] = $sug;
                    $this->registrarHallazgo($hallazgos, 'USR_REASIGNAR_CLASES_DOCENTE', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
                }
            }
        }

        return [
            'advertencias' => $advertencias,
            'sugerencias' => $sugerencias,
            'hallazgos' => $hallazgos,
        ];
    }
}
