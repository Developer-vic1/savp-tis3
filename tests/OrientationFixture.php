<?php

namespace Tests;

use App\Models\Oficial\Sistema\Role;
use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/** Explicit test data, exclusively in a verified isolated validation database. */
final class OrientationFixture
{
    public static function create(): User
    {
        if (DB::connection()->getDriverName() !== 'sqlite'
            || ! in_array(DB::connection()->getDatabaseName(), [':memory:', storage_path('framework/fusion-e2e.sqlite')], true)) {
            throw new \RuntimeException('Orientation fixture requires isolated SQLite.');
        }
        $user = User::factory()->create(['email' => 'orientation@example.test']);
        $role = Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
        foreach (['Acceso_Aula_Virtual', 'Aula_Virtual_Estudiante', 'Orientacion_Academica_Profesional', 'calificaciones.ver.propias', 'Perfil_Academico', 'Materiales_Aula'] as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $user->assignRole($role);
        DB::table('tipo_vinculacion_estudiante')->insert(['cod_tve' => 'TVE_TEST', 'nom_tve' => 'Prueba aislada']);
        DB::table('estudiante')->insert(['cod_est' => 'EST_TEST', 'rud_est' => 'TEST_ONLY', 'cod_per' => $user->cod_per, 'cod_tve' => 'TVE_TEST', 'est_est' => 'ACTIVO']);
        DB::table('gestion_academica')->insert(['cod_gea' => 'GEA_TEST', 'ani_gea' => 2026]);
        DB::table('curso')->insert(['cod_cur' => 'CUR_TEST', 'nom_cur' => 'Curso de prueba']);
        DB::table('paralelo')->insert(['cod_par' => 'PAR_TEST', 'nom_par' => 'A']);
        DB::table('turno')->insert(['cod_tur' => 'TUR_TEST', 'nom_tur' => 'Mañana']);
        DB::table('asignatura')->insert(['cod_asi' => 'ASI_TEST', 'nom_asi' => 'Matemática']);
        DB::table('personal_institucional')->insert(['cod_pin' => 'PIN_TEST', 'cod_per' => $user->cod_per, 'car_pin' => 'Fixture docente']);
        DB::table('docente')->insert(['cod_doc' => 'DOC_TEST', 'cod_pin' => 'PIN_TEST']);
        DB::table('plan_asignatura')->insert(['cod_pas' => 'PAS_TEST', 'cod_asi' => 'ASI_TEST', 'cod_doc' => 'DOC_TEST', 'cod_cur' => 'CUR_TEST', 'cod_par' => 'PAR_TEST', 'cod_tur' => 'TUR_TEST', 'cod_gea' => 'GEA_TEST']);
        DB::table('inscripcion_estudiante')->insert(['cod_ins' => 'INS_TEST', 'cod_est' => 'EST_TEST', 'cod_gea' => 'GEA_TEST', 'cod_cur' => 'CUR_TEST', 'cod_par' => 'PAR_TEST', 'cod_tur' => 'TUR_TEST', 'fei_ins' => '2026-02-01', 'est_ins' => 'ACTIVA', 'est_esp_tec_ins' => 'NO_APLICA']);
        DB::table('periodo_evaluacion')->insert(['cod_pev' => 'PEV_TEST', 'nom_pev' => 'Primer trimestre', 'ord_pev' => 1]);
        DB::table('calificacion')->insert(['cod_cal' => 'CAL_TEST', 'cod_est' => 'EST_TEST', 'cod_asi' => 'ASI_TEST', 'cod_pev' => 'PEV_TEST', 'cod_pas' => 'PAS_TEST', 'not_cal' => 75, 'est_cal' => 'ACTIVO']);
        return $user;
    }
}
