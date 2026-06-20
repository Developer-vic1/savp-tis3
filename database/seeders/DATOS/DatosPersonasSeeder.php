<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosPersonasSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearPersonasBase();
    }
}
