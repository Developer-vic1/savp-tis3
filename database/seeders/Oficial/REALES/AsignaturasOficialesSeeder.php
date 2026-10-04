<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Asignatura;
use Illuminate\Database\Seeder;

class AsignaturasOficialesSeeder extends Seeder
{
    /**
     * Registra las 14 asignaturas institucionales oficiales de SAVP.
     */
    public function run(): void
    {
        $asignaturas = [
            [
                'cod_asi' => 'ASI_0001',
                'nom_asi' => 'Comunicación y Lenguaje',
                'sig_asi' => 'LCO',
                'hor_asi' => 4,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0002',
                'nom_asi' => 'Matemática',
                'sig_asi' => 'MAT',
                'hor_asi' => 5,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0003',
                'nom_asi' => 'Ciencias Sociales',
                'sig_asi' => 'CSO',
                'hor_asi' => 4,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0004',
                'nom_asi' => 'Ciencias Biológicas',
                'sig_asi' => 'CBG',
                'hor_asi' => 3,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0005',
                'nom_asi' => 'Física',
                'sig_asi' => 'FIS',
                'hor_asi' => 3,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0006',
                'nom_asi' => 'Química',
                'sig_asi' => 'QMC',
                'hor_asi' => 3,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0007',
                'nom_asi' => 'Educación Física',
                'sig_asi' => 'EFD',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0008',
                'nom_asi' => 'Lengua Extranjera - Inglés',
                'sig_asi' => 'LEX',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0009',
                'nom_asi' => 'Valores y Espiritualidades',
                'sig_asi' => 'VER',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0010',
                'nom_asi' => 'Cosmovisiones y Filosofía',
                'sig_asi' => 'CFS',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0011',
                'nom_asi' => 'Psicología',
                'sig_asi' => 'PSI',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0012',
                'nom_asi' => 'Educación Musical',
                'sig_asi' => 'EMU',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0013',
                'nom_asi' => 'Artes Plásticas y Visuales',
                'sig_asi' => 'APV',
                'hor_asi' => 2,
                'est_asi' => 'ACTIVO',
            ],
            [
                'cod_asi' => 'ASI_0014',
                'nom_asi' => 'Técnica Tecnología General',
                'sig_asi' => 'TTG',
                'hor_asi' => 4,
                'est_asi' => 'ACTIVO',
            ],
        ];

        foreach ($asignaturas as $asignatura) {
            Asignatura::updateOrCreate(
                ['cod_asi' => $asignatura['cod_asi']],
                $asignatura
            );
        }
    }
}
