<?php

namespace App\Support\Academico;

use App\Rules\UrlFuenteUniversitaria;
use Illuminate\Support\Facades\Validator;

/** Consulta el catálogo trazable del aporte; no inventa carreras ni incorpora fuentes. */
final class UniversidadesCalendario
{
    public function consultar(): array
    {
        $catalogo = $this->leer(base_path('ai-service/data/catalog/careers.json'));
        $fuentes = collect($this->leer(base_path('ai-service/data/sources/sources.json'))['sources'] ?? []);
        return collect($catalogo['universities'] ?? [])->map(function ($u) use ($catalogo, $fuentes) {
            $carreras = collect($catalogo['careers'] ?? [])->where('university_id', $u['university_id']);
            $ids = $carreras->pluck('source_ids')->flatten()->unique();
            $referencias = $fuentes->whereIn('source_id', $ids)->filter(fn ($f) => ($f['official'] ?? false)
                && Validator::make(['url' => $f['url'] ?? ''], ['url' => ['url:https', new UrlFuenteUniversitaria]])->passes());
            return ['valor' => $u['university_id'], 'etiqueta' => $u['name'], 'sede' => $u['campus'].' · '.$u['city'],
                'carreras' => $carreras->pluck('name')->unique()->values()->all(),
                'fuentes' => $referencias->map(fn ($f) => ['nombre' => $f['title'], 'url' => $f['url']])->values()->all(),
                'fecha' => $catalogo['generated_at'] ?? null];
        })->values()->all();
    }

    private function leer(string $ruta): array
    {
        if (! is_file($ruta)) return [];
        try { return json_decode(file_get_contents($ruta), true, 64, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { report($e); return []; }
    }

    public function identificar(string $url): array
    {
        if (Validator::make(['url'=>$url], ['url'=>['required','url:https',new UrlFuenteUniversitaria]])->fails()) {
            return ['bloqueada'=>true, 'mensaje'=>'Escribe una página HTTPS pública y válida.'];
        }
        $host = strtolower(rtrim(parse_url($url, PHP_URL_HOST), '.'));
        foreach ([
            'google.com'=>'Google, un buscador', 'google.com.bo'=>'Google, un buscador', 'gmail.com'=>'Gmail, un servicio de correo',
            'youtube.com'=>'YouTube, una plataforma de videos', 'youtu.be'=>'YouTube, una plataforma de videos',
            'facebook.com'=>'Facebook, una red social', 'fb.com'=>'Facebook, una red social',
            'instagram.com'=>'Instagram, una red social', 'tiktok.com'=>'TikTok, una red social',
        ] as $dominio=>$tipo) {
            if ($host===$dominio || str_ends_with($host, '.'.$dominio)) return ['bloqueada'=>true,
                'mensaje'=>'Este enlace pertenece a '.$tipo.'. Comparte la página institucional de la universidad.'];
        }
        foreach ($this->consultar() as $universidad) {
            foreach ($universidad['fuentes'] as $fuente) {
                $dominio = $this->dominioInstitucional(strtolower(parse_url($fuente['url'], PHP_URL_HOST)));
                if ($host!==$dominio && ! str_ends_with($host, '.'.$dominio)) continue;
                return ['bloqueada'=>false, 'conocida'=>true, 'clave'=>$dominio,
                    'url'=>'https://'.$host.'/', 'titulo'=>$universidad['etiqueta'], 'catalogo'=>$universidad,
                    'mensaje'=>'Esta universidad ya tiene un expediente de orientación. Sus páginas de carreras pertenecen a la misma institución; no es necesario crearla otra vez.'];
            }
        }
        return ['bloqueada'=>false, 'conocida'=>false, 'url'=>'https://'.$host.'/',
            'mensaje'=>'Todavía no reconocemos este dominio como una universidad boliviana. La revisión debe identificar a la institución antes de estudiar sus carreras.'];
    }

    private function dominioInstitucional(string $host): string
    {
        $partes = explode('.', rtrim($host, '.'));
        return implode('.', array_slice($partes, str_ends_with($host, '.edu.bo') || str_ends_with($host, '.ac.bo') ? -3 : -2));
    }
}
