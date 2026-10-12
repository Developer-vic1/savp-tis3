<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Support\Conocimiento\FuenteUniversitariaInteligente;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FuenteUniversitariaInteligenteTest extends TestCase
{
    private function ficha(): array
    {
        return ['title' => 'Plan de estudios de Ingeniería de Sistemas', 'declared_institution' => 'Universidad Privada Boliviana',
            'url' => 'https://www.upb.edu/documentos/sistemas.pdf', 'source_type' => 'OFFICIAL_CURRICULUM_PDF',
            'scope' => 'Malla curricular de Ingeniería de Sistemas.', 'publication_date' => '2026',
            'version' => 'Gestión 2026', 'campus' => 'La Paz', 'city' => 'La Paz',
            'justification' => 'Amplía la comparación de materias y preparación académica.',
            'limitations_text' => 'No informa costos de matrícula.'];
    }

    private function director(): User
    {
        $usuario = Mockery::mock(User::class)->makePartial();
        $usuario->forceFill(['cod_usu' => 'FUENTE_TEST', 'email' => 'director@example.test', 'email_verified_at' => now(), 'est_usu' => 'ACTIVO']);
        $usuario->shouldReceive('hasRole')->andReturnUsing(fn ($rol) => $rol === 'Director');
        $usuario->shouldReceive('can')->andReturnTrue();
        $usuario->shouldReceive('checkPermissionTo')->andReturnTrue();

        return $usuario;
    }

    public function test_registro_local_conserva_dominios_y_nombres_del_contrato_python_sin_verificacion_remota(): void
    {
        Http::preventStrayRequests();
        $soporte = new FuenteUniversitariaInteligente;
        $contrato = $soporte->contratoParaInterfaz();
        $catalogo = json_decode(file_get_contents(base_path($contrato['catalogo_fuente'])), true, 512, JSON_THROW_ON_ERROR);
        $dominios = array_column($contrato['universidades'], 'domain');
        foreach ($catalogo['institutions'] as $institucion) {
            if (($institucion['website_verification_status'] ?? '') !== 'OFFICIAL_DOMAIN_VERIFIED' || ($institucion['ingestion_status'] ?? '') === 'QUARANTINE_SITE_REVIEW_REQUIRED') {
                continue;
            }
            foreach ($institucion['official_domains'] as $dominio) {
                $this->assertContains($dominio, $dominios);
            }
        }
        $this->assertGreaterThan(7, count($contrato['universidades']));
        $this->assertFalse($contrato['verificacion_remota']);
        $analisis = $soporte->analizar($this->ficha());
        $this->assertTrue($analisis['puede_continuar']);
        $this->assertSame('VALIDA_LOCALMENTE', $analisis['estado']);
        $this->assertFalse($analisis['verificacion_remota']);
        Http::assertNothingSent();
    }

    #[DataProvider('entradasExtremas')]
    public function test_servidor_bloquea_entrada_extrema_antes_de_comprobar_documento_o_emitir_token(string $campo, mixed $valor): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldNotReceive('inspectKnowledgeSource');
        $cliente->shouldNotReceive('analyzeKnowledgeSource');
        $cliente->shouldNotReceive('submitKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $entrada = array_replace($this->ficha(), [$campo => $valor]);
        $this->actingAs($this->director())->postJson('/conocimiento/fuentes/analizar', $entrada)
            ->assertUnprocessable()->assertJsonValidationErrors($campo);
        $this->postJson('/conocimiento/fuentes', $entrada + ['analysis_token' => str_repeat('a', 64)])
            ->assertUnprocessable()->assertJsonValidationErrors($campo);
        $this->assertNull(session('knowledge.source_analysis'));
    }

    public static function entradasExtremas(): array
    {
        return [
            'extranjera' => ['url', 'https://www.stanford.edu/carrera.pdf'],
            'dominio-enganoso' => ['url', 'https://upb.edu.example.com/carrera.pdf'],
            'host-numerico' => ['url', 'https://universidad.123/carrera.pdf'],
            'escape-incompleto' => ['url', 'https://www.upb.edu/carrera%2.pdf'],
            'control-unicode-url' => ['url', "https://www.upb.edu/carrera\u{0085}.pdf"],
            'invisible-url' => ['url', "https://www.upb.edu/carrera\u{200B}.pdf"],
            'espacio-unicode-url' => ['url', "https://www.upb.edu/carrera\u{00A0}.pdf"],
            'url-larga' => ['url', 'https://www.upb.edu/'.str_repeat('a', 2000)],
            'url-arreglo' => ['url', ['https://www.upb.edu/']],
            'institucion-distinta' => ['declared_institution', 'Universidad Franz Tamayo'],
            'titulo-invisible' => ['title', "Plan\u{200B} de estudios"],
            'titulo-signos' => ['title', '....'],
            'titulo-multilinea' => ['title', "Plan de estudios\nOtro título"],
            'ciudad-controles' => ['city', "La\x01Paz"],
            'aporte-control-c1' => ['justification', "Descripción de la carrera\u{009F} con información académica."],
            'version-controles' => ['version', "2026\t1.0"],
            'version-signos' => ['version', '----'],
            'alcance-inflado-espacios' => ['scope', 'a              b'],
            'aporte-inflado-espacios' => ['justification', 'a                        b'],
            'tipo-arreglo' => ['source_type', ['OFFICIAL_CURRICULUM_PDF']],
            'tipo-inventado' => ['source_type', 'OFFICIAL_VIDEO'],
            'fecha-multilinea' => ['publication_date', "2026\n2027"],
            'fecha-calendario' => ['publication_date', '2026-02-29'],
            'limitacion-invisible' => ['limitations_text', "No informa\u{202E} costos."],
            'limite-lineas' => ['limitations_text', implode("\n", array_fill(0, 11, 'No informa costos.'))],
            'limite-linea-larga' => ['limitations_text', str_repeat('a', 501)],
        ];
    }

    public function test_dominio_boliviano_desconocido_y_pdf_sin_extension_son_advertencias_sin_acreditar_documento(): void
    {
        Http::preventStrayRequests();
        $analisis = (new FuenteUniversitariaInteligente)->analizar(array_replace($this->ficha(), [
            'url' => 'https://universidad-no-registrada.edu.bo/descargar?id=123',
            'declared_institution' => 'Universidad por verificar',
        ]));
        $this->assertTrue($analisis['puede_continuar']);
        $this->assertSame('REQUIERE_REVISION', $analisis['estado']);
        $this->assertCount(2, $analisis['advertencias']);
        $this->assertFalse($analisis['verificacion_remota']);
        Http::assertNothingSent();
    }

    public function test_alias_subdominios_fechas_reales_y_texto_multilinea_permanecen_validos(): void
    {
        $soporte = new FuenteUniversitariaInteligente;
        foreach ($soporte->contratoParaInterfaz()['universidades'] as $universidad) {
            foreach ($universidad['aliases'] as $alias) {
                $analisis = $soporte->analizar(array_replace($this->ficha(), [
                    'url' => 'https://carreras.'.$universidad['domain'].'/malla.PDF?v=2026',
                    'declared_institution' => $alias, 'publication_date' => '2024-02-29',
                    'scope' => "Plan curricular registrado.\nMaterias por semestre.",
                    'justification' => "Compara materias de la carrera.\nDescribe preparación académica.",
                    'limitations_text' => "No informa costos.\r\nNo acredita admisión.",
                ]));
                $this->assertTrue($analisis['puede_continuar'], $alias);
                $this->assertSame([], $analisis['bloqueos']);
            }
        }
    }

    public function test_catalogo_local_disponible_cuando_aporte_esta_caido_no_inventa_fuentes_ni_disponibilidad(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldReceive('knowledgeGovernance')->once()->andReturn(AporteResponse::unavailable());
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->director())->get('/conocimiento/fuentes')->assertOk()
            ->assertViewHas('soporteFuente', fn ($contrato) => count($contrato['universidades']) >= 7 && collect($contrato['universidades'])->contains('domain', 'usfx.bo') && $contrato['verificacion_remota'] === false)
            ->assertViewHas('result', fn ($resultado) => $resultado->available === false && $resultado->data === []);
    }

    public function test_datos_verificados_manipulados_no_se_reenvian_y_limites_unicode_se_cuentan_como_caracteres(): void
    {
        $entrada = array_replace($this->ficha(), ['title' => str_repeat('Á', 240),
            'can_submit' => true, 'assessment' => ['status' => 'ACEPTADA'], 'country' => 'Otro país',
            'trusted_universities' => [['domain' => 'universidad.example']]]);
        $validada = (new FuenteUniversitariaInteligente)->validar($entrada);
        $this->assertSame(str_repeat('Á', 240), $validada['title']);
        foreach (['can_submit', 'assessment', 'country', 'trusted_universities'] as $campo) {
            $this->assertArrayNotHasKey($campo, $validada);
        }
        $analisis = (new FuenteUniversitariaInteligente)->analizar(array_replace($entrada, ['title' => str_repeat('Á', 241)]));
        $this->assertFalse($analisis['puede_continuar']);
        $this->assertArrayHasKey('title', $analisis['errores']);
    }

    public function test_comprobacion_url_rechaza_dominio_extranjero_antes_de_inspeccion_remota(): void
    {
        $cliente = Mockery::mock(AporteIngenierilClient::class);
        $cliente->shouldNotReceive('inspectKnowledgeSource');
        $this->app->instance(AporteIngenierilClient::class, $cliente);
        $this->actingAs($this->director())->postJson('/conocimiento/fuentes/comprobar-url', ['url' => 'https://www.stanford.edu/carrera.pdf'])
            ->assertUnprocessable()->assertJsonValidationErrors('url');
    }

    public function test_censo_habilita_usfx_con_alias_y_excluye_sitio_ubi_en_cuarentena_sin_consulta_remota(): void
    {
        Http::preventStrayRequests();
        $soporte = new FuenteUniversitariaInteligente;
        $analisis = $soporte->analizar(array_replace($this->ficha(), ['url' => 'https://usfx.bo/malla.pdf', 'declared_institution' => 'USFX']));
        $this->assertTrue($analisis['puede_continuar']);
        $this->assertNotNull($soporte->universidad('https://usfx.bo/malla.pdf'));
        $this->assertNull($soporte->universidad('https://ubilapaz.edu.bo/'));
        $this->assertStringContainsString('cuarentena', $soporte->errorUrl('https://ubilapaz.edu.bo/'));
        $this->assertFalse($soporte->analizar(array_replace($this->ficha(), ['url' => 'https://ubilapaz.edu.bo/malla.pdf']))['puede_continuar']);
        Http::assertNothingSent();
    }

    public function test_censo_no_autoriza_registro_historico_dominios_invalidos_o_cuarentena_y_conserva_respaldo(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'savp-censo-');
        try {
            file_put_contents($ruta, json_encode(['institutions' => [
                ['name' => 'Histórica', 'website_verification_status' => 'HISTORICAL_REGISTER', 'official_domains' => ['historica.test']],
                ['name' => 'Cuarentena', 'website_verification_status' => 'OFFICIAL_DOMAIN_VERIFIED', 'ingestion_status' => 'QUARANTINE_SITE_REVIEW_REQUIRED', 'official_domains' => ['cuarentena.test']],
                ['name' => 'Inválida', 'website_verification_status' => 'OFFICIAL_DOMAIN_VERIFIED', 'official_domains' => ['127.0.0.1', 'https://universidad.test', 'universidad.123', 'institución.test']],
            ]]));
            $soporte = new FuenteUniversitariaInteligente($ruta);
            foreach (['historica.test', 'cuarentena.test', 'universidad.test'] as $dominio) {
                $this->assertNull($soporte->universidad('https://'.$dominio));
            }
            $this->assertCount(7, $soporte->contratoParaInterfaz()['universidades']);
            $this->assertNotNull($soporte->universidad('https://upb.edu/'));
            $this->assertNotNull($soporte->errorUrl('https://cuarentena.test/'));
        } finally {
            unlink($ruta);
        }
    }
}
