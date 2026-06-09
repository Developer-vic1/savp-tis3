<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class CatalogoInteligenteBase
{
    public function normalizarTexto(?string $texto): string
    {
        return Str::of((string) $texto)->squish()->lower()->title()->toString();
    }

    public function normalizarDescripcion(?string $texto): string
    {
        $texto = Str::of((string) $texto)->squish()->toString();

        return $texto === '' ? '' : Str::ucfirst($texto);
    }

    public function canonico(?string $texto): string
    {
        return Str::of((string) $texto)->ascii()->lower()->squish()->toString();
    }

    public function analizarDuplicidad(string $nombre, Collection $existentes, ?string $ignorarCodigo = null): array
    {
        $canonico = $this->canonico($nombre);
        $mejor = null;
        $mejorSimilitud = 0.0;

        foreach ($existentes as $registro) {
            if ($ignorarCodigo && ($registro->getKey() === $ignorarCodigo)) {
                continue;
            }

            similar_text($canonico, $this->canonico($this->nombreRegistro($registro)), $similitud);

            if ($similitud > $mejorSimilitud) {
                $mejorSimilitud = $similitud;
                $mejor = $registro;
            }
        }

        $exacto = $mejor && $mejorSimilitud >= 99.5;
        $critico = ! $exacto && $mejor && $mejorSimilitud >= 88;
        $leve = ! $exacto && ! $critico && $mejor && $mejorSimilitud >= 70;

        return [
            'exacto' => $exacto,
            'aproximado_critico' => $critico,
            'aproximado_leve' => $leve,
            'similitud' => round($mejorSimilitud, 1),
            'registro' => ($exacto || $critico || $leve) ? [
                'codigo' => $mejor->getKey(),
                'nombre' => $this->nombreRegistro($mejor),
            ] : null,
        ];
    }

    public function completitud(array $datos, array $requeridos): int
    {
        $completos = collect($requeridos)->filter(
            fn (string $campo) => trim((string) ($datos[$campo] ?? '')) !== ''
        )->count();

        return $requeridos === [] ? 100 : (int) round(($completos / count($requeridos)) * 100);
    }

    abstract protected function nombreRegistro(object $registro): string;
}
