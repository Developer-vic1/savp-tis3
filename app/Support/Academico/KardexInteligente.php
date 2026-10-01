<?php

namespace App\Support\Academico;

use App\Support\CatalogoInteligenteBase;

/** Prevención local del borrador; no asigna sanciones, niveles ni estados institucionales. */
class KardexInteligente extends CatalogoInteligenteBase
{
    public function analizar(array $datos, array $eventosRelacionados = []): array
    {
        $motivo = $this->normalizarDescripcion($datos['mot_seg'] ?? '');
        $contexto = $this->normalizarDescripcion($datos['ori_seg'] ?? '');
        $accion = $this->normalizarDescripcion($datos['pro_acc_seg'] ?? '');
        $bloqueos = $motivo === '' ? ['Describe la observación antes de continuar.'] : [];
        $advertencias = [];
        $sugerencias = [];
        if ($motivo !== '' && mb_strlen($motivo) < 20) {
            $advertencias[] = 'La descripción es breve; revisa si permite comprender lo ocurrido.';
            $sugerencias[] = 'Incluye hechos observables y el contexto, sin atribuir intenciones.';
        }
        if ($contexto === '') {
            $sugerencias[] = 'Completa el contexto de la observación.';
        }
        if ($accion === '') {
            $sugerencias[] = 'Considera una acción de acompañamiento y una fecha de revisión.';
        }
        $duplicidad = $this->analizarDuplicidad($motivo, collect($eventosRelacionados));
        if ($duplicidad['registro']) {
            $advertencias[] = 'Hay una observación similar en el contexto autorizado; revisa si corresponde al mismo evento.';
            $sugerencias[] = 'Revisa la recurrencia y el seguimiento existente antes de registrar otro evento.';
        }

        return [
            'datos' => array_merge($datos, ['mot_seg' => $motivo, 'ori_seg' => $contexto, 'pro_acc_seg' => $accion]),
            'bloqueos' => $bloqueos, 'advertencias' => $advertencias, 'sugerencias' => $sugerencias,
            'duplicidad' => $duplicidad, 'completitud' => $this->completitud(['motivo' => $motivo, 'contexto' => $contexto, 'accion' => $accion], ['motivo', 'contexto', 'accion']),
            'puede_guardar' => $bloqueos === [],
        ];
    }

    protected function nombreRegistro(object $registro): string
    {
        return (string) ($registro->mot_seg ?? '');
    }
}
