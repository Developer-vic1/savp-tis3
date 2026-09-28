<?php

namespace App\Services\AporteIngenieril;

use App\Services\AporteIngenieril\DTO\AporteResponse;

class KnowledgeService
{
    public function __construct(private readonly AporteIngenierilClient $client) {}

    public function search(string $query, array $academicContext = []): AporteResponse
    {
        return $this->client->knowledge(['consulta' => $query, 'contexto_academico' => $academicContext]);
    }
}
