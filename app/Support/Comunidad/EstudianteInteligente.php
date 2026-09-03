<?php

namespace App\Support\Comunidad;

use App\Models\Estudiante;
use App\Models\InscripcionEstudiante;
use App\Models\Persona;
use App\Support\Core\SoporteInteligenteBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EstudianteInteligente extends SoporteInteligenteBase
{
    public const EDAD_MINIMA_SECUNDARIA = 11;
    public const EDAD_MAXIMA_SECUNDARIA = 19;

    /**
     * Analiza el registro de un estudiante en tiempo real.
     */
    public function analizarRegistro(array $datos, ?string $ignorarCodEst = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['RM 0001/2026 MinEdu', 'Sistema de Información Educativa (SIE)'];

        $codPer = trim((string) ($datos['cod_per'] ?? ''));
        $rude = trim((string) ($datos['rud_est'] ?? ''));
        $codTve = trim((string) ($datos['cod_tve'] ?? ''));
        $codIpe = trim((string) ($datos['cod_ipe'] ?? ''));
        $codEsp = trim((string) ($datos['cod_esp'] ?? ''));
        $estado = trim((string) ($datos['est_est'] ?? 'ACTIVO'));

        // 1. Verificación de la Persona asociada
        if ($codPer === '') {
            $msg = 'Debe seleccionar una persona existente para registrar al estudiante.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'EST_PERSONA_REQUERIDA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $persona = Persona::find($codPer);
            if (! $persona) {
                $msg = "La persona seleccionada ({$codPer}) no existe en el sistema.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'EST_PERSONA_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            } else {
                $datosCalculados['persona'] = [
                    'cod_per' => $persona->cod_per,
                    'nombre_completo' => trim("{$persona->nom_per} {$persona->ape_pat_per} {$persona->ape_mat_per}"),
                    'ci' => $persona->ci_per,
                    'estado' => $persona->est_per,
                ];

                $estaActiva = $persona->est_per === true || $persona->est_per === 1 || Str::upper((string) $persona->est_per) === 'ACTIVO' || Str::upper((string) $persona->est_per) === '1';
                if (! $estaActiva) {
                    $msg = 'La persona seleccionada está INACTIVA. Debe reactivar a la persona antes de registrarla como estudiante.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'EST_PERSONA_INACTIVA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
                }

                // Evaluar si la persona ya está registrada como estudiante
                $queryEst = Estudiante::where('cod_per', $codPer);
                if ($ignorarCodEst) {
                    $queryEst->where('cod_est', '!=', $ignorarCodEst);
                }
                $estudianteExistente = $queryEst->first();
                if ($estudianteExistente) {
                    $msg = "La persona ya está registrada como estudiante con código {$estudianteExistente->cod_est} (RUDE: {$estudianteExistente->rud_est}).";
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'EST_PERSONA_DUPLICADA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['cod_est' => $estudianteExistente->cod_est]);
                }

                // Evaluar edad respecto al nivel secundario
                if ($persona->fec_nac_per) {
                    $edad = Carbon::parse($persona->fec_nac_per)->age;
                    $datosCalculados['edad'] = $edad;
                    if ($edad < self::EDAD_MINIMA_SECUNDARIA || $edad > self::EDAD_MAXIMA_SECUNDARIA) {
                        $adv = "El estudiante tiene {$edad} años, edad no habitual para el nivel Secundario Comunitaria Productiva (rango regular 11 a 19 años). Verifique antecedentes pedagógicos.";
                        $advertencias[] = $adv;
                        $this->registrarHallazgo($hallazgos, 'EST_EDAD_ATIPICA_SECUNDARIA', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['edad' => $edad]);
                    }
                }
            }
        }

        // 2. Verificación de RUDE
        if ($rude === '') {
            $msg = 'El código RUDE es obligatorio para el registro del estudiante.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'EST_RUDE_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            // Verificar formato de RUDE boliviano (generalmente numérico o alfanumérico SIE)
            if (! preg_match('/^[A-Za-z0-9\-]{8,25}$/', $rude)) {
                $adv = 'El formato del código RUDE parece inusual. Asegúrese de que coincida con el registro del SIE del Ministerio de Educación.';
                $advertencias[] = $adv;
                $this->registrarHallazgo($hallazgos, 'EST_RUDE_FORMATO_INUSUAL', self::TIPO_NORMATIVA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
            }

            // Duplicidad de RUDE
            $queryRude = Estudiante::where('rud_est', $rude);
            if ($ignorarCodEst) {
                $queryRude->where('cod_est', '!=', $ignorarCodEst);
            }
            if ($queryRude->exists()) {
                $msg = "El código RUDE '{$rude}' ya pertenece a otro estudiante en el sistema.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'EST_RUDE_DUPLICADO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            }
        }

        // 3. Verificación de Tipo de Vinculación
        if ($codTve === '') {
            $msg = 'Debe especificar el tipo de vinculación del estudiante.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'EST_TIPO_VINCULACION_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        // 4. Especialidad Técnica (Secundaria BTH)
        if ($codEsp !== '') {
            $datosCalculados['especialidad_bth'] = $codEsp;
            $sug = 'El estudiante cuenta con especialidad técnica asignada (Bachillerato Técnico Humanístico BTH).';
            $sugerencias[] = $sug;
            $this->registrarHallazgo($hallazgos, 'EST_ESPECIALIDAD_BTH_ASIGNADA', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
        }

        // 5. Historial de inscripciones si es edición
        if ($ignorarCodEst && Schema::hasTable('inscripcion_estudiante')) {
            $inscripciones = DB::table('inscripcion_estudiante')
                ->where('cod_est', $ignorarCodEst)
                ->orderByDesc('created_at')
                ->get();
            $datosCalculados['total_inscripciones_historicas'] = $inscripciones->count();
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
