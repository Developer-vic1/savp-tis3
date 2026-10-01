<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Turno;
use App\Services\InstitutionalQueryService;
use Tests\TestCase;

/** Compila SQL en memoria; no abre conexión ni aplica schema. */
class InstitutionalCatalogFilterTest extends TestCase
{
    public function test_course_filters_are_correlated_in_one_plan_and_keep_historical_plans(): void
    {
        $query = (new InstitutionalQueryService)->constrainCourseCatalog(Curso::query(), [
            'gestion' => 'GEA1', 'paralelo' => 'PAR1', 'turno' => 'TUR1', 'nivel' => 'Primaria', 'estado' => 'ACTIVO',
        ]);
        $sql = $query->toSql();
        $this->assertSame(1, substr_count($sql, 'exists'));
        foreach (['cod_gea', 'cod_par', 'cod_tur'] as $field) {
            $this->assertStringContainsString($field, $sql);
        }
        $this->assertSame(['Primaria', 'ACTIVO', 'GEA1', 'PAR1', 'TUR1'], $query->getBindings());
        $this->assertStringNotContainsString('est_pas', $sql);
    }

    public function test_shift_range_and_state_are_bound_as_values_without_interpolation(): void
    {
        $query = (new InstitutionalQueryService)->constrainShiftCatalog(Turno::query(), [
            'estado' => 'INACTIVO', 'desde' => '07:00', 'hasta' => '14:30',
        ]);
        $this->assertSame(['INACTIVO', '07:00', '14:30'], $query->getBindings());
        $this->assertStringContainsString('hor_ini_tur', $query->toSql());
        $this->assertStringContainsString('hor_fin_tur', $query->toSql());
        $this->assertStringNotContainsString('14:30', $query->toSql());
    }
}
