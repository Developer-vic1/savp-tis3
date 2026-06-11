<?php

namespace App\Support\Comunidad;

use App\Models\Docente;
use App\Models\PlanAsignatura;
use Illuminate\Support\Str;

class DocenteInteligente
{
    public const ESTADO_RECONOCIDA = 'RECONOCIDA';
    public const ESTADO_REVISION = 'REQUIERE_REVISION';
    public const ESTADO_BLOQUEADA = 'BLOQUEADA';
    public const ESTADO_INCOMPLETA = 'INCOMPLETA';

    public function analizar(array $datos, ?string $ignorarCodigo = null): array
    {
        $especialidad = $datos['esp_doc'] ?? '';
        $estado = $datos['est_doc'] ?? 'ACTIVO';

        $normalizada = Str::of((string) $especialidad)->squish()->lower()->title()->toString();

        $bloqueos = [];
        $advertencias = [];
        if (mb_strlen($normalizada) < 3) {
            $bloqueos[] = 'La especialidad profesional es incompleta (mínimo 3 caracteres).';
        }

        // State detection
        if (!empty($bloqueos)) {
            $estadoInteligente = self::ESTADO_BLOQUEADA;
        } elseif ($normalizada === '') {
            $estadoInteligente = self::ESTADO_INCOMPLETA;
        } else {
            $estadoInteligente = self::ESTADO_RECONOCIDA;
        }

        $completitud = empty($bloqueos) ? 100 : 30;

        // Perform workload and profile analysis if Docente model is queryable
        $perfilDocente = "Docente con perfil especializado en {$normalizada}.";
        $cargaAcademica = 0;
        $coherencia = 'Coherente';
        $recomendaciones = [];

        if ($ignorarCodigo) {
            $docente = Docente::with('planAsignaturas.asignatura')->find($ignorarCodigo);
            if ($docente) {
                $cargaAcademica = $docente->planAsignaturas->sum('hor_pas');
                if ($cargaAcademica > 40) {
                    $coherencia = 'Carga horaria excesiva';
                    $advertencias[] = "Alerta de sobrecarga: El docente tiene {$cargaAcademica} horas asignadas.";
                    $recomendaciones[] = 'Redistribuir materias para no sobrepasar el límite legal de 40 horas.';
                } elseif ($cargaAcademica === 0) {
                    $coherencia = 'Sin asignación de materias';
                    $advertencias[] = 'El docente se encuentra activo pero no cuenta con materias a su cargo.';
                    $recomendaciones[] = 'Asignar asignaturas correspondientes a su perfil técnico BTH.';
                }
            }
        }

        $visualizacion = [
            'carga_total_horas' => $cargaAcademica,
            'coherencia_carga' => $coherencia,
            'nivel_participacion' => $cargaAcademica > 20 ? 'Alto' : ($cargaAcademica > 0 ? 'Moderado' : 'Nulo'),
        ];

        return [
            'datos' => [
                'esp_doc' => $normalizada,
                'est_doc' => $estado,
            ],
            'estado_inteligente' => $estadoInteligente,
            'confianza' => empty($bloqueos) ? 100 : 40,
            'completitud' => $completitud,
            'duplicidad' => [
                'exacto' => false,
                'aproximado_critico' => false,
                'aproximado_leve' => false,
                'similitud' => 0.0,
                'registro' => null,
            ],
            'coincidencias' => [],
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'sugerencias' => ['Sistemas Informáticos', 'Gastronomía', 'Contabilidad', 'Construcción Civil', 'Electrónica', 'Textiles y Confecciones'],
            'explicacion' => $perfilDocente,
            'acciones_recomendadas' => $recomendaciones ?: ['Perfil docente validado para el plan de materias.'],
            'visualizacion' => $visualizacion,
            'puede_guardar' => empty($bloqueos),
        ];
    }

    public function analizarEspecialidad(?string $especialidad): array
    {
        $res = $this->analizar(['esp_doc' => $especialidad]);
        $res['especialidad'] = $res['datos']['esp_doc'] ?? '';
        return $res;
    }
}
