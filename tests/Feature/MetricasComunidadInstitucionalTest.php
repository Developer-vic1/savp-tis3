<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use App\Support\MetricasComunidadInstitucional;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class MetricasComunidadInstitucionalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach ([
            'roles' => ['id','name','guard_name'],
            'model_has_roles' => ['role_id','model_type','cod_usu'],
            'estudiante' => ['cod_est','est_est','cod_esp'],
            'inscripcion_estudiante' => ['cod_ins','cod_est','cod_gea','est_ins','cod_esp_tec'],
            'inscripcion_vigencia' => ['cod_ivg','cod_ins','cod_esp_tec','est_ivg','fii_ivg','ffi_ivg'],
            'especialidad_tecnica' => ['cod_esp','nom_esp','est_esp'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $estructura) use ($campos) {
                foreach ($campos as $campo) $estructura->string($campo)->nullable();
            });
        }
    }

    private function actor(bool $permiso = true): User
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->shouldReceive('can')->andReturn($permiso);
        return $actor;
    }

    private function tramo(string $codigo, array $inscripcion = [], array $vigencia = [], array $estudiante = []): void
    {
        DB::table('estudiante')->insert(array_replace(['cod_est'=>$codigo,'est_est'=>'ACTIVO'], $estudiante));
        DB::table('inscripcion_estudiante')->insert(array_replace(['cod_ins'=>$codigo,'cod_est'=>$codigo,'cod_gea'=>'G','est_ins'=>'ACTIVA'], $inscripcion));
        DB::table('inscripcion_vigencia')->insert(array_replace(['cod_ivg'=>$codigo,'cod_ins'=>$codigo,'cod_esp_tec'=>'S','est_ivg'=>'ACTIVO','fii_ivg'=>'2026-02-01','ffi_ivg'=>null], $vigencia));
    }

    public function test_roles_consulta_morfotipo_conservado_y_excluye_roles_complementarios_y_otro_guard(): void
    {
        DB::table('roles')->insert([
            ['id'=>1,'name'=>'Administrador','guard_name'=>'web'],
            ['id'=>2,'name'=>'Estudiante','guard_name'=>'web'],
            ['id'=>3,'name'=>'Complementario','guard_name'=>'web'],
            ['id'=>4,'name'=>'Docente','guard_name'=>'api'],
        ]);
        foreach ([[1,'A'],[2,'E1'],[2,'E2'],[3,'A'],[4,'D']] as [$rol,$cuenta]) {
            DB::table('model_has_roles')->insert(['role_id'=>$rol,'cod_usu'=>$cuenta,'model_type'=>(new User)->getMorphClass()]);
        }
        DB::table('model_has_roles')->insert(['role_id'=>2,'cod_usu'=>'CLASE_NUEVA','model_type'=>User::class]);
        $this->assertSame('App\\Models\\User', (new User)->getMorphClass());
        $this->assertSame(['Administrador'=>1,'Estudiante'=>2], (new MetricasComunidadInstitucional)->roles($this->actor()));
    }

    public function test_especialidades_usa_tramos_en_fecha_y_gestion_con_estudiantes_distintos(): void
    {
        DB::table('especialidad_tecnica')->insert([
            ['cod_esp'=>'S','nom_esp'=>'Sistemas','est_esp'=>'ACTIVO'],
            ['cod_esp'=>'I','nom_esp'=>'Inactiva','est_esp'=>'INACTIVO'],
        ]);
        $this->tramo('VIGENTE');
        $this->tramo('LIMITE', vigencia: ['fii_ivg'=>'2026-10-07','ffi_ivg'=>'2026-10-07']);
        DB::table('inscripcion_vigencia')->insert(['cod_ivg'=>'DUPLICADO','cod_ins'=>'VIGENTE','cod_esp_tec'=>'S','est_ivg'=>'ACTIVO','fii_ivg'=>'2026-02-01']);
        DB::table('inscripcion_estudiante')->insert(['cod_ins'=>'SEGUNDA_INSCRIPCION','cod_est'=>'VIGENTE','cod_gea'=>'G','est_ins'=>'ACTIVA']);
        DB::table('inscripcion_vigencia')->insert(['cod_ivg'=>'SEGUNDO_TRAMO','cod_ins'=>'SEGUNDA_INSCRIPCION','cod_esp_tec'=>'S','est_ivg'=>'ACTIVO','fii_ivg'=>'2026-02-01']);
        $this->tramo('OTRA_GESTION', inscripcion: ['cod_gea'=>'ANTERIOR']);
        $this->tramo('VENCIDO', vigencia: ['ffi_ivg'=>'2026-10-06']);
        $this->tramo('FUTURO', vigencia: ['fii_ivg'=>'2026-10-08']);
        $this->tramo('RETIRADO', inscripcion: ['est_ins'=>'RETIRADA']);
        $this->tramo('INACTIVO', estudiante: ['est_est'=>'INACTIVO']);
        $this->tramo('ESPECIALIDAD_INACTIVA', vigencia: ['cod_esp_tec'=>'I']);
        $this->tramo('CERRADO', vigencia: ['est_ivg'=>'CERRADO']);
        $this->tramo('ANULADO', vigencia: ['est_ivg'=>'ANULADO']);
        $this->tramo('REGULAR', vigencia: ['cod_esp_tec'=>null]);

        $this->assertSame(0, DB::table('estudiante')->whereNotNull('cod_esp')->count());
        $this->assertSame(0, DB::table('inscripcion_estudiante')->whereNotNull('cod_esp_tec')->count());
        $this->assertSame(['Sistemas'=>2], (new MetricasComunidadInstitucional)->especialidades($this->actor(), 'G', '2026-10-07'));
    }

    public function test_sin_permiso_o_sin_gestion_no_consulta_datos(): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $servicio = new MetricasComunidadInstitucional;
        $this->assertSame([], $servicio->roles($this->actor(false)));
        $this->assertSame([], $servicio->especialidades($this->actor(false), 'G', '2026-10-07'));
        $this->assertSame([], $servicio->especialidades($this->actor(), null, '2026-10-07'));
        $this->assertSame([], $servicio->especialidades($this->actor(), '', '2026-10-07'));
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }
}
