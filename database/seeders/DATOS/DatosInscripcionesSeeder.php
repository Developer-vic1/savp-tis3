<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosInscripcionesSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearInscripciones();
    }
}
