<?php

namespace App\Support\Comunidad;

use App\Models\Docente;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DocenteInteligente extends SoporteInteligenteBase
{
    public const LIMITE_HORAS_SEMANALES_RECOMENDADO = 36;
    public const LIMITE_HORAS_SEMANALES_MAXIMO = 48;

    /**
     * Mantiene compatibilidad directa con implementaciones existentes.
     */
    public function analizarEspecialidad(?string $especialidad): array
    {
        $normalizada = $this->normalizarTexto($especialidad);
        $bloqueos = mb_strlen($normalizada) < 3 ? ['La especialidad profesional es incompleta.'] : [];

        return [
            'especialidad' => $normalizada,
            'completitud' => $bloqueos === [] ? 100 : 30,
            'bloqueos' => $bloqueos,
            'puede_guardar' => $bloqueos === [],
        ];
    }

    /**
     * Analiza el registro de un docente y su vinculación con personal institucional.
     */
    public function analizarRegistro(array $datos, ?string $ignorarCodDoc = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Reglamento del Escalafón Nacional', 'Ley 070 MinEdu'];

        $codPin = trim((string) ($datos['cod_pin'] ?? ''));
        $especialidad = trim((string) ($datos['esp_doc'] ?? ''));
        $estado = trim((string) ($datos['est_doc'] ?? 'ACTIVO'));

        // 1. Verificación del Personal Institucional base
        if ($codPin === '') {
            $msg = 'Debe seleccionar un registro de Personal Institucional para habilitar al Docente.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'DOC_PIN_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $pin = PersonalInstitucional::with('persona')->find($codPin);
            if (! $pin) {
                $msg = "El registro de personal institucional '{$codPin}' no existe.";
                $bloqueos[] = $msg;
                $this->registrarHallazgo($hallazgos, 'DOC_PIN_INEXISTENTE', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO);
            } else {
                $persona = $pin->persona;
                $datosCalculados['docente'] = [
                    'cod_pin' => $pin->cod_pin,
                    'nombre' => $persona ? trim("{$persona->nom_per} {$persona->ape_pat_per} {$persona->ape_mat_per}") : 'Sin Persona',
                    'cargo' => $pin->car_pin,
                    'estado_pin' => $pin->est_pin,
                ];

                if (Str::upper($pin->est_pin) === 'INACTIVO') {
                    $msg = 'El registro de personal institucional está INACTIVO. Debe reactivarlo antes de asignar carga docente.';
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'DOC_PIN_INACTIVO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
                }

                // Verificar duplicidad de docente para el mismo personal
                $queryDoc = Docente::where('cod_pin', $codPin);
                if ($ignorarCodDoc) {
                    $queryDoc->where('cod_doc', '!=', $ignorarCodDoc);
                }
                $existente = $queryDoc->first();
                if ($existente) {
                    $msg = "Este funcionario ya se encuentra registrado como docente con código {$existente->cod_doc}.";
                    $bloqueos[] = $msg;
                    $this->registrarHallazgo($hallazgos, 'DOC_DUPLICADO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_CRITICO, ['cod_doc' => $existente->cod_doc]);
                }
            }
        }

        // 2. Especialidad docente
        if ($especialidad !== '') {
            $analisisEsp = $this->analizarEspecialidad($especialidad);
            if (! empty($analisisEsp['bloqueos'])) {
                $advertencias[] = 'La especialidad descrita es muy breve o poco específica.';
                $this->registrarHallazgo($hallazgos, 'DOC_ESPECIALIDAD_BREVE', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, 'Especialidad breve', self::RIESGO_BAJO);
            }
            $datosCalculados['especialidad_normalizada'] = $analisisEsp['especialidad'];
        } else {
            $sug = 'Se recomienda registrar la especialidad o título profesional para facilitar la asignación curricular BTH.';
            $sugerencias[] = $sug;
            $this->registrarHallazgo($hallazgos, 'DOC_SIN_ESPECIALIDAD', self::TIPO_RECOMENDACION, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
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

    /**
     * Analiza la carga horaria y materias asignadas a un docente en una gestión.
     */
    public function analizarCargaHoraria(string $codDoc, ?string $codGea = null): array
    {
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $fuentes = ['Carga Horaria y Techo Presupuestario MinEdu'];

        if (! Schema::hasTable('plan_asignatura')) {
            return $this->construirResultado();
        }

        $query = DB::table('plan_asignatura')
            ->where('cod_doc', $codDoc)
            ->where('est_pas', 'ACTIVO');

        if ($codGea) {
            $query->where('cod_gea', $codGea);
        }

        $planes = $query->get();
        $totalMaterias = $planes->count();
        $totalHorasSemanales = $planes->sum('hor_pas');

        $datosCalculados['total_asignaciones'] = $totalMaterias;
        $datosCalculados['total_horas_semanales'] = $totalHorasSemanales;

        if ($totalMaterias === 0) {
            $sug = 'El docente no cuenta con asignaturas asignadas en el período evaluado.';
            $sugerencias[] = $sug;
            $this->registrarHallazgo($hallazgos, 'DOC_SIN_ASIGNATURAS', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
        } elseif ($totalHorasSemanales > self::LIMITE_HORAS_SEMANALES_MAXIMO) {
            $adv = "El docente acumula {$totalHorasSemanales} horas pedagógicas semanales, superando el límite máximo sugerido de " . self::LIMITE_HORAS_SEMANALES_MAXIMO . ' horas.';
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'DOC_SOBRECARGA_HORARIA', self::TIPO_NORMATIVA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['horas' => $totalHorasSemanales]);
        }

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK,
            nivelRiesgo: count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            fuentesRegla: $fuentes
        );
    }
}
