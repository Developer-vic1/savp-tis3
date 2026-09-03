<?php

namespace App\Support\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class SoporteInteligenteBase
{
    // Estados generales
    public const ESTADO_OK = 'OK';
    public const ESTADO_OBSERVADO = 'OBSERVADO';
    public const ESTADO_ALERTA = 'ALERTA';
    public const ESTADO_BLOQUEADO = 'BLOQUEADO';

    // Niveles de riesgo
    public const RIESGO_BAJO = 'BAJO';
    public const RIESGO_MEDIO = 'MEDIO';
    public const RIESGO_ALTO = 'ALTO';
    public const RIESGO_CRITICO = 'CRITICO';

    // Tipos de regla
    public const TIPO_INTEGRIDAD = 'INTEGRIDAD';
    public const TIPO_NORMATIVA = 'NORMATIVA';
    public const TIPO_INSTITUCIONAL = 'INSTITUCIONAL';
    public const TIPO_PEDAGOGICA = 'PEDAGOGICA';
    public const TIPO_ESTADISTICA = 'ESTADISTICA';
    public const TIPO_RECOMENDACION = 'RECOMENDACION';

    // Comportamientos
    public const COMP_BLOQUEO = 'BLOQUEO';
    public const COMP_ADVERTENCIA = 'ADVERTENCIA';
    public const COMP_SUGERENCIA = 'SUGERENCIA';

    /**
     * Construye la estructura canónica de respuesta de un Soporte Inteligente.
     */
    protected function construirResultado(
        bool $puedeContinuar = true,
        bool $puedeGuardar = true,
        string $estado = self::ESTADO_OK,
        string $nivelRiesgo = self::RIESGO_BAJO,
        array $bloqueos = [],
        array $advertencias = [],
        array $sugerencias = [],
        array $hallazgos = [],
        array $datosCalculados = [],
        array $impacto = [],
        array $resumen = [],
        array $fuentesRegla = []
    ): array {
        $bloqueosUnicos = array_values(array_unique(array_filter($bloqueos)));
        $advertenciasUnicas = array_values(array_unique(array_filter($advertencias)));
        $sugerenciasUnicas = array_values(array_unique(array_filter($sugerencias)));

        // Si existen bloqueos, de forma estricta NO se puede guardar ni continuar
        if (count($bloqueosUnicos) > 0) {
            $puedeContinuar = false;
            $puedeGuardar = false;
            $estado = self::ESTADO_BLOQUEADO;
            $nivelRiesgo = count($bloqueosUnicos) >= 2 ? self::RIESGO_CRITICO : self::RIESGO_ALTO;
        } elseif (count($advertenciasUnicas) > 0) {
            if ($estado === self::ESTADO_OK) {
                $estado = count($advertenciasUnicas) >= 3 ? self::ESTADO_ALERTA : self::ESTADO_OBSERVADO;
            }
            if ($nivelRiesgo === self::RIESGO_BAJO) {
                $nivelRiesgo = count($advertenciasUnicas) >= 3 ? self::RIESGO_MEDIO : self::RIESGO_BAJO;
            }
        }

        return [
            'estado' => $estado,
            'nivel_riesgo' => $nivelRiesgo,
            'puede_continuar' => $puedeContinuar,
            'puede_guardar' => $puedeGuardar,
            'bloqueos' => $bloqueosUnicos,
            'advertencias' => $advertenciasUnicas,
            'sugerencias' => $sugerenciasUnicas,
            'hallazgos' => array_values($hallazgos),
            'datos_calculados' => $datosCalculados,
            'impacto' => $impacto,
            'resumen' => $resumen,
            'fuentes_regla' => array_values(array_unique($fuentesRegla)),
        ];
    }

    /**
     * Agrega un hallazgo estructurado al contenedor de hallazgos.
     */
    protected function registrarHallazgo(
        array &$hallazgos,
        string $codigo,
        string $tipo,
        string $comportamiento,
        string $mensaje,
        string $nivelRiesgo = self::RIESGO_BAJO,
        array $contexto = [],
        ?string $fuente = null
    ): void {
        $hallazgos[] = [
            'codigo' => $codigo,
            'tipo' => $tipo,
            'comportamiento' => $comportamiento,
            'mensaje' => $mensaje,
            'nivel_riesgo' => $nivelRiesgo,
            'contexto' => $contexto,
            'fuente' => $fuente,
        ];
    }

    /**
     * Normaliza un texto eliminando espacios sobrantes y capitalizando adecuadamente.
     */
    public function normalizarTexto(?string $texto): string
    {
        return Str::of((string) $texto)->squish()->lower()->title()->toString();
    }

    /**
     * Devuelve una cadena canónica sin acentos y en minúsculas para comparaciones semánticas.
     */
    public function canonico(?string $texto): string
    {
        return Str::of((string) $texto)->ascii()->lower()->squish()->toString();
    }

    /**
     * Evalúa la similitud fonética y de caracteres entre dos cadenas.
     */
    public function calcularSimilitud(string $cadenaA, string $cadenaB): float
    {
        $canA = $this->canonico($cadenaA);
        $canB = $this->canonico($cadenaB);

        if ($canA === '' && $canB === '') {
            return 100.0;
        }

        if ($canA === '' || $canB === '') {
            return 0.0;
        }

        if ($canA === $canB) {
            return 100.0;
        }

        similar_text($canA, $canB, $similitud);

        return round($similitud, 1);
    }
}
