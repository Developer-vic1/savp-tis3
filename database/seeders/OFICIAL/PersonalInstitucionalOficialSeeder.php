<?php

namespace Database\Seeders\OFICIAL;

use App\Models\Administrador;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\Regente;
use App\Models\SecretariaGeneral;
use Illuminate\Database\Seeder;

class PersonalInstitucionalOficialSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 56 registros de
     * personas del personal institucional, sus registros en personal_institucional
     * y sus tipificaciones oficiales (Administrador, Director, Secretaria General,
     * Docentes y Administrativos base).
     */
    public function run(): void
    {
        $fuente = __DIR__.'/FUENTES/personal_institucional.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $nomina = require $fuente;

        foreach ($nomina as $item) {
            $pData = $item['persona'];
            $piData = $item['personal_institucional'];
            $subtipo = $item['subtipo'] ?? [];

            // 1. Registro en Persona
            Persona::updateOrCreate(
                ['cod_per' => $pData['cod_per']],
                [
                    'nom_per' => $pData['nom_per'],
                    'ape_pat_per' => $pData['ape_pat_per'],
                    'ape_mat_per' => $pData['ape_mat_per'] ?? null,
                    'ci_per' => $pData['ci_per'],
                    'com_per' => $pData['com_per'] ?? null,
                    'exp_per' => $pData['exp_per'] ?? null,
                    'fec_nac_per' => $pData['fec_nac_per'] ?? null,
                    'gen_per' => $pData['gen_per'] ?? null,
                    'tel_per' => $pData['tel_per'] ?? null,
                    'ema_per' => $pData['ema_per'] ?? null,
                    'dir_per' => $pData['dir_per'] ?? null,
                    'fot_per' => $pData['fot_per'] ?? null,
                    'est_per' => $pData['est_per'] ?? true,
                ]
            );

            // 2. Registro en Personal Institucional
            PersonalInstitucional::updateOrCreate(
                ['cod_pin' => $piData['cod_pin']],
                [
                    'cod_per' => $piData['cod_per'],
                    'car_pin' => $piData['car_pin'],
                    'est_pin' => $piData['est_pin'] ?? 'ACTIVO',
                ]
            );

            // 3. Subtipos Institucionales
            if (! empty($subtipo['administrador'])) {
                Administrador::updateOrCreate(
                    ['cod_adm' => $subtipo['administrador']['cod_adm']],
                    [
                        'cod_pin' => $subtipo['administrador']['cod_pin'],
                        'est_adm' => $subtipo['administrador']['est_adm'] ?? 'ACTIVO',
                    ]
                );
            }

            if (! empty($subtipo['director'])) {
                Director::updateOrCreate(
                    ['cod_dir' => $subtipo['director']['cod_dir']],
                    [
                        'cod_pin' => $subtipo['director']['cod_pin'],
                        'est_dir' => $subtipo['director']['est_dir'] ?? 'ACTIVO',
                    ]
                );
            }

            if (! empty($subtipo['secretaria_general'])) {
                SecretariaGeneral::updateOrCreate(
                    ['cod_sge' => $subtipo['secretaria_general']['cod_sge']],
                    [
                        'cod_pin' => $subtipo['secretaria_general']['cod_pin'],
                        'est_sge' => $subtipo['secretaria_general']['est_sge'] ?? 'ACTIVO',
                    ]
                );
            }

            if (! empty($subtipo['regente'])) {
                Regente::updateOrCreate(
                    ['cod_reg' => $subtipo['regente']['cod_reg']],
                    [
                        'cod_pin' => $subtipo['regente']['cod_pin'],
                        'est_reg' => $subtipo['regente']['est_reg'] ?? 'ACTIVO',
                    ]
                );
            }

            if (! empty($subtipo['docente'])) {
                Docente::updateOrCreate(
                    ['cod_doc' => $subtipo['docente']['cod_doc']],
                    [
                        'cod_pin' => $subtipo['docente']['cod_pin'],
                        'esp_doc' => $subtipo['docente']['esp_doc'] ?? null,
                        'num_mod_doc' => $subtipo['docente']['num_mod_doc'] ?? 0,
                        'est_doc' => $subtipo['docente']['est_doc'] ?? 'ACTIVO',
                    ]
                );
            }
        }
    }
}
