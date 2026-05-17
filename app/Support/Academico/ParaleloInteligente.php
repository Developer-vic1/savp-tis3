<?php

namespace App\Support\Academico;

class ParaleloInteligente
{
    public const ESTADO_VALIDO = 'VALIDO';
    public const ESTADO_REDACTABLE = 'REDACTABLE';
    public const ESTADO_DUPLICADO_ACTIVO = 'DUPLICADO_ACTIVO';
    public const ESTADO_DUPLICADO_INACTIVO = 'DUPLICADO_INACTIVO';
    public const ESTADO_REQUIERE_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADO = 'BLOQUEADO';

    public const TELEFONO_SOPORTE = '75836807';

    public const MIN_SIMILITUD_DUPLICADO = 96;
    public const MIN_SIMILITUD_SIMILAR = 82;
    public const MAX_LONGITUD_NOMBRE = 30;

    /*
    |--------------------------------------------------------------------------
    | Interpretación principal
    |--------------------------------------------------------------------------
    */

    public static function interpretar(?string $entrada, array $existentes = []): array
    {
        $original = trim((string) $entrada);
        $normalizado = self::normalizar($original);

        if ($normalizado === '') {
            return self::respuestaBase(
                valido: false,
                puedeCrear: false,
                estado: self::ESTADO_BLOQUEADO,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                nombreSugerido: '',
                mensaje: 'Escribe el nombre del paralelo para analizarlo.',
                confianza: 0,
                requiereSoporte: false
            );
        }

        if (self::entradaClaramenteInvalida($normalizado)) {
            return self::respuestaBloqueada(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: self::mensajeNoDescifrable()
            );
        }

        if (self::mezclaConceptosAcademicos($normalizado)) {
            return self::respuestaBloqueada(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'El paralelo no debe incluir curso, turno, gestión, docente, aula ni especialidad. Registra solo el grupo académico, por ejemplo: A, B, C o Único.'
            );
        }

        $redaccion = self::redactar($original);

        if (! $redaccion['puede_redactarse']) {
            return self::respuestaBloqueada(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: self::mensajeNoDescifrable(),
                confianza: $redaccion['confianza']
            );
        }

        $nombreSugerido = $redaccion['nombre_sugerido'];
        $coincidencias = self::buscarCoincidencias($nombreSugerido, $existentes);
        $duplicado = self::duplicadoPrincipal($coincidencias);

        if ($duplicado !== null) {
            $estadoRegistro = mb_strtoupper((string) ($duplicado['estado'] ?? ''));

            if ($estadoRegistro === 'ACTIVO') {
                return self::respuestaDuplicadoActivo(
                    entradaOriginal: $original,
                    entradaNormalizada: $normalizado,
                    nombreSugerido: $nombreSugerido,
                    duplicado: $duplicado,
                    coincidencias: $coincidencias
                );
            }

            if ($estadoRegistro === 'INACTIVO') {
                return self::respuestaDuplicadoInactivo(
                    entradaOriginal: $original,
                    entradaNormalizada: $normalizado,
                    nombreSugerido: $nombreSugerido,
                    duplicado: $duplicado,
                    coincidencias: $coincidencias
                );
            }
        }

        if ($redaccion['estado_inteligente'] === self::ESTADO_REQUIERE_REVISION) {
            return self::respuestaRevision(
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                nombreSugerido: $nombreSugerido,
                redaccion: $redaccion,
                coincidencias: $coincidencias
            );
        }

        return self::respuestaValida(
            entradaOriginal: $original,
            entradaNormalizada: $normalizado,
            nombreSugerido: $nombreSugerido,
            redaccion: $redaccion,
            coincidencias: $coincidencias
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Catálogo sugerido
    |--------------------------------------------------------------------------
    */

    public static function catalogoSugerido(): array
    {
        return [
            [
                'nombre' => 'A',
                'descripcion' => 'Paralelo estándar principal.',
                'tipo' => 'Estándar',
                'recomendado' => true,
            ],
            [
                'nombre' => 'B',
                'descripcion' => 'Paralelo estándar para dividir grupos con alta cantidad de estudiantes.',
                'tipo' => 'Estándar',
                'recomendado' => true,
            ],
            [
                'nombre' => 'C',
                'descripcion' => 'Paralelo adicional para ampliación institucional.',
                'tipo' => 'Estándar',
                'recomendado' => true,
            ],
            [
                'nombre' => 'D',
                'descripcion' => 'Paralelo adicional para organización académica extendida.',
                'tipo' => 'Estándar',
                'recomendado' => true,
            ],
            [
                'nombre' => 'E',
                'descripcion' => 'Paralelo excepcional para alta demanda estudiantil.',
                'tipo' => 'Extendido',
                'recomendado' => false,
            ],
            [
                'nombre' => 'F',
                'descripcion' => 'Paralelo excepcional para instituciones con alta matrícula.',
                'tipo' => 'Extendido',
                'recomendado' => false,
            ],
            [
                'nombre' => 'Único',
                'descripcion' => 'Paralelo usado cuando solo existe un grupo académico para un curso.',
                'tipo' => 'Institucional',
                'recomendado' => true,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Redacción y normalización
    |--------------------------------------------------------------------------
    */

    public static function redactar(?string $entrada): array
    {
        $original = trim((string) $entrada);
        $normalizado = self::normalizar($original);

        if ($normalizado === '' || self::entradaClaramenteInvalida($normalizado)) {
            return [
                'puede_redactarse' => false,
                'nombre_sugerido' => '',
                'estado_inteligente' => self::ESTADO_BLOQUEADO,
                'mensaje' => self::mensajeNoDescifrable(),
                'confianza' => 0,
            ];
        }

        $letra = self::extraerLetraParalelo($normalizado);

        if ($letra !== null) {
            return [
                'puede_redactarse' => true,
                'nombre_sugerido' => $letra,
                'estado_inteligente' => self::normalizar($original) === self::normalizar($letra)
                    ? self::ESTADO_VALIDO
                    : self::ESTADO_REDACTABLE,
                'mensaje' => self::normalizar($original) === self::normalizar($letra)
                    ? 'Paralelo válido.'
                    : 'La entrada fue interpretada y redactada como un paralelo académico formal.',
                'confianza' => self::normalizar($original) === self::normalizar($letra) ? 100 : 92,
            ];
        }

        if (self::esParaleloUnico($normalizado)) {
            return [
                'puede_redactarse' => true,
                'nombre_sugerido' => 'Único',
                'estado_inteligente' => self::normalizar($original) === 'unico'
                    ? self::ESTADO_VALIDO
                    : self::ESTADO_REDACTABLE,
                'mensaje' => 'La entrada fue interpretada como paralelo único.',
                'confianza' => 94,
            ];
        }

        if (self::esNombreInstitucionalPosible($normalizado)) {
            return [
                'puede_redactarse' => true,
                'nombre_sugerido' => self::formatearNombre($original),
                'estado_inteligente' => self::ESTADO_REQUIERE_REVISION,
                'mensaje' => 'El nombre parece institucional, pero no corresponde al formato estándar de paralelos. Requiere revisión antes de consolidarse.',
                'confianza' => 70,
            ];
        }

        return [
            'puede_redactarse' => false,
            'nombre_sugerido' => '',
            'estado_inteligente' => self::ESTADO_BLOQUEADO,
            'mensaje' => self::mensajeNoDescifrable(),
            'confianza' => 35,
        ];
    }

    public static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        $texto = strtr($texto, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);

        $texto = preg_replace('/[^a-z0-9\s]/u', ' ', $texto) ?? '';
        $texto = preg_replace('/\s+/', ' ', $texto) ?? '';
        $texto = trim($texto);

        $reemplazosExactos = [
            'paralelo unico' => 'unico',
            'grupo unico' => 'unico',
            'curso unico' => 'unico',
            'seccion unica' => 'unico',
            'seccion unico' => 'unico',
            'paralelo a' => 'a',
            'paralelo b' => 'b',
            'paralelo c' => 'c',
            'paralelo d' => 'd',
            'paralelo e' => 'e',
            'paralelo f' => 'f',
            'grupo a' => 'a',
            'grupo b' => 'b',
            'grupo c' => 'c',
            'grupo d' => 'd',
            'grupo e' => 'e',
            'grupo f' => 'f',
            'seccion a' => 'a',
            'seccion b' => 'b',
            'seccion c' => 'c',
            'seccion d' => 'd',
            'seccion e' => 'e',
            'seccion f' => 'f',
        ];

        return $reemplazosExactos[$texto] ?? $texto;
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicados e histórico
    |--------------------------------------------------------------------------
    */

    public static function buscarCoincidencias(string $entrada, array $existentes): array
    {
        $nombreNormalizado = self::normalizar($entrada);
        $coincidencias = [];

        foreach ($existentes as $item) {
            $codigo = (string) ($item['cod_par'] ?? $item['codigo'] ?? '');
            $nombre = (string) ($item['nom_par'] ?? $item['nombre'] ?? '');
            $estado = mb_strtoupper((string) ($item['est_par'] ?? $item['estado'] ?? ''));
            $bitacora = $item['bitacora'] ?? $item['ultima_bitacora'] ?? null;

            if ($nombre === '') {
                continue;
            }

            $similitud = self::calcularSimilitud($nombreNormalizado, self::normalizar($nombre));

            if ($similitud >= self::MIN_SIMILITUD_SIMILAR) {
                $coincidencias[] = [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'estado' => $estado,
                    'similitud' => $similitud,
                    'tipo' => $similitud >= self::MIN_SIMILITUD_DUPLICADO ? 'ALTA' : 'MEDIA',
                    'mensaje' => $similitud >= self::MIN_SIMILITUD_DUPLICADO
                        ? 'Coincidencia fuerte con un paralelo existente.'
                        : 'Nombre similar a un paralelo existente.',
                    'bitacora' => is_array($bitacora) ? $bitacora : null,
                ];
            }
        }

        usort($coincidencias, fn(array $a, array $b) => $b['similitud'] <=> $a['similitud']);

        return $coincidencias;
    }

    public static function existeActivoConNombre(string $nombre, array $existentes): bool
    {
        foreach (self::buscarCoincidencias($nombre, $existentes) as $coincidencia) {
            if (
                ($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_DUPLICADO
                && ($coincidencia['estado'] ?? '') === 'ACTIVO'
            ) {
                return true;
            }
        }

        return false;
    }

    public static function existeInactivoConNombre(string $nombre, array $existentes): bool
    {
        foreach (self::buscarCoincidencias($nombre, $existentes) as $coincidencia) {
            if (
                ($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_DUPLICADO
                && ($coincidencia['estado'] ?? '') === 'INACTIVO'
            ) {
                return true;
            }
        }

        return false;
    }

    public static function esCambioMenor(string $antes, string $despues): bool
    {
        $antesNormalizado = self::normalizar($antes);
        $despuesNormalizado = self::normalizar($despues);

        if ($antesNormalizado === $despuesNormalizado) {
            return true;
        }

        return self::calcularSimilitud($antesNormalizado, $despuesNormalizado) >= self::MIN_SIMILITUD_DUPLICADO;
    }

    private static function duplicadoPrincipal(array $coincidencias): ?array
    {
        foreach ($coincidencias as $coincidencia) {
            if (($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_DUPLICADO) {
                return $coincidencia;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas académicas
    |--------------------------------------------------------------------------
    */

    public static function entradaClaramenteInvalida(string $normalizado): bool
    {
        $normalizado = trim($normalizado);

        if ($normalizado === '') {
            return true;
        }

        if (mb_strlen($normalizado) < 1) {
            return true;
        }

        if (mb_strlen($normalizado) > self::MAX_LONGITUD_NOMBRE) {
            return true;
        }

        if (preg_match('/^(.)\1{3,}$/u', $normalizado)) {
            return true;
        }

        if (preg_match('/^[0-9\s]+$/u', $normalizado)) {
            return true;
        }

        $invalidas = [
            'random',
            'cualquier cosa',
            'cualquiera',
            'nose',
            'no se',
            'prueba',
            'test',
            'testing',
            'asd',
            'asdf',
            'qwerty',
            'inventado',
            'inventada',
            'sin nombre',
            'ninguno',
            'nada',
            'xxx',
            'paralelo random',
            'paralelo cualquiera',
        ];

        foreach ($invalidas as $palabra) {
            if (str_contains($normalizado, self::normalizar($palabra))) {
                return true;
            }
        }

        return false;
    }

    public static function mezclaConceptosAcademicos(string $normalizado): bool
    {
        $normalizado = self::normalizar($normalizado);

        $conceptosBloqueados = [
            'primero',
            'segundo',
            'tercero',
            'cuarto',
            'quinto',
            'sexto',
            '1ro',
            '2do',
            '3ro',
            '4to',
            '5to',
            '6to',
            'curso',
            'secundaria',
            'primaria',
            'manana',
            'mañana',
            'tarde',
            'noche',
            'turno',
            'gestion',
            '2024',
            '2025',
            '2026',
            '2027',
            'aula',
            'docente',
            'profesor',
            'profesora',
            'tecnico',
            'tecnica',
            'humanistico',
            'especialidad',
            'sistemas',
            'gastronomia',
            'contabilidad',
            'electronica',
            'mecanica',
            'construccion',
            'textiles',
            'belleza',
        ];

        foreach ($conceptosBloqueados as $concepto) {
            if (str_contains($normalizado, self::normalizar($concepto))) {
                return true;
            }
        }

        return false;
    }

    private static function extraerLetraParalelo(string $normalizado): ?string
    {
        $normalizado = self::normalizar($normalizado);

        if (preg_match('/^[a-z]$/u', $normalizado)) {
            return mb_strtoupper($normalizado);
        }

        if (preg_match('/^(paralelo|grupo|seccion)\s+([a-z])$/u', $normalizado, $matches)) {
            return mb_strtoupper($matches[2]);
        }

        return null;
    }

    private static function esParaleloUnico(string $normalizado): bool
    {
        $normalizado = self::normalizar($normalizado);

        return in_array($normalizado, [
            'unico',
            'unica',
            'solo',
            'general',
        ], true);
    }

    private static function esNombreInstitucionalPosible(string $normalizado): bool
    {
        $normalizado = self::normalizar($normalizado);

        $permitidos = [
            'experimental',
            'integrado',
            'inclusivo',
            'regular',
            'especial',
            'piloto',
            'comunitario',
        ];

        foreach ($permitidos as $permitido) {
            if ($normalizado === $permitido) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuestas
    |--------------------------------------------------------------------------
    */

    private static function respuestaValida(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $nombreSugerido,
        array $redaccion,
        array $coincidencias
    ): array {
        return self::respuestaBase(
            valido: true,
            puedeCrear: true,
            estado: $redaccion['estado_inteligente'] ?? self::ESTADO_VALIDO,
            entradaOriginal: $entradaOriginal,
            entradaNormalizada: $entradaNormalizada,
            nombreSugerido: $nombreSugerido,
            mensaje: $redaccion['mensaje'] ?? 'Paralelo válido para registro.',
            confianza: $redaccion['confianza'] ?? 100,
            coincidencias: $coincidencias,
            advertencias: self::advertenciasPorCoincidencias($coincidencias)
        );
    }

    private static function respuestaRevision(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $nombreSugerido,
        array $redaccion,
        array $coincidencias
    ): array {
        return self::respuestaBase(
            valido: true,
            puedeCrear: true,
            estado: self::ESTADO_REQUIERE_REVISION,
            entradaOriginal: $entradaOriginal,
            entradaNormalizada: $entradaNormalizada,
            nombreSugerido: $nombreSugerido,
            mensaje: $redaccion['mensaje'] ?? 'El paralelo requiere revisión institucional antes de consolidarse.',
            confianza: $redaccion['confianza'] ?? 70,
            coincidencias: $coincidencias,
            advertencias: array_merge([
                'El nombre no corresponde al formato estándar A, B, C o Único.',
                'Verifica si la institución realmente maneja este paralelo especial.',
            ], self::advertenciasPorCoincidencias($coincidencias))
        );
    }

    private static function respuestaDuplicadoActivo(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $nombreSugerido,
        array $duplicado,
        array $coincidencias
    ): array {
        return self::respuestaBase(
            valido: false,
            puedeCrear: false,
            estado: self::ESTADO_DUPLICADO_ACTIVO,
            entradaOriginal: $entradaOriginal,
            entradaNormalizada: $entradaNormalizada,
            nombreSugerido: $nombreSugerido,
            mensaje: 'Ya existe un paralelo activo con este nombre. No se puede crear un duplicado.',
            confianza: 100,
            coincidencias: $coincidencias,
            codigoExistente: $duplicado['codigo'] ?? null,
            nombreExistente: $duplicado['nombre'] ?? null,
            estadoExistente: $duplicado['estado'] ?? null,
            puedeReactivar: false,
            advertencias: [
                'El paralelo ya está activo en el sistema.',
                'Usa el registro existente en lugar de crear uno nuevo.',
            ]
        );
    }

    private static function respuestaDuplicadoInactivo(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $nombreSugerido,
        array $duplicado,
        array $coincidencias
    ): array {
        $bitacora = $duplicado['bitacora'] ?? null;

        $mensaje = 'Ya existe un paralelo inactivo con este nombre. Se recomienda reactivarlo en lugar de crear un duplicado.';

        if (is_array($bitacora) && ! empty($bitacora['fecha'])) {
            $mensaje .= ' Fue desactivado el ' . $bitacora['fecha'] . '.';
        }

        return self::respuestaBase(
            valido: false,
            puedeCrear: false,
            estado: self::ESTADO_DUPLICADO_INACTIVO,
            entradaOriginal: $entradaOriginal,
            entradaNormalizada: $entradaNormalizada,
            nombreSugerido: $nombreSugerido,
            mensaje: $mensaje,
            confianza: 100,
            coincidencias: $coincidencias,
            codigoExistente: $duplicado['codigo'] ?? null,
            nombreExistente: $duplicado['nombre'] ?? null,
            estadoExistente: $duplicado['estado'] ?? null,
            puedeReactivar: true,
            ultimaBitacora: $bitacora,
            advertencias: [
                'El paralelo existe en estado inactivo.',
                'No se creará un nuevo registro para evitar duplicidad histórica.',
                'Puedes reactivar el paralelo existente si la institución volverá a utilizarlo.',
            ]
        );
    }

    private static function respuestaBloqueada(
        string $entradaOriginal,
        string $entradaNormalizada,
        string $mensaje,
        array $coincidencias = [],
        int $confianza = 0
    ): array {
        return self::respuestaBase(
            valido: false,
            puedeCrear: false,
            estado: self::ESTADO_BLOQUEADO,
            entradaOriginal: $entradaOriginal,
            entradaNormalizada: $entradaNormalizada,
            nombreSugerido: '',
            mensaje: $mensaje,
            confianza: $confianza,
            coincidencias: $coincidencias,
            requiereSoporte: true,
            advertencias: [
                'No se puede crear el paralelo porque la entrada no cumple las reglas académicas del catálogo.',
                'Corrige la redacción o contacta con soporte académico al ' . self::TELEFONO_SOPORTE . '.',
            ]
        );
    }

    private static function respuestaBase(
        bool $valido,
        bool $puedeCrear,
        string $estado,
        string $entradaOriginal,
        string $entradaNormalizada,
        string $nombreSugerido,
        string $mensaje,
        int $confianza = 0,
        array $coincidencias = [],
        ?string $codigoExistente = null,
        ?string $nombreExistente = null,
        ?string $estadoExistente = null,
        bool $puedeReactivar = false,
        ?array $ultimaBitacora = null,
        bool $requiereSoporte = false,
        array $advertencias = []
    ): array {
        return [
            'valido' => $valido,
            'puede_crear' => $puedeCrear,
            'puede_reactivar' => $puedeReactivar,
            'estado_inteligente' => $estado,

            'entrada_original' => $entradaOriginal,
            'entrada_normalizada' => $entradaNormalizada,
            'nombre_sugerido' => $nombreSugerido,
            'nombre' => $nombreSugerido,

            'mensaje' => $mensaje,
            'confianza' => max(0, min($confianza, 100)),

            'codigo_existente' => $codigoExistente,
            'nombre_existente' => $nombreExistente,
            'estado_existente' => $estadoExistente,

            'requiere_soporte' => $requiereSoporte,
            'telefono_soporte' => self::TELEFONO_SOPORTE,

            'ultima_bitacora' => $ultimaBitacora,
            'coincidencias' => $coincidencias,
            'advertencias' => $advertencias,
            'sugerencias' => self::catalogoSugerido(),

            'reglas' => [
                'No duplicar paralelos activos.',
                'No crear un paralelo nuevo si existe uno inactivo con el mismo nombre; se debe reactivar.',
                'El paralelo no debe mezclar curso, turno, gestión, docente, aula ni especialidad.',
                'La eliminación visible se maneja como desactivación lógica.',
            ],
        ];
    }

    private static function advertenciasPorCoincidencias(array $coincidencias): array
    {
        $advertencias = [];

        foreach ($coincidencias as $coincidencia) {
            if (($coincidencia['similitud'] ?? 0) >= self::MIN_SIMILITUD_SIMILAR) {
                $advertencias[] = 'Existe un paralelo similar: '
                    . ($coincidencia['nombre'] ?? 'sin nombre')
                    . ' con estado '
                    . ($coincidencia['estado'] ?? 'no definido')
                    . '.';
            }
        }

        return array_values(array_unique($advertencias));
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    public static function calcularSimilitud(string $a, string $b): int
    {
        $a = self::normalizar($a);
        $b = self::normalizar($b);

        if ($a === '' || $b === '') {
            return 0;
        }

        if ($a === $b) {
            return 100;
        }

        similar_text($a, $b, $similarText);

        $distancia = levenshtein($a, $b);
        $maxLength = max(strlen($a), strlen($b));

        $levenshtein = $maxLength > 0
            ? (1 - min($distancia, $maxLength) / $maxLength) * 100
            : 0;

        $resultado = max($similarText, $levenshtein);

        if (str_contains($a, $b) || str_contains($b, $a)) {
            $resultado += 8;
        }

        return (int) min(round($resultado), 100);
    }

    public static function formatearNombre(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre) ?? '');

        if ($nombre === '') {
            return '';
        }

        $normalizado = self::normalizar($nombre);

        $letra = self::extraerLetraParalelo($normalizado);

        if ($letra !== null) {
            return $letra;
        }

        if (self::esParaleloUnico($normalizado)) {
            return 'Único';
        }

        return mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
    }

    public static function mensajeNoDescifrable(): string
    {
        return 'No se pudo descifrar la entrada como un paralelo académico válido. Registra solo el grupo académico, por ejemplo A, B, C o Único. Si corresponde a una configuración especial, contacta con soporte académico al ' . self::TELEFONO_SOPORTE . '.';
    }
}
