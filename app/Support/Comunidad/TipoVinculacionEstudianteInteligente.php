<?php

namespace App\Support\Comunidad;

use App\Models\TipoVinculacionEstudiante;
use App\Support\CatalogoInteligenteBase;

class TipoVinculacionEstudianteInteligente extends CatalogoInteligenteBase
{
    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = $this->normalizarTexto($datos['nom_tve'] ?? '');
        $descripcion = $this->normalizarDescripcion($datos['des_tve'] ?? '');
        $duplicidad = $this->analizarDuplicidad($nombre, TipoVinculacionEstudiante::all(), $ignorarCodigo);

        if ($descripcion === '' && $nombre !== '') {
            $descripcion = "Estudiante vinculado a la institución bajo la condición {$nombre}.";
        }

        $bloqueos = [];
        if (mb_strlen($nombre) < 3 || in_array($this->canonico($nombre), ['tipo', 'otro', 'general'], true)) {
            $bloqueos[] = 'El tipo debe ser específico y comprensible.';
        }
        if ($duplicidad['exacto'] || $duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe un tipo de vinculación igual o críticamente similar.';
        }

        return [
            'datos' => ['nom_tve' => $nombre, 'des_tve' => $descripcion, 'est_tve' => $datos['est_tve'] ?? 'ACTIVO'],
            'duplicidad' => $duplicidad,
            'completitud' => $this->completitud(compact('nombre', 'descripcion'), ['nombre', 'descripcion']),
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_tve;
    }
}
