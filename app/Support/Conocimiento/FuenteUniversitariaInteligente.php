<?php

namespace App\Support\Conocimiento;

use App\Rules\FechaPublicacionAcademica;
use App\Rules\UrlFuenteUniversitaria;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as Validador;

/** Prevención local: no consulta URLs, no acredita documentos ni sustituye la revisión. */
class FuenteUniversitariaInteligente
{
    public function __construct(private readonly ?string $catalogoPath = null) {}

    private const CAMPOS = [
        'title' => ['min' => 3, 'max' => 240, 'obligatorio' => true, 'multilinea' => false],
        'declared_institution' => ['min' => 3, 'max' => 240, 'obligatorio' => true, 'multilinea' => false],
        'url' => ['min' => 12, 'max' => 2000, 'obligatorio' => true, 'multilinea' => false],
        'source_type' => ['min' => 1, 'max' => 40, 'obligatorio' => true, 'multilinea' => false],
        'publication_date' => ['min' => 4, 'max' => 10, 'obligatorio' => false, 'multilinea' => false],
        'scope' => ['min' => 10, 'max' => 500, 'obligatorio' => true, 'multilinea' => true],
        'version' => ['min' => 1, 'max' => 120, 'obligatorio' => true, 'multilinea' => false],
        'campus' => ['min' => 2, 'max' => 160, 'obligatorio' => true, 'multilinea' => false],
        'city' => ['min' => 2, 'max' => 120, 'obligatorio' => true, 'multilinea' => false],
        'justification' => ['min' => 20, 'max' => 1000, 'obligatorio' => true, 'multilinea' => true],
        'limitations_text' => ['min' => 0, 'max' => 2000, 'obligatorio' => false, 'multilinea' => true],
    ];

    // Espejo conservador del registro existente en ai-service/app/knowledge/governance.py.
    private const UNIVERSIDADES = [
        ['domain' => 'ucb.edu.bo', 'institution' => 'Universidad Católica Boliviana San Pablo', 'aliases' => ['UCB', 'Universidad Católica Boliviana']],
        ['domain' => 'umsa.bo', 'institution' => 'Universidad Mayor de San Andrés', 'aliases' => ['UMSA']],
        ['domain' => 'upb.edu', 'institution' => 'Universidad Privada Boliviana', 'aliases' => ['UPB']],
        ['domain' => 'unifranz.edu.bo', 'institution' => 'Universidad Franz Tamayo', 'aliases' => ['UNIFRANZ', 'Franz Tamayo']],
        ['domain' => 'emi.edu.bo', 'institution' => 'Escuela Militar de Ingeniería', 'aliases' => ['EMI']],
        ['domain' => 'udabol.edu.bo', 'institution' => 'Universidad de Aquino Bolivia', 'aliases' => ['UDABOL']],
        ['domain' => 'upea.bo', 'institution' => 'Universidad Pública de El Alto', 'aliases' => ['UPEA']],
    ];

    private const TIPOS = ['OFFICIAL_CURRICULUM_PDF', 'OFFICIAL_CAREER_HTML', 'OFFICIAL_REGULATION_PDF', 'OFFICIAL_UNIVERSITY_PAGE'];

    public function contratoParaInterfaz(): array
    {
        return ['version_contrato' => '1.0', 'campos' => self::CAMPOS, 'tipos_fuente' => self::TIPOS,
            'universidades' => $this->universidades(), 'catalogo_fuente' => 'ai-service/data/catalog/universidades_bolivia.json',
            'verificacion_remota' => false, 'limitaciones' => ['max_lineas' => 10, 'min_linea' => 3, 'max_linea' => 500]];
    }

    public function reglas(): array
    {
        $reglas = [];
        foreach (self::CAMPOS as $campo => $limites) {
            $reglas[$campo] = ['bail', $limites['obligatorio'] ? 'required' : 'nullable', 'string',
                'min:'.$limites['min'], 'max:'.$limites['max']];
            if ($campo !== 'url') {
                $reglas[$campo][] = function (string $attribute, mixed $value, Closure $fail) use ($limites): void {
                    $mensaje = $this->errorTexto($value, $limites);
                    if ($mensaje !== null) {
                        $fail($mensaje);
                    }
                };
            }
        }
        $reglas['url'][] = new UrlFuenteUniversitaria;
        $reglas['url'][] = function (string $attribute, mixed $value, Closure $fail): void {
            $mensaje = $this->errorUrl($value);
            if ($mensaje !== null) {
                $fail($mensaje);
            }
        };
        $reglas['source_type'] = ['bail', 'required', 'string', 'in:'.implode(',', self::TIPOS)];
        $reglas['publication_date'] = ['bail', 'nullable', 'string', 'max:10', new FechaPublicacionAcademica];
        $reglas['limitations_text'][] = function (string $attribute, mixed $value, Closure $fail): void {
            $lineas = $this->limitaciones($value);
            if (count($lineas) > 10) {
                $fail('Use como máximo 10 limitaciones; ninguna se descarta automáticamente.');
            }
            foreach ($lineas as $linea) {
                if (mb_strlen($linea) < 3 || mb_strlen($linea) > 500) {
                    $fail('Cada limitación debe tener entre 3 y 500 caracteres.');
                    break;
                }
            }
        };
        $reglas['submitted_by_role'] = ['prohibited'];

        return $reglas;
    }

