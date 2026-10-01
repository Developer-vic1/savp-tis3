<?php

namespace App\Contracts;

use App\Services\AporteIngenieril\DTO\AporteResponse;

interface SpecializedAcademicClient
{
    public function health(): AporteResponse;

    public function analysis(array $payload): AporteResponse;

    public function knowledge(array $payload): AporteResponse;

    public function tutor(array $payload): AporteResponse;
}
