<?php

namespace App\Services\AporteIngenieril;

use App\Services\AporteIngenieril\DTO\AcademicAnalysisData;
use App\Services\AporteIngenieril\DTO\AporteResponse;

class AnalysisService
{
    public function __construct(private readonly AporteIngenierilClient $client) {}

    public function analyze(AcademicAnalysisData $data): AporteResponse
    {
        return $this->client->analysis($data->toPayload());
    }
}
