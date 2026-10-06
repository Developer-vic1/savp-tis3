<?php

namespace App\Services\AporteIngenieril;

final class InspectorConexionAporte
{
    public function __construct(private readonly AporteIngenierilClient $cliente) {}

    public function inspeccionar(): array
    {
        $salud = $this->cliente->health();
        $conocimiento = $salud->available ? $this->cliente->knowledgeGovernance() : null;
        $recuperacion = $conocimiento?->available ? $this->cliente->warmUp() : null;
        $tutor = $recuperacion?->available ? $this->cliente->tutor([
            'schema_version' => '1.0', 'question' => 'Hola',
        ]) : null;
        $conectado = $salud->available && $conocimiento?->available && $recuperacion?->available && $tutor?->available;

        return [
            'connected' => (bool) $conectado,
            'checked_at' => now()->toIso8601String(),
            'source_count' => $conocimiento?->available ? $conocimiento->data['source_count'] : null,
            'checks' => [
                ['label' => 'Laravel recibe la solicitud autorizada', 'available' => true],
                ['label' => 'FastAPI responde al servidor Laravel', 'available' => $salud->available],
                ['label' => 'La credencial interna permite consultar las fuentes', 'available' => (bool) $conocimiento?->available],
                ['label' => 'El índice documental permite recuperar conocimiento', 'available' => (bool) $recuperacion?->available],
                ['label' => 'El tutor responde usando el servicio conectado', 'available' => (bool) $tutor?->available],
            ],
            'message' => $conectado ? 'Conexión completa comprobada desde Laravel.' : 'Hay una conexión pendiente. Revise el servicio y su configuración; no se muestra una conexión ficticia.',
        ];
    }
}
