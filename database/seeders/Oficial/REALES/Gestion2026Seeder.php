<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\GestionAcademica;
use Illuminate\Database\Seeder;

class Gestion2026Seeder extends Seeder
{
    /**
     * Registra la gestión académica oficial 2026.
     */
    public function run(): void
    {
        GestionAcademica::updateOrCreate(
            ['cod_gea' => 'GEA_0001'],
            [
                'ani_gea' => 2026,
                'fii_gea' => '2026-02-02',
                'ffi_gea' => '2026-12-04',
                'est_gea' => 'ACTIVO',
            ]
        );
    }
}
