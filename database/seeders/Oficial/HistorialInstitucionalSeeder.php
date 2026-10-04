<?php

declare(strict_types=1);

namespace Database\Seeders\Oficial;

use Database\Seeders\Oficial\Soporte\HistorialInstitucional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class HistorialInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $conexion = DB::connection();
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new \RuntimeException('El historial oficial requiere PostgreSQL.');
        }
        $historial = new HistorialInstitucional($conexion, $this->command);
        $conexion->transaction(function () use ($historial, $conexion): void {
            $conexion->select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['SAVP:semillas:2020-2026']);
            if ($conexion->table('estudiante')->exists()) {
                throw new \RuntimeException('Existen estudiantes: este seeder no elimina ni reemplaza datos.');
            }
            $historial->preparar();
            foreach (range(2020, 2026) as $anio) {
                foreach (['Gestion', 'Inscripciones', 'AulaVirtual', 'Asistencias', 'Orientacion', 'Cierre'] as $fase) {
                    $this->command?->info("GESTIÓN {$anio}: {$fase}");
                    (require __DIR__."/{$anio}/{$fase}Seeder.php")($historial);
                }
                $conexion->statement('SET CONSTRAINTS ALL IMMEDIATE');
                $conexion->statement('SET CONSTRAINTS ALL DEFERRED');
                $this->command?->info("GESTIÓN {$anio} COMPLETADA");
            }
            $historial->validar();
        });
    }
}
