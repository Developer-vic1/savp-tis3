<?php

namespace Database\Seeders\AulaVirtual;

use App\Models\Asignatura;
use App\Models\AulaVirtual\AsistenciaClase;
use App\Models\AulaVirtual\AsistenciaEstudiante;
use App\Models\AulaVirtual\ClaseEstudiante;
use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\EntregaTarea;
use App\Models\AulaVirtual\EstadoAsistencia;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\AulaVirtual\OrientacionActividad;
use App\Models\AulaVirtual\PublicacionClase;
use App\Models\AulaVirtual\Tarea;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\InscripcionEstudiante;
use App\Models\Paralelo;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\TipoVinculacionEstudiante;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\AulaVirtualDatosSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AulaVirtualFlujoEstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AulaVirtualDatosSeeder::class);

        $correo = '3a2victorasturizaga@gmail.com';

        $personaEstudiante = Persona::updateOrCreate(
            ['cod_per' => 'PER_AV001'],
            [
                'nom_per' => 'Víctor Grover',
                'ape_pat_per' => 'Asturizaga',
                'ape_mat_per' => 'Plata',
                'ci_per' => '30260620',
                'exp_per' => 'LP',
                'fec_nac_per' => '2008-05-12',
                'gen_per' => 'M',
                'tel_per' => '75836807',
                'ema_per' => $correo,
                'dir_per' => 'Villa Victoria, La Paz',
                'est_per' => true,
            ]
        );

        $tipoVinculacion = TipoVinculacionEstudiante::firstOrCreate(
            ['nom_tve' => 'Regular'],
            ['cod_tve' => 'TVE_AV001', 'des_tve' => 'Estudiante regular de la unidad educativa.', 'est_tve' => 'ACTIVO']
        );

        $especialidad = EspecialidadTecnica::firstOrCreate(
            ['nom_esp' => 'Sistemas Informáticos'],
            ['cod_esp' => 'ESP_AV001', 'des_esp' => 'Formación técnica en sistemas informáticos.', 'est_esp' => 'ACTIVO']
        );

        $estudiante = Estudiante::updateOrCreate(
            ['cod_est' => 'EST_AV001'],
            [
                'cod_per' => $personaEstudiante->cod_per,
                'rud_est' => 'RUDE30260620',
                'cod_tve' => $tipoVinculacion->cod_tve,
                'cod_ipe' => null,
                'cod_esp' => $especialidad->cod_esp,
                'est_est' => 'ACTIVO',
            ]
        );

        $usuario = User::updateOrCreate(
            ['email' => $correo],
            [
                'cod_usu' => User::where('email', $correo)->value('cod_usu') ?: 'USU_AV001',
                'cod_per' => $personaEstudiante->cod_per,
                'password' => Hash::make(env('AULA_VIRTUAL_SEED_PASSWORD', Str::random(40))),
                'email_verified_at' => now(),
                'auth_provider' => 'local',
                'est_usu' => 'ACTIVO',
            ]
        );

        $rolEstudiante = Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
        $usuario->assignRole($rolEstudiante);

        $permisos = [
            'Acceso_Aula_Virtual',
            'Aula_Virtual_Estudiante',
            'Mis_Asignaturas',
            'Actividades_Aula',
            'Tareas_Aula',
            'Entregas_Aula',
            'Calificaciones_Aula',
            'Asistencia_Aula',
            'Orientacion_Academica_Profesional',
        ];

        $permisosDocente = [
            'Acceso_Aula_Virtual',
            'Aula_Virtual_Docente',
            'Mis_Cursos',
            'Materiales_Aula',
            'Tareas_Aula',
            'Entregas_Aula',
            'Asistencia_Aula',
            'Calificaciones_Aula',
            'Reportes_Aula',
            'Orientacion_Academica_Profesional',
        ];

        foreach (array_unique([...$permisos, ...$permisosDocente]) as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $usuario->givePermissionTo($permisos);

        $gestion = GestionAcademica::firstOrCreate(
            ['ani_gea' => 2026],
            ['cod_gea' => 'GEA_AV2026', 'fii_gea' => '2026-02-01', 'ffi_gea' => '2026-11-30', 'est_gea' => 'ACTIVO']
        );

        $curso = Curso::firstOrCreate(
            ['nom_cur' => '5to de Secundaria'],
            ['cod_cur' => 'CUR_AV005', 'niv_cur' => 'Secundaria', 'est_cur' => 'ACTIVO']
        );

        $paralelo = Paralelo::firstOrCreate(
            ['nom_par' => 'A'],
            ['cod_par' => 'PAR_AV001', 'est_par' => 'ACTIVO']
        );

        $turno = Turno::firstOrCreate(
            ['nom_tur' => 'Mañana'],
            ['cod_tur' => 'TUR_AV001', 'hor_ini_tur' => '08:00', 'hor_fin_tur' => '12:30', 'est_tur' => 'ACTIVO']
        );

        InscripcionEstudiante::updateOrCreate(
            ['cod_est' => $estudiante->cod_est, 'cod_gea' => $gestion->cod_gea],
            [
                'cod_cur' => $curso->cod_cur,
                'cod_par' => $paralelo->cod_par,
                'cod_tur' => $turno->cod_tur,
                'fei_ins' => '2026-02-05',
                'tip_ins' => 'REGULAR',
                'con_ins' => 'NORMAL',
                'est_ins' => 'ACTIVA',
                'doc_com_ins' => true,
                'cod_esp_tec' => $especialidad->cod_esp,
                'est_esp_tec_ins' => 'ASIGNADA',
            ]
        );

        $personaDocente = Persona::updateOrCreate(
            ['cod_per' => 'PER_AV002'],
            [
                'nom_per' => 'Marcelo',
                'ape_pat_per' => 'Quispe',
                'ape_mat_per' => 'Mamani',
                'ci_per' => '70992026',
                'exp_per' => 'LP',
                'fec_nac_per' => '1984-04-18',
                'gen_per' => 'M',
                'tel_per' => '70112233',
                'ema_per' => 'docente.sistemas.aula@gmail.com',
                'dir_per' => 'La Paz',
                'est_per' => true,
            ]
        );

        $personal = PersonalInstitucional::updateOrCreate(
            ['cod_pin' => 'PIN_AV001'],
            ['cod_per' => $personaDocente->cod_per, 'car_pin' => 'Docente de Sistemas Informáticos', 'est_pin' => 'ACTIVO']
        );

        $docente = Docente::updateOrCreate(
            ['cod_doc' => 'DOC_AV001'],
            ['cod_pin' => $personal->cod_pin, 'esp_doc' => 'Sistemas Informáticos', 'est_doc' => 'ACTIVO']
        );

        $usuarioDocente = User::updateOrCreate(
            ['email' => 'docente.sistemas.aula@gmail.com'],
            [
                'cod_usu' => User::where('email', 'docente.sistemas.aula@gmail.com')->value('cod_usu') ?: 'USU_AV002',
                'cod_per' => $personaDocente->cod_per,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'auth_provider' => 'local',
                'est_usu' => 'ACTIVO',
            ]
        );
        $usuarioDocente->assignRole(Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']));
        $usuarioDocente->givePermissionTo($permisosDocente);

        $asignatura = Asignatura::firstOrCreate(
            ['nom_asi' => 'Sistemas Informáticos'],
            ['cod_asi' => 'ASI_AV001', 'sig_asi' => 'SIS', 'hor_asi' => 4, 'est_asi' => 'ACTIVO']
        );

        $plan = PlanAsignatura::updateOrCreate(
            ['cod_pas' => 'PAS_AV001'],
            [
                'cod_asi' => $asignatura->cod_asi,
                'cod_doc' => $docente->cod_doc,
                'cod_cur' => $curso->cod_cur,
                'cod_par' => $paralelo->cod_par,
                'cod_tur' => $turno->cod_tur,
                'cod_gea' => $gestion->cod_gea,
                'hor_pas' => 4,
                'est_pas' => 'ACTIVO',
            ]
        );

        $clase = ClaseVirtual::updateOrCreate(
            ['cod_cla' => 'CLA_AV001'],
            [
                'cod_pas' => $plan->cod_pas,
                'nom_cla' => 'Sistemas Informáticos - 5to A Mañana',
                'des_cla' => 'Curso virtual institucional para actividades, materiales, asistencia y orientación académica-profesional.',
                'fec_ini_cla' => '2026-02-05',
                'fec_fin_cla' => '2026-11-30',
                'est_cla' => 'ACTIVA',
            ]
        );

        ClaseEstudiante::updateOrCreate(
            ['cod_cla_est' => 'CLE_AV001'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_est' => $estudiante->cod_est,
                'fec_inc_cla_est' => '2026-02-05',
                'ult_acc_cla_est' => null,
                'ult_act_cla_est' => null,
                'cant_acc_cla_est' => 0,
                'est_cla_est' => 'ACTIVO',
            ]
        );

        $publicacion = PublicacionClase::updateOrCreate(
            ['cod_pub' => 'PUB_AV001'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_usu' => $usuarioDocente->cod_usu,
                'tip_pub' => 'MATERIAL',
                'tit_pub' => 'Material de apoyo del trimestre',
                'con_pub' => 'Recursos iniciales para el seguimiento académico del trimestre.',
                'fec_pub' => now(),
                'est_pub' => 'PUBLICADO',
            ]
        );

        MaterialClase::updateOrCreate(
            ['cod_mat' => 'MATC_AV001'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_pub' => $publicacion->cod_pub,
                'cod_usu' => $usuarioDocente->cod_usu,
                'nom_mat' => 'Guía de ejercicios del trimestre',
                'tip_mat' => 'DOCUMENTO',
                'url_mat' => 'https://classroom.google.com/',
                'est_mat' => 'ACTIVO',
            ]
        );

        MaterialClase::updateOrCreate(
            ['cod_mat' => 'MATC_AV002'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_pub' => $publicacion->cod_pub,
                'cod_usu' => $usuarioDocente->cod_usu,
                'nom_mat' => 'Lectura de apoyo para orientación académica',
                'tip_mat' => 'ENLACE',
                'url_mat' => 'https://www.educacion.gob.bo/',
                'est_mat' => 'ACTIVO',
            ]
        );

        $tarea = Tarea::updateOrCreate(
            ['cod_tar' => 'TAR_AV001'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_doc' => $docente->cod_doc,
                'tit_tar' => 'Actividad de orientación académica',
                'des_tar' => 'Reflexiona sobre tus intereses académicos y completa el explorador académico-vocacional.',
                'tip_tar' => 'TAREA',
                'fec_pub_tar' => now(),
                'fec_lim_tar' => now()->addDays(10),
                'pun_max_tar' => 100,
                'perm_ent_tardia' => true,
                'est_tar' => 'PUBLICADA',
            ]
        );

        EntregaTarea::updateOrCreate(
            ['cod_ent' => 'ENT_AV001'],
            ['cod_tar' => $tarea->cod_tar, 'cod_est' => $estudiante->cod_est, 'tex_ent' => null, 'fec_ent' => null, 'est_ent' => 'PENDIENTE', 'obs_ent' => null]
        );

        $estadoPresente = EstadoAsistencia::where('nom_est_asi', 'Presente')->firstOrFail();

        $asistencia = AsistenciaClase::updateOrCreate(
            ['cod_asi_cla' => 'ASIC_AV001'],
            [
                'cod_cla' => $clase->cod_cla,
                'cod_doc' => $docente->cod_doc,
                'cod_usu_reg' => $usuarioDocente->cod_usu,
                'fec_asi_cla' => now()->toDateString(),
                'tip_asi_cla' => 'CLASE',
                'tit_asi_cla' => 'Asistencia del Aula Virtual',
                'obs_asi_cla' => 'Registro académico para seguimiento del flujo estudiante.',
                'ori_asi_cla' => 'MANUAL',
                'est_asi_cla' => 'CERRADA',
            ]
        );

        AsistenciaEstudiante::updateOrCreate(
            ['cod_asi_est' => 'ASIE_AV001'],
            [
                'cod_asi_cla' => $asistencia->cod_asi_cla,
                'cod_est' => $estudiante->cod_est,
                'cod_est_asi' => $estadoPresente->cod_est_asi,
                'cod_usu_reg' => $usuarioDocente->cod_usu,
                'min_retraso' => 0,
                'obs_asi_est' => 'Presente en actividades del Aula Virtual.',
                'fec_reg_asi_est' => now(),
                'est_asi_est' => 'REGISTRADO',
            ]
        );

        OrientacionActividad::updateOrCreate(
            ['cod_est' => $estudiante->cod_est, 'estado' => 'en_proceso'],
            [
                'cod_gea' => $gestion->cod_gea,
                'avance' => 0,
                'iniciado_at' => now(),
                'finalizado_at' => null,
            ]
        );
    }
}
