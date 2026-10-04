<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\AulaVirtual\EstadoAsistencia;
use Illuminate\Database\Seeder;

class EstadosAsistenciaOficialSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            [
                'cod_est_asi' => 'EASI_0001',
                'nom_est_asi' => 'Presente',
                'abr_est_asi' => 'P',
                'des_est_asi' => 'Estudiante asistió puntualmente a la sesión de clase.',
                'color_est_asi' => 'verde',
                'valor_porcentual' => 100.00,
                'afecta_asistencia' => true,
                'requiere_observacion' => false,
                'est_est_asi' => 'ACTIVO',
            ],
            [
                'cod_est_asi' => 'EASI_0002',
                'nom_est_asi' => 'Tardanza',
                'abr_est_asi' => 'T',
                'des_est_asi' => 'Estudiante ingresó con retraso a la sesión de clase.',
                'color_est_asi' => 'ambar',
                'valor_porcentual' => 75.00,
                'afecta_asistencia' => true,
                'requiere_observacion' => true,
                'est_est_asi' => 'ACTIVO',
            ],
            [
                'cod_est_asi' => 'EASI_0003',
                'nom_est_asi' => 'Falta',
                'abr_est_asi' => 'F',
                'des_est_asi' => 'Estudiante no asistió a la sesión de clase.',
                'color_est_asi' => 'rojo',
                'valor_porcentual' => 0.00,
                'afecta_asistencia' => true,
                'requiere_observacion' => false,
                'est_est_asi' => 'ACTIVO',
            ],
            [
                'cod_est_asi' => 'EASI_0004',
                'nom_est_asi' => 'Justificado',
                'abr_est_asi' => 'J',
                'des_est_asi' => 'Falta o inasistencia justificada formalmente ante la institución.',
                'color_est_asi' => 'verde',
                'valor_porcentual' => 100.00,
                'afecta_asistencia' => false,
                'requiere_observacion' => true,
                'est_est_asi' => 'ACTIVO',
            ],
            [
                'cod_est_asi' => 'EASI_0005',
                'nom_est_asi' => 'Licencia',
                'abr_est_asi' => 'L',
                'des_est_asi' => 'Permiso especial o licencia concedida institucionalmente.',
                'color_est_asi' => 'morado',
                'valor_porcentual' => 100.00,
                'afecta_asistencia' => false,
                'requiere_observacion' => true,
                'est_est_asi' => 'ACTIVO',
            ],
        ];

        foreach ($estados as $estado) {
            EstadoAsistencia::updateOrCreate(
                ['cod_est_asi' => $estado['cod_est_asi']],
                $estado
            );
        }
    }
}
