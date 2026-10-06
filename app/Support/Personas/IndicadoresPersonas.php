<?php

namespace App\Support\Personas;

use Illuminate\Database\Eloquent\Builder;

class IndicadoresPersonas
{
    /** Indicadores de los registros filtrados, sin cargar identidades ni contactos en memoria. */
    public function analizar(Builder $consulta): array
    {
        $resumen = (clone $consulta)->reorder()->setEagerLoads([])->selectRaw("COUNT(*) AS total,
            SUM(CASE WHEN TRIM(COALESCE(tel_per, '')) <> '' THEN 1 ELSE 0 END) AS telefono,
            SUM(CASE WHEN TRIM(COALESCE(ema_per, '')) <> '' THEN 1 ELSE 0 END) AS correo,
            SUM(CASE WHEN TRIM(COALESCE(dir_per, '')) <> '' THEN 1 ELSE 0 END) AS direccion,
            SUM(CASE WHEN UPPER(TRIM(COALESCE(gen_per, ''))) IN ('M', 'MASCULINO') THEN 1 ELSE 0 END) AS masculino,
            SUM(CASE WHEN UPPER(TRIM(COALESCE(gen_per, ''))) IN ('F', 'FEMENINO') THEN 1 ELSE 0 END) AS femenino,
            SUM(CASE WHEN DATE(fec_nac_per) > ? AND DATE(fec_nac_per) <= ? THEN 1 ELSE 0 END) AS menores,
            SUM(CASE WHEN DATE(fec_nac_per) > ? AND DATE(fec_nac_per) <= ? THEN 1 ELSE 0 END) AS adolescentes,
            SUM(CASE WHEN DATE(fec_nac_per) > ? AND DATE(fec_nac_per) <= ? THEN 1 ELSE 0 END) AS jovenes,
            SUM(CASE WHEN DATE(fec_nac_per) >= ? AND DATE(fec_nac_per) <= ? THEN 1 ELSE 0 END) AS adultos", [
            now()->subYears(13)->toDateString(), now()->toDateString(),
            now()->subYears(18)->toDateString(), now()->subYears(13)->toDateString(),
            now()->subYears(26)->toDateString(), now()->subYears(18)->toDateString(),
            now()->subYears(120)->toDateString(), now()->subYears(26)->toDateString(),
        ])->first();
        $total = (int) $resumen->total;
        $edades = array_map(fn ($campo) => (int) $resumen->$campo, ['menores', 'adolescentes', 'jovenes', 'adultos']);
        $edades[] = $total - array_sum($edades);
        $conCuenta = (clone $consulta)->whereHas('usuario')->count();

        return [
            'total' => $total,
            'contacto' => ['labels' => ['Con teléfono', 'Con correo', 'Con dirección'],
                'data' => [(int) $resumen->telefono, (int) $resumen->correo, (int) $resumen->direccion]],
            'edades' => ['labels' => ['Hasta 12 años', '13 a 17 años', '18 a 25 años', '26 años o más', 'Fecha por revisar'], 'data' => $edades],
            'cuentas' => ['labels' => ['Con cuenta', 'Sin cuenta'], 'data' => [$conCuenta, $total - $conCuenta]],
            'generos' => ['labels' => ['Masculino', 'Femenino', 'Sin registrar / otros'], 'data' => [(int) $resumen->masculino, (int) $resumen->femenino, $total - (int) $resumen->masculino - (int) $resumen->femenino]],
        ];
    }
}
