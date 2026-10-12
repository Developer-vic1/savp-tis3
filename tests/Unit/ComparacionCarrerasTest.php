<?php

namespace Tests\Unit;

use App\Support\Orientacion\ComparacionCarreras;
use App\Support\Orientacion\EnlaceUniversitario;
use App\Support\Orientacion\FuentesEconomicasUniversitarias;
use Tests\TestCase;

class ComparacionCarrerasTest extends TestCase
{
    private function datos(array $scores, string $code = 'IC', bool $source = true): array
    {
        $public = ['instrument_version' => '2.0-es-2025', 'responses' => array_map(fn ($id) => ['item_id' => $id, 'value' => 3], range(1, 30))];
        $score = ['schema_version' => '2.0', 'instrument_version' => '2.0-es-2025', 'trace_id' => 'test', 'scores' => $scores, 'top_codes' => ['I'], 'holland_code' => 'IRC', 'limitations' => []];
        $program = ['career_id' => 'C1', 'career_name' => 'Sistemas', 'university' => 'Universidad A', 'academic_program' => ['documented_subjects' => ['Programación I']],
            'vocational_interest_relation' => ['occupational_references' => [['occupation_id' => 'O1', 'occupation_title' => 'Ocupación', 'holland_code' => $code, 'evidence_status' => 'HYPOTHESIS', 'sources' => $source ? [['reference' => 'https://example.test/onet', 'title' => 'Fuente']] : []]]]];

        return [$public, $score, ['career_evidence_profiles' => [$program]]];
    }

    public function test_contador_descriptivo_conserva_denominador_real_y_programas_por_institucion(): void
    {
        [$public, $score, $study] = $this->datos(['R' => 15, 'I' => 19, 'A' => 3, 'S' => 4, 'E' => 2, 'C' => 12]);
        $other = $study['career_evidence_profiles'][0];
        $other['career_id'] = 'C2';
        $other['university'] = 'Universidad B';
        $study['career_evidence_profiles'][] = $other;
        $result = ComparacionCarreras::presentar($public, $score, $study);
        $reference = $result['programas']['C1']['referencias'][0];
        $this->assertSame(2, $reference['cantidad']);
        $this->assertSame(['I', 'C'], $reference['compartidas']);
        $this->assertSame(3, $reference['denominador_estudiante']);
        $this->assertSame(2, $reference['denominador_referencia']);
        $this->assertCount(2, $result['programas']);
        $this->assertTrue($reference['hipotesis']);
        $this->assertArrayNotHasKey('compatibility', $reference);
    }

    public function test_empate_en_corte_sin_datos_y_codigo_repetido_no_generan_cero_ficticio(): void
    {
        foreach ([array_fill_keys(str_split('RIASEC'), 10), array_fill_keys(str_split('RIASEC'), 0)] as $scores) {
            $result = ComparacionCarreras::presentar(...$this->datos($scores));
            $this->assertSame('TIED', $result['perfil']['estado']);
            $this->assertNull($result['programas']['C1']['referencias'][0]['cantidad']);
        }
        $invalid = ComparacionCarreras::presentar(...$this->datos(['R' => 15, 'I' => 19, 'A' => 3, 'S' => 4, 'E' => 2, 'C' => 12], 'II'));
        $this->assertNull($invalid['programas']['C1']['referencias'][0]['cantidad']);
        $missing = ComparacionCarreras::presentar(null, null, null);
        $this->assertNull($missing['perfil']['areas'][0]['puntaje']);
        $this->assertSame('UNAVAILABLE', $missing['perfil']['estado']);
    }

    public function test_empate_en_tercer_puntaje_incluye_todas_las_areas_sin_seleccion_arbitraria(): void
    {
        $result = ComparacionCarreras::presentar(...$this->datos(['R' => 16, 'I' => 16, 'A' => 5, 'S' => 19, 'E' => 8, 'C' => 17], 'IC'));
        $this->assertSame('READY', $result['perfil']['estado']);
        $this->assertTrue($result['perfil']['empatadas_corte']);
        $this->assertFalse($result['perfil']['plano']);
        $this->assertSame(['S', 'C', 'R', 'I'], $result['perfil']['top']);
        $reference = $result['programas']['C1']['referencias'][0];
        $this->assertSame(2, $reference['cantidad']);
        $this->assertSame(4, $reference['denominador_estudiante']);
        $this->assertSame(2, $reference['denominador_referencia']);
        $five = ComparacionCarreras::presentar(...$this->datos(['R' => 16, 'I' => 16, 'A' => 16, 'S' => 19, 'E' => 8, 'C' => 17], 'IC'));
        $this->assertCount(5, $five['perfil']['top']);
        $this->assertSame(5, $five['programas']['C1']['referencias'][0]['denominador_estudiante']);
    }

