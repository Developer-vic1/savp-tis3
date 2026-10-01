<?php

namespace Tests\Unit;

use App\Livewire\Admin\GestionAcademica;
use App\Support\Academico\GestionAcademicaInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AcademicPeriodPresentationTest extends TestCase
{
    public function test_global_period_catalog_does_not_present_suggested_dates_as_real(): void
    {
        Schema::shouldReceive('hasTable')->with('periodo_evaluacion')->andReturnTrue();
        $query = Mockery::mock();
        DB::shouldReceive('table')->with('periodo_evaluacion')->once()->andReturn($query);
        $query->shouldReceive('orderByRaw', 'orderBy')->andReturnSelf();
        $query->shouldReceive('get')->andReturn(collect([(object) ['cod_pev' => 'PEV1', 'nom_pev' => 'Primer Trimestre', 'ord_pev' => 1, 'est_pev' => 'ACTIVO']]));
        $periods = (new GestionAcademica)->getPeriodosProperty();
        $this->assertSame('PEV1', $periods[0]['id']);
        $this->assertNull($periods[0]['fecha_inicio']);
        $this->assertNull($periods[0]['fecha_fin']);
        $this->assertNull($periods[0]['progreso']);
        $this->assertCount(3, (new GestionAcademicaInteligente)->sugerirPeriodosEvaluacion(2026));
    }

    public function test_missing_catalog_does_not_create_suggested_records(): void
    {
        Schema::shouldReceive('hasTable')->with('periodo_evaluacion')->andReturnFalse();
        DB::shouldReceive('table')->never();
        $this->assertSame([], (new GestionAcademica)->getPeriodosProperty());
    }
}
