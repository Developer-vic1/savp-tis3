<?php

namespace Tests\Feature\Authorization;

use App\Models\Oficial\Academico\Calificacion;
use App\Models\Oficial\Academico\Estudiante;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\InscripcionEstudiante;
use App\Models\Oficial\Academico\PlanEspecialidad;
use App\Models\Oficial\Academico\ReporteGenerado;
use App\Models\Oficial\AporteAcademicoVocacional\OrientacionResultado;
use App\Models\Oficial\AulaVirtual\ClaseEstudiante;
use App\Models\Oficial\AulaVirtual\EntregaTarea;
use App\Models\Oficial\AulaVirtual\Tarea;
use App\Models\Oficial\Sistema\Permission;
use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\RoleHasPermission;
use App\Models\Oficial\Sistema\User;
use App\Services\AulaVirtual\EntregaService;
use App\Services\GradeService;
use App\Services\InscripcionAcademicaService;
use App\Services\Reportes\DatosReporteVocacionalService;
use App\Services\Reportes\GeneradorMpdfService;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/** Contrato PostgreSQL real, siempre en una fixture explícita y con rollback. */
class AcademicContextTest extends TestCase
{
    private function migracionTokens(): Migration
    {
        return require database_path('migrations/2026_10_04_000009_compatibilizar_tokens_con_clave_de_usuario.php');
    }

    private function tipoTokens(): string
    {
        return DB::selectOne("SELECT format_type(atttypid,atttypmod) tipo FROM pg_attribute WHERE attrelid='personal_access_tokens'::regclass AND attname='tokenable_id'")->tipo;
    }

    public function test_tokens_accept_official_user_keys_and_preserve_historical_morph(): void
    {
        $migration = $this->migracionTokens();
        $migration->up();
        $user = User::factory()->create();
        $token = $user->createToken('VERIFICACION AISLADA')->accessToken;
        $this->assertSame($user->cod_usu, $token->fresh()->tokenable_id);
        $this->assertSame($user->cod_usu, $token->fresh()->tokenable->cod_usu);
        $this->assertSame('App\\Models\\User', $token->tokenable_type);
        $this->assertSame(DB::selectOne("SELECT format_type(atttypid,atttypmod) tipo FROM pg_attribute WHERE attrelid='users'::regclass AND attname='cod_usu'")->tipo, $this->tipoTokens());
        try {
            $migration->down();
            $this->fail('El rollback no puede destruir tokens textuales.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Rollback bloqueado', $e->getMessage());
        }
        $this->assertSame($user->cod_usu, $token->fresh()->tokenable_id);
    }

    public function test_token_migration_preserves_other_owners_and_can_roll_back(): void
    {
        $id = DB::table('personal_access_tokens')->insertGetId(['tokenable_type' => 'OTRO PROPIETARIO', 'tokenable_id' => 42, 'name' => 'PRUEBA AISLADA', 'token' => hash('sha256', 'AISLADO'.random_bytes(16))]);
        $migration = $this->migracionTokens();
        $migration->up();
        $this->assertSame('42', DB::table('personal_access_tokens')->where('id', $id)->value('tokenable_id'));
        $migration->down();
        $this->assertSame('bigint', $this->tipoTokens());
        $this->assertSame(42, (int) DB::table('personal_access_tokens')->where('id', $id)->value('tokenable_id'));
    }

