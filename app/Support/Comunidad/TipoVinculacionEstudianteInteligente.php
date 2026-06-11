<?php

namespace App\Support\Comunidad;

use App\Models\TipoVinculacionEstudiante;
use App\Support\CatalogoInteligenteBase;

class TipoVinculacionEstudianteInteligente extends CatalogoInteligenteBase
{
    public const ESTADO_RECONOCIDA = 'RECONOCIDA';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_DUPLICADA = 'DUPLICADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = trim((string) ($datos['nom_tve'] ?? ''));
        $descripcion = trim((string) ($datos['des_tve'] ?? ''));
        $estado = $datos['est_tve'] ?? 'ACTIVO';

        $nombreNormalizado = $this->normalizarTexto($nombre);
        $descripcionNormalizada = $this->normalizarDescripcion($descripcion);

        $bloqueos = [];
        $advertencias = [];
        $puedeGuardar = true;

        if ($nombreNormalizado === '') {
            return $this->respuestaVacia(
                datos: ['nom_tve' => '', 'des_tve' => $descripcionNormalizada, 'est_tve' => $estado],
                estadoInteligente: self::ESTADO_INCOMPLETA,
                mensaje: 'El nombre del tipo de vinculación es obligatorio.'
            );
        }

        if (mb_strlen($nombreNormalizado) < 3 || in_array($this->canonico($nombreNormalizado), ['tipo', 'otro', 'general'], true)) {
            $bloqueos[] = 'El tipo de vinculación debe ser específico y comprensible (mínimo 3 caracteres, evite términos genéricos).';
            $puedeGuardar = false;
        }

        // Duplicate checks
        $duplicidad = $this->analizarDuplicidad($nombreNormalizado, TipoVinculacionEstudiante::all(), $ignorarCodigo);
        $coincidencias = [];

        if ($duplicidad['exacto']) {
            $bloqueos[] = 'Ya existe un tipo de vinculación registrado con este nombre exacto o equivalente.';
            $puedeGuardar = false;
            $estadoInteligente = self::ESTADO_DUPLICADA;
            $coincidencias[] = ['tipo' => 'ALTA', 'mensaje' => 'Duplicado exacto'];
        } elseif ($duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe un tipo de vinculación críticamente similar registrado (' . $duplicidad['registro']['nombre'] . ').';
            $puedeGuardar = false;
            $estadoInteligente = self::ESTADO_DUPLICADA;
            $coincidencias[] = ['tipo' => 'ALTA', 'mensaje' => 'Duplicado crítico'];
        } else {
            if ($duplicidad['aproximado_leve']) {
                $advertencias[] = 'Existe otro tipo de vinculación similar registrado: "' . $duplicidad['registro']['nombre'] . '".';
            }
            $estadoInteligente = empty($bloqueos) ? self::ESTADO_RECONOCIDA : self::ESTADO_BLOQUEADA;
        }

        if ($descripcionNormalizada === '') {
            $descripcionNormalizada = "Estudiante vinculado a la institución bajo la condición {$nombreNormalizado}.";
        }

        // Student counts
        $estudiantesRelacionados = 0;
        if ($ignorarCodigo) {
            $tve = TipoVinculacionEstudiante::withCount('estudiantes')->find($ignorarCodigo);
            $estudiantesRelacionados = $tve ? $tve->estudiantes_count : 0;
        }

        $explicacion = "Condición de matrícula institucional: {$nombreNormalizado}.";
        $accionesRecomendadas = ['Auditar la regularidad del estado del alumno al inicio del periodo', 'Verificar si aplica a políticas de becas o convenios particulares'];

        $visualizacion = [
            'tipo_relacion' => $this->clasificarRelacion($nombreNormalizado),
            'nivel_institucional' => $estudiantesRelacionados > 20 ? 'Generalizado' : 'Particular',
            'estudiantes_relacionados' => $estudiantesRelacionados,
        ];

        return [
            'datos' => [
                'nom_tve' => $nombreNormalizado,
                'des_tve' => $descripcionNormalizada,
                'est_tve' => $estado,
            ],
            'estado_inteligente' => $estadoInteligente,
            'confianza' => empty($bloqueos) ? 100 : 30,
            'completitud' => $this->completitud(
                ['nom_tve' => $nombreNormalizado, 'des_tve' => $descripcionNormalizada],
                ['nom_tve', 'des_tve']
            ),
            'duplicidad' => $duplicidad,
            'coincidencias' => $coincidencias,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => ['Regular', 'Beca de Excelencia', 'Convenio Técnico', 'Traspaso Internacional', 'Becado Social'],
            'explicacion' => $explicacion,
            'acciones_recomendadas' => $accionesRecomendadas,
            'visualizacion' => $visualizacion,
            'puede_guardar' => $puedeGuardar,
        ];
    }

    private function clasificarRelacion(string $nombre): string
    {
        $nom = mb_strtolower($nombre);
        if (str_contains($nom, 'beca') || str_contains($nom, 'becado')) {
            return 'Subvencionado / Apoyo Social';
        }
        if (str_contains($nom, 'convenio') || str_contains($nom, 'acuerdo')) {
            return 'Vinculación Externa / Convenio';
        }
        if (str_contains($nom, 'regular') || str_contains($nom, 'normal') || str_contains($nom, 'comun')) {
            return 'Ordinario / Regular';
        }
        return 'Especial / Condicional';
    }

    private function respuestaVacia(array $datos, string $estadoInteligente, string $mensaje): array
    {
        return [
            'datos' => $datos,
            'estado_inteligente' => $estadoInteligente,
            'confianza' => 0,
            'completitud' => 0,
            'duplicidad' => ['exacto' => false, 'aproximado_critico' => false, 'aproximado_leve' => false, 'similitud' => 0.0, 'registro' => null],
            'coincidencias' => [],
            'bloqueos' => [$mensaje],
            'advertencias' => [$mensaje],
            'sugerencias' => ['Regular', 'Beca de Excelencia', 'Convenio Técnico'],
            'explicacion' => '',
            'acciones_recomendadas' => [],
            'visualizacion' => [],
            'puede_guardar' => false,
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_tve;
    }
}
