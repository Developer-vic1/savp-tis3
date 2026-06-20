<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosAulaVirtualSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearAulaVirtual();
    }
}
