<?php

namespace Tests\Unit;

use App\Support\Orientacion\TrayectoriaIntereses;
use PHPUnit\Framework\TestCase;

class TrayectoriaInteresesTest extends TestCase
{
    private function registro(string $materia, mixed $nota, ?string $periodo, ?int $orden = null): array
    {
        return ['subject' => $materia, 'score' => $nota, 'period' => $periodo, 'period_order' => $orden, 'scale_min' => 0, 'scale_max' => 100];
    }

    public function test_separa_gestiones_y_materias_y_promedia_cero_sin_inventar_faltantes(): void
    {
        $vista = TrayectoriaIntereses::presentar(['gestiones' => ['GEA_1' => 2025, 'GEA_2' => 2026], 'payload' => [
            'history' => [['period' => 'GEA_1', 'records' => [$this->registro('Matemática', 90, 'Primer trimestre', 1)]]],
            'academic_period' => 'GEA_2', 'academic' => ['records' => [
                $this->registro('Matemática', 80, 'Primer trimestre', 1), $this->registro('Matemática', 0, 'Segundo trimestre', 2),
                $this->registro('Lenguaje', 60, 'Primer trimestre', 1),
            ]],
        ]]);
        $this->assertSame('GEA_2', $vista['seleccionada']);
        $actual = $vista['gestiones'][0];
        $this->assertSame(2, $actual['trimestres']);
        $this->assertSame(3, $actual['registros']);
        $matematica = $actual['materias'][1];
        $this->assertSame(40.0, $matematica['promedio']);
        $this->assertSame(80.0, (float) $matematica['suma']);
        $this->assertSame([0.0], $matematica['celdas']['Segundo trimestre']);
        $this->assertSame(0, $matematica['sin_nota']);
        $this->assertSame(1, $actual['materias'][0]['sin_nota']);
        $this->assertSame(90.0, $vista['gestiones'][1]['materias'][0]['promedio']);
    }

    public function test_notas_ausentes_no_se_convierten_en_cero_y_periodos_conservan_nombre_real(): void
    {
        $vista = TrayectoriaIntereses::presentar(['payload' => ['academic_period' => 'A', 'academic' => ['records' => [
            $this->registro('Biología', null, 'Evaluación diagnóstica', 1), $this->registro('Biología', 70, 'Cierre semestral', 2),
            $this->registro('Física', null, 'Cierre semestral', 2),
        ]]]]);
        $gestion = $vista['gestiones'][0];
        $this->assertSame(['Evaluación diagnóstica', 'Cierre semestral'], array_column($gestion['periodos'], 'nombre'));
        $this->assertSame(0, $gestion['trimestres']);
        $this->assertSame([null], $gestion['materias'][0]['celdas']['Evaluación diagnóstica']);
        $this->assertSame(70.0, $gestion['materias'][0]['promedio']);
        $this->assertNull($gestion['materias'][1]['promedio']);
        $this->assertNull($gestion['anio']);
        $this->assertSame(1, $gestion['registros']);
    }

    public function test_conserva_varias_notas_del_mismo_periodo_y_no_oculta_un_registro(): void
    {
        $vista = TrayectoriaIntereses::presentar(['payload' => ['academic_period' => 'A', 'academic' => ['records' => [
            $this->registro('Historia', 60, 'Primer trimestre', 1), $this->registro('Historia', 90, 'Primer trimestre', 1),
        ]]]]);
        $materia = $vista['gestiones'][0]['materias'][0];
        $this->assertSame([60.0, 90.0], $materia['celdas']['Primer trimestre']);
        $this->assertSame(75.0, $materia['promedio']);
        $this->assertSame(2, $materia['cantidad']);
        $this->assertSame(1, $materia['periodos_con_nota']);
        $this->assertTrue($materia['varias_notas']);
    }

    public function test_no_promedia_escalas_distintas_ni_incluye_notas_fuera_de_escala(): void
    {
        $escalaDistinta = $this->registro('Química', 8, 'Segundo período', 2);
        $escalaDistinta['scale_max'] = 10;
        $vista = TrayectoriaIntereses::presentar(['payload' => ['academic_period' => 'A', 'academic' => ['records' => [
            $this->registro('Química', 80, 'Primer período', 1), $escalaDistinta,
            $this->registro('Química', 101, 'Tercer período', 3),
        ]]]]);
        $materia = $vista['gestiones'][0]['materias'][0];
        $this->assertTrue($materia['escalas_distintas']);
        $this->assertNull($materia['promedio']);
        $this->assertNull($materia['barra']);
        $this->assertSame(2, $materia['cantidad']);
        $this->assertSame([null], $materia['celdas']['Tercer período']);
    }

    public function test_contexto_vacio_y_gestion_actual_sin_notas_no_inventan_promedios(): void
    {
        $this->assertSame(['gestiones' => [], 'seleccionada' => '', 'opciones' => []], TrayectoriaIntereses::presentar([]));
        $gestion = TrayectoriaIntereses::presentar(['payload' => ['academic_period' => 'A']])['gestiones'][0];
        $this->assertSame([], $gestion['materias']);
        $this->assertSame([], $gestion['periodos']);
        $this->assertSame(0, $gestion['registros']);
    }
}
