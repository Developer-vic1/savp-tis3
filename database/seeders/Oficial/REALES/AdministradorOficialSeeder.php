<?php

namespace Database\Seeders\Oficial\REALES;

use Database\Seeders\Oficial\REALES\ADMINISTRADOR\AdministradorSistemaSeeder;
use Illuminate\Database\Seeder;

/** Entrada histórica: reutiliza el único seeder administrativo oficial. */
class AdministradorOficialSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdministradorSistemaSeeder::class);
    }
}
