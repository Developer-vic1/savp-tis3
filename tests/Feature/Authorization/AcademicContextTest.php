<?php

namespace Tests\Feature\Authorization;

use App\Models\Calificacion;
use App\Models\InscripcionEstudiante;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\GradeService;
use App\Services\RegencyAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/** Pruebas de servicios con esquema mínimo aislado; no valida las migrations de producción. */
class AcademicContextTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        if (! extension_loaded('pdo_sqlite')) { $this->markTestSkipped('Se requiere PDO SQLite ya disponible.'); }
        DB::purge('sqlite');
        $tables = [
            'regente' => ['cod_reg', 'cod_pin', 'est_reg'],
            'personal_institucional' => ['cod_pin', 'cod_per', 'est_pin'],
            'gestion_academica' => ['cod_gea', 'ani_gea', 'est_gea'],
            'curso' => ['cod_cur', 'nom_cur', 'est_cur'],
            'plan_asignatura' => ['cod_pas', 'cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'cod_asi', 'cod_doc', 'est_pas'],
            'periodo_evaluacion' => ['cod_pev', 'nom_pev', 'est_pev'],
            'inscripcion_estudiante' => ['cod_ins', 'cod_est', 'cod_gea', 'cod_cur', 'cod_par', 'cod_tur', 'est_ins'],
            'calificacion' => ['cod_cal', 'cod_est', 'cod_pas', 'cod_pev', 'cod_asi', 'est_cal', 'not_cal', 'obs_cal'],
        ];
        foreach ($tables as $name => $fields) {
            Schema::create($name, function (Blueprint $table) use ($fields, $name) {
                foreach ($fields as $index => $field) { $column = $table->string($field)->nullable(); if ($index === 0) { $column->primary(); } }
                $table->timestamps();
                if ($name === 'calificacion') { $table->unique(['cod_est', 'cod_pas', 'cod_pev']); }
            });
        }
        Schema::create('regente_asignaciones', function (Blueprint $table) {
            $table->id(); $table->string('cod_reg'); $table->string('cod_gea'); $table->string('cod_cur');
            $table->boolean('activa'); $table->timestamps(); $table->unique(['cod_reg', 'cod_gea', 'cod_cur']);
        });
        DB::table('personal_institucional')->insert(['cod_pin' => 'PIN', 'cod_per' => 'PER', 'est_pin' => 'ACTIVO']);
        DB::table('regente')->insert(['cod_reg' => 'REG', 'cod_pin' => 'PIN', 'est_reg' => 'ACTIVO']);
        DB::table('periodo_evaluacion')->insert(['cod_pev' => 'T1', 'nom_pev' => 'Primero', 'est_pev' => 'ACTIVO']);
        foreach ([2026, 2027] as $year) {
            DB::table('gestion_academica')->insert(['cod_gea' => (string) $year, 'ani_gea' => $year, 'est_gea' => 'ACTIVO']);
            DB::table('plan_asignatura')->insert(['cod_pas' => 'P'.$year, 'cod_gea' => (string) $year, 'cod_cur' => 'C1', 'cod_par' => 'A', 'cod_tur' => 'M', 'cod_asi' => 'MAT', 'cod_doc' => 'DOC', 'est_pas' => 'ACTIVO']);
            DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'I'.$year, 'cod_est' => 'EST', 'cod_gea' => (string) $year, 'cod_cur' => 'C1', 'cod_par' => 'A', 'cod_tur' => 'M', 'est_ins' => 'ACTIVA']);
        }
        foreach (['C1', 'C2', 'C3'] as $course) { DB::table('curso')->insert(['cod_cur' => $course, 'nom_cur' => $course, 'est_cur' => 'ACTIVO']); }
    }

    private function actor(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->est_usu = 'ACTIVO'; $user->cod_per = 'PER';
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($name) => $name === $role);
        $user->shouldReceive('can')->andReturn(true);
        return $user;
    }

    private function assign(string $course, bool $active = true): void
    {
        (new RegencyAccessService())->assign($this->actor('Administrador'), ['cod_reg' => 'REG', 'cod_gea' => '2026', 'cod_cur' => $course, 'activa' => $active]);
    }

    public function test_assignment_limit_and_duplicate_are_enforced(): void
    {
        $this->assign('C1'); $this->assign('C1'); $this->assign('C2');
        $this->assertSame(2, DB::table('regente_asignaciones')->count());
        $this->expectException(ValidationException::class);
        $this->assign('C3');
    }

    public function test_retirement_preserves_record_and_releases_capacity(): void
    {
        $this->assign('C1'); $this->assign('C2'); $this->assign('C1', false); $this->assign('C3');
        $this->assertSame(3, DB::table('regente_asignaciones')->count());
        $this->assertSame(2, DB::table('regente_asignaciones')->where('activa', true)->count());
    }

    public function test_regent_cannot_read_same_student_in_another_year(): void
    {
        $this->assign('C1');
        $query = (new RegencyAccessService())->constrain(InscripcionEstudiante::query(), $this->actor('Regente'), 'inscripcion_estudiante');
        $this->assertSame(['I2026'], $query->pluck('cod_ins')->all());
    }

    public function test_inactive_regent_cannot_receive_an_assignment(): void
    {
        DB::table('regente')->update(['est_reg' => 'INACTIVO']);
        $this->expectException(ValidationException::class);
        $this->assign('C1');
    }

    public function test_official_grades_preserve_both_years(): void
    {
        $grades = new GradeService(Mockery::mock(CursoVirtualService::class));
        $admin = $this->actor('Administrador');
        $grades->save($admin, 'P2026', 'EST', 'T1', 60, null);
        $grades->save($admin, 'P2027', 'EST', 'T1', 90, null);
        $this->assertEquals(['P2026' => '60.00', 'P2027' => '90.00'], Calificacion::orderBy('cod_pas')->pluck('not_cal', 'cod_pas')->all());
        $this->expectException(ValidationException::class);
        $grades->save($admin, 'P2026', 'EST', 'T1', 70, null);
    }

    public function test_closed_period_requires_reason_for_rectification(): void
    {
        $grades = new GradeService(Mockery::mock(CursoVirtualService::class));
        $admin = $this->actor('Administrador');
        $grade = $grades->save($admin, 'P2026', 'EST', 'T1', 60, null);
        DB::table('periodo_evaluacion')->update(['est_pev' => 'INACTIVO']);
        $this->expectException(ValidationException::class);
        $grades->update($admin, $grade, 80, null);
    }
}
