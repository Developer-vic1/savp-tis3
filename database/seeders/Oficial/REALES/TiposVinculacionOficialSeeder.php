<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\TipoVinculacionEstudiante;
use Illuminate\Database\Seeder;

class TiposVinculacionOficialSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'cod_tve' => 'TVE_0001',
                'nom_tve' => 'Regular',
                'des_tve' => 'Estudiante inscrito de forma regular en la institución.',
                'est_tve' => 'ACTIVO',
            ],
            [
                'cod_tve' => 'TVE_0002',
                'nom_tve' => 'Traslado',
                'des_tve' => 'Estudiante proveniente de otra unidad educativa.',
                'est_tve' => 'ACTIVO',
            ],
            [
                'cod_tve' => 'TVE_0003',
                'nom_tve' => 'Reincorporado',
                'des_tve' => 'Estudiante que retorna a la institución después de una interrupción académica.',
                'est_tve' => 'ACTIVO',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoVinculacionEstudiante::updateOrCreate(
                ['cod_tve' => $tipo['cod_tve']],
                $tipo
            );
        }
    }
}
