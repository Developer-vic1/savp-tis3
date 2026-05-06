<?php

namespace App\Support\Academico;

class CursoInteligente
{
    public static function catalogo(): array
    {
        return [
            1 => self::cursoOficial(1),
            2 => self::cursoOficial(2),
            3 => self::cursoOficial(3),
            4 => self::cursoOficial(4),
            5 => self::cursoOficial(5),
            6 => self::cursoOficial(6),
        ];
    }

    public static function interpretar(?string $entrada): array
    {
        $original = trim((string) $entrada);
        $normalizado = self::normalizar($original);

        if ($normalizado === '') {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'Escribe o selecciona un curso institucional.'
            );
        }

        $ordenesDetectados = self::detectarOrdenes($normalizado);

        if (count($ordenesDetectados) === 0) {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'No se pudo interpretar el curso. Usa expresiones como “1ro secundaria”, “cuarto secundaria” o “6to”.'
            );
        }

        if (count($ordenesDetectados) > 1) {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'La redacción parece mencionar más de un curso. Selecciona uno solo para continuar.',
                ordenesDetectados: $ordenesDetectados
            );
        }

        $orden = $ordenesDetectados[0];
        $curso = self::cursoOficial($orden);

        return array_merge(
            self::respuestaBase(
                valido: true,
                entradaOriginal: $original,
                entradaNormalizada: $normalizado,
                mensaje: 'Curso interpretado correctamente.',
                ordenesDetectados: $ordenesDetectados
            ),
            $curso,
            [
                'confianza' => self::calcularConfianza($normalizado, $orden),
                'advertencias' => self::advertencias($normalizado, $orden),
                'relaciones_esperadas' => self::relacionesEsperadas($orden),
            ]
        );
    }

    public static function desdeOrden(int $orden): array
    {
        if ($orden < 1 || $orden > 6) {
            return self::respuestaBase(
                valido: false,
                entradaOriginal: (string) $orden,
                entradaNormalizada: (string) $orden,
                mensaje: 'El curso institucional debe estar entre 1ro y 6to de Secundaria.'
            );
        }

        return array_merge(
            self::respuestaBase(
                valido: true,
                entradaOriginal: (string) $orden,
                entradaNormalizada: (string) $orden,
                mensaje: 'Curso seleccionado desde catálogo institucional.',
                ordenesDetectados: [$orden]
            ),
            self::cursoOficial($orden),
            [
                'confianza' => 100,
                'advertencias' => [],
                'relaciones_esperadas' => self::relacionesEsperadas($orden),
            ]
        );
    }

    public static function cursoOficial(int $orden): array
    {
        $nombres = [
            1 => '1ro de Secundaria',
            2 => '2do de Secundaria',
            3 => '3ro de Secundaria',
            4 => '4to de Secundaria',
            5 => '5to de Secundaria',
            6 => '6to de Secundaria',
        ];

        return [
            'orden' => $orden,
            'nombre' => $nombres[$orden] ?? "{$orden}to de Secundaria",
            'nivel' => self::nivelPorOrden($orden),
            'descripcion' => self::descripcionPorOrden($orden),
            'categoria' => $orden <= 3 ? 'Formación general' : 'Formación técnica especializada',
            'requiere_plan_especialidad' => $orden >= 4,
            'requiere_horario_tecnico' => $orden >= 4,
        ];
    }

    public static function nivelPorOrden(int $orden): string
    {
        if ($orden >= 1 && $orden <= 3) {
            return 'Técnica Tecnológica General';
        }

        if ($orden >= 4 && $orden <= 6) {
            return 'Especialización Técnica';
        }

        return 'Secundaria Comunitaria Productiva';
    }

    public static function descripcionPorOrden(int $orden): string
    {
        return match ($orden) {
            1 => 'Curso correspondiente al primer año de secundaria, orientado a la adaptación académica, formación general y bases de la educación técnica tecnológica.',
            2 => 'Curso correspondiente al segundo año de secundaria, orientado al fortalecimiento de aprendizajes generales y continuidad de la formación técnica tecnológica.',
            3 => 'Curso correspondiente al tercer año de secundaria, orientado a la consolidación de la formación general y preparación para la especialización técnica.',
            4 => 'Curso correspondiente al cuarto año de secundaria, orientado al fortalecimiento académico y al desarrollo de la especialización técnica.',
            5 => 'Curso correspondiente al quinto año de secundaria, orientado a la profundización académica, técnica y preparación progresiva para el egreso.',
            6 => 'Curso correspondiente al sexto año de secundaria, orientado a la consolidación académica, técnica y cierre formativo de la etapa secundaria.',
            default => 'Curso oficial de secundaria registrado en el catálogo académico institucional.',
        };
    }

    private static function relacionesEsperadas(int $orden): array
    {
        $relaciones = [
            'Plan de Asignatura',
            'Horarios',
            'Estudiantes inscritos',
        ];

        if ($orden >= 4) {
            $relaciones[] = 'Plan de Especialidad';
            $relaciones[] = 'Bloques técnicos por turno';
        }

        return $relaciones;
    }

    private static function advertencias(string $texto, int $orden): array
    {
        $advertencias = [];

        if (! str_contains($texto, 'secundaria')) {
            $advertencias[] = 'No se escribió “secundaria”, pero el sistema asumirá que corresponde al nivel secundario.';
        }

        if ($orden >= 4 && ! str_contains($texto, 'tecn') && ! str_contains($texto, 'especial')) {
            $advertencias[] = 'El curso pertenece al tramo de especialización técnica; recuerda configurar Plan de Especialidad.';
        }

        return $advertencias;
    }

    private static function calcularConfianza(string $texto, int $orden): int
    {
        $confianza = 60;

        if (str_contains($texto, 'secundaria')) {
            $confianza += 15;
        }

        if (preg_match('/\b' . $orden . '\b/u', $texto)) {
            $confianza += 10;
        }

        if ($orden >= 4 && (str_contains($texto, 'tecn') || str_contains($texto, 'especial'))) {
            $confianza += 10;
        }

        if ($orden <= 3 && (str_contains($texto, 'general') || str_contains($texto, 'tecnologica'))) {
            $confianza += 5;
        }

        return min($confianza, 100);
    }

    private static function detectarOrdenes(string $texto): array
    {
        $patrones = [
            1 => [
                '/\b1\s*(ro|ero|°|º)?\b/u',
                '/\bprimero\b/u',
                '/\bprimer\b/u',
                '/\buno\b/u',
            ],
            2 => [
                '/\b2\s*(do|°|º)?\b/u',
                '/\bsegundo\b/u',
                '/\bdos\b/u',
            ],
            3 => [
                '/\b3\s*(ro|ero|°|º)?\b/u',
                '/\btercero\b/u',
                '/\btercer\b/u',
                '/\btres\b/u',
            ],
            4 => [
                '/\b4\s*(to|°|º)?\b/u',
                '/\bcuarto\b/u',
                '/\bcuarta\b/u',
                '/\bcuatro\b/u',
            ],
            5 => [
                '/\b5\s*(to|°|º)?\b/u',
                '/\bquinto\b/u',
                '/\bquinta\b/u',
                '/\bcinco\b/u',
            ],
            6 => [
                '/\b6\s*(to|°|º)?\b/u',
                '/\bsexto\b/u',
                '/\bsexta\b/u',
                '/\bseis\b/u',
            ],
        ];

        $detectados = [];

        foreach ($patrones as $orden => $regexList) {
            foreach ($regexList as $regex) {
                if (preg_match($regex, $texto)) {
                    $detectados[] = $orden;
                    break;
                }
            }
        }

        return array_values(array_unique($detectados));
    }

    private static function respuestaBase(
        bool $valido,
        string $entradaOriginal,
        string $entradaNormalizada,
        string $mensaje,
        array $ordenesDetectados = []
    ): array {
        return [
            'valido' => $valido,
            'entrada_original' => $entradaOriginal,
            'entrada_normalizada' => $entradaNormalizada,
            'mensaje' => $mensaje,
            'ordenes_detectados' => $ordenesDetectados,
            'orden' => null,
            'nombre' => '',
            'nivel' => '',
            'descripcion' => '',
            'categoria' => '',
            'confianza' => 0,
            'requiere_plan_especialidad' => false,
            'requiere_horario_tecnico' => false,
            'relaciones_esperadas' => [],
            'advertencias' => [],
            'sugerencias' => array_values(array_map(
                fn(array $curso) => [
                    'orden' => $curso['orden'],
                    'nombre' => $curso['nombre'],
                    'nivel' => $curso['nivel'],
                ],
                self::catalogo()
            )),
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

        $texto = preg_replace('/[^\pL\pN\s°º]/u', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }
}
