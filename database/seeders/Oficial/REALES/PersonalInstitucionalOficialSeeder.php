<?php

namespace Database\Seeders\Oficial\REALES;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\PersonalInstitucional;
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
                    'est_pin' => $piData['est_pin'] ?? 'ACTIVO',
                ]
            );

            // 3. Docente: entidad propia; los demás cargos se representan mediante roles/vínculos.








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
