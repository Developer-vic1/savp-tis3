<?php

namespace Tests\Unit;

use App\Support\Orientacion\ExperienciaIntereses;
use Tests\TestCase;

class ExperienciaInteresesTest extends TestCase
{
    private function respuestas(): array
    {
        return ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 1], range(1, 30))];
    }

    private function resultado(int $puntaje = 0): array
    {
        return ['schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'trace_id' => 'prueba-aislada',
            'scores' => array_fill_keys(str_split('RIASEC'), $puntaje), 'top_codes' => str_split('RIASEC'),
            'holland_code' => 'RIA', 'limitations' => []];
    }

    private function presentar(?array $respuestas, ?array $resultado, array $payload = [], array $gestiones = []): array
    {
        return ExperienciaIntereses::presentar(['activity' => (object) [
            'riasec_public' => $respuestas, 'riasec_score' => $resultado, 'finalizado_at' => null,
        ], 'payload' => $payload, 'gestiones' => $gestiones, 'precheck' => ['requirements' => []]]);
    }

    public function test_resultado_historico_no_convierte_puntajes_ausentes_en_ceros(): void
    {
        $perfil = $this->presentar(['instrument_id' => 'onet-mini-ip-2.0-es', 'version' => '2.0-es-2025', 'responses' => []], [
            'raw_scores' => ['R' => 13, 'I' => 6, 'A' => 6, 'S' => 10, 'E' => 6, 'C' => 13], 'holland_code' => 'RCS',
        ]);

        $this->assertFalse($perfil['vigente']);
        $this->assertTrue($perfil['anterior']);
        $this->assertNull($perfil['codigo']);
        $this->assertSame([], $perfil['destacados']);
        foreach ($perfil['areas'] as $area) {
            $this->assertNull($area['puntaje']);
            $this->assertFalse($area['destacada']);
        }
    }

    public function test_perfil_vigente_con_cero_es_diferente_de_ausencia_de_puntaje(): void
    {
        $perfil = $this->presentar($this->respuestas(), $this->resultado());

        $this->assertTrue($perfil['vigente']);
        $this->assertFalse($perfil['anterior']);
        $this->assertSame('RIA', $perfil['codigo']);
        foreach ($perfil['areas'] as $area) {
            $this->assertSame(0, $area['puntaje']);
        }
    }

    public function test_empate_de_seis_areas_se_presenta_como_perfil_plano(): void
    {
        $perfil = $this->presentar($this->respuestas(), $this->resultado(10));

        $this->assertTrue($perfil['empate']);
        $this->assertTrue($perfil['plano']);
        $this->assertSame(str_split('RIASEC'), $perfil['destacados']);
        $this->assertCount(6, array_filter($perfil['areas'], fn ($area) => $area['destacada']));
    }

    public function test_versiones_distintas_no_habilitan_un_perfil_vigente(): void
    {
        $resultado = $this->resultado();
        $resultado['instrument_version'] = 'version-anterior';

        $this->assertFalse(ExperienciaIntereses::perfilVigente($this->respuestas(), $resultado));
        $this->assertNull($this->presentar($this->respuestas(), $resultado)['areas']['R']['puntaje']);
    }

    public function test_puntajes_y_respuestas_fuera_de_rango_invalidan_el_perfil(): void
    {
        $resultado = $this->resultado();
        $resultado['scores']['R'] = 21;
        $this->assertFalse(ExperienciaIntereses::perfilVigente($this->respuestas(), $resultado));
        $resultado['scores']['R'] = -1;
        $this->assertFalse(ExperienciaIntereses::perfilVigente($this->respuestas(), $resultado));
        $respuestas = $this->respuestas();
        $respuestas['responses'][0]['value'] = 0;
        $this->assertFalse(ExperienciaIntereses::perfilVigente($respuestas, $this->resultado()));
    }

    public function test_sin_cuestionario_no_hay_resultado_vigente(): void
    {
        $perfil = $this->presentar(null, null);

        $this->assertFalse($perfil['vigente']);
        $this->assertFalse($perfil['anterior']);
        $this->assertFalse($perfil['empate']);
        $this->assertFalse($perfil['plano']);
    }

    public function test_trayectoria_cuenta_registros_y_materias_sin_inferir_promocion_o_aptitud(): void
    {
        $perfil = $this->presentar(null, null, [
            'history' => [['period' => 'GEA_2025', 'records' => [
                ['subject' => 'Matemática', 'score' => 90], ['subject' => 'Matemática', 'score' => 70], ['subject' => 'Lenguaje', 'score' => 20],
            ]]],
            'academic_period' => 'GEA_2026', 'academic' => ['records' => [['subject' => 'Biología', 'score' => 55]]],
        ], ['GEA_2025' => 2025, 'GEA_2026' => 2026]);

        $this->assertSame([
            ['codigo' => 'GEA_2025', 'anio' => 2025, 'actual' => false, 'registros' => 3, 'materias' => ['Matemática', 'Lenguaje']],
            ['codigo' => 'GEA_2026', 'anio' => 2026, 'actual' => true, 'registros' => 1, 'materias' => ['Biología']],
        ], $perfil['periodos']);
    }

    public function test_periodo_sin_gestion_resuelta_no_inventa_un_anio(): void
    {
        $perfil = $this->presentar(null, null, ['academic_period' => 'CODIGO_INTERNO']);

        $this->assertSame([['codigo' => 'CODIGO_INTERNO', 'anio' => null, 'actual' => true, 'registros' => 0, 'materias' => []]], $perfil['periodos']);
    }
}
