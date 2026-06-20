<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class GeneradorSqlAcademicoService
{
    /**
     * Tablas académicas a exportar (en orden de dependencia).
     */
    protected array $tablas = [
        'personas',
        'estudiante',
        'personal_institucional',
        'gestion_academica',
        'cursos',
        'paralelo',
        'turno',
        'asignatura',
        'inscripcion_estudiante',
        'plantilla_horaria',
        'horario',
        'bloque_horario',
        'calificacion',
        'periodo_evaluacion',
        'plan_asignatura',
        'especialidad_tecnica',
        'institucion_procedencia',
        'tipo_vinculacion_estudiante',
    ];

    /**
     * Genera el archivo SQL y lo guarda en storage.
     * Retorna la ruta relativa dentro del disco 'private'.
     */
    public function generar(): string
    {
        $usuario = Auth::user();
        $fecha   = now()->format('Y-m-d H:i:s');
        $nombre  = now()->format('Ymd-His');

        $sql  = "-- ============================================================\n";
        $sql .= "-- Exportación SQL de Gestión Académica\n";
        $sql .= "-- Sistema SAVP-TIS3 — Unidad Educativa Franz Tamayo N° 3\n";
        $sql .= "-- Fecha de generación: {$fecha}\n";
        $sql .= "-- Responsable: " . ($usuario?->email ?? 'Sistema') . "\n";
        $sql .= "-- ============================================================\n\n";

        $sql .= "SET client_encoding = 'UTF8';\n";
        $sql .= "SET standard_conforming_strings = on;\n\n";

        $observaciones = [];

        foreach ($this->tablas as $tabla) {
            try {
                if (! Schema::hasTable($tabla)) {
                    $observaciones[] = "-- AVISO: tabla '{$tabla}' no existe en la base de datos.\n";
                    continue;
                }

                $registros = DB::table($tabla)->get();

                if ($registros->isEmpty()) {
                    $sql .= "-- TABLA: {$tabla} (sin registros)\n\n";
                    continue;
                }

                $sql .= "-- ────────────────────────────────────────\n";
                $sql .= "-- TABLA: {$tabla} ({$registros->count()} registros)\n";
                $sql .= "-- ────────────────────────────────────────\n";

                foreach ($registros as $registro) {
                    $arrReg = (array) $registro;
                    $cols   = implode(', ', array_map(fn ($c) => '"' . $c . '"', array_keys($arrReg)));
                    $vals   = implode(', ', array_map(fn ($v) => $this->escapar($v), array_values($arrReg)));

                    $sql .= "INSERT INTO \"{$tabla}\" ({$cols}) VALUES ({$vals});\n";
                }

                $sql .= "\n";
            } catch (\Throwable $e) {
                $observaciones[] = "-- ERROR en tabla '{$tabla}': " . $e->getMessage() . "\n";
            }
        }

        // Agregar observaciones al final
        if (!empty($observaciones)) {
            $sql .= "\n-- ── OBSERVACIONES ───────────────────────────────────\n";
            foreach ($observaciones as $obs) {
                $sql .= $obs;
            }
        }

        $sql .= "\n-- Fin de exportación\n";

        $archivo = "respaldo-academico-{$nombre}.sql";
        $ruta    = "reportes/sql/{$archivo}";

        Storage::disk('local')->put($ruta, $sql);

        return $ruta;
    }

    /**
     * Escapa un valor para SQL seguro.
     */
    protected function escapar(mixed $valor): string
    {
        if ($valor === null) {
            return 'NULL';
        }
        if (is_bool($valor)) {
            return $valor ? 'TRUE' : 'FALSE';
        }
        if (is_numeric($valor) && !is_string($valor)) {
            return (string) $valor;
        }
        // Escapar strings: comillas simples duplicadas
        $v = (string) $valor;
        $v = str_replace("'", "''", $v);
        // Escapar caracteres problemáticos
        $v = str_replace(['\\'], ['\\\\'], $v);
        return "'{$v}'";
    }
}
