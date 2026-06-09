<?php

namespace App\Support\Comunidad;

use App\Models\InstitucionProcedencia;
use App\Support\CatalogoInteligenteBase;

class InstitucionProcedenciaInteligente extends CatalogoInteligenteBase
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_ipe'] ?? '');
        $tipo = $this->normalizarTexto($datos['tip_ipe'] ?? '');
        $ciudad = $this->normalizarTexto($datos['ciu_ipe'] ?? '');
        $duplicidad = $this->analizarDuplicidad($nombre, InstitucionProcedencia::all(), $ignorarCodigo);
        $bloqueos = [];

        if (mb_strlen($nombre) < 5) {
            $bloqueos[] = 'El nombre institucional es incompleto.';
        }
        if ($ciudad === '') {
            $bloqueos[] = 'La ciudad es necesaria para identificar la institución.';
        }
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe una institución igual o críticamente similar.';
        }

        return [
            'datos' => ['nom_ipe' => $nombre, 'tip_ipe' => $tipo ?: 'Pública', 'ciu_ipe' => $ciudad, 'est_ipe' => $datos['est_ipe'] ?? 'ACTIVO'],
            'duplicidad' => $duplicidad,
            'completitud' => $this->completitud(compact('nombre', 'tipo', 'ciudad'), ['nombre', 'tipo', 'ciudad']),
            'bloqueos' => $bloqueos,
            'sugerencias' => ['Pública', 'Privada', 'Convenio'],
            'puede_guardar' => $bloqueos === [],
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_ipe;
    }
}