    public function validar(array $entrada, array $reglasAdicionales = []): array
    {
        return $this->validador($entrada, $reglasAdicionales)->validate();
    }

    private function validador(array $entrada, array $reglasAdicionales = []): Validador
    {
        $validador = Validator::make($entrada, $this->reglas() + $reglasAdicionales);
        $validador->after(function (Validador $validador) use ($entrada): void {
            if ($validador->errors()->hasAny(['url', 'declared_institution'])) {
                return;
            }
            $universidad = $this->universidad($entrada['url']);
            if ($universidad && ! $this->coincideInstitucion($entrada['declared_institution'], $universidad)) {
                $validador->errors()->add('declared_institution', 'El dominio corresponde a '.$universidad['institution'].'. Corrija la universidad declarada.');
            }
        });

        return $validador;
    }

    public function analizar(array $entrada): array
    {
        $errores = $this->validador($entrada)->errors()->toArray();
        $advertencias = [];
        $sugerencias = [];
        $url = $entrada['url'] ?? null;
        if (is_string($url) && $this->errorUrl($url) === null) {
            if ($this->universidad($url) === null) {
                $advertencias[] = 'Dominio boliviano no registrado: requiere verificación humana antes de aprobarse.';
            }
            if (is_string($entrada['source_type'] ?? null) && str_ends_with($entrada['source_type'], '_PDF') && ! str_ends_with(mb_strtolower((string) parse_url($url, PHP_URL_PATH)), '.pdf')) {
                $advertencias[] = 'Se declaró PDF y el enlace no termina en .pdf; el formato debe comprobarse con el documento.';
                $sugerencias[] = 'Use el enlace directo del documento o compruebe su formato antes del análisis.';
            }
        }
        if (empty($entrada['publication_date'])) {
            $sugerencias[] = 'La fecha puede quedar vacía si la publicación no la informa.';
        }
        $sugerencias[] = 'Compruebe el documento y su título antes de analizar; la revisión local no acredita disponibilidad ni duplicidad.';

        return ['puede_continuar' => $errores === [], 'estado' => $errores ? 'BLOQUEADA' : ($advertencias ? 'REQUIERE_REVISION' : 'VALIDA_LOCALMENTE'),
            'bloqueos' => collect($errores)->flatMap(fn ($mensajes, $campo) => array_map(fn ($mensaje) => ['campo' => $campo, 'mensaje' => $mensaje], $mensajes))->values()->all(),
            'errores' => $errores, 'advertencias' => $advertencias, 'sugerencias' => $sugerencias, 'verificacion_remota' => false];
    }

    public function errorUrl(mixed $valor, bool $validarDominio = true): ?string
    {
        if (! is_string($valor) || mb_strlen($valor) < 12 || mb_strlen($valor) > 2000) {
            return 'Use una URL completa de entre 12 y 2000 caracteres.';
        }
        if (! mb_check_encoding($valor, 'UTF-8') || preg_match('/[\s\p{Cc}\p{Cf}]|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)/iu', $valor) || str_contains($valor, '\\') || preg_match('/%(?![0-9a-f]{2})/i', $valor)) {
            return 'Use una URL HTTPS sin espacios, controles, barras invertidas ni escapes incompletos.';
        }
        $partes = parse_url($valor);
        $host = strtolower(rtrim($partes['host'] ?? '', '.'));
        if (! is_array($partes) || strtolower($partes['scheme'] ?? '') !== 'https'
            || isset($partes['user']) || isset($partes['pass']) || ($partes['port'] ?? 443) !== 443
            || filter_var($host, FILTER_VALIDATE_IP)
            || ! preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}\z/', $host)) {
            return 'Use el dominio público de la universidad, con HTTPS, sin IP, credenciales ni puertos personalizados.';
        }
        if ($validarDominio && $this->universidad($valor) === null && ! str_ends_with($host, '.bo')) {
            return 'Este dominio no está registrado como universidad boliviana; no se aceptan fuentes extranjeras.';
        }

