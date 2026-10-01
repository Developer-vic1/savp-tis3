<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\OrientationReadiness;
use PHPUnit\Framework\TestCase;

class OrientationReadinessTest extends TestCase
{
    public function test_optional_missing_values_do_not_block_non_bth_student(): void
    {
        $result = (new OrientationReadiness)->evaluate(true, ['academic' => ['records' => [['score' => 0]]], 'riasec_public' => ['instrument_version' => 'version']], 'NO_APLICA');
        $this->assertTrue($result['ready']);
        $this->assertSame('NO_APLICA', $result['requirements'][3]['type']);
    }

    public function test_missing_is_not_a_zero_and_applicable_bth_requires_evidence(): void
    {
        $readiness = new OrientationReadiness;
        $this->assertFalse($readiness->evaluate(true, [])['ready']);
        $this->assertFalse($readiness->evaluate(true, ['academic' => ['records' => [1]], 'riasec_public' => [1]], 'PENDIENTE')['ready']);
        $this->assertFalse($readiness->evaluate(false, ['academic' => ['records' => [1]], 'riasec_public' => [1]], 'NO_APLICA')['ready']);
    }
}
