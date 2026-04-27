<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EspecialidadTecnicaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('especialidad_tecnica')->insert([
            [
                'cod_esp' => 'ESP_0001',
                'nom_esp' => 'Técnica Tecnología General',
                'des_esp' => 'Formación técnica inicial correspondiente al nivel de transición hacia especialidades.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0002',
                'nom_esp' => 'Técnica Tecnología Especializada',
                'des_esp' => 'Formación técnica aplicada con orientación especializada.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0003',
                'nom_esp' => 'Sistemas Informáticos',
                'des_esp' => 'Especialidad orientada al desarrollo de competencias en informática y tecnologías digitales.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0004',
                'nom_esp' => 'Contabilidad',
                'des_esp' => 'Especialidad orientada a procesos contables, financieros y administrativos.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0005',
                'nom_esp' => 'Electrónica',
                'des_esp' => 'Especialidad técnica enfocada en circuitos, dispositivos y sistemas electrónicos.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0006',
                'nom_esp' => 'Mecánica Industrial',
                'des_esp' => 'Especialidad técnica enfocada en procesos industriales y mantenimiento mecánico.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0007',
                'nom_esp' => 'Mecánica Automotriz',
                'des_esp' => 'Especialidad técnica orientada al diagnóstico y mantenimiento de vehículos.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0008',
                'nom_esp' => 'Gastronomía',
                'des_esp' => 'Especialidad técnica enfocada en preparación de alimentos y gestión gastronómica.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cod_esp' => 'ESP_0009',
                'nom_esp' => 'Textiles y Confección',
                'des_esp' => 'Especialidad técnica enfocada en diseño, patronaje y confección textil.',
                'est_esp' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}