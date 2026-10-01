<?php

namespace App\Services\AporteIngenieril\DTO;

final readonly class AporteResponse
{
    public function __construct(
        public bool $available,
        public array $data = [],
        public string $message = 'El análisis académico no está disponible temporalmente.',
        public ?int $status = null,
        public ?string $traceId = null,
        public ?float $latencyMs = null,
        public ?float $serverLatencyMs = null,
    ) {}

    public static function unavailable(): self
    {
        return new self(false);
    }
}