        foreach ($this->institucionesNacionales() as $institucion) {
            if (($institucion['ingestion_status'] ?? '') !== 'QUARANTINE_SITE_REVIEW_REQUIRED') {
                continue;
            }
            foreach ([...($institucion['official_domains'] ?? []), ...($institucion['historical_domains_2016'] ?? [])] as $dominio) {
                if (is_string($dominio) && $this->dominioValido($dominio) && ($host === $dominio || str_ends_with($host, '.'.$dominio))) {
                    return 'Este sitio está en cuarentena por contenido ajeno al ámbito universitario. Requiere revisión antes de proponer o extraer fuentes.';
                }
            }
        }

        return null;
    }

    public function universidad(string $url): ?array
    {
        $host = strtolower(rtrim((string) parse_url($url, PHP_URL_HOST), '.'));
        foreach ($this->universidades() as $universidad) {
            if ($host === $universidad['domain'] || str_ends_with($host, '.'.$universidad['domain'])) {
                return $universidad;
            }
        }

        return null;
    }

    private function institucionesNacionales(): array
    {
        $ruta = $this->catalogoPath ?? base_path('ai-service/data/catalog/universidades_bolivia.json');
        if (! is_file($ruta) || ! is_readable($ruta)) {
            return [];
        }
        try {
            $catalogo = json_decode(file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($catalogo['institutions'] ?? null) ? array_values(array_filter($catalogo['institutions'], 'is_array')) : [];
    }

    private function universidades(): array
    {
        $universidades = array_column(self::UNIVERSIDADES, null, 'domain');
        foreach ($this->institucionesNacionales() as $institucion) {
            if (($institucion['website_verification_status'] ?? '') !== 'OFFICIAL_DOMAIN_VERIFIED'
                || ($institucion['ingestion_status'] ?? '') === 'QUARANTINE_SITE_REVIEW_REQUIRED'
                || ! is_string($institucion['name'] ?? null) || trim($institucion['name']) === '') {
                continue;
            }
            $aliases = array_values(array_unique(array_filter([$institucion['acronym'] ?? null, $institucion['name']], fn ($valor) => is_string($valor) && trim($valor) !== '')));
            foreach ($institucion['official_domains'] ?? [] as $dominio) {
                if (! is_string($dominio) || ! $this->dominioValido($dominio)) {
                    continue;
                }
                if (isset($universidades[$dominio])) {
                    $universidades[$dominio]['aliases'] = array_values(array_unique([...$universidades[$dominio]['aliases'], ...$aliases]));
                } else {
                    $universidades[$dominio] = ['domain' => $dominio, 'institution' => $institucion['name'], 'aliases' => $aliases];
                }
            }
        }

        return array_values($universidades);
    }

    private function dominioValido(string $dominio): bool
    {
        return (bool) preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}\z/', $dominio)
            && ! filter_var($dominio, FILTER_VALIDATE_IP);
    }

    private function coincideInstitucion(string $declarada, array $universidad): bool
    {
        $normalizar = fn ($texto) => trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(Str::ascii($texto))) ?? '');

        return in_array($normalizar($declarada), array_map($normalizar, [$universidad['institution'], ...$universidad['aliases']]), true);
    }

    private function errorTexto(string $valor, array $limites): ?string
    {
        if (! mb_check_encoding($valor, 'UTF-8') || preg_match('/(?![\r\n\x{0085}])\p{Cc}|\p{Cf}/u', $valor)
            || (! $limites['multilinea'] && preg_match('/[\r\n\x{0085}\x{2028}\x{2029}]/u', $valor))) {
            return 'Use texto legible sin caracteres de control ni invisibles; los campos breves deben ocupar una sola línea.';
        }
        $legible = trim(preg_replace('/\s+/u', ' ', $valor) ?? '');
        if ($legible !== '' && (! preg_match('/[\p{L}\p{N}]/u', $legible) || mb_strlen($legible) < $limites['min'])) {
            return 'Escriba información legible suficiente; los espacios o signos aislados no completan el campo.';
        }
        if ($limites['obligatorio'] && $legible === '') {
            return 'Complete este campo con información legible.';
        }

        return null;
    }

    public function limitaciones(?string $valor): array
    {
        return collect(preg_split('/\R+/', $valor ?? ''))->map(fn ($linea) => trim($linea))->filter(fn ($linea) => $linea !== '')->values()->all();
    }
}
