<?php

namespace App\Support\Academico;

use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Support\CatalogoInteligenteBase;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeriodoEvaluacionInteligente extends CatalogoInteligenteBase
{
    /** Orientación de lectura: las fechas sugeridas no confirman un periodo oficial. */
    public function orientarPeriodoActual(?GestionAcademica $gestion, iterable $catalogo, CarbonInterface $fecha): array
    {
        $sinDatos = ['nombre' => 'Por confirmar', 'estado' => 'SIN_FECHAS',
            'explicacion' => 'El catálogo no guarda fechas por gestión. Se requiere confirmar el calendario institucional.',
            'rango' => null];
        if (! $gestion || (int) $gestion->ani_gea !== (int) $fecha->year) {
            return $sinDatos;
        }
        if (! $gestion->fii_gea || ! $gestion->ffi_gea || $fecha->toDateString() < substr($gestion->fii_gea, 0, 10) || $fecha->toDateString() > substr($gestion->ffi_gea, 0, 10)) {
            return $sinDatos;
        }
        $referencias = (new GestionAcademicaInteligente)->sugerirPeriodosEvaluacion((int) $gestion->ani_gea);
        foreach ($referencias as $referencia) {
            if ($fecha->toDateString() < $referencia['fecha_inicio'] || $fecha->toDateString() > $referencia['fecha_fin']) {
                continue;
            }
            $coincidentes = collect($catalogo)->filter(fn ($periodo) => $periodo->est_pev === 'ACTIVO'
                && (int) $periodo->ord_pev === $referencia['orden']
                && mb_strtolower(trim($periodo->nom_pev)) === mb_strtolower($referencia['nombre']));
            if ($coincidentes->count() !== 1) {
                return $sinDatos;
            }

            return ['nombre' => $coincidentes->first()->nom_pev, 'estado' => 'REFERENCIAL',
                'explicacion' => 'Según la planificación sugerida por Support. Pendiente de confirmación institucional.',
                'rango' => $referencia['fecha_inicio'].' / '.$referencia['fecha_fin']];
        }

        return ['nombre' => 'Fuera del rango curricular de referencia', 'estado' => 'FUERA_DE_RANGO',
            'explicacion' => 'La fecha de consulta no pertenece a un trimestre de la planificación sugerida.', 'rango' => null];
    }

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_pev'] ?? '');
        $orden = is_numeric($datos['ord_pev'] ?? null) ? (int) $datos['ord_pev'] : 0;
        $duplicidad = $this->analizarDuplicidad($nombre, PeriodoEvaluacion::all(), $ignorarCodigo);
        $ordenDuplicado = PeriodoEvaluacion::query()
            ->when($ignorarCodigo, fn ($q) => $q->where('cod_pev', '!=', $ignorarCodigo))
            ->where('ord_pev', $orden)->exists();
        $bloqueos = [];

        if (mb_strlen($nombre) < 4) {
            $bloqueos[] = 'El nombre del periodo es incompleto.';
        }
        if ($orden < 1 || $orden > GestionAcademicaInteligente::CANTIDAD_TRIMESTRES) {
            $bloqueos[] = 'La planificación regular contempla tres trimestres. Selecciona un orden del 1 al 3 (RM 0001/2026, artículo 3).';
        }
        $gestion = Schema::hasTable('gestion_academica') ? DB::table('gestion_academica')
            ->whereIn('est_gea', ['PLANIFICADA', 'PLANIFICADO', 'ACTIVA', 'ACTIVO'])->orderByDesc('ani_gea')->first() : null;
        if ($ignorarCodigo === null) {
            if (! $gestion || (int) $gestion->ani_gea !== 2026) {
                $bloqueos[] = 'Confirma la gestión y su normativa ministerial antes de incorporar un trimestre.';
            } elseif (! $gestion->fii_gea || today()->toDateString() > substr($gestion->fii_gea, 0, 10)) {
                $bloqueos[] = 'La incorporación de trimestres se realiza durante la planificación inicial, antes de comenzar las clases.';
            }
        } elseif ($gestion && $gestion->fii_gea && today()->toDateString() > substr($gestion->fii_gea, 0, 10)) {
            $anterior = PeriodoEvaluacion::find($ignorarCodigo);
            if ($anterior && (int) $anterior->ord_pev !== $orden) {
                $bloqueos[] = 'La gestión ya inició. Conserva el orden de los trimestres registrados.';
            }
        }
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe un periodo igual o críticamente similar.';
        }
        if ($ordenDuplicado) {
            $bloqueos[] = 'El orden seleccionado ya está asignado a otro periodo.';
        }

        return [
            'datos' => ['nom_pev' => $nombre, 'ord_pev' => $orden, 'est_pev' => $datos['est_pev'] ?? 'ACTIVO'],
            'duplicidad' => $duplicidad,
            'completitud' => $this->completitud(['nombre' => $nombre, 'orden' => $orden], ['nombre', 'orden']),
            'bloqueos' => $bloqueos,
            'sugerencias' => ['Primer Trimestre', 'Segundo Trimestre', 'Tercer Trimestre'],
            'puede_guardar' => $bloqueos === [],
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_pev;
    }
}
