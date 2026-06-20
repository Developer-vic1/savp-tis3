<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosSAVPTIS3Seeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->asegurarGestion2026();

        $this->call([
            DatosPersonasSeeder::class,
            DatosDocentesSeeder::class,
            DatosEstudiantesSeeder::class,
            DatosInscripcionesSeeder::class,
            DatosPlanesAsignaturaSeeder::class,
            DatosCalificacionesSeeder::class,
            DatosAulaVirtualSeeder::class,
            DatosOrientacionVocacionalSeeder::class,
        ]);

        (new SoporteDatosBolivia())->imprimirResumen();
    }
}
