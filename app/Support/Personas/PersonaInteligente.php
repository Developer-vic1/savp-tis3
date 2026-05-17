<?php

namespace App\Support\Personas;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PersonaInteligente
{
    // ============================================================
    // PARÁMETROS BASE
    // ============================================================

    public const ESTADO_ACTIVO = 'ACTIVO';
    public const ESTADO_INACTIVO = 'INACTIVO';

    public const ESTADO_VALIDO = 'VALIDO';
    public const ESTADO_OBSERVADO = 'OBSERVADO';
    public const ESTADO_BLOQUEADO = 'BLOQUEADO';
    public const ESTADO_RECUPERABLE = 'RECUPERABLE';
    public const ESTADO_INCOMPLETO = 'INCOMPLETO';

    public const EDAD_MINIMA_ESTUDIANTE = 4;
    public const EDAD_MAXIMA_ESTUDIANTE = 25;
    public const EDAD_MINIMA_PERSONAL = 18;
    public const EDAD_MAXIMA_PERSONA = 120;

    public const TIPOS_VINCULACION = [
        'SOLO_PERSONA',
        'ESTUDIANTE',
        'PERSONAL_INSTITUCIONAL',
        'USUARIO',
        'ESTUDIANTE_USUARIO',
        'PERSONAL_USUARIO',
    ];

    public const EXPEDIDOS_BOLIVIA = [
        'LP',
        'CBBA',
        'SCZ',
        'OR',
        'ORU',
        'PT',
        'CH',
        'TJ',
        'BN',
        'PD',
    ];

    public const GENEROS_SUGERIDOS = [
        'MASCULINO',
        'FEMENINO',
        'OTRO',
        'NO_ESPECIFICA',
    ];

    // ============================================================
    // ANÁLISIS PRINCIPAL EN TIEMPO REAL
    // ============================================================

    public function reconocerEnTiempoReal(array $datos): array
    {
        return $this->analizarRegistro($datos, true);
    }

    public function analizarRegistro(array $datos, bool $modoTiempoReal = false): array
    {
        $datos = $this->normalizarDatos($datos);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! Schema::hasTable('persona')) {
            return $this->resultado(
                puedeContinuar: false,
                estadoInteligente: self::ESTADO_BLOQUEADO,
                nivelRiesgo: 'CRITICO',
                mensaje: 'No existe la tabla persona.',
                bloqueos: ['La tabla persona no está disponible en la base de datos.'],
                advertencias: [],
                sugerencias: ['Verifica las migraciones antes de registrar personas.'],
                coincidencias: [],
                resumen: []
            );
        }

        $identidad = $this->analizarIdentidad($datos, $modoTiempoReal);
        $contacto = $this->analizarContacto($datos, $modoTiempoReal);
        $direccion = $this->analizarDireccion($datos, $modoTiempoReal);
        $edad = $this->analizarEdad($datos['fec_nac_per'], $datos['tipo_vinculacion']);
        $duplicidad = $this->analizarDuplicidad($datos);
        $vinculacion = $this->analizarVinculacionDeseada($datos);

        $bloqueos = array_merge(
            $bloqueos,
            $identidad['bloqueos'],
            $contacto['bloqueos'],
            $direccion['bloqueos'],
            $edad['bloqueos'],
            $duplicidad['bloqueos'],
            $vinculacion['bloqueos']
        );

        $advertencias = array_merge(
            $advertencias,
            $identidad['advertencias'],
            $contacto['advertencias'],
            $direccion['advertencias'],
            $edad['advertencias'],
            $duplicidad['advertencias'],
            $vinculacion['advertencias']
        );

        $sugerencias = array_merge(
            $sugerencias,
            $identidad['sugerencias'],
            $contacto['sugerencias'],
            $direccion['sugerencias'],
            $edad['sugerencias'],
            $duplicidad['sugerencias'],
            $vinculacion['sugerencias']
        );

        $coincidencias = $this->buscarCoincidencias($datos);

        $personaPrincipal = $coincidencias['persona_principal'] ?? null;
        $resumenPersona = $personaPrincipal
            ? $this->resumenPersona((string) $personaPrincipal->cod_per)
            : [];

        $estadoInteligente = $this->determinarEstadoInteligente(
            $bloqueos,
            $advertencias,
            $coincidencias,
            $resumenPersona,
            $modoTiempoReal
        );

        $puedeContinuar = empty($bloqueos);

        if (($coincidencias['duplicado_ci'] ?? false) && ! ($coincidencias['persona_inactiva_recuperable'] ?? false)) {
            $puedeContinuar = false;
        }

        if (($coincidencias['duplicado_correo'] ?? false) && ! ($coincidencias['persona_inactiva_recuperable'] ?? false)) {
            $puedeContinuar = false;
        }

        if ($modoTiempoReal && $this->datosMinimosInsuficientes($datos)) {
            $puedeContinuar = false;
            $estadoInteligente = self::ESTADO_INCOMPLETO;
        }

