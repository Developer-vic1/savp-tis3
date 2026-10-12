<?php

namespace Tests\Unit;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Support\Conocimiento\DirectorioNacional;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class DirectorioNacionalTest extends TestCase
{
    public static function directorio(): array
    {
        return [
            'schema_version' => '1.0', 'trace_id' => 'directorio-test', 'generated_at' => '2026-10-08',
            'coverage_note' => 'Registro histórico; no acredita vigencia actual.',
            'evidence_sources' => [], 'coverage_discrepancies' => [], 'collection_updated_at' => null,
            'statistics' => ['institutions' => 2, 'domains_verified' => 1, 'documented_programs' => 1, 'extracted_documents' => 0, 'pending_urls' => 1],
            'institutions' => [
                ['institution_id' => 'uno', 'name' => 'Universidad Pública de prueba', 'regime' => 'PUBLICA_AUTONOMA', 'departments' => ['La Paz'], 'collection' => ['valid' => 0, 'pending' => 1],
                    'official_url' => 'https://example.test', 'programs' => [
                        ['name' => 'Ingeniería de Sistemas', 'degree' => 'Licenciatura', 'duration_semesters' => 8, 'duration_text' => null, 'curriculum_status' => 'MATERIAS_DOCUMENTADAS', 'initial_subjects' => ['Álgebra'], 'sources' => [['url' => 'javascript:alert(1)', 'title' => 'Enlace inseguro']]],
                    ]],
                ['institution_id' => 'dos', 'name' => 'Institución histórica de prueba', 'regime' => 'PRIVADA', 'departments' => ['Santa Cruz'], 'collection' => [], 'programs' => []],
            ],
        ];
    }

    public function test_filtra_departamento_regimen_busqueda_con_acentos_y_duracion_solo_documentada(): void
    {
        $resultado = DirectorioNacional::presentar(self::directorio(), ['departamento' => 'La Paz', 'regimen' => 'PUBLICA_AUTONOMA', 'buscar' => 'ingenieria', 'duracion' => '8 semestres']);
        $this->assertCount(1, $resultado['instituciones']);
        $this->assertSame('uno', $resultado['instituciones'][0]['institution_id']);
        $this->assertSame(['8 semestres'], $resultado['duraciones']);
        $this->assertCount(0, DirectorioNacional::presentar(self::directorio(), ['duracion' => '10 semestres'])['instituciones']);
        $this->assertCount(0, DirectorioNacional::presentar(self::directorio(), ['departamento' => 'Otro'])['instituciones']);
    }

    public function test_cliente_lee_endpoint_interno_sin_datos_personales_y_rechaza_contrato_incompleto(): void
    {
        config()->set('services.aporte_ingenieril.url', 'https://aporte.test');
        config()->set('services.aporte_ingenieril.enabled', true);
        config()->set('services.aporte_ingenieril.key', 'test-only-internal-api-key-32-characters');
        config()->set('services.aporte_ingenieril.allowed_hosts', ['aporte.test']);
        Http::preventStrayRequests();
        Http::fakeSequence()->push(self::directorio())->push(['schema_version' => '1.0', 'trace_id' => 'directorio-test']);
        $this->assertTrue(app(AporteIngenierilClient::class)->nationalDirectory()->available);
        $this->assertFalse(app(AporteIngenierilClient::class)->nationalDirectory()->available);
        Http::assertSent(fn ($request) => $request->url() === 'https://aporte.test/api/v1/knowledge/national-directory' && $request->method() === 'GET' && $request->data() === [] && $request->hasHeader('X-SAVP-AI-Key'));
    }

