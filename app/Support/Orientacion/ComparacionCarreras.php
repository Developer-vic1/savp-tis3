<?php

namespace App\Support\Orientacion;

use Illuminate\Support\Str;

/** Comparación descriptiva de áreas; no calcula compatibilidad, correlación ni ranking. */
final class ComparacionCarreras
{
    private const NOMBRES_OCUPACIONALES = [
        'ONET-15-1252.00' => 'Desarrolladores de software',
        'ONET-15-1211.00' => 'Analistas de sistemas informáticos',
        'ONET-15-1241.00' => 'Arquitectos de redes informáticas',
        'ONET-19-3033.00' => 'Psicólogos clínicos y de asesoramiento',
        'ONET-19-3034.00' => 'Psicólogos escolares',
        'ONET-19-3032.00' => 'Psicólogos del trabajo y las organizaciones',
        'ONET-17-2051.00' => 'Ingenieros civiles',
        'ONET-17-2081.00' => 'Ingenieros ambientales',
        'ONET-19-2041.00' => 'Científicos y especialistas ambientales',
    ];

    public static function presentar(?array $respuestas, ?array $puntajes, ?array $estudio, array $payload = []): array
    {
        $vigente = ExperienciaIntereses::perfilVigente($respuestas, $puntajes);
        $scores = $vigente ? $puntajes['scores'] : [];
        $orden = array_keys(ExperienciaIntereses::AREAS);
        usort($orden, fn ($a, $b) => ($scores[$b] ?? -1) <=> ($scores[$a] ?? -1));
        $empate = $vigente && $scores[$orden[2]] === $scores[$orden[3]];
        $plano = $vigente && count(array_unique($scores)) === 1;
        // Incluye todas las áreas empatadas con el tercer puntaje, sin romper el empate por el orden de letras.
        $top = $vigente && ! $plano ? array_values(array_filter($orden, fn ($codigo) => $scores[$codigo] >= $scores[$orden[2]])) : [];
        $perfil = ['vigente' => $vigente, 'estado' => ! $vigente ? 'UNAVAILABLE' : ($plano ? 'TIED' : 'READY'),
            'codigo' => $vigente ? $puntajes['holland_code'] : null, 'top' => $top, 'empatadas_corte' => $empate, 'plano' => $plano,
            'nota' => ! $vigente ? 'Completa el cuestionario vigente para comparar tus intereses.' : ($plano ? 'Las seis áreas tienen el mismo puntaje. Ninguna destaca; mostramos tus puntajes sin convertirlos en una coincidencia numérica.' : ($empate ? 'Incluimos las '.count($top).' áreas destacadas, también las empatadas con el tercer puntaje. No excluimos ninguna por el orden de letras. La coincidencia describe áreas compartidas, no compatibilidad con la carrera.' : 'Contamos áreas compartidas sin valorar su orden. Una coincidencia no mide compatibilidad con la carrera.'))];
        $perfil['areas'] = [];
        foreach (ExperienciaIntereses::AREAS as $codigo => $area) {
            $perfil['areas'][] = ['codigo' => $codigo, 'nombre' => $area['nombre'], 'puntaje' => $scores[$codigo] ?? null,
                'porcentaje' => $vigente ? $scores[$codigo] * 5 : null];
        }
        $programas = [];
        foreach ($estudio['career_evidence_profiles'] ?? [] as $programa) {
            if (! is_array($programa)) {
                continue;
            }
            $nombre = trim((string) data_get($programa, 'career_name'));
            $universidad = trim((string) data_get($programa, 'university'));
            if ($nombre === '' || $universidad === '') {
                continue;
            }
            $clave = (string) data_get($programa, 'career_id', '');
            if ($clave === '') {
                $clave = 'programa-'.substr(hash('sha256', $nombre.'|'.$universidad), 0, 20);
            }
            $fuentes = self::fuentes(data_get($programa, 'sources', []));
            $referencias = [];
            foreach (data_get($programa, 'vocational_interest_relation.occupational_references', []) as $referencia) {
                if (! is_array($referencia)) {
                    continue;
                }
                $codigo = (string) data_get($referencia, 'holland_code', '');
                $letras = str_split($codigo);
                $referenceSources = self::fuentes(data_get($referencia, 'sources', []));
                $valida = preg_match('/^[RIASEC]{1,3}$/', $codigo) === 1 && count(array_unique($letras)) === count($letras)
                    && is_string(data_get($referencia, 'occupation_id')) && trim(data_get($referencia, 'occupation_id')) !== ''
                    && is_string(data_get($referencia, 'occupation_title')) && trim(data_get($referencia, 'occupation_title')) !== ''
                    && $referenceSources !== [] && in_array(data_get($referencia, 'evidence_status'), ['DIRECTLY_DOCUMENTED', 'DOCUMENT_SUPPORTED_INFERENCE', 'HYPOTHESIS'], true);
                $disponible = $valida && $perfil['estado'] === 'READY';
                $compartidas = $disponible ? array_values(array_intersect($perfil['top'], $letras)) : [];
                $referencias[] = ['id' => data_get($referencia, 'occupation_id'),
                    'titulo' => self::NOMBRES_OCUPACIONALES[data_get($referencia, 'occupation_id')] ?? data_get($referencia, 'occupation_title', 'Ocupación documentada'),
                    'titulo_original' => data_get($referencia, 'occupation_title', 'Ocupación documentada'),
                    'codigo' => $valida ? $codigo : null, 'estado' => ! $valida || ! $vigente ? 'UNAVAILABLE' : ($plano ? 'TIED' : 'READY'),
                    'compartidas' => $compartidas, 'cantidad' => $disponible ? count($compartidas) : null,
                    'denominador_estudiante' => $disponible ? count($top) : null, 'denominador_referencia' => $valida ? count($letras) : null,
                    'fuentes' => $referenceSources, 'hipotesis' => data_get($referencia, 'evidence_status') !== 'DIRECTLY_DOCUMENTED',
                    'nota' => ! $valida ? 'Falta una referencia estructurada con fuente y código verificables.' : ($disponible && $compartidas === [] ? 'No comparten áreas en este resumen; revisa las materias antes de descartar esta opción.' : $perfil['nota'])];
            }
            $programas[$clave] = ['clave' => $clave, 'nombre' => $nombre, 'universidad' => $universidad,
                'materias' => self::textos(data_get($programa, 'academic_program.documented_subjects', [])),
                'areas' => self::textos(data_get($programa, 'academic_program.knowledge_areas', [])),
                'refuerzos' => $vigente ? data_get($programa, 'preparation.reinforcement_areas', []) : [],
                'puentes' => $vigente ? data_get($programa, 'preparation.academic_evidence', []) : [],
                'fuentes' => $fuentes, 'referencias' => $referencias,
                'estado' => $referencias === [] ? 'UNAVAILABLE' : $perfil['estado'],
                'nota' => $referencias === [] ? 'El estudio guardado no incluye códigos ocupacionales estructurados para esta opción. Puedes comparar sus materias y fuentes.' : $perfil['nota']];
        }

        return ['perfil' => $perfil, 'programas' => $programas,
            'carreras' => self::agruparCarreras($programas, data_get($estudio, 'informational_external_careers', [])),
            'metodo' => 'Conteo descriptivo de áreas compartidas entre las áreas con puntaje igual o mayor al tercer puntaje de tu perfil y cada referencia ocupacional. Incluye todas las áreas empatadas; si las seis son iguales no calcula un conteo de coincidencia. No reproduce el algoritmo de correspondencia de O*NET ni estima compatibilidad.',
            'fuente_metodo' => 'https://www.onetcenter.org/reports/IP_Career_Returns.html'];
    }

