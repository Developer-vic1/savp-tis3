<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Ejecuta exclusivamente el flujo maestro oficial de reconstrucción SAVP 2026.
     */
    public function run(): void
    {
        $this->call([
            SAVPEstado2026Seeder::class,
        ]);
    }
}
