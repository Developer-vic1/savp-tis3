<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosCalificacionesSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearCalificaciones();
    }
}
