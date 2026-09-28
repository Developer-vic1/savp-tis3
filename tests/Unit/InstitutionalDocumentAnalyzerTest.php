<?php

namespace Tests\Unit;

use App\Models\RoleRequest;
use App\Services\InstitutionalDocumentAnalyzer;
use PHPUnit\Framework\TestCase;

class InstitutionalDocumentAnalyzerTest extends TestCase
{
    public function test_unavailable_analyzer_never_invents_document_evidence(): void
    {
        $result = (new InstitutionalDocumentAnalyzer())->analyze(new RoleRequest());
        $this->assertSame('REQUIERE_REVISION_MANUAL', $result['status']);
        foreach (['document_readable', 'director_name_detected', 'authorization_language_detected', 'signature_detected', 'seal_detected'] as $key) {
            $this->assertNull($result[$key]);
        }
    }
}