    public function test_token_migration_stops_on_unmapped_historical_user(): void
    {
        $id = DB::table('personal_access_tokens')->insertGetId(['tokenable_type' => 'App\\Models\\User', 'tokenable_id' => 42, 'name' => 'PRUEBA AISLADA', 'token' => hash('sha256', 'AISLADO'.random_bytes(16))]);
        try {
            $this->migracionTokens()->up();
            $this->fail('No debe inferirse la identidad histórica.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('sin usuario compatible', $e->getMessage());
        }
        $this->assertSame('bigint', $this->tipoTokens());
        $this->assertSame(42, (int) DB::table('personal_access_tokens')->where('id', $id)->value('tokenable_id'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('SAVP_TEST_PG_DATABASE');
        if (! $database) {
            $this->markTestSkipped('Indica SAVP_TEST_PG_DATABASE y SAVP_TEST_PG_PORT para una fixture PostgreSQL aislada.');
        }
        $port = (int) getenv('SAVP_TEST_PG_PORT');
        if ($port < 1 || $port === 5432 || ! preg_match('/^savp_(revision|historial_integral)_[a-z0-9_]+$/D', $database)) {
            throw new \RuntimeException('Destino de pruebas no permitido; nunca utilizar SAVPTIS3-OFICIAL.');
        }
        config([
            'database.default' => 'pgsql', 'database.connections.pgsql.host' => '127.0.0.1',
            'database.connections.pgsql.port' => $port, 'database.connections.pgsql.database' => $database,
            'database.connections.pgsql.username' => getenv('SAVP_TEST_PG_USER') ?: 'revision_savp',
            'database.connections.pgsql.password' => getenv('SAVP_TEST_PG_PASSWORD') ?: '',
            'database.connections.pgsql.url' => null, 'cache.default' => 'array',
        ]);
        DB::purge('pgsql');
        $actual = DB::selectOne('SELECT current_database() nombre, inet_server_port() puerto');
        $this->assertSame($database, $actual->nombre);
        $this->assertSame($port, (int) $actual->puerto);
        DB::beginTransaction();
        Http::fake();
        Mail::fake();
        (new RolSeeder)->run();
    }

    protected function tearDown(): void
    {
        try {
            if (config('database.default') === 'pgsql') {
                while (DB::transactionLevel()) {
                    DB::rollBack();
                }
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_each_of_the_104_tables_has_exactly_one_canonical_model(): void
    {
        $tables = array_column(DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public'"), 'tablename');
        $mapped = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path('Models/Oficial'))) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            preg_match('/namespace\s+([^;]+);/', $source, $namespace);
            preg_match('/\bclass\s+(\w+)/', $source, $name);
            $class = $namespace[1].'\\'.$name[1];
            $model = new $class;
            $mapped[] = $model->getTable();
        }
        sort($tables);
        sort($mapped);
        $this->assertCount(104, $tables);
        $this->assertSame($tables, $mapped);
        $this->assertCount(104, array_unique($mapped));
    }

    public function test_models_directory_contains_only_the_104_official_models(): void
    {
        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path('Models'))));
        $files = array_filter($files, fn ($file) => $file->isFile() && $file->getExtension() === 'php');
        $this->assertCount(104, $files);
        $this->assertFalse(class_exists('App\\Models\\User'));
    }

    public function test_user_factory_and_historical_morph_type_resolve_the_only_official_user_model(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertMatchesRegularExpression('/^USU_\d{6,}$/', $user->cod_usu);
        $this->assertSame('App\\Models\\User', $user->getMorphClass());
        $this->assertSame(User::class, Relation::getMorphedModel('App\\Models\\User'));
    }

    public function test_composite_key_update_and_delete_preserve_other_rows(): void
    {
        $role = Role::create(['name' => 'REVISION AISLADA', 'guard_name' => 'web']);
        $first = Permission::create(['name' => 'REVISION_PK_1', 'guard_name' => 'web']);
        $second = Permission::create(['name' => 'REVISION_PK_2', 'guard_name' => 'web']);
        $third = Permission::create(['name' => 'REVISION_PK_3', 'guard_name' => 'web']);
        $a = RoleHasPermission::create(['permission_id' => $first->id, 'role_id' => $role->id]);
        $b = RoleHasPermission::create(['permission_id' => $second->id, 'role_id' => $role->id]);
        $a->permission_id = $third->id;
        $a->save();
        $a->refresh();
        $a->delete();
        $this->assertNotNull($b->fresh());
    }

    public function test_regular_and_technical_rectifications_preserve_context_and_academic_date(): void
    {
        $admin = User::role('Administrador')->where('est_usu', 'ACTIVO')->firstOrFail();
        Auth::setUser($admin);
        foreach (['cod_pas', 'cod_pes'] as $planColumn) {
            $grade = Calificacion::whereNotNull($planColumn)->whereHas('inscripcionEstudiante.gestionAcademica', fn ($q) => $q->where('ani_gea', 2026))->firstOrFail();
            $before = $grade->only(['cod_ins', 'cod_pas', 'cod_pes', 'cod_pev', 'fea_cal']);
            app(GradeService::class)->update($admin, $grade, (float) $grade->not_cal, 'REVISION AISLADA', 'REVISION AISLADA AUTORIZADA');
            $this->assertEquals($before, $grade->fresh()->only(array_keys($before)));
        }
    }

    public function test_new_grade_requires_academic_date(): void
    {
        $grade = Calificacion::whereNotNull('cod_pas')->firstOrFail();
        $admin = User::role('Administrador')->where('est_usu', 'ACTIVO')->firstOrFail();
        $this->expectException(ValidationException::class);
        app(GradeService::class)->save($admin, $grade->cod_pas, $grade->cod_est, $grade->cod_pev, 70, null);
    }

    public function test_second_attempt_keeps_previous_submission_unchanged(): void
    {
        if (DB::selectOne("SELECT 1 FROM pg_constraint WHERE conrelid='entrega_tarea'::regclass AND conname='uq_entrega_tarea_estudiante'")) {
            $migration = require database_path('migrations/AulaVirtual/2026_10_04_000008_conservar_intentos_de_entrega_tarea.php');
            $migration->up();
        }
        $task = Tarea::whereHas('claseVirtual.planAsignatura.gestionAcademica', fn ($q) => $q->where('ani_gea', 2026))->whereHas('entregas')->firstOrFail();
        $submission = $task->entregas()->whereHas('estudiante', fn ($q) => $q->where('est_est', 'ACTIVO'))->orderByDesc('int_ent')->firstOrFail();
        $student = $submission->estudiante;
        Auth::setUser($student->persona->usuario);
        $task->forceFill(['est_tar' => 'PUBLICADA', 'int_tar' => 5, 'ape_tar' => now()->subDay(), 'cor_tar' => now()->addDay(), 'fec_lim_tar' => now()->addDay()])->save();
        $before = $submission->getAttributes();
        $new = app(EntregaService::class)->guardarEntrega($task, $student, ['accion' => 'enviar', 'tex_ent' => 'RESPUESTA SINTETICA EXCLUSIVA DE PRUEBA']);
        $this->assertNotSame($submission->cod_ent, $new->cod_ent);
        $this->assertSame($submission->int_ent + 1, $new->int_ent);
        $this->assertSame($before, $submission->fresh()->getAttributes());
    }

    public function test_rollback_is_blocked_when_multiple_attempts_exist(): void
    {
        $migration = require database_path('migrations/AulaVirtual/2026_10_04_000008_conservar_intentos_de_entrega_tarea.php');
        if (DB::selectOne("SELECT 1 FROM pg_constraint WHERE conrelid='entrega_tarea'::regclass AND conname='uq_entrega_tarea_estudiante'")) {
            $migration->up();
        }
        $submission = EntregaTarea::whereNotNull('fec_ent')->firstOrFail();
        $data = $submission->getAttributes();
        unset($data['cod_ent']);
        $data['int_ent'] = 2;
        EntregaTarea::create($data);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rollback bloqueado');
        $migration->down();
    }

    public function test_riasec_report_counts_recorded_results_without_inferring_interests_from_grades(): void
    {
        $data = app(DatosReporteVocacionalService::class)->obtenerGeneral();
        $count = OrientacionResultado::where('estado', 'generado')->where('mod_ors', 'onet-mini-ip-2.0-es')->whereNotNull('rea_ors')->distinct('cod_est')->count('cod_est');
        $this->assertSame($count, $data['total_estudiantes']);
        $this->assertSame($count, array_sum($data['distribucion_riasec']));
    }

    private function nuevaInscripcion(array $extra = []): InscripcionEstudiante
    {
        $student = Estudiante::whereDoesntHave('inscripciones', fn ($q) => $q->whereHas('gestionAcademica', fn ($g) => $g->where('ani_gea', 2026)))->firstOrFail();
        $student->forceFill(['est_est' => 'ACTIVO'])->save();
        $group = GrupoAcademico::whereHas('gestionAcademica', fn ($q) => $q->where('ani_gea', 2026))
            ->whereHas('turno', fn ($q) => $q->where('nom_tur', 'ILIKE', 'Mañana'))
            ->whereHas('planAsignaturaRegistros.claseVirtualRegistros')->firstOrFail();

        return app(InscripcionAcademicaService::class)->guardar(array_replace([
            'cod_est' => $student->cod_est, 'cod_gea' => $group->cod_gea, 'cod_cur' => $group->cod_cur,
            'cod_par' => $group->cod_par, 'cod_tur' => $group->cod_tur,
            'fei_ins' => '2026-02-02', 'tip_ins' => 'REGULAR', 'con_ins' => 'NORMAL', 'est_ins' => 'ACTIVA',
        ], $extra));
    }

    public function test_new_enrollment_creates_context_and_existing_class_memberships(): void
    {
        $enrollment = $this->nuevaInscripcion();
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        $this->assertSame(1, $enrollment->inscripcionVigenciaRegistros()->whereNull('cod_esp_tec')->count());
        $this->assertTrue(ClaseEstudiante::where('cod_est', $enrollment->cod_est)->exists());
        $this->expectException(ValidationException::class);
        app(InscripcionAcademicaService::class)->guardar(array_replace($enrollment->only($enrollment->getFillable()), ['fei_ins' => $enrollment->fei_ins->toDateString()]));
    }

    public function test_return_to_same_group_creates_new_membership_and_keeps_first_interval(): void
    {
        $enrollment = $this->nuevaInscripcion();
        $first = $enrollment->inscripcionVigenciaRegistros()->firstOrFail();
        $other = GrupoAcademico::where('cod_gea', $enrollment->cod_gea)->where('cod_cur', $enrollment->cod_cur)
            ->where('cod_tur', $enrollment->cod_tur)->where('cod_par', '!=', $enrollment->cod_par)->whereHas('planAsignaturaRegistros.claseVirtualRegistros')->firstOrFail();
        $data = $enrollment->only($enrollment->getFillable());
        $data['fei_ins'] = $enrollment->fei_ins->toDateString();
        $service = app(InscripcionAcademicaService::class);
        $service->guardar(array_replace($data, ['cod_par' => $other->cod_par]), $enrollment, '2026-05-01');
        $service->guardar($data, $enrollment, '2026-08-01');
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        $this->assertSame(3, $enrollment->inscripcionVigenciaRegistros()->count());
        $this->assertSame('2026-04-30', $first->fresh()->ffi_ivg->toDateString());
        $memberships = ClaseEstudiante::where('cod_est', $enrollment->cod_est)
            ->whereHas('claseVirtual.planAsignatura', fn ($q) => $q->where('cod_gac', $first->cod_gac))->get()->groupBy('cod_cla');
        $this->assertTrue($memberships->isNotEmpty());
        foreach ($memberships as $history) {
            $this->assertCount(2, $history);
        }
    }

    public function test_reactivation_requires_explicit_academic_date_and_preserves_closed_history(): void
    {
        $enrollment = $this->nuevaInscripcion();
        $service = app(InscripcionAcademicaService::class);
        $data = $enrollment->only($enrollment->getFillable());
        $data['fei_ins'] = $enrollment->fei_ins->toDateString();
        $service->guardar(array_replace($data, ['est_ins' => 'RETIRADA']), $enrollment, '2026-05-01');
        $closed = $enrollment->inscripcionVigenciaRegistros()->firstOrFail();
        $this->assertSame('2026-05-01', $closed->ffi_ivg->toDateString());
        try {
            $service->guardar($data, $enrollment);
            $this->fail('La reactivación no puede deducir una fecha histórica.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('fii_ivg', $e->errors());
        }
        $service->guardar($data, $enrollment, '2026-06-01');
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        $this->assertSame(2, $enrollment->inscripcionVigenciaRegistros()->count());
        $this->assertSame('2026-05-01', $closed->fresh()->ffi_ivg->toDateString());
    }

    public function test_technical_context_complements_one_enrollment_and_keeps_specialty_history(): void
    {
        $plan = PlanEspecialidad::deGestion(
            GestionAcademica::where('ani_gea', 2026)->sole()->cod_gea
        )->where('est_pes', 'ACTIVO')->whereHas('claseVirtualRegistros')->firstOrFail();
        $group = $plan->grupoAcademico;
        $regular = GrupoAcademico::where('cod_gea', $group->cod_gea)->where('cod_cur', $group->cod_cur)
            ->whereHas('turno', fn ($q) => $q->where('nom_tur', 'ILIKE', 'Mañana'))->firstOrFail();
        $enrollment = $this->nuevaInscripcion(['cod_cur' => $regular->cod_cur, 'cod_par' => $regular->cod_par, 'cod_tur' => $regular->cod_tur]);
        $data = array_replace($enrollment->only($enrollment->getFillable()), ['fei_ins' => $enrollment->fei_ins->toDateString(), 'cod_esp_tec' => $plan->cod_esp]);
        $service = app(InscripcionAcademicaService::class);
        $service->guardar($data, $enrollment, '2026-03-01', $group->cod_gac);
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        $this->assertSame(2, $enrollment->inscripcionVigenciaRegistros()->count());
        $this->assertSame(1, InscripcionEstudiante::where('cod_est', $enrollment->cod_est)->where('cod_gea', $enrollment->cod_gea)->count());
        $this->assertSame($plan->cod_esp, $enrollment->inscripcionVigenciaRegistros()->whereNotNull('cod_esp_tec')->sole()->cod_esp_tec);
        $this->assertTrue(ClaseEstudiante::where('cod_est', $enrollment->cod_est)
            ->whereHas('claseVirtual.planEspecialidad', fn ($q) => $q->where('cod_pes', $plan->cod_pes))->exists());
    }

    public function test_filtered_grade_pdf_is_generated_against_canonical_schema(): void
    {
        Storage::fake('local');
        $grade = Calificacion::whereNotNull('cod_pas')->whereHas('inscripcionEstudiante.gestionAcademica', fn ($q) => $q->where('ani_gea', 2026))->firstOrFail();
        $this->actingAs(User::role('Administrador')->where('est_usu', 'ACTIVO')->firstOrFail());
        $response = $this->get(route('admin.reportes.calificaciones.pdf', ['gestion' => $grade->inscripcionEstudiante->cod_gea, 'estudiante' => $grade->cod_est]));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertGreaterThan(0, ReporteGenerado::count());
    }

    public function test_zip_grade_parts_keep_every_record_without_overwriting_files(): void
    {
        $grades = Calificacion::whereIn('est_cal', ['VIGENTE', 'RECTIFICADA'])->limit(1250)->get();
        $seen = [];
        $generator = \Mockery::mock(GeneradorMpdfService::class)->makePartial();
        $generator->shouldReceive('generarCalificaciones')->twice()->andReturnUsing(function ($part) use (&$seen) {
            $this->assertLessThanOrEqual(1000, $part['calificaciones']->count());
            $seen = array_merge($seen, $part['calificaciones']->pluck('cod_cal')->all());

            return 'reportes/academicos/calificaciones-'.$part['parte_reporte'].'.pdf';
        });
        $files = $generator->generarCalificacionesPorPartes(['calificaciones' => $grades]);
        $this->assertCount(2, array_unique($files));
        $this->assertSame($grades->pluck('cod_cal')->all(), $seen);
    }
}
