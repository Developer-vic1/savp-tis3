<?php

namespace Database\Seeders;

use App\Models\AulaVirtual\EstadoAsistencia;
use Illuminate\Database\Seeder;

class AulaVirtualDatosSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['cod_est_asi' => 'EASI_0001', 'nom_est_asi' => 'Presente', 'abr_est_asi' => 'P', 'valor_porcentual' => 100, 'color_est_asi' => 'verde'],
            ['cod_est_asi' => 'EASI_0002', 'nom_est_asi' => 'Tardanza', 'abr_est_asi' => 'T', 'valor_porcentual' => 75, 'color_est_asi' => 'ambar'],
            ['cod_est_asi' => 'EASI_0003', 'nom_est_asi' => 'Falta', 'abr_est_asi' => 'F', 'valor_porcentual' => 0, 'color_est_asi' => 'rojo'],
            ['cod_est_asi' => 'EASI_0004', 'nom_est_asi' => 'Justificado', 'abr_est_asi' => 'J', 'valor_porcentual' => 100, 'color_est_asi' => 'verde'],
        ];

        foreach ($estados as $estado) {
            EstadoAsistencia::updateOrCreate(
                ['cod_est_asi' => $estado['cod_est_asi']],
                [
                    'nom_est_asi' => $estado['nom_est_asi'],
                    'abr_est_asi' => $estado['abr_est_asi'],
                    'des_est_asi' => 'Estado pedagógico de asistencia del Aula Virtual SAVP-TIS3.',
                    'color_est_asi' => $estado['color_est_asi'],
                    'valor_porcentual' => $estado['valor_porcentual'],
                    'afecta_asistencia' => true,
                    'requiere_observacion' => $estado['nom_est_asi'] === 'Justificado',
                    'est_est_asi' => 'ACTIVO',
                ]
            );
        }
    }
}
