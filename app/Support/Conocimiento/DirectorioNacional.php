<?php

namespace App\Support\Conocimiento;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DirectorioNacional
{
    public const REGIMENES = [
        'PUBLICA_AUTONOMA' => 'Pública autónoma',
        'PRIVADA' => 'Privada',
        'REGIMEN_ESPECIAL' => 'Régimen especial',
        'INDIGENA' => 'Indígena',
        'ESPECIAL' => 'Especial',
    ];

    public static function presentar(array $directorio, array $filtros): array
    {
        $instituciones = collect($directorio['institutions'] ?? []);
        $departamentos = $instituciones->flatMap(fn ($item) => $item['departments'] ?? [])->unique()->sort()->values()->all();
        $regimenes = $instituciones->pluck('regime')->unique()->sort()->mapWithKeys(fn ($regimen) => [$regimen => self::REGIMENES[$regimen] ?? Str::headline($regimen)])->all();
        $duraciones = $instituciones->flatMap(fn ($item) => $item['programs'] ?? [])->map(fn ($programa) => self::duracion($programa))->filter()->unique()->sort()->values()->all();
        $busqueda = self::normalizar($filtros['buscar'] ?? '');
        $duracion = $filtros['duracion'] ?? '';
        $visibles = $instituciones->filter(function ($item) use ($filtros, $busqueda, $duracion) {
            if (($filtros['departamento'] ?? '') !== '' && ! in_array($filtros['departamento'], $item['departments'] ?? [], true)) {
                return false;
            }
            if (($filtros['regimen'] ?? '') !== '' && ($item['regime'] ?? '') !== $filtros['regimen']) {
                return false;
            }
            $programas = collect($item['programs'] ?? []);
            $economia = $filtros['economia'] ?? '';
            $programasFiltrados = $programas->filter(fn ($programa) => self::programaCoincide($programa, $filtros));
            if (($duracion !== '' || $economia === 'verificados' || ($economia === 'pendientes' && $programas->isNotEmpty())) && $programasFiltrados->isEmpty()) {
                return false;
            }
            $ofertaPublicada = collect(data_get($item, 'published_offer.careers', []))->pluck('name')->implode(' ');
            $texto = self::normalizar(implode(' ', [$item['name'] ?? '', $item['acronym'] ?? '', implode(' ', $item['departments'] ?? []), $programas->pluck('name')->implode(' '), $ofertaPublicada]));

            return $busqueda === '' || str_contains($texto, $busqueda);
        })->map(function ($item) use ($filtros) {
            $item['regimen_label'] = self::REGIMENES[$item['regime']] ?? Str::headline($item['regime']);
            $item['recoleccion_label'] = match ($item['collection']['status'] ?? 'NO_INICIADA') {
                'PENDIENTE_VERIFICACION_DOMINIO' => 'Dominio pendiente de verificación; recolección bloqueada',
                'PARCIAL_CON_PENDIENTES' => 'Recolección parcial con enlaces pendientes',
                'PARCIAL_CON_ERRORES' => 'Recolección parcial; algunos documentos no pudieron leerse',
                'COLA_AGOTADA_REQUIERE_REVISION' => 'Documentos recolectados; revisión académica pendiente',
                default => 'Recolección todavía no iniciada',
            };
            $item['dominio_label'] = ($item['website_verification_status'] ?? '') === 'OFFICIAL_DOMAIN_VERIFIED'
                ? 'Dominio institucional contrastado' : 'Dominio pendiente de verificación actual';
            if (($item['ingestion_status'] ?? '') === 'QUARANTINE_SITE_REVIEW_REQUIRED') {
                $item['dominio_label'] = 'Sitio en cuarentena: contenido actual ajeno al ámbito universitario';
                $item['recoleccion_label'] = 'Recolección bloqueada hasta revisión del sitio';
            }
            $item['programas_visibles'] = collect($item['programs'] ?? [])->filter(fn ($programa) => self::programaCoincide($programa, $filtros))->map(fn ($programa) => $programa + ['duracion_label' => self::duracion($programa)])->values()->all();

            return $item;
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        if (($filtros['orden'] ?? '') === 'departamento') {
            $visibles = $visibles->sortBy(fn ($item) => self::normalizar(implode(' ', $item['departments'] ?? []).' '.$item['name']))->values();
        }

        $fechaRecoleccion = null;
        if (! empty($directorio['collection_updated_at'])) {
            try {
                $fechaRecoleccion = Carbon::parse($directorio['collection_updated_at'])->timezone('America/La_Paz')->format('d/m/Y H:i').' (Bolivia)';
            } catch (\Exception) {
                $fechaRecoleccion = null;
            }
        }

        return ['instituciones' => $visibles->all(), 'departamentos' => $departamentos, 'regimenes' => $regimenes, 'duraciones' => $duraciones, 'filtros' => $filtros, 'fechaRecoleccion' => $fechaRecoleccion];
    }

    public static function duracion(array $programa): ?string
    {
        if (is_string($programa['duration_text'] ?? null) && trim($programa['duration_text']) !== '') {
            return trim($programa['duration_text']);
        }

        return isset($programa['duration_semesters']) ? $programa['duration_semesters'].' semestres' : null;
    }

    /** Datos públicos mínimos para filtrar en el navegador sin repetir la consulta documental. */
    public static function datosReactivos(array $instituciones): array
    {
        return collect($instituciones)->values()->map(fn (array $institucion, int $indice) => [
            'indice' => $indice,
            'nombre' => $institucion['name'] ?? '',
            'sigla' => $institucion['acronym'] ?? '',
            'departamentos' => $institucion['departments'] ?? [],
            'regimen' => $institucion['regime'] ?? '',
            'programas' => collect($institucion['programs'] ?? [])->map(fn (array $programa) => [
                'nombre' => $programa['name'] ?? '',
                'duracion' => self::duracion($programa),
                'costosVerificados' => self::costosVerificados($programa),
            ])->values()->all(),
            'ofertaPublicada' => collect(data_get($institucion, 'published_offer.careers', []))->pluck('name')->all(),
        ])->all();
    }

    public static function programaCoincide(array $programa, array $filtros): bool
    {
        $economia = $filtros['economia'] ?? '';

        return (($filtros['duracion'] ?? '') === '' || self::duracion($programa) === $filtros['duracion'])
            && ($economia !== 'verificados' || self::costosVerificados($programa))
            && ($economia !== 'pendientes' || ! self::costosVerificados($programa));
    }

    private static function costosVerificados(array $programa): bool
    {
        return ($programa['economic_status'] ?? '') === 'VERIFICADO' && ! empty($programa['economic_evidence']);
    }

    public static function enlacePublico(mixed $url): ?string
    {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
            && parse_url($url, PHP_URL_SCHEME) === 'https'
            && parse_url($url, PHP_URL_USER) === null && parse_url($url, PHP_URL_PASS) === null ? $url : null;
    }

    private static function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }
}
