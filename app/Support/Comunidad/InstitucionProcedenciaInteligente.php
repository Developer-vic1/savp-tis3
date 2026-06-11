<?php

namespace App\Support\Comunidad;

use App\Models\InstitucionProcedencia;
use App\Support\CatalogoInteligenteBase;

class InstitucionProcedenciaInteligente extends CatalogoInteligenteBase
{
    public const ESTADO_RECONOCIDA = 'RECONOCIDA';
    public const ESTADO_REDACTABLE = 'REDACTABLE';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_DUPLICADA = 'DUPLICADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $nombre = trim((string) ($datos['nom_ipe'] ?? ''));
        $tipo = trim((string) ($datos['tip_ipe'] ?? ''));
        $ciudad = trim((string) ($datos['ciu_ipe'] ?? ''));
        $estado = $datos['est_ipe'] ?? 'ACTIVO';

        $nombreNormalizado = $this->normalizarTexto($nombre);
        $tipoNormalizado = $this->normalizarTexto($tipo ?: 'Pública');
        $ciudadNormalizada = $this->normalizarTexto($ciudad);

        $bloqueos = [];
        $advertencias = [];
        $puedeGuardar = true;

        if ($nombreNormalizado === '') {
            return $this->respuestaVacia(
                datos: ['nom_ipe' => '', 'tip_ipe' => $tipoNormalizado, 'ciu_ipe' => $ciudadNormalizada, 'est_ipe' => $estado],
                estadoInteligente: self::ESTADO_INCOMPLETA,
                mensaje: 'El nombre de la institución es obligatorio.'
            );
        }

        if (mb_strlen($nombreNormalizado) < 5) {
            $bloqueos[] = 'El nombre institucional es incompleto (mínimo 5 caracteres).';
            $puedeGuardar = false;
        }
        if ($ciudadNormalizada === '') {
            $bloqueos[] = 'La ciudad es necesaria para identificar la procedencia geográfica de la institución.';
            $puedeGuardar = false;
        }

        // Duplicate checks
        $duplicidad = $this->analizarDuplicidad($nombreNormalizado, InstitucionProcedencia::all(), $ignorarCodigo);
        $coincidencias = [];

        if ($duplicidad['exacto']) {
            $bloqueos[] = 'Ya existe una institución de procedencia registrada con un nombre idéntico o equivalente.';
            $puedeGuardar = false;
            $estadoInteligente = self::ESTADO_DUPLICADA;
            $coincidencias[] = ['tipo' => 'ALTA', 'mensaje' => 'Duplicado exacto'];
        } elseif ($duplicidad['aproximado_critico']) {
            $bloqueos[] = 'Existe una institución críticamente similar registrada (' . $duplicidad['registro']['nombre'] . ').';
            $puedeGuardar = false;
            $estadoInteligente = self::ESTADO_DUPLICADA;
            $coincidencias[] = ['tipo' => 'ALTA', 'mensaje' => 'Duplicado crítico'];
        } else {
            if ($duplicidad['aproximado_leve']) {
                $advertencias[] = 'Existe otra institución similar en el sistema: "' . $duplicidad['registro']['nombre'] . '".';
            }
            $estadoInteligente = empty($bloqueos) ? self::ESTADO_RECONOCIDA : self::ESTADO_BLOQUEADA;
        }

        // Normalization corrections
        $corregido = false;
        $nombreSugerido = $nombreNormalizado;
        if (str_starts_with(mb_strtolower($nombreNormalizado), 'col ')) {
            $nombreSugerido = 'Colegio ' . mb_substr($nombreNormalizado, 4);
            $corregido = true;
        } elseif (str_starts_with(mb_strtolower($nombreNormalizado), 'u.e. ')) {
            $nombreSugerido = 'Unidad Educativa ' . mb_substr($nombreNormalizado, 5);
            $corregido = true;
        } elseif (str_starts_with(mb_strtolower($nombreNormalizado), 'ue ')) {
            $nombreSugerido = 'Unidad Educativa ' . mb_substr($nombreNormalizado, 3);
            $corregido = true;
        }

        if ($corregido && $estadoInteligente !== self::ESTADO_DUPLICADA) {
            $estadoInteligente = self::ESTADO_REDACTABLE;
            $advertencias[] = 'El nombre fue abreviado informalmente y se expandió a una denominación formal.';
        }

        // Student counts
        $estudiantesVinculados = 0;
        if ($ignorarCodigo) {
            $inst = InstitucionProcedencia::withCount('estudiantes')->find($ignorarCodigo);
            $estudiantesVinculados = $inst ? $inst->estudiantes_count : 0;
        }

        $explicacion = "Institución educativa de procedencia fiscal o privada ubicada en la ciudad de {$ciudadNormalizada}.";
        $accionesRecomendadas = ['Establecer contacto para seguimiento de transición vocacional', 'Validar el RUDE oficial de los estudiantes provenientes de esta unidad'];

        // Visualizacion metrics
        $visualizacion = [
            'ciudad' => $ciudadNormalizada,
            'tipo_institucion' => $tipoNormalizado,
            'estudiantes_vinculados' => $estudiantesVinculados,
            'zona_inferida' => $this->inferirZona($ciudadNormalizada),
        ];

        return [
            'datos' => [
                'nom_ipe' => $nombreSugerido,
                'tip_ipe' => $tipoNormalizado,
                'ciu_ipe' => $ciudadNormalizada,
                'est_ipe' => $estado,
            ],
            'estado_inteligente' => $estadoInteligente,
            'confianza' => empty($bloqueos) ? 100 : 30,
            'completitud' => $this->completitud(
                ['nom_ipe' => $nombreSugerido, 'tip_ipe' => $tipoNormalizado, 'ciu_ipe' => $ciudadNormalizada],
                ['nom_ipe', 'tip_ipe', 'ciu_ipe']
            ),
            'duplicidad' => $duplicidad,
            'coincidencias' => $coincidencias,
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => ['Pública', 'Privada', 'Convenio'],
            'explicacion' => $explicacion,
            'acciones_recomendadas' => $accionesRecomendadas,
            'visualizacion' => $visualizacion,
            'puede_guardar' => $puedeGuardar,
        ];
    }

    private function inferirZona(string $ciudad): string
    {
        $urbana = ['Cochabamba', 'La Paz', 'Santa Cruz', 'El Alto', 'Oruro', 'Potosi', 'Sucre', 'Tarija', 'Trinidad', 'Cobija', 'Sacaba', 'Quillacollo'];
        foreach ($urbana as $u) {
            if (str_contains(mb_strtolower($ciudad), mb_strtolower($u))) {
                return 'Urbana';
            }
        }
        return 'Rural / Provincial';
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
            'sugerencias' => ['Pública', 'Privada', 'Convenio'],
            'explicacion' => '',
            'acciones_recomendadas' => [],
            'visualizacion' => [],
            'puede_guardar' => false,
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) $registro->nom_ipe;
    }
}
