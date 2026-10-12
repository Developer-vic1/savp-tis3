<?php

namespace App\Support\Orientacion;

/** Enlaces oficiales para investigar costos y ayudas; no contienen tarifas comparables. */
final class FuentesEconomicasUniversitarias
{
    private const FUENTES = [
        'BO-UCB-LP' => [
            ['titulo' => 'Becas UCB La Paz', 'url' => 'https://lpz.ucb.edu.bo/becas/'],
        ],
        'BO-UMSA-LP' => [
            ['titulo' => 'Convocatorias de ingreso UMSA', 'url' => 'https://convocatorias.umsa.bo/es/'],
        ],
        'BO-UNIFRANZ-LP' => [
            ['titulo' => 'Becas Unifranz', 'url' => 'https://unifranz.edu.bo/admisiones/becas/'],
            ['titulo' => 'Admisiones Unifranz', 'url' => 'https://unifranz.edu.bo/admisiones/'],
        ],
        'BO-UPB-LP' => [
            ['titulo' => 'Becas y admisión UPB', 'url' => 'https://www.upb.edu/postula-a-la-upb'],
        ],
        'BO-EMI-LP' => [
            ['titulo' => 'Admisión EMI', 'url' => 'https://www.emi.edu.bo/universidad/admision'],
            ['titulo' => 'Becas EMI', 'url' => 'https://www.emi.edu.bo/universidad/becas'],
        ],
    ];

    public static function paraPrograma(?string $careerId): array
    {
        if ($careerId === null) {
            return [];
        }

        foreach (self::FUENTES as $universidadId => $fuentes) {
            if (str_starts_with($careerId, $universidadId.'-')) {
                return $fuentes;
            }
        }

        return [];
    }
}
