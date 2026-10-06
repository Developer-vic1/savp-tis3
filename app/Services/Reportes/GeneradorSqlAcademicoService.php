<?php

namespace App\Services\Reportes;

use App\Services\ReportAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/** Respaldo nativo: estructura, datos, secuencias, funciones y restricciones. */
class GeneradorSqlAcademicoService
{
    public function generar(): string
    {
        app(ReportAccessService::class)->authorize(auth()->user(), ['Reportes_Administrativos', 'Gestion_Academica']);
        $conexion = DB::connection();
        if ($conexion->getDriverName() !== 'pgsql') {
            throw new \RuntimeException('El respaldo oficial requiere PostgreSQL.');
        }
        $config = $conexion->getConfig();
        $version = (int) $conexion->selectOne("SELECT current_setting('server_version_num') numero")->numero;
        $mayor = intdiv($version, 10000);
        $candidatos = array_filter([
            $config['dump_binary'] ?? null,
            (new ExecutableFinder)->find('pg_dump'),
            PHP_OS_FAMILY === 'Windows' ? (getenv('ProgramFiles') ?: 'C:/Program Files').'/PostgreSQL/'.$mayor.'/bin/pg_dump.exe' : null,
        ]);
        $binario = null;
        foreach ($candidatos as $candidato) {
            if (! is_file($candidato)) {
                continue;
            }
            $comprobar = new Process([$candidato, '--version']);
            $comprobar->setTimeout(10)->run();
            if ($comprobar->isSuccessful() && preg_match('/PostgreSQL\)?\s+(\d+)/', $comprobar->getOutput(), $coincidencia) && (int) $coincidencia[1] === $mayor) {
                $binario = $candidato;
                break;
            }
        }
        if (! $binario) {
            throw new \RuntimeException('Configura PG_DUMP_BINARY con pg_dump de la misma versión mayor que el servidor PostgreSQL.');
        }
        $ruta = 'reportes/sql/respaldo-academico-'.now()->format('Ymd-His').'-'.Str::random(12).'.sql';
        Storage::disk('local')->makeDirectory(dirname($ruta));
        $proceso = new Process([$binario, '--format=plain', '--no-owner', '--no-acl', '--encoding=UTF8', '--file', Storage::disk('local')->path($ruta)], null, [
            'PGHOST' => (string) $config['host'], 'PGPORT' => (string) ($config['port'] ?? 5432),
            'PGDATABASE' => (string) $config['database'], 'PGUSER' => (string) $config['username'],
            'PGPASSWORD' => (string) ($config['password'] ?? ''), 'PGSSLMODE' => (string) ($config['sslmode'] ?? 'prefer'),
            'PGCONNECT_TIMEOUT' => '10',
        ]);
        $proceso->setTimeout(600);
        try {
            $proceso->run();
            if (! $proceso->isSuccessful() || ! Storage::disk('local')->exists($ruta) || Storage::disk('local')->size($ruta) === 0) {
                throw new \RuntimeException('El respaldo PostgreSQL no se completó. No se entregará un archivo parcial.');
            }
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($ruta);
            throw $e;
        }

        return $ruta;
    }
}
