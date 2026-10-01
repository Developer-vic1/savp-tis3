<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;

class KnowledgeService
{
    public function __construct(private readonly SpecializedAcademicClient $client) {}

    public function search(string $query, array $academicContext = []): AporteResponse
    {
        return $this->client->knowledge(['schema_version' => '1.0', 'query' => trim($query), 'top_k' => 5, 'official_only' => true]);
    }
}
