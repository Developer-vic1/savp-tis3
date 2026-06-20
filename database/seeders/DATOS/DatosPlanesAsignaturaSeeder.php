<?php

namespace Database\Seeders\DATOS;

use Illuminate\Database\Seeder;

class DatosPlanesAsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        (new SoporteDatosBolivia())->crearPlanesAsignatura();
    }
}