    /**
     * La carrera se explora antes que la sede. Cada oferta conserva su propia
     * evidencia: una referencia o malla de una universidad no avala a otra.
     */
    private static function agruparCarreras(array $programas, mixed $externas): array
    {
        $carreras = [];
        foreach ($programas as $programa) {
            $clave = Str::slug($programa['nombre']);
            if ($clave === '') {
                continue;
            }
            $carreras[$clave] ??= self::carreraVacia($programa['nombre'], $clave);
            $carreras[$clave]['programas'][] = $programa;
            $carreras[$clave]['ofertas'][] = ['universidad' => $programa['universidad'], 'estado' => 'DOCUMENTADA', 'clave' => $programa['clave'],
                'fuentes_economicas' => FuentesEconomicasUniversitarias::paraPrograma($programa['clave'])];
            if ($carreras[$clave]['universidad_fuente'] === null && ($programa['materias'] !== [] || $programa['refuerzos'] !== [])) {
                $carreras[$clave]['materias'] = $programa['materias'];
                $carreras[$clave]['refuerzos'] = $programa['refuerzos'];
                $carreras[$clave]['universidad_fuente'] = $programa['universidad'];
            }
            foreach ($programa['referencias'] as $referencia) {
                $identidad = implode('|', [$referencia['id'] ?? '', $referencia['codigo'] ?? '', $referencia['estado'] ?? '']);
                if (! isset($carreras[$clave]['referencias'][$identidad])) {
                    $carreras[$clave]['referencias'][$identidad] = $referencia + ['universidades_origen' => [$programa['universidad']]];
                } elseif (! in_array($programa['universidad'], $carreras[$clave]['referencias'][$identidad]['universidades_origen'], true)) {
                    $carreras[$clave]['referencias'][$identidad]['universidades_origen'][] = $programa['universidad'];
                }
            }
        }

        foreach (is_array($externas) ? $externas : [] as $externa) {
            if (! is_array($externa)) {
                continue;
            }
            $nombre = trim((string) data_get($externa, 'career_name'));
            $universidad = trim((string) data_get($externa, 'university'));
            $clave = Str::slug($nombre);
            if ($clave === '' || $universidad === '' || self::fuentes(data_get($externa, 'sources', [])) === []) {
                continue;
            }
            $carreras[$clave] ??= self::carreraVacia($nombre, $clave);
            $yaIncluida = collect($carreras[$clave]['ofertas'])->contains(fn ($oferta) => Str::slug($oferta['universidad']) === Str::slug($universidad));
            if (! $yaIncluida) {
                $carreras[$clave]['ofertas'][] = ['universidad' => $universidad, 'estado' => 'PENDIENTE', 'clave' => data_get($externa, 'career_id'),
                    'fuentes_economicas' => FuentesEconomicasUniversitarias::paraPrograma(data_get($externa, 'career_id'))];
            }
        }

        foreach ($carreras as &$carrera) {
            $carrera['referencias'] = array_values($carrera['referencias']);
            $carrera['referencia_principal'] = collect($carrera['referencias'])->first(fn ($referencia) => $referencia['cantidad'] !== null) ?? ($carrera['referencias'][0] ?? null);
            $carrera['nota'] = $carrera['referencias'] === []
                ? 'Todavía no hay una referencia ocupacional verificada para relacionar esta carrera con tus intereses. Revisa sus materias y fuentes sin interpretarlo como una coincidencia de cero.'
                : 'La coincidencia pertenece a las referencias indicadas; cada universidad conserva su propia malla y evidencia.';
        }
        unset($carrera);

        return array_values($carreras);
    }

    private static function carreraVacia(string $nombre, string $clave): array
    {
        return ['nombre' => $nombre, 'clave' => $clave, 'programas' => [], 'ofertas' => [], 'referencias' => [],
            'referencia_principal' => null, 'materias' => [], 'refuerzos' => [], 'universidad_fuente' => null, 'nota' => ''];
    }

    private static function textos(mixed $valores): array
    {
        return collect(is_array($valores) ? $valores : [])->filter(fn ($valor) => is_string($valor) && trim($valor) !== '')->unique()->values()->all();
    }

    private static function fuentes(mixed $valores): array
    {
        return collect(is_array($valores) ? $valores : [])->filter(function ($fuente) {
            $url = data_get($fuente, 'reference');

            return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false
                && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
                && parse_url($url, PHP_URL_USER) === null && parse_url($url, PHP_URL_PASS) === null;
        })->unique('reference')->values()->all();
    }
}