        return $this->resultado(
            puedeContinuar: $puedeContinuar,
            estadoInteligente: $estadoInteligente,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias, $coincidencias),
            mensaje: $this->mensajePrincipal($estadoInteligente, $coincidencias, $modoTiempoReal),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            coincidencias: $this->formatearCoincidencias($coincidencias),
            resumen: [
                'datos_normalizados' => $datos,
                'direccion_completa_sugerida' => $this->generarDireccionCompleta($datos),
                'edad' => $edad['resumen']['edad'] ?? null,
                'tipo_vinculacion' => $datos['tipo_vinculacion'],
                'persona_detectada' => $resumenPersona,
                'campos_completitud' => $this->calcularCompletitud($datos),
                'accion_recomendada' => $this->sugerirAccion($datos, $coincidencias, $resumenPersona),
            ]
        );
    }

    // ============================================================
    // IDENTIDAD, CONTACTO Y DIRECCIÓN
    // ============================================================

    public function analizarIdentidad(array $datos, bool $modoTiempoReal = false): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! $modoTiempoReal || $this->tieneValor($datos['nom_per'])) {
            if (mb_strlen($datos['nom_per']) < 2) {
                $bloqueos[] = 'El nombre debe tener al menos 2 caracteres.';
            }
        }

        if (! $modoTiempoReal || $this->tieneValor($datos['ape_pat_per'])) {
            if (mb_strlen($datos['ape_pat_per']) < 2) {
                $advertencias[] = 'El apellido paterno está vacío o es demasiado corto.';
                $sugerencias[] = 'Verifica si corresponde registrar apellido materno como apoyo de identificación.';
            }
        }

        if ($this->tieneValor($datos['ci_per'])) {
            if (! preg_match('/^[0-9]{4,12}$/', $datos['ci_per'])) {
                $bloqueos[] = 'El carnet de identidad debe contener solo números y tener entre 4 y 12 dígitos.';
            }
        } elseif (! $modoTiempoReal) {
            $bloqueos[] = 'El carnet de identidad es obligatorio para evitar duplicidad institucional.';
        }

        if ($this->tieneValor($datos['com_per']) && ! preg_match('/^[A-Z0-9-]{1,10}$/', $datos['com_per'])) {
            $advertencias[] = 'El complemento del CI contiene caracteres poco habituales.';
            $sugerencias[] = 'Usa solo letras, números o guion si corresponde.';
        }

        if ($this->tieneValor($datos['exp_per']) && ! in_array($datos['exp_per'], self::EXPEDIDOS_BOLIVIA, true)) {
            $advertencias[] = 'La expedición del CI no coincide con los valores bolivianos sugeridos.';
        }

        if ($this->tieneValor($datos['gen_per']) && ! in_array($datos['gen_per'], self::GENEROS_SUGERIDOS, true)) {
            $advertencias[] = 'El género ingresado no coincide con los valores sugeridos del sistema.';
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'ci_normalizado' => $datos['ci_per'],
                'nombre_completo' => $this->generarNombreCompleto($datos),
            ],
        ];
    }

    public function analizarContacto(array $datos, bool $modoTiempoReal = false): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if ($this->tieneValor($datos['ema_per']) && ! filter_var($datos['ema_per'], FILTER_VALIDATE_EMAIL)) {
            $bloqueos[] = 'El correo electrónico de la persona no tiene un formato válido.';
        }

        if ($this->tieneValor($datos['tel_per']) && ! preg_match('/^[0-9+\-\s]{6,20}$/', $datos['tel_per'])) {
            $advertencias[] = 'El teléfono tiene un formato poco habitual.';
            $sugerencias[] = 'Usa números, espacios, guion o prefijo internacional si corresponde.';
        }

        if (! $modoTiempoReal && ! $this->tieneValor($datos['tel_per']) && ! $this->tieneValor($datos['ema_per'])) {
            $advertencias[] = 'La persona no tiene teléfono ni correo registrados.';
            $sugerencias[] = 'Registra al menos un medio de contacto institucional.';
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'telefono' => $datos['tel_per'],
                'correo' => $datos['ema_per'],
            ],
        ];
    }

    public function analizarDireccion(array $datos, bool $modoTiempoReal = false): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $camposDireccion = [
            'dir_per',
            'zona_per',
            'ave_per',
            'cal_per',
            'num_per',
            'ref_per',
            'ciu_per',
            'mun_per',
            'dep_per',
        ];

        $llenos = collect($camposDireccion)
            ->filter(fn (string $campo) => $this->tieneValor($datos[$campo] ?? null))
            ->count();

        if ($llenos === 0 && ! $modoTiempoReal) {
            $advertencias[] = 'La dirección de la persona está vacía.';
            $sugerencias[] = 'Registra al menos zona, calle o referencia para ubicación institucional.';
        }

        if ($llenos > 0 && ! $this->tieneValor($datos['dir_per'])) {
            $sugerencias[] = 'Se puede generar una dirección completa visible a partir de zona, avenida, calle, número y referencia.';
        }

        if ($this->tieneValor($datos['dep_per']) && mb_strlen($datos['dep_per']) < 3) {
            $advertencias[] = 'El departamento parece incompleto.';
        }

        if ($this->tieneValor($datos['mun_per']) && mb_strlen($datos['mun_per']) < 3) {
            $advertencias[] = 'El municipio parece incompleto.';
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'completitud_direccion' => $llenos,
                'direccion_completa' => $this->generarDireccionCompleta($datos),
            ],
        ];
    }

    public function analizarEdad(?string $fechaNacimiento, string $tipoVinculacion = 'SOLO_PERSONA'): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $edad = null;

        if (! $this->tieneValor($fechaNacimiento)) {
            return compact('bloqueos', 'advertencias', 'sugerencias') + [
                'resumen' => [
                    'edad' => null,
                    'fecha_nacimiento' => null,
                ],
            ];
        }

        try {
            $fecha = Carbon::parse($fechaNacimiento)->startOfDay();

            if ($fecha->greaterThan(now()->startOfDay())) {
                $bloqueos[] = 'La fecha de nacimiento no puede ser futura.';
            } else {
                $edad = $fecha->age;

                if ($edad > self::EDAD_MAXIMA_PERSONA) {
                    $bloqueos[] = 'La edad calculada supera el máximo razonable para el registro institucional.';
                }

                if (in_array($tipoVinculacion, ['ESTUDIANTE', 'ESTUDIANTE_USUARIO'], true)) {
                    if ($edad < self::EDAD_MINIMA_ESTUDIANTE) {
                        $bloqueos[] = 'La edad no corresponde a un registro estudiantil regular.';
                    }

                    if ($edad > self::EDAD_MAXIMA_ESTUDIANTE) {
                        $advertencias[] = 'La edad es alta para estudiante regular.';
                        $sugerencias[] = 'Verifica si corresponde a educación especial, regularización, adulto o registro administrativo.';
                    }
                }

                if (in_array($tipoVinculacion, ['PERSONAL_INSTITUCIONAL', 'PERSONAL_USUARIO'], true) && $edad < self::EDAD_MINIMA_PERSONAL) {
                    $bloqueos[] = 'La persona no cumple la edad mínima para personal institucional.';
                }
            }
        } catch (Throwable) {
            $bloqueos[] = 'La fecha de nacimiento no tiene un formato válido.';
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'edad' => $edad,
                'fecha_nacimiento' => $fechaNacimiento,
            ],
        ];
    }

    // ============================================================
    // DUPLICIDAD Y RECONOCIMIENTO
    // ============================================================

    public function analizarDuplicidad(array $datos): array
    {
        $datos = $this->normalizarDatos($datos);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        $coincidencias = $this->buscarCoincidencias($datos);

        if ($coincidencias['duplicado_ci'] ?? false) {
            if ($coincidencias['persona_inactiva_recuperable'] ?? false) {
                $advertencias[] = 'Ya existe una persona inactiva con este CI.';
                $sugerencias[] = 'Se recomienda reactivar o actualizar la persona existente en lugar de crear un duplicado.';
            } else {
                $bloqueos[] = 'Ya existe una persona activa registrada con este CI.';
                $sugerencias[] = 'Usa la persona existente y vincúlala como estudiante, personal institucional o usuario según corresponda.';
            }
        }

        if ($coincidencias['duplicado_correo'] ?? false) {
            if ($coincidencias['persona_inactiva_recuperable'] ?? false) {
                $advertencias[] = 'El correo pertenece a una persona inactiva recuperable.';
            } else {
                $bloqueos[] = 'Ya existe una persona registrada con este correo electrónico.';
            }
        }

        if ($coincidencias['usuario_existente'] ?? false) {
            $advertencias[] = 'La persona detectada ya tiene usuario de acceso al sistema.';
        }

        if ($coincidencias['estudiante_existente'] ?? false) {
            $advertencias[] = 'La persona detectada ya tiene vinculación como estudiante.';
        }

        if ($coincidencias['personal_existente'] ?? false) {
            $advertencias[] = 'La persona detectada ya tiene vinculación como personal institucional.';
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'coincidencias' => $this->formatearCoincidencias($coincidencias),
            ],
        ];
    }

    public function buscarCoincidencias(array $datos): array
    {
        $datos = $this->normalizarDatos($datos);

        $personasPorCi = collect();
        $personasPorCorreo = collect();
        $personasPorNombre = collect();

        if (Schema::hasTable('persona')) {
            if ($this->tieneValor($datos['ci_per']) && Schema::hasColumn('persona', 'ci_per')) {
                $personasPorCi = DB::table('persona')
                    ->where('ci_per', $datos['ci_per'])
                    ->when(Schema::hasColumn('persona', 'com_per') && $this->tieneValor($datos['com_per']), function ($query) use ($datos) {
                        $query->where(function ($sub) use ($datos) {
                            $sub->where('com_per', $datos['com_per'])
                                ->orWhereNull('com_per')
                                ->orWhere('com_per', '');
                        });
                    })
                    ->limit(5)
                    ->get();
            }

            if ($this->tieneValor($datos['ema_per']) && Schema::hasColumn('persona', 'ema_per')) {
                $personasPorCorreo = DB::table('persona')
                    ->whereRaw('LOWER(ema_per) = ?', [$datos['ema_per']])
                    ->limit(5)
                    ->get();
            }

            if (
                mb_strlen($datos['nom_per']) >= 2
                && mb_strlen($datos['ape_pat_per']) >= 2
                && Schema::hasColumn('persona', 'nom_per')
                && Schema::hasColumn('persona', 'ape_pat_per')
            ) {
                $personasPorNombre = DB::table('persona')
                    ->whereRaw('LOWER(nom_per) LIKE ?', ['%' . mb_strtolower($datos['nom_per']) . '%'])
                    ->whereRaw('LOWER(ape_pat_per) LIKE ?', ['%' . mb_strtolower($datos['ape_pat_per']) . '%'])
                    ->limit(8)
                    ->get();
            }
        }

        $principal = $personasPorCi->first()
            ?? $personasPorCorreo->first()
            ?? $personasPorNombre->first();

        $codPer = $principal->cod_per ?? null;

        $vinculaciones = $codPer
            ? $this->obtenerVinculaciones((string) $codPer)
            : [];

        $personaInactiva = $principal
            ? ! $this->personaEstaActiva($principal)
            : false;

        return [
            'persona_principal' => $principal,
            'por_ci' => $personasPorCi,
            'por_correo' => $personasPorCorreo,
            'por_nombre' => $personasPorNombre,
            'duplicado_ci' => $personasPorCi->isNotEmpty(),
            'duplicado_correo' => $personasPorCorreo->isNotEmpty(),
            'posible_homonimo' => $personasPorNombre->isNotEmpty() && $personasPorCi->isEmpty() && $personasPorCorreo->isEmpty(),
            'persona_inactiva_recuperable' => $personaInactiva,
            'usuario_existente' => (int) ($vinculaciones['usuario']['cantidad'] ?? 0) > 0,
            'estudiante_existente' => (int) ($vinculaciones['estudiante']['cantidad'] ?? 0) > 0,
            'personal_existente' => (int) ($vinculaciones['personal_institucional']['cantidad'] ?? 0) > 0,
            'vinculaciones' => $vinculaciones,
        ];
    }

    // ============================================================
    // VINCULACIONES
    // ============================================================

    public function analizarVinculacionDeseada(array $datos): array
    {
        $datos = $this->normalizarDatos($datos);

        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (! in_array($datos['tipo_vinculacion'], self::TIPOS_VINCULACION, true)) {
            $bloqueos[] = 'El tipo de vinculación solicitado no es válido.';
            $sugerencias[] = 'Usa SOLO_PERSONA, ESTUDIANTE, PERSONAL_INSTITUCIONAL, USUARIO, ESTUDIANTE_USUARIO o PERSONAL_USUARIO.';
        }

        if (in_array($datos['tipo_vinculacion'], ['ESTUDIANTE', 'ESTUDIANTE_USUARIO'], true)) {
            if (! Schema::hasTable('estudiante')) {
                $bloqueos[] = 'No existe la tabla estudiante para crear la vinculación académica.';
            }

            if (! $this->tieneValor($datos['rud_est'])) {
                $advertencias[] = 'No se proporcionó RUDE para la vinculación estudiantil.';
                $sugerencias[] = 'Para crear estudiante, registra el RUDE antes de finalizar la vinculación.';
            } elseif (Schema::hasTable('estudiante') && Schema::hasColumn('estudiante', 'rud_est')) {
                $existeRude = DB::table('estudiante')
                    ->where('rud_est', $this->normalizarTextoBasico($datos['rud_est']))
                    ->exists();

                if ($existeRude) {
                    $bloqueos[] = 'Ya existe un estudiante registrado con ese RUDE.';
                }
            }
        }

        if (in_array($datos['tipo_vinculacion'], ['PERSONAL_INSTITUCIONAL', 'PERSONAL_USUARIO'], true)) {
            if (! Schema::hasTable('personal_institucional')) {
                $bloqueos[] = 'No existe la tabla personal_institucional para crear la vinculación institucional.';
            }

            if (! $this->tieneValor($datos['car_pin'])) {
                $advertencias[] = 'No se proporcionó cargo institucional.';
                $sugerencias[] = 'Para personal institucional, registra el cargo o función principal.';
            }
        }

        if (in_array($datos['tipo_vinculacion'], ['USUARIO', 'ESTUDIANTE_USUARIO', 'PERSONAL_USUARIO'], true)) {
            if (! Schema::hasTable('users')) {
                $bloqueos[] = 'No existe la tabla users para crear acceso al sistema.';
            }

            if (! $this->tieneValor($datos['email_usuario']) && ! $this->tieneValor($datos['ema_per'])) {
                $bloqueos[] = 'Para crear usuario se requiere un correo electrónico.';
            }

            $email = $this->normalizarCorreo($datos['email_usuario'] ?: $datos['ema_per']);

            if ($email && Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
                $existeUsuario = DB::table('users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->exists();

                if ($existeUsuario) {
                    $bloqueos[] = 'Ya existe un usuario con el correo indicado.';
                }
            }
        }

        return compact('bloqueos', 'advertencias', 'sugerencias') + [
            'resumen' => [
                'tipo_vinculacion' => $datos['tipo_vinculacion'],
            ],
        ];
    }

    public function analizarVinculacion(string $codPer, string $tipoVinculacion): array
    {
        $persona = $this->obtenerPersona($codPer);

        if (! $persona) {
            return $this->resultado(
                puedeContinuar: false,
                estadoInteligente: self::ESTADO_BLOQUEADO,
                nivelRiesgo: 'ALTO',
                mensaje: 'La persona seleccionada no existe.',
                bloqueos: ['No se encontró la persona en la base de datos.'],
                advertencias: [],
                sugerencias: [],
                coincidencias: [],
                resumen: []
            );
        }

        $vinculaciones = $this->obtenerVinculaciones($codPer);
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];

        if (in_array($tipoVinculacion, ['ESTUDIANTE', 'ESTUDIANTE_USUARIO'], true) && ($vinculaciones['estudiante']['cantidad'] ?? 0) > 0) {
            $bloqueos[] = 'La persona ya está vinculada como estudiante.';
            $sugerencias[] = 'Abre su ficha estudiantil existente en lugar de crear una nueva.';
        }

        if (in_array($tipoVinculacion, ['PERSONAL_INSTITUCIONAL', 'PERSONAL_USUARIO'], true) && ($vinculaciones['personal_institucional']['cantidad'] ?? 0) > 0) {
            $bloqueos[] = 'La persona ya está vinculada como personal institucional.';
            $sugerencias[] = 'Actualiza su cargo o rol institucional existente.';
        }

        if (in_array($tipoVinculacion, ['USUARIO', 'ESTUDIANTE_USUARIO', 'PERSONAL_USUARIO'], true) && ($vinculaciones['usuario']['cantidad'] ?? 0) > 0) {
            $bloqueos[] = 'La persona ya tiene usuario de acceso al sistema.';
            $sugerencias[] = 'Gestiona el acceso desde usuarios y roles.';
        }

        if (! $this->personaEstaActiva($persona)) {
            $advertencias[] = 'La persona está inactiva.';
            $sugerencias[] = 'Reactiva la persona antes de crear nuevas vinculaciones.';
        }

        return $this->resultado(
            puedeContinuar: empty($bloqueos),
            estadoInteligente: empty($bloqueos) ? self::ESTADO_VALIDO : self::ESTADO_BLOQUEADO,
            nivelRiesgo: $this->nivelRiesgo($bloqueos, $advertencias, []),
            mensaje: empty($bloqueos)
                ? 'La persona puede vincularse con el tipo solicitado.'
                : 'La vinculación solicitada está bloqueada.',
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            coincidencias: [],
            resumen: [
                'persona' => $this->resumenPersona($codPer),
                'vinculaciones' => $vinculaciones,
            ]
        );
    }

    public function obtenerVinculaciones(string $codPer): array
    {
        $resultado = [
            'usuario' => $this->consultaVinculacionDirecta('users', 'cod_per', $codPer, 'cod_usu', 'est_usu'),
            'estudiante' => $this->consultaVinculacionDirecta('estudiante', 'cod_per', $codPer, 'cod_est', 'est_est'),
            'personal_institucional' => $this->consultaVinculacionDirecta('personal_institucional', 'cod_per', $codPer, 'cod_pin', 'est_pin'),
            'roles_personal' => [],
        ];

        $personal = $resultado['personal_institucional']['registros'] ?? [];

        foreach ($personal as $registro) {
            $codPin = $registro['codigo'] ?? null;

            if ($codPin) {
                $resultado['roles_personal'] = array_merge(
                    $resultado['roles_personal'],
                    $this->detectarRolesPersonal((string) $codPin)
                );
            }
        }

        return $resultado;
    }

    private function detectarRolesPersonal(string $codPin): array
    {
        $tablas = [
            'docente' => ['pk' => 'cod_doc', 'estado' => 'est_doc', 'nombre' => 'Docente'],
            'regente' => ['pk' => 'cod_reg', 'estado' => 'est_reg', 'nombre' => 'Regente'],
            'director' => ['pk' => 'cod_dir', 'estado' => 'est_dir', 'nombre' => 'Director'],
            'secretaria' => ['pk' => 'cod_sec', 'estado' => 'est_sec', 'nombre' => 'Secretaria'],
            'administrador' => ['pk' => 'cod_adm', 'estado' => 'est_adm', 'nombre' => 'Administrador'],
        ];

        $roles = [];

        foreach ($tablas as $tabla => $config) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'cod_pin')) {
                continue;
            }

            $pk = Schema::hasColumn($tabla, $config['pk']) ? $config['pk'] : 'cod_pin';
            $estado = Schema::hasColumn($tabla, $config['estado']) ? $config['estado'] : null;

            $registros = DB::table($tabla)
                ->where('cod_pin', $codPin)
                ->get()
                ->map(function ($row) use ($pk, $estado, $config, $tabla) {
                    return [
                        'tabla' => $tabla,
                        'rol' => $config['nombre'],
                        'codigo' => $row->{$pk} ?? null,
                        'estado' => $estado ? ($row->{$estado} ?? null) : null,
                    ];
                })
                ->values()
                ->all();

            if (! empty($registros)) {
                $roles = array_merge($roles, $registros);
            }
        }

        return $roles;
    }

    // ============================================================
    // RESÚMENES Y SUGERENCIAS
    // ============================================================

    public function resumenPersona(string $codPer): array
    {
        $persona = $this->obtenerPersona($codPer);

        if (! $persona) {
            return [
                'existe' => false,
                'mensaje' => 'Persona no encontrada.',
            ];
        }

        $datos = $this->objetoPersonaADatos($persona);

        return [
            'existe' => true,
            'cod_per' => $persona->cod_per,
            'nombre_completo' => $this->generarNombreCompleto($datos),
            'ci' => $persona->ci_per ?? null,
            'complemento' => $persona->com_per ?? null,
            'expedido' => $persona->exp_per ?? null,
            'correo' => $persona->ema_per ?? null,
            'telefono' => $persona->tel_per ?? null,
            'estado' => $this->personaEstaActiva($persona) ? self::ESTADO_ACTIVO : self::ESTADO_INACTIVO,
            'fecha_nacimiento' => $persona->fec_nac_per ?? null,
            'edad' => $this->calcularEdad($persona->fec_nac_per ?? null),
            'direccion' => $this->generarDireccionCompleta($datos),
            'completitud' => $this->calcularCompletitud($datos),
            'vinculaciones' => $this->obtenerVinculaciones((string) $persona->cod_per),
        ];
    }

    public function sugerirAccion(array $datos, array $coincidencias = [], array $resumenPersona = []): array
    {
        if (($coincidencias['persona_inactiva_recuperable'] ?? false) === true) {
            return [
                'accion' => 'REACTIVAR_PERSONA',
                'titulo' => 'Reactivar persona existente',
                'descripcion' => 'Existe una persona inactiva con coincidencia fuerte. Conviene reactivarla para conservar trazabilidad.',
            ];
        }

        if (($coincidencias['duplicado_ci'] ?? false) || ($coincidencias['duplicado_correo'] ?? false)) {
            return [
                'accion' => 'USAR_EXISTENTE',
                'titulo' => 'Usar persona existente',
                'descripcion' => 'La identidad ya está registrada. No se debe crear duplicado.',
            ];
        }

        if (($coincidencias['posible_homonimo'] ?? false)) {
            return [
                'accion' => 'REVISAR_HOMONIMO',
                'titulo' => 'Revisar posible homónimo',
                'descripcion' => 'Existe una persona con nombre similar. Verifica CI, correo o fecha de nacimiento antes de registrar.',
            ];
        }

        if ($this->datosMinimosInsuficientes($datos)) {
            return [
                'accion' => 'COMPLETAR_DATOS',
                'titulo' => 'Completar datos mínimos',
                'descripcion' => 'Faltan datos esenciales para reconocer correctamente a la persona.',
            ];
        }

        return [
            'accion' => 'CREAR_PERSONA',
            'titulo' => 'Registrar nueva persona',
            'descripcion' => 'No se detectan duplicados fuertes. La persona puede continuar a registro y vinculación.',
        ];
    }

    public function calcularCompletitud(array $datos): array
    {
        $camposBase = [
            'nom_per',
            'ape_pat_per',
            'ci_per',
            'fec_nac_per',
            'gen_per',
            'tel_per',
            'ema_per',
            'dep_per',
            'mun_per',
            'zona_per',
            'cal_per',
        ];

        $total = count($camposBase);
        $llenos = collect($camposBase)
            ->filter(fn (string $campo) => $this->tieneValor($datos[$campo] ?? null))
            ->count();

        $porcentaje = $total > 0 ? (int) round(($llenos / $total) * 100) : 0;

        return [
            'total' => $total,
            'completos' => $llenos,
            'porcentaje' => $porcentaje,
            'nivel' => match (true) {
                $porcentaje >= 90 => 'COMPLETA',
                $porcentaje >= 70 => 'BUENA',
                $porcentaje >= 45 => 'MEDIA',
                default => 'BAJA',
            },
        ];
    }

    // ============================================================
    // NORMALIZADORES
    // ============================================================

    public function normalizarDatos(array $datos): array
    {
        $normalizados = [
            'cod_per' => $this->normalizarTextoBasico($datos['cod_per'] ?? null),
            'nom_per' => $this->normalizarNombre($datos['nom_per'] ?? null),
            'ape_pat_per' => $this->normalizarNombre($datos['ape_pat_per'] ?? null),
            'ape_mat_per' => $this->normalizarNombre($datos['ape_mat_per'] ?? null),
            'ci_per' => $this->normalizarCi($datos['ci_per'] ?? null),
            'com_per' => $this->normalizarTextoMayuscula($datos['com_per'] ?? null),
            'exp_per' => $this->normalizarExpedido($datos['exp_per'] ?? null),
            'fec_nac_per' => $this->normalizarFecha($datos['fec_nac_per'] ?? null),
            'gen_per' => $this->normalizarTextoMayuscula($datos['gen_per'] ?? null),
            'tel_per' => $this->normalizarTelefono($datos['tel_per'] ?? null),
            'ema_per' => $this->normalizarCorreo($datos['ema_per'] ?? null),
            'dir_per' => $this->normalizarTextoBasico($datos['dir_per'] ?? null),
            'zona_per' => $this->normalizarTextoTitulo($datos['zona_per'] ?? null),
            'ave_per' => $this->normalizarTextoTitulo($datos['ave_per'] ?? null),
            'cal_per' => $this->normalizarTextoTitulo($datos['cal_per'] ?? null),
            'num_per' => $this->normalizarTextoBasico($datos['num_per'] ?? null),
            'ref_per' => $this->normalizarTextoBasico($datos['ref_per'] ?? null),
            'ciu_per' => $this->normalizarTextoTitulo($datos['ciu_per'] ?? null),
            'mun_per' => $this->normalizarTextoTitulo($datos['mun_per'] ?? null),
            'dep_per' => $this->normalizarTextoTitulo($datos['dep_per'] ?? null),
            'fot_per' => $this->normalizarTextoBasico($datos['fot_per'] ?? null),
            'est_per' => $datos['est_per'] ?? true,

            'tipo_vinculacion' => $this->normalizarTextoMayuscula($datos['tipo_vinculacion'] ?? 'SOLO_PERSONA'),
            'rud_est' => $this->normalizarTextoMayuscula($datos['rud_est'] ?? null),
            'car_pin' => $this->normalizarTextoTitulo($datos['car_pin'] ?? null),
            'email_usuario' => $this->normalizarCorreo($datos['email_usuario'] ?? null),
        ];

        if (! $normalizados['dir_per']) {
            $normalizados['dir_per'] = $this->generarDireccionCompleta($normalizados);
        }

        return $normalizados;
    }

    public function normalizarCi(?string $ci): string
    {
        return preg_replace('/\D+/', '', (string) $ci) ?: '';
    }

    public function normalizarCorreo(?string $correo): ?string
    {
        $correo = mb_strtolower(trim((string) $correo));

        return $correo !== '' ? $correo : null;
    }

    public function normalizarTelefono(?string $telefono): ?string
    {
        $telefono = trim((string) $telefono);
        $telefono = preg_replace('/\s+/', ' ', $telefono);

        return $telefono !== '' ? $telefono : null;
    }

    public function normalizarFecha(?string $fecha): ?string
    {
        if (! $this->tieneValor($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (Throwable) {
            return trim((string) $fecha);
        }
    }

    public function normalizarExpedido(?string $expedido): ?string
    {
        $expedido = $this->normalizarTextoMayuscula($expedido);

        if ($expedido === 'CB') {
            return 'CBBA';
        }

        if ($expedido === 'SC') {
            return 'SCZ';
        }

        if ($expedido === 'OR') {
            return 'ORU';
        }

        return $expedido;
    }

    public function normalizarNombre(?string $texto): string
    {
        $texto = $this->normalizarEspacios($texto);

        return Str::of($texto)->lower()->title()->toString();
    }

    public function normalizarTextoTitulo(?string $texto): ?string
    {
        $texto = $this->normalizarEspacios($texto);

        return $texto !== '' ? Str::of($texto)->lower()->title()->toString() : null;
    }

    public function normalizarTextoMayuscula(?string $texto): ?string
    {
        $texto = $this->normalizarEspacios($texto);

        return $texto !== '' ? mb_strtoupper($texto) : null;
    }

    public function normalizarTextoBasico(?string $texto): ?string
    {
        $texto = $this->normalizarEspacios($texto);

        return $texto !== '' ? $texto : null;
    }

    private function normalizarEspacios(?string $texto): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $texto));
    }

    // ============================================================
    // GENERADORES
    // ============================================================

    public function generarDireccionCompleta(array $datos): ?string
    {
        $partes = [];

        foreach ([
            'zona_per' => 'Zona',
            'ave_per' => 'Av.',
            'cal_per' => 'Calle',
            'num_per' => 'N°',
            'ref_per' => 'Ref.',
            'ciu_per' => 'Ciudad',
            'mun_per' => 'Municipio',
            'dep_per' => 'Departamento',
        ] as $campo => $etiqueta) {
            $valor = $datos[$campo] ?? null;

            if ($this->tieneValor($valor)) {
                $partes[] = "{$etiqueta} {$valor}";
            }
        }

        if (empty($partes) && $this->tieneValor($datos['dir_per'] ?? null)) {
            return $datos['dir_per'];
        }

        return ! empty($partes) ? implode(', ', $partes) : null;
    }

    public function generarNombreCompleto(array $datos): string
    {
        return collect([
            $datos['nom_per'] ?? null,
            $datos['ape_pat_per'] ?? null,
            $datos['ape_mat_per'] ?? null,
        ])
            ->filter(fn ($valor) => $this->tieneValor($valor))
            ->implode(' ');
    }

    // ============================================================
    // CONSULTAS SEGURAS
    // ============================================================

    private function obtenerPersona(string $codPer): ?object
    {
        if (! Schema::hasTable('persona') || ! Schema::hasColumn('persona', 'cod_per')) {
            return null;
        }

        return DB::table('persona')
            ->where('cod_per', $codPer)
            ->first();
    }

    private function consultaVinculacionDirecta(
        string $tabla,
        string $fk,
        string $valor,
        string $pk,
        ?string $estado = null
    ): array {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $fk)) {
            return [
                'tabla' => $tabla,
                'cantidad' => 0,
                'registros' => [],
            ];
        }

        $pkReal = Schema::hasColumn($tabla, $pk) ? $pk : $fk;
        $estadoReal = $estado && Schema::hasColumn($tabla, $estado) ? $estado : null;

        $registros = DB::table($tabla)
            ->where($fk, $valor)
            ->get()
            ->map(function ($row) use ($tabla, $pkReal, $estadoReal) {
                return [
                    'tabla' => $tabla,
                    'codigo' => $row->{$pkReal} ?? null,
                    'estado' => $estadoReal ? ($row->{$estadoReal} ?? null) : null,
                ];
            })
            ->values()
            ->all();

        return [
            'tabla' => $tabla,
            'cantidad' => count($registros),
            'registros' => $registros,
        ];
    }

    private function objetoPersonaADatos(object $persona): array
    {
        return [
            'cod_per' => $persona->cod_per ?? null,
            'nom_per' => $persona->nom_per ?? null,
            'ape_pat_per' => $persona->ape_pat_per ?? null,
            'ape_mat_per' => $persona->ape_mat_per ?? null,
            'ci_per' => $persona->ci_per ?? null,
            'com_per' => $persona->com_per ?? null,
            'exp_per' => $persona->exp_per ?? null,
            'fec_nac_per' => $persona->fec_nac_per ?? null,
            'gen_per' => $persona->gen_per ?? null,
            'tel_per' => $persona->tel_per ?? null,
            'ema_per' => $persona->ema_per ?? null,
            'dir_per' => $persona->dir_per ?? null,
            'zona_per' => $persona->zona_per ?? null,
            'ave_per' => $persona->ave_per ?? null,
            'cal_per' => $persona->cal_per ?? null,
            'num_per' => $persona->num_per ?? null,
            'ref_per' => $persona->ref_per ?? null,
            'ciu_per' => $persona->ciu_per ?? null,
            'mun_per' => $persona->mun_per ?? null,
            'dep_per' => $persona->dep_per ?? null,
            'fot_per' => $persona->fot_per ?? null,
            'est_per' => $persona->est_per ?? null,
            'tipo_vinculacion' => 'SOLO_PERSONA',
            'rud_est' => null,
            'car_pin' => null,
            'email_usuario' => null,
        ];
    }

    // ============================================================
    // UTILIDADES
    // ============================================================

    private function personaEstaActiva(object $persona): bool
    {
        $estado = $persona->est_per ?? true;

        if (is_bool($estado)) {
            return $estado;
        }

        if (is_numeric($estado)) {
            return (int) $estado === 1;
        }

        return in_array(mb_strtoupper((string) $estado), ['ACTIVO', 'ACTIVA', 'TRUE', '1'], true);
    }

    private function datosMinimosInsuficientes(array $datos): bool
    {
        return ! $this->tieneValor($datos['ci_per'])
            && ! $this->tieneValor($datos['ema_per'])
            && (
                ! $this->tieneValor($datos['nom_per'])
                || ! $this->tieneValor($datos['ape_pat_per'])
            );
    }

    private function tieneValor(mixed $valor): bool
    {
        return trim((string) $valor) !== '';
    }

    private function calcularEdad(?string $fecha): ?int
    {
        if (! $this->tieneValor($fecha)) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->age;
        } catch (Throwable) {
            return null;
        }
    }

    private function determinarEstadoInteligente(
        array $bloqueos,
        array $advertencias,
        array $coincidencias,
        array $resumenPersona,
        bool $modoTiempoReal
    ): string {
        if (! empty($bloqueos)) {
            return self::ESTADO_BLOQUEADO;
        }

        if (($coincidencias['persona_inactiva_recuperable'] ?? false) === true) {
            return self::ESTADO_RECUPERABLE;
        }

        if (($coincidencias['posible_homonimo'] ?? false) === true) {
            return self::ESTADO_OBSERVADO;
        }

        if (! empty($advertencias)) {
            return self::ESTADO_OBSERVADO;
        }

        if ($modoTiempoReal && empty($coincidencias['persona_principal']) && empty($resumenPersona)) {
            return self::ESTADO_VALIDO;
        }

        return self::ESTADO_VALIDO;
    }

    private function mensajePrincipal(string $estado, array $coincidencias, bool $modoTiempoReal): string
    {
        return match ($estado) {
            self::ESTADO_BLOQUEADO => 'El registro está bloqueado por duplicidad o datos críticos.',
            self::ESTADO_RECUPERABLE => 'Se detectó una persona inactiva recuperable. Conviene reactivarla.',
            self::ESTADO_OBSERVADO => 'El registro puede continuar con observaciones que deben revisarse.',
            self::ESTADO_INCOMPLETO => 'Ingresa CI, correo o nombre completo para reconocer a la persona.',
            default => ($coincidencias['persona_principal'] ?? null)
                ? 'Se detectó una persona existente. Revisa sus vinculaciones antes de continuar.'
                : 'No se detectan duplicados fuertes. Puede continuar el registro.',
        };
    }

    private function nivelRiesgo(array $bloqueos, array $advertencias, array $coincidencias): string
    {
        if (count($bloqueos) >= 2) {
            return 'CRITICO';
        }

        if (count($bloqueos) === 1) {
            return 'ALTO';
        }

        if (($coincidencias['persona_inactiva_recuperable'] ?? false) === true) {
            return 'MEDIO';
        }

        if (($coincidencias['posible_homonimo'] ?? false) === true) {
            return 'MEDIO';
        }

        if (count($advertencias) >= 3) {
            return 'MEDIO';
        }

        if (count($advertencias) > 0) {
            return 'BAJO';
        }

        return 'BAJO';
    }

    private function formatearCoincidencias(array $coincidencias): array
    {
        $personaPrincipal = $coincidencias['persona_principal'] ?? null;

        return [
            'duplicado_ci' => (bool) ($coincidencias['duplicado_ci'] ?? false),
            'duplicado_correo' => (bool) ($coincidencias['duplicado_correo'] ?? false),
            'posible_homonimo' => (bool) ($coincidencias['posible_homonimo'] ?? false),
            'persona_inactiva_recuperable' => (bool) ($coincidencias['persona_inactiva_recuperable'] ?? false),
            'usuario_existente' => (bool) ($coincidencias['usuario_existente'] ?? false),
            'estudiante_existente' => (bool) ($coincidencias['estudiante_existente'] ?? false),
            'personal_existente' => (bool) ($coincidencias['personal_existente'] ?? false),
            'persona_principal' => $personaPrincipal ? [
                'cod_per' => $personaPrincipal->cod_per ?? null,
                'nombre' => trim(($personaPrincipal->nom_per ?? '') . ' ' . ($personaPrincipal->ape_pat_per ?? '') . ' ' . ($personaPrincipal->ape_mat_per ?? '')),
                'ci' => $personaPrincipal->ci_per ?? null,
                'correo' => $personaPrincipal->ema_per ?? null,
                'estado' => $this->personaEstaActiva($personaPrincipal) ? self::ESTADO_ACTIVO : self::ESTADO_INACTIVO,
            ] : null,
            'cantidad_por_ci' => isset($coincidencias['por_ci']) ? $coincidencias['por_ci']->count() : 0,
            'cantidad_por_correo' => isset($coincidencias['por_correo']) ? $coincidencias['por_correo']->count() : 0,
            'cantidad_por_nombre' => isset($coincidencias['por_nombre']) ? $coincidencias['por_nombre']->count() : 0,
            'vinculaciones' => $coincidencias['vinculaciones'] ?? [],
        ];
    }

    private function resultado(
        bool $puedeContinuar,
        string $estadoInteligente,
        string $nivelRiesgo,
        string $mensaje,
        array $bloqueos = [],
        array $advertencias = [],
        array $sugerencias = [],
        array $coincidencias = [],
        array $resumen = []
    ): array {
        return [
            'puede_continuar' => $puedeContinuar,
            'estado_inteligente' => $estadoInteligente,
            'nivel_riesgo' => $nivelRiesgo,
            'mensaje' => $mensaje,
            'bloqueos' => array_values(array_unique($bloqueos)),
            'advertencias' => array_values(array_unique($advertencias)),
            'sugerencias' => array_values(array_unique($sugerencias)),
            'coincidencias' => $coincidencias,
            'resumen' => $resumen,
        ];
    }
}