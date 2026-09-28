<?php

namespace App\Services\AporteIngenieril;

use App\Services\AporteIngenieril\DTO\AporteResponse;

class TutorService
{
    public function __construct(private readonly AporteIngenierilClient $client) {}

    public function ask(string $studentId, string $question, array $academicContext = []): AporteResponse
    {
        return $this->client->tutor([
            'student_id' => $studentId,
            'pregunta' => $question,
            'contexto_academico' => $academicContext,
        ]);
    }
}
