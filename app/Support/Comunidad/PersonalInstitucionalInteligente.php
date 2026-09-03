<?php

namespace App\Support\Comunidad;

use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PersonalInstitucionalInteligente extends SoporteInteligenteBase
{
    public const CARGOS_ESTANDAR = [
        'DIRECTOR GENERAL',
        'DIRECTOR ACADEMICO',
        'SECRETARIA GENERAL',
        'SECRETARIA ACADEMICA',
        'REGENTE',
        'DOCENTE',
        'AUXILIAR DE SISTEMAS',
        'ADMINISTRATIVO',
    ];

    /**
     * Analiza el registro o actualización del personal institucional.
     */
    public function analizarRegistro(array $datos, ?string $ignorarCodPin = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Estatuto y Estructura Orgánica Institucional'];

        $codPer = trim((string) ($datos['cod_per'] ?? ''));
        $cargo = trim((string) ($datos['car_pin'] ?? ''));
        $estado = trim((string) ($datos['est_pin'] ?? 'ACTIVO'));

        // 1. Verificación de Persona
        if ($codPer === '') {
            $msg = 'Debe seleccionar una persona existente para registrar al personal institucional.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'PIN_PERSONA_REQUERIDA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $persona = Persona::find($codPer);
            if (! $persona) {
                $msg = "La persona seleccionada ({$codPer}) no existe en el sistema.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'PIN_PERSONA_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            } else {
                $datosCalculados['persona'] = [
                    'cod_per' => $persona->cod_per,
                    'nombre' => trim("{$persona->nom_per} {$persona->ape_pat_per} {$persona->ape_mat_per}"),
                    'estado' => $persona->est_per,
                ];

                $estaActiva = $persona->est_per === true || $persona->est_per === 1 || Str::upper((string) $persona->est_per) === 'ACTIVO' || Str::upper((string) $persona->est_per) === '1';
                if (! $estaActiva) {
                    $msg = 'La persona se encuentra en estado INACTIVO. No se puede registrar como personal activo.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'PIN_PERSONA_INACTIVA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
                }

                // Verificar duplicidad de registro de personal institucional
                $queryPin = PersonalInstitucional::where('cod_per', $codPer);
                if ($ignorarCodPin) {
                    $queryPin->where('cod_pin', '!=', $ignorarCodPin);
                }
                $existente = $queryPin->first();
                if ($existente) {
                    $msg = "La persona ya cuenta con una ficha de personal institucional activa ({$existente->cod_pin} - {$existente->car_pin}).";
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'PIN_PERSONA_DUPLICADA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['cod_pin' => $existente->cod_pin]);
                }
            }
        }

        // 2. Verificación de Cargo
        if ($cargo === '') {
            $msg = 'El cargo institucional es obligatorio.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'PIN_CARGO_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $cargoMayus = Str::upper(Str::squish($cargo));
            $datosCalculados['cargo_normalizado'] = $cargoMayus;

            if (mb_strlen($cargoMayus) < 3) {
                $adv = 'El nombre del cargo institucional es muy corto. Especifique la denominación completa del puesto.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'PIN_CARGO_INCOMPLETO', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
            }
        }

        $resumen = [
            'total_bloqueos' => count($bloqueos),
            'total_advertencias' => count($advertencias),
            'total_sugerencias' => count($sugerencias),
        ];

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            resumen: $resumen,
            fuentesRegla: $fuentes
        );
    }
}