    public function test_sin_fuente_o_snapshot_solo_prosa_no_infiere_codigo_y_cero_compartidas_no_es_incompatibilidad(): void
    {
        $scores = ['R' => 15, 'I' => 19, 'A' => 3, 'S' => 4, 'E' => 2, 'C' => 12];
        $withoutSource = ComparacionCarreras::presentar(...$this->datos($scores, 'IC', false));
        $this->assertNull($withoutSource['programas']['C1']['referencias'][0]['cantidad']);
        $none = ComparacionCarreras::presentar(...$this->datos($scores, 'ASE'));
        $this->assertSame(0, $none['programas']['C1']['referencias'][0]['cantidad']);
        $this->assertStringContainsString('antes de descartar', $none['programas']['C1']['referencias'][0]['nota']);
        [$public, $score, $study] = $this->datos($scores);
        unset($study['career_evidence_profiles'][0]['vocational_interest_relation']['occupational_references']);
        $study['career_evidence_profiles'][0]['vocational_interest_relation']['evidence'] = [['statement' => 'perfil ocupacional documentado IC']];
        $legacy = ComparacionCarreras::presentar($public, $score, $study);
        $this->assertSame([], $legacy['programas']['C1']['referencias']);
        $this->assertSame(['Programación I'], $legacy['programas']['C1']['materias']);
    }

    public function test_agrupa_carrera_antes_de_universidades_sin_traspasar_evidencia_a_otra_oferta(): void
    {
        [$public, $score, $study] = $this->datos(['R' => 15, 'I' => 19, 'A' => 3, 'S' => 4, 'E' => 2, 'C' => 12]);
        $segunda = $study['career_evidence_profiles'][0];
        $segunda['career_id'] = 'C2';
        $segunda['university'] = 'Universidad B';
        unset($segunda['vocational_interest_relation']);
        $study['career_evidence_profiles'][] = $segunda;
        $variante = $segunda;
        $variante['career_id'] = 'C3';
        $variante['career_name'] = 'Sistemas Computacionales';
        $study['career_evidence_profiles'][] = $variante;
        $study['informational_external_careers'] = [['career_id' => 'C4', 'career_name' => 'Sistemas', 'university' => 'Universidad C',
            'sources' => [['reference' => 'https://example.test/carrera', 'title' => 'Página de carrera']]]];

        $result = ComparacionCarreras::presentar($public, $score, $study);
        $this->assertCount(2, $result['carreras']);
        $sistemas = $result['carreras'][0];
        $this->assertSame('Sistemas', $sistemas['nombre']);
        $this->assertSame(['DOCUMENTADA', 'DOCUMENTADA', 'PENDIENTE'], array_column($sistemas['ofertas'], 'estado'));
        $this->assertSame(['Universidad A'], $sistemas['referencia_principal']['universidades_origen']);
        $this->assertSame('Universidad A', $sistemas['universidad_fuente']);
        $this->assertSame([], $result['carreras'][1]['referencias']);
        $this->assertNull($result['carreras'][1]['referencia_principal']);
    }

    public function test_fuentes_economicas_se_asocian_a_la_universidad_concreta_sin_inventar_aranceles(): void
    {
        $umsa = FuentesEconomicasUniversitarias::paraPrograma('BO-UMSA-LP-ING-CIVIL');
        $ucb = FuentesEconomicasUniversitarias::paraPrograma('BO-UCB-LP-ING-CIVIL');
        $this->assertSame('https://convocatorias.umsa.bo/es/', $umsa[0]['url']);
        $this->assertSame('https://lpz.ucb.edu.bo/becas/', $ucb[0]['url']);
        $this->assertSame('https://www.emi.edu.bo/universidad/admision', FuentesEconomicasUniversitarias::paraPrograma('BO-EMI-LP-ING-SISTEMAS')[0]['url']);
        $this->assertSame([], FuentesEconomicasUniversitarias::paraPrograma('BO-DESCONOCIDA-LP-ING-CIVIL'));
        $this->assertArrayNotHasKey('monto', $umsa[0]);
    }

    public function test_enlace_obsoleto_de_emi_se_actualiza_al_mostrar_estudios_guardados(): void
    {
        $this->assertSame(
            'https://www.emi.edu.bo/universidad/licenciatura/ingenieria-de-sistemas',
            EnlaceUniversitario::vigente('https://emi.edu.bo/index.php/universidad/licenciatura/ingenieria-de-sistemas'),
        );
    }
}
