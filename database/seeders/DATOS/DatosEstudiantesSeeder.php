<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosEstudiantesSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearEstudiantes();
    }
}
