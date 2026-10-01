<?php

namespace App\Services;

use App\Services\Kardex\KardexService;

class DomainReadinessService
{
    public const DOMAINS = [
        'kardex' => ['Kardex formativo', 'Contrato institucional, catálogos de tipo/categoría/nivel/estado/medida y persistencia aprobada.', 'El docente registra observaciones justificadas; no se convierten tareas pendientes en sanciones.', ['Registro', 'Tipo y categoría', 'Nivel y estado', 'Medida y seguimiento', 'Rectificación con motivo', 'Evidencia privada']],
        'seguimientos' => ['Seguimiento formativo', 'Modelo institucional de seguimiento y relación con Kardex aprobados.', 'Las notas y la asistencia son evidencia académica; no sustituyen un seguimiento persistido.', ['Contexto académico', 'Acción acordada', 'Responsable', 'Fecha de revisión', 'Estado', 'Historial']],
        'prevencion' => ['Prevención educativa', 'Reglas institucionales y catálogo aprobado de medidas preventivas.', 'No se calculan scores de riesgo ni se generan alertas punitivas a partir de actividad aislada.', ['Evidencia', 'Revisión humana', 'Acción formativa', 'Seguimiento']],
        'alertas' => ['Alertas de seguimiento', 'Reglas, umbrales, destinatarios y persistencia institucional aprobados.', 'Los avisos deben indicar su evidencia y permitir revisión humana.', ['Motivo', 'Origen', 'Alcance', 'Revisión']],
        'plan' => ['Mi plan personal', 'Persistencia y reglas de objetivos, acciones y estados aprobadas.', 'Las actividades de tus materias siguen disponibles en Mi preparación.', ['Objetivo', 'Acción', 'Fecha', 'Estado', 'Avance']],
        'lms-configuracion' => ['Configuración del LMS', 'Reglas institucionales y catálogo de configuración aprobados.', 'No se modifican opciones de evaluación ni se inventa su persistencia.', ['Reglas de publicación', 'Evaluación', 'Cierre', 'Auditoría']],
        'configuracion' => ['Configuración institucional', 'Catálogo de parámetros institucionales y permisos de modificación aprobados.', 'Los parámetros no se almacenan en un modelo inventado ni se cambian mediante esta pantalla.', ['Parámetro', 'Valor', 'Permiso', 'Historial']],
    ];

    public function describe(string $domain): array
    {
        abort_unless(isset(self::DOMAINS[$domain]), 404);
        [$title,$dependency,$impact,$fields] = self::DOMAINS[$domain];
        $repositoryAvailable = $domain === 'kardex' ? app(KardexService::class)->available() : false;

        return compact('title', 'dependency', 'impact', 'fields', 'repositoryAvailable') + ['domain' => $domain, 'status' => 'BLOCKED_EXTERNALLY'];
    }
}
