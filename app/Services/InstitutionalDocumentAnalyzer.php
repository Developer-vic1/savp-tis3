<?php

namespace App\Services;

use App\Models\RoleRequest;

class InstitutionalDocumentAnalyzer
{
    public function analyze(RoleRequest $request): array
    {
        // Contrato reservado para Peter 3. Ninguna evidencia se inventa si falta el analizador.
        return ['version' => '1', 'status' => 'REQUIERE_REVISION_MANUAL', 'document_readable' => null,
            'director_name_detected' => null, 'director_role_detected' => null,
            'requested_role_detected' => null, 'authorization_language_detected' => null,
            'signature_detected' => null, 'seal_detected' => null, 'document_date' => null,
            'warnings' => ['Análisis automático no disponible. Se requiere revisión documental por otro administrador.']];
    }
}
