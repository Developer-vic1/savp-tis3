<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Curso;
use Illuminate\Database\Seeder;

class CursosOficialesSeeder extends Seeder
{
    /**
     * Registra los cursos institucionales oficiales de SAVP (1ro a 6to de Secundaria).
     */
    public function run(): void
    {
        $cursos = [
            [
                'cod_cur' => 'CUR_0001',
                'nom_cur' => '1ro de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
            [
                'cod_cur' => 'CUR_0002',
                'nom_cur' => '2do de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
            [
                'cod_cur' => 'CUR_0003',
                'nom_cur' => '3ro de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
            [
                'cod_cur' => 'CUR_0004',
                'nom_cur' => '4to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
            [
                'cod_cur' => 'CUR_0005',
                'nom_cur' => '5to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
            [
                'cod_cur' => 'CUR_0006',
                'nom_cur' => '6to de Secundaria',
                'niv_cur' => 'Secundaria',
                'est_cur' => 'ACTIVO',
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::updateOrCreate(
                ['cod_cur' => $curso['cod_cur']],
                $curso
            );
        }
    }
}
