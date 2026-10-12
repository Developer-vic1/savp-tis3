<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FuentesFuturoAcademicoTest extends TestCase
{
    private function render(array $programSources, array $studySources): \DOMXPath
    {
        DB::connection()->beforeExecuting(function () {
            throw new \RuntimeException('La prueba de presentación no permite consultas a la base de datos.');
        });
        $source = file_get_contents(resource_path('views/estudiante/orientacion/futuro.blade.php'));
        $source = preg_replace('/^@(?:extends|section)\([^\n]*\)\R/m', '', $source);
        $source = str_replace('@endsection', '', $source);
        $activity = (object) ['riasec_public' => null, 'riasec_score' => null, 'analysis_completed_at' => null,
            'analysis_snapshot' => ['career_evidence_profiles' => [[
                'career_name' => 'Ingeniería de Sistemas', 'university' => 'Universidad de prueba',
                'academic_program' => ['documented_subjects' => [], 'sources' => $programSources],
                'sources' => $studySources,
            ]]],
        ];
        $html = Blade::render($source, compact('activity'));
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($document);
    }

    private function source(string $reference, string $institution, string $type, string $title): array
    {
        return ['reference' => $reference, 'institution' => $institution, 'source_type' => $type, 'title' => $title];
    }

    public function test_curriculo_escolar_y_referencias_ocupacionales_no_se_presentan_como_mallas_de_la_carrera(): void
    {
        $school = $this->source('https://minedu.example/curriculo.pdf', 'Ministerio de Educación', 'OFFICIAL_CURRICULUM_PDF', 'Currículo Base Escolar');
        $occupation = $this->source('https://onet.example/ocupacion', 'O*NET', 'EXTERNAL_OFFICIAL', 'Referencia ocupacional O*NET');
        $dom = $this->render([$school], [$school, $occupation]);

        $this->assertSame(0, $dom->query('//a[contains(., "Ver malla oficial") or contains(., "Ver fuente del programa")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://minedu.example/curriculo.pdf" and contains(., "Currículo Base Escolar")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://onet.example/ocupacion" and contains(., "Referencia ocupacional O*NET")]')->length);
        $this->assertSame(1, $dom->query('//p[contains(., "Falta una malla curricular verificable")]')->length);
        $this->assertSame(1, $dom->query('//summary[contains(., "Otras referencias del estudio")]')->length);
    }

    public function test_malla_requiere_procedencia_del_programa_y_la_pagina_de_carrera_debe_ser_de_la_universidad(): void
    {
        $curriculum = $this->source('https://universidad.example/malla.pdf', 'Universidad de prueba', 'OFFICIAL_CURRICULUM_PDF', 'Malla de Sistemas');
        $profile = $this->source('https://universidad.example/sistemas', 'Universidad de prueba', 'OFFICIAL_CAREER_HTML', 'Perfil de Sistemas');
        $wrongUniversity = $this->source('https://otra.example/sistemas', 'Otra universidad', 'OFFICIAL_CAREER_HTML', 'Otro programa');
        $unassignedCurriculum = $this->source('https://universidad.example/otra-malla.pdf', 'Universidad de prueba', 'OFFICIAL_CURRICULUM_PDF', 'Malla ajena al programa');
        $dom = $this->render([$curriculum], [$profile, $wrongUniversity, $unassignedCurriculum]);

        $this->assertSame(1, $dom->query('//a[contains(., "Ver malla oficial")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://universidad.example/malla.pdf" and contains(., "Ver malla oficial")]')->length);
        $this->assertSame(1, $dom->query('//a[contains(., "Ver fuente del programa")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://universidad.example/sistemas" and contains(., "Ver fuente del programa")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://otra.example/sistemas" and contains(., "Otro programa")]')->length);
        $this->assertSame(1, $dom->query('//a[@href="https://universidad.example/otra-malla.pdf" and contains(., "Malla ajena al programa")]')->length);
    }
}