    public function test_costos_pendientes_no_se_convierten_en_montos_y_fecha_se_presenta_en_bolivia(): void
    {
        $directorio = self::directorio();
        $directorio['collection_updated_at'] = '2026-10-08T18:30:00+00:00';
        $resultado = DirectorioNacional::presentar($directorio, ['economia' => 'verificados']);
        $this->assertSame([], $resultado['instituciones']);
        $this->assertSame('08/10/2026 14:30 (Bolivia)', $resultado['fechaRecoleccion']);
        $this->assertCount(2, DirectorioNacional::presentar($directorio, ['economia' => 'pendientes'])['instituciones']);
        $orden = DirectorioNacional::presentar($directorio, ['orden' => 'departamento']);
        $this->assertSame('uno', $orden['instituciones'][0]['institution_id']);
        $this->assertStringContainsString('todavía no iniciada', $orden['instituciones'][0]['recoleccion_label']);
        $directorio['institutions'][0]['ingestion_status'] = 'QUARANTINE_SITE_REVIEW_REQUIRED';
        $cuarentena = DirectorioNacional::presentar($directorio, []);
        $institucion = collect($cuarentena['instituciones'])->firstWhere('institution_id', 'uno');
        $this->assertStringContainsString('cuarentena', $institucion['dominio_label']);
        $this->assertStringContainsString('bloqueada', $institucion['recoleccion_label']);
    }

    public function test_datos_reactivos_conservan_los_filtros_del_servidor_sin_publicar_fuentes_adicionales(): void
    {
        $todos = DirectorioNacional::presentar(self::directorio(), [])['instituciones'];
        $datos = DirectorioNacional::datosReactivos($todos);
        $this->assertCount(2, $datos);
        $this->assertSame('8 semestres', collect($datos)->firstWhere('indice', 1)['programas'][0]['duracion']);
        $this->assertFalse(collect($datos)->firstWhere('indice', 1)['programas'][0]['costosVerificados']);
        $this->assertArrayNotHasKey('sources', $datos[1]['programas'][0]);
        $this->assertTrue(DirectorioNacional::programaCoincide(self::directorio()['institutions'][0]['programs'][0], ['economia' => 'pendientes', 'duracion' => '8 semestres']));
        $this->assertFalse(DirectorioNacional::programaCoincide(self::directorio()['institutions'][0]['programs'][0], ['economia' => 'verificados']));
    }

    public function test_oferta_institucional_se_busca_sin_confundirla_con_mallas_documentadas(): void
    {
        $directorio = self::directorio();
        $directorio['institutions'][1]['published_offer'] = [
            'source_url' => 'https://universidad.example/carreras/',
            'careers' => [['name' => 'Medicina', 'url' => 'https://universidad.example/carreras/medicina/']],
        ];
        $resultado = DirectorioNacional::presentar($directorio, ['buscar' => 'medicina']);
        $this->assertCount(1, $resultado['instituciones']);
        $this->assertSame('dos', $resultado['instituciones'][0]['institution_id']);
        $this->assertSame([], $resultado['instituciones'][0]['programas_visibles']);
        $this->assertSame(['Medicina'], DirectorioNacional::datosReactivos($resultado['instituciones'])[0]['ofertaPublicada']);
    }

    public function test_vista_distingue_cobertura_costos_pendientes_y_no_publica_enlaces_inseguros(): void
    {
        DB::listen(fn () => throw new \RuntimeException('La vista del directorio no consulta la base de datos.'));
        $source = file_get_contents(resource_path('views/conocimiento/directorio-nacional.blade.php'));
        $source = preg_replace('/^@(?:extends|section)\([^\n]*\)\R/m', '', $source);
        $source = str_replace('@endsection', '', $source);
        $directorio = self::directorio();
        $html = Blade::render($source, [
            'errors' => new ViewErrorBag,
            'estudiante' => true, 'resultado' => (object) ['available' => true], 'directorio' => $directorio,
            'consultaUrl' => '/estudiante/directorio-nacional', 'volverUrl' => '/estudiante/futuro',
        ] + DirectorioNacional::presentar($directorio, []));
        $this->assertStringContainsString('Registro histórico; no acredita vigencia actual.', $html);
        $this->assertStringContainsString('Pendientes de verificación por sede y gestión', $html);
        $this->assertStringContainsString('8 semestres', $html);
        $this->assertStringContainsString('no significa que la institución carezca de carreras', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('Enlace inseguro', $html);
        $this->assertStringNotContainsString('<select', $html);
        $this->assertSame(5, substr_count($html, 'role="combobox"'));
    }
}
