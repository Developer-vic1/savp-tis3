<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HorarioBloqueSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('turno')) {
            $this->command?->warn('La tabla turno no existe.');
            return;
        }

        if (! Schema::hasTable('horario_bloque')) {
            $this->command?->warn('La tabla horario_bloque no existe.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | LIMPIEZA CONTROLADA
        |--------------------------------------------------------------------------
        | En etapa de desarrollo se eliminan los detalles y bloques para reconstruir
        | correctamente la estructura horaria institucional.
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('horario_detalle')) {
            DB::table('horario_detalle')->delete();
        }

        DB::table('horario_bloque')->delete();

        $turnoManana = $this->buscarTurno(['mañana', 'manana', 'matutino', 'am']);
        $turnoTarde = $this->buscarTurno(['tarde', 'vespertino', 'pm']);

        if (! $turnoManana) {
            $this->command?->warn('No se encontró un turno de mañana. Verifica la tabla turno.');
        }

        if (! $turnoTarde) {
            $this->command?->warn('No se encontró un turno de tarde. Verifica la tabla turno.');
        }

        if ($turnoManana) {
            $this->crearBloquesManana($turnoManana->cod_tur);
        }

        if ($turnoTarde) {
            $this->crearBloquesTarde($turnoTarde->cod_tur);
        }

        $this->command?->info('Bloques de horario de mañana y tarde reconstruidos correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | TURNO MAÑANA
    |--------------------------------------------------------------------------
    | Ajustado hasta las 13:30.
    |--------------------------------------------------------------------------
    */

    private function crearBloquesManana(string $codTur): void
    {
        $bloques = [
            [
                'num_hbl' => 1,
                'hor_ini_hbl' => '08:00',
                'hor_fin_hbl' => '08:40',
                'nom_hbl' => 'Bloque 1',
                'obs_hbl' => 'Primer bloque del turno mañana.',
            ],
            [
                'num_hbl' => 2,
                'hor_ini_hbl' => '08:40',
                'hor_fin_hbl' => '09:20',
                'nom_hbl' => 'Bloque 2',
                'obs_hbl' => 'Segundo bloque del turno mañana.',
            ],
            [
                'num_hbl' => 3,
                'hor_ini_hbl' => '09:20',
                'hor_fin_hbl' => '10:00',
                'nom_hbl' => 'Bloque 3',
                'obs_hbl' => 'Tercer bloque del turno mañana.',
            ],
            [
                'num_hbl' => 4,
                'hor_ini_hbl' => '10:20',
                'hor_fin_hbl' => '11:00',
                'nom_hbl' => 'Bloque 4',
                'obs_hbl' => 'Cuarto bloque del turno mañana, posterior al recreo.',
            ],
            [
                'num_hbl' => 5,
                'hor_ini_hbl' => '11:00',
                'hor_fin_hbl' => '11:40',
                'nom_hbl' => 'Bloque 5',
                'obs_hbl' => 'Quinto bloque del turno mañana.',
            ],
            [
                'num_hbl' => 6,
                'hor_ini_hbl' => '11:40',
                'hor_fin_hbl' => '12:20',
                'nom_hbl' => 'Bloque 6',
                'obs_hbl' => 'Sexto bloque del turno mañana.',
            ],
            [
                'num_hbl' => 7,
                'hor_ini_hbl' => '12:20',
                'hor_fin_hbl' => '13:00',
                'nom_hbl' => 'Bloque 7',
                'obs_hbl' => 'Séptimo bloque del turno mañana.',
            ],
            [
                'num_hbl' => 8,
                'hor_ini_hbl' => '13:00',
                'hor_fin_hbl' => '13:30',
                'nom_hbl' => 'Bloque 8',
                'obs_hbl' => 'Octavo bloque del turno mañana.',
            ],
        ];

        $this->guardarBloques($codTur, $bloques);
    }

    /*
    |--------------------------------------------------------------------------
    | TURNO TARDE
    |--------------------------------------------------------------------------
    | Estructura equivalente de 8 bloques.
    |--------------------------------------------------------------------------
    */

    private function crearBloquesTarde(string $codTur): void
    {
        $bloques = [
            [
                'num_hbl' => 1,
                'hor_ini_hbl' => '14:00',
                'hor_fin_hbl' => '14:40',
                'nom_hbl' => 'Bloque 1',
                'obs_hbl' => 'Primer bloque del turno tarde.',
            ],
            [
                'num_hbl' => 2,
                'hor_ini_hbl' => '14:40',
                'hor_fin_hbl' => '15:20',
                'nom_hbl' => 'Bloque 2',
                'obs_hbl' => 'Segundo bloque del turno tarde.',
            ],
            [
                'num_hbl' => 3,
                'hor_ini_hbl' => '15:20',
                'hor_fin_hbl' => '16:00',
                'nom_hbl' => 'Bloque 3',
                'obs_hbl' => 'Tercer bloque del turno tarde.',
            ],
            [
                'num_hbl' => 4,
                'hor_ini_hbl' => '16:20',
                'hor_fin_hbl' => '17:00',
                'nom_hbl' => 'Bloque 4',
                'obs_hbl' => 'Cuarto bloque del turno tarde, posterior al descanso.',
            ],
            [
                'num_hbl' => 5,
                'hor_ini_hbl' => '17:00',
                'hor_fin_hbl' => '17:40',
                'nom_hbl' => 'Bloque 5',
                'obs_hbl' => 'Quinto bloque del turno tarde.',
            ],
            [
                'num_hbl' => 6,
                'hor_ini_hbl' => '17:40',
                'hor_fin_hbl' => '18:20',
                'nom_hbl' => 'Bloque 6',
                'obs_hbl' => 'Sexto bloque del turno tarde.',
            ],
            [
                'num_hbl' => 7,
                'hor_ini_hbl' => '18:20',
                'hor_fin_hbl' => '19:00',
                'nom_hbl' => 'Bloque 7',
                'obs_hbl' => 'Séptimo bloque del turno tarde.',
            ],
            [
                'num_hbl' => 8,
                'hor_ini_hbl' => '19:00',
                'hor_fin_hbl' => '19:30',
                'nom_hbl' => 'Bloque 8',
                'obs_hbl' => 'Octavo bloque del turno tarde.',
            ],
        ];

        $this->guardarBloques($codTur, $bloques);
    }

    private function guardarBloques(string $codTur, array $bloques): void
    {
        foreach ($bloques as $bloque) {
            DB::table('horario_bloque')->insert([
                'cod_hbl' => $this->generarCodigoHorarioBloque(),
                'cod_tur' => $codTur,
                'num_hbl' => $bloque['num_hbl'],
                'hor_ini_hbl' => $bloque['hor_ini_hbl'],
                'hor_fin_hbl' => $bloque['hor_fin_hbl'],
                'nom_hbl' => $bloque['nom_hbl'],
                'obs_hbl' => $bloque['obs_hbl'],
                'est_hbl' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function buscarTurno(array $palabrasClave): ?object
    {
        if (! Schema::hasColumn('turno', 'cod_tur')) {
            return null;
        }

        $columnasNombre = [
            'nom_tur',
            'tur_tur',
            'nombre',
            'des_tur',
        ];

        $columnasExistentes = array_filter($columnasNombre, function ($columna) {
            return Schema::hasColumn('turno', $columna);
        });

        foreach ($columnasExistentes as $columna) {
            foreach ($palabrasClave as $palabra) {
                $turno = DB::table('turno')
                    ->whereRaw("LOWER({$columna}) LIKE ?", [
                        '%' . mb_strtolower($palabra) . '%',
                    ])
                    ->first();

                if ($turno) {
                    return $turno;
                }
            }
        }

        return null;
    }

    private function generarCodigoHorarioBloque(): string
    {
        $ultimo = DB::table('horario_bloque')
            ->where('cod_hbl', 'like', 'HBL_%')
            ->orderByDesc('cod_hbl')
            ->value('cod_hbl');

        if (! $ultimo) {
            return 'HBL_0001';
        }

        $numero = (int) str_replace('HBL_', '', $ultimo);

        return 'HBL_' . str_pad((string) ($numero + 1), 4, '0', STR_PAD_LEFT);
    }
}