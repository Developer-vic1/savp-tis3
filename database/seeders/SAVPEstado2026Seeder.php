<?php

namespace Database\Seeders;

use Database\Seeders\POBLACION\Estudiantes2026Seeder;
use Database\Seeders\POBLACION\Inscripciones2026Seeder;
use Database\Seeders\POBLACION\VigenciasInscripcion2026Seeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SAVPEstado2026Seeder extends Seeder
{
    /**
     * Seeder Maestro Integral de SAVP Estado 2026.
     * Reconstruye el 100% del estado funcional del sistema:
     * - Toda la estructura institucional, catálogos, personal y horarios.
     * - Toda la población estudiantil (612 estudiantes) y sus inscripciones (600 inscripciones).
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Núcleo Institucional Oficial
            $this->call(SAVPInstitucionalOficialSeeder::class);

            // 2. Población Estudiantil (612 estudiantes, personas y usuarios)
            $this->call(Estudiantes2026Seeder::class);

            // 3. Inscripciones Académicas (600 inscripciones reales)
            $this->call(Inscripciones2026Seeder::class);

            // 4. Vigencias de Inscripción (0 vigencias en estado actual)
            $this->call(VigenciasInscripcion2026Seeder::class);
        });
    }
}
