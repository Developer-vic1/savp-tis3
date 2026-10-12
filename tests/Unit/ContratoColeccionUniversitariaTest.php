<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Support\Conocimiento\ContratoColeccionUniversitaria;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContratoColeccionUniversitariaTest extends TestCase
{
    private function respuesta(): array
    {
        return ['schema_version' => '1.0', 'trace_id' => 'collection-test', 'institution' => 'UNIFRANZ', 'source_url' => 'https://unifranz.edu.bo/carreras/', 'status' => 'SIN_INICIAR', 'careers' => [], 'progress' => ['completed' => 0, 'total' => 0, 'current' => null], 'statistics' => ['careers' => 0, 'curricula' => 0, 'extracted_documents' => 0], 'warnings' => [], 'updated_at' => null];
    }

    public function test_rechaza_fuente_ajena_y_progreso_incoherente(): void
    {
        $datos = $this->respuesta();
        $this->assertTrue(ContratoColeccionUniversitaria::valido($datos));
        $datos['progress']['completed'] = 3;
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
        $datos = $this->respuesta();
        $datos['careers'] = [['name' => 'Medicina', 'url' => 'https://unifranz.edu.bo.evil.test/carreras/medicina/', 'status' => 'EXTRAIDA', 'campuses' => [], 'duration' => null, 'curricula' => [], 'warnings' => []]];
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
    }

    public function test_cliente_solo_consulta_servicio_interno_con_clave(): void
    {
        config()->set('services.aporte_ingenieril.url', 'https://aporte.test');
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.key', 'test-only-internal-api-key-32-characters');
        config()->set('services.aporte_ingenieril.allowed_hosts', ['aporte.test']);
        Http::preventStrayRequests();
        Http::fake(['aporte.test/*' => Http::response($this->respuesta())]);
        $this->assertTrue(app(AporteIngenierilClient::class)->unifranzCollection(true)->available);
        Http::assertSent(fn ($solicitud) => $solicitud->url() === 'https://aporte.test/api/v1/knowledge/collections/unifranz' && $solicitud->method() === 'POST' && $solicitud->data() === [] && $solicitud->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_lectura_de_imagenes_exige_paginas_y_metodos_coherentes(): void
    {
        $datos = $this->respuesta();
        $malla = ['title' => 'Malla curricular', 'url' => 'https://unifranz.edu.bo/malla.pdf', 'status' => 'EXTRAIDA', 'pages' => 2, 'digital_pages' => 1, 'recognized_pages' => 1, 'readable_pages' => 2, 'chunks' => 2, 'document_hash' => 'sha256:'.str_repeat('a', 64), 'warnings' => [], 'page_methods' => [['page' => 1, 'method' => 'DIGITAL', 'review_required' => false], ['page' => 2, 'method' => 'OCR', 'review_required' => true]]];
        $datos['careers'] = [['name' => 'Medicina', 'url' => 'https://unifranz.edu.bo/carreras/medicina/', 'status' => 'COMPLETADA', 'campuses' => [], 'duration' => null, 'curricula' => [$malla], 'warnings' => []]];
        $this->assertTrue(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'][0]['curricula'][0]['recognized_pages'] = 2;
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'][0]['curricula'][0] = $malla;
        $datos['careers'][0]['curricula'][0]['page_methods'][1]['review_required'] = false;
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
    }

    public function test_estructura_no_puede_inventar_materias_ni_duplicar_semestres(): void
    {
        $datos = $this->respuesta();
        $malla = ['title' => 'Malla curricular', 'url' => 'https://unifranz.edu.bo/malla.pdf', 'status' => 'EXTRAIDA', 'pages' => 1, 'digital_pages' => 1, 'chunks' => 1, 'document_hash' => 'sha256:'.str_repeat('a', 64), 'warnings' => []];
        $materia = ['name' => 'Derecho Civil', 'semester' => 8, 'page' => 1, 'method' => 'DIGITAL', 'status' => 'PENDIENTE_REVISION', 'evidence_text' => 'Derecho Civil', 'source_url' => $malla['url'], 'document_hash' => $malla['document_hash']];
        $malla['subjects_candidates'] = [$materia];
        $malla['curricular_structure'] = ['version' => 'estructura-curricular-v1', 'status' => 'ORGANIZADA_PENDIENTE_REVISION', 'semester_count' => 1, 'subject_count' => 1, 'semesters' => [['number' => 8, 'subjects' => [$materia]]], 'message' => 'Contenido ordenado.'];
        $datos['careers'] = [['name' => 'Derecho', 'url' => 'https://unifranz.edu.bo/carreras/derecho/', 'status' => 'COMPLETADA', 'campuses' => [], 'duration' => null, 'curricula' => [$malla], 'warnings' => []]];
        $this->assertTrue(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'][0]['curricula'][0]['curricular_structure']['semesters'][0]['subjects'][0]['name'] = 'Materia inventada';
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'][0]['curricula'][0] = $malla;
        $datos['careers'][0]['curricula'][0]['curricular_structure']['semesters'][] = ['number' => 8, 'subjects' => []];
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
    }

    public function test_sedes_deben_corresponder_a_los_indicadores_y_a_la_carrera(): void
    {
        $datos = $this->respuesta();
        $url = 'https://unifranz.edu.bo/carreras/arquitectura/';
        $oferta = array_map(fn ($sede) => ['campus' => $sede, 'status' => $sede === 'Santa Cruz' ? 'OFERTADA' : 'NO_INDICADA', 'source_url' => $url, 'evidence_text' => 'Disponible en:'], ['Cochabamba', 'El Alto', 'La Paz', 'Santa Cruz']);
        $carrera = ['name' => 'Arquitectura', 'url' => $url, 'status' => 'PARCIAL', 'campuses' => ['Santa Cruz'], 'campus_availability' => $oferta, 'campus_evidence' => ['status' => 'OFERTA_IDENTIFICADA', 'source_url' => $url, 'document_hash' => 'sha256:'.str_repeat('a', 64), 'retrieved_at' => '2026-10-09T20:00:00+00:00'], 'duration' => null, 'curricula' => [], 'warnings' => []];
        $datos['careers'] = [$carrera];
        $this->assertTrue(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'][0]['campuses'][] = 'La Paz';
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
        $datos['careers'] = [$carrera];
        $datos['careers'][0]['campus_evidence']['source_url'] = 'https://unifranz.edu.bo/carreras/medicina/';
        $this->assertFalse(ContratoColeccionUniversitaria::valido($datos));
    }

    public function test_descarga_rechaza_bytes_corruptos_nombres_inseguros_y_redirecciones(): void
    {
        config()->set('services.aporte_ingenieril.url', 'https://aporte.test');
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.key', 'test-only-internal-api-key-32-characters');
        config()->set('services.aporte_ingenieril.allowed_hosts', ['aporte.test']);
        Http::preventStrayRequests();
        $pdf = '%PDF-documento verificado';
        $hash = hash('sha256', $pdf);
        $headers = ['Content-Type' => 'application/pdf', 'X-SAVP-Document-Name' => rawurlencode('UNIFRANZ - Medicina - Malla.pdf')];
        Http::fake(['aporte.test/*' => Http::sequence()
            ->push($pdf, 200, $headers)
            ->push('%PDF-corrupto', 200, $headers)
            ->push($pdf, 200, array_replace($headers, ['X-SAVP-Document-Name' => '../secreto.pdf']))
            ->push('', 302, ['Location' => 'https://ajeno.test'])]);
        $this->assertTrue(app(AporteIngenierilClient::class)->documentoUnifranz($hash)->available);
        $this->assertFalse(app(AporteIngenierilClient::class)->documentoUnifranz($hash)->available);
        $this->assertFalse(app(AporteIngenierilClient::class)->documentoUnifranz($hash)->available);
        $this->assertFalse(app(AporteIngenierilClient::class)->documentoUnifranz($hash)->available);
    }
}
