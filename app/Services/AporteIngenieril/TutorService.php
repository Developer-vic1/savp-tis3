<?php

namespace App\Services\AporteIngenieril;

use App\Contracts\SpecializedAcademicClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;

class TutorService
{
    public function __construct(private readonly SpecializedAcademicClient $client) {}

    public function ask(string $studentId, string $question, array $academicContext = []): AporteResponse
    {
        return $this->client->tutor([
            'schema_version' => '1.0',
            'question' => trim($question),
        ]);
    }
}
