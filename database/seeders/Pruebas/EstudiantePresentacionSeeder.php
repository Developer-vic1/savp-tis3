<?php

namespace Database\Seeders\Pruebas;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\User;
use Carbon\Carbon;
use Database\Seeders\AulaVirtual\AulaVirtualOrientacionPreguntasSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Completa un estudiante existente con un escenario de entrenamiento coherente.
 *
 * Ejecucion intencional:
 * SAVP_TRAINING_DATA_ENABLED=true
 * SAVP_TRAINING_PASSWORD="..."
 * SAVP_TRAINING_ORIENTATION_COMPLETE=true (opcional: deja las 30 respuestas completas)
 * php artisan db:seed --class="Database\\Seeders\\Pruebas\\EstudiantePresentacionSeeder"
 */
class EstudiantePresentacionSeeder extends Seeder
{
    private const CURRENT_MANAGEMENT = 'GEA_2026';

    private const SPECIALTY = 'ESP_0003';

    private const SUBJECTS = [
        'ASI_0001',
        'ASI_0002',
        'ASI_0003',
        'ASI_0004',
        'ASI_0005',
        'ASI_0006',
        'ASI_0008',
        'ASI_0014',
    ];

    public function run(): void
    {
        $this->assertTrainingDatabase();

        $password = (string) env('SAVP_TRAINING_PASSWORD', '');
        $completeOrientation = filter_var(env('SAVP_TRAINING_ORIENTATION_COMPLETE', false), FILTER_VALIDATE_BOOL);
        if (mb_strlen($password) < 12) {
            throw new RuntimeException('SAVP_TRAINING_PASSWORD debe tener al menos 12 caracteres.');
        }

        $user = $this->findStudentUser((string) env('SAVP_TRAINING_STUDENT_EMAIL', ''));
        if (! $user->hasRole('Estudiante')) {
            throw new RuntimeException("El usuario {$user->email} no tiene el rol Estudiante; el seeder no modifica RBAC.");
        }

        $student = DB::table('estudiante')
            ->where('cod_per', $user->cod_per)
            ->where('est_est', 'ACTIVO')
            ->first();

        if (! $student) {
            throw new RuntimeException('El usuario seleccionado no tiene un estudiante activo vinculado.');
        }

        $this->call(AulaVirtualOrientacionPreguntasSeeder::class);

        DB::transaction(function () use ($user, $student, $password, $completeOrientation): void {
            $now = now();
            $user->forceFill([
                'password' => Hash::make($password),
                'email_verified_at' => $user->email_verified_at ?? $now,
                'est_usu' => 'ACTIVO',
            ])->save();

            DB::table('estudiante')->where('cod_est', $student->cod_est)->update([
                'cod_esp' => self::SPECIALTY,
                'est_est' => 'ACTIVO',
                'updated_at' => $now,
            ]);

            [$parallel, $shift] = $this->academicPlacement();
            $teachers = DB::table('docente')
                ->where('est_doc', 'ACTIVO')
                ->orderBy('cod_doc')
                ->pluck('cod_doc')
                ->values();

            if ($teachers->isEmpty()) {
                throw new RuntimeException('No existen docentes activos para construir el historial academico.');
            }

            $plansByYear = $this->seedAcademicHistory($student->cod_est, $parallel, $shift, $teachers->all());
            $this->seedVirtualClassroom($student->cod_est, $plansByYear[2026]);
            $this->seedAttendance($student->cod_est, $user->cod_usu);
            $this->seedOrientation($student->cod_est, $completeOrientation);
        });

        $name = DB::table('persona')->where('cod_per', $user->cod_per)
            ->selectRaw("TRIM(COALESCE(nom_per, '') || ' ' || COALESCE(ape_pat_per, '') || ' ' || COALESCE(ape_mat_per, '')) AS nombre")
            ->value('nombre');

        $this->command?->info('Escenario de entrenamiento preparado sobre un estudiante existente.');
        $this->command?->line("Estudiante: {$name} ({$student->cod_est})");
        $this->command?->line("Acceso: {$user->email}");
        $this->command?->line('Historial: 1ro a 6to de secundaria | Gestion vigente: 2026 | Especialidad: Sistemas Informaticos');
        $this->command?->line($completeOrientation
            ? 'Orientacion: 30 de 30 preguntas respondidas; escenario listo para generar y presentar el analisis.'
            : 'Orientacion: 26 de 30 preguntas respondidas; quedan 4 pendientes.');
    }

    private function assertTrainingDatabase(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Este seeder de entrenamiento no puede ejecutarse en produccion.');
        }

        if (! filter_var(env('SAVP_TRAINING_DATA_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            throw new RuntimeException('Define SAVP_TRAINING_DATA_ENABLED=true para habilitar este seeder de forma explicita.');
        }

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Este seeder solo puede ejecutarse sobre SQLite aislado.');
        }

        $database = (string) $connection->getDatabaseName();
        $actual = realpath($database) ?: $database;
        $expected = realpath(storage_path('framework')).DIRECTORY_SEPARATOR.'savp-training.sqlite';

        if (strcasecmp(str_replace('/', DIRECTORY_SEPARATOR, $actual), $expected) !== 0) {
            throw new RuntimeException("Base no autorizada: {$actual}. Usa exclusivamente {$expected}.");
        }
    }

    private function findStudentUser(string $email): User
    {
        $query = User::query()
            ->where('users.est_usu', 'ACTIVO')
            ->whereExists(function ($student): void {
                $student->selectRaw('1')
                    ->from('estudiante')
                    ->whereColumn('estudiante.cod_per', 'users.cod_per')
                    ->where('estudiante.est_est', 'ACTIVO');
            });

        if ($email !== '') {
            $query->where('users.email', $email);
        } else {
            $query->whereExists(function ($enrollment): void {
                $enrollment->selectRaw('1')
                    ->from('estudiante')
                    ->join('inscripcion_estudiante', 'inscripcion_estudiante.cod_est', '=', 'estudiante.cod_est')
                    ->whereColumn('estudiante.cod_per', 'users.cod_per')
                    ->where('inscripcion_estudiante.cod_gea', self::CURRENT_MANAGEMENT)
                    ->where('inscripcion_estudiante.cod_cur', 'CUR_0006')
                    ->where('inscripcion_estudiante.est_ins', 'ACTIVA');
            });
        }

        $user = $query->orderBy('users.cod_usu')->first();
        if (! $user) {
            $detail = $email !== '' ? " con correo {$email}" : ' de sexto de secundaria';
            throw new RuntimeException("No se encontro un usuario estudiante activo{$detail}.");
        }

        return $user;
    }

    /** @return array{0: string, 1: string} */
    private function academicPlacement(): array
    {
        $parallel = DB::table('paralelo')->where('est_par', 'ACTIVO')->orderBy('cod_par')->value('cod_par');
        $shift = DB::table('turno')->where('est_tur', 'ACTIVO')->orderBy('cod_tur')->value('cod_tur');

        if (! $parallel || ! $shift) {
            throw new RuntimeException('Faltan paralelos o turnos activos.');
        }

        if (! DB::table('especialidad_tecnica')->where('cod_esp', self::SPECIALTY)->where('est_esp', 'ACTIVO')->exists()) {
            throw new RuntimeException('No existe la especialidad activa ESP_0003 (Sistemas Informaticos).');
        }

        foreach (self::SUBJECTS as $subject) {
            if (! DB::table('asignatura')->where('cod_asi', $subject)->where('est_asi', 'ACTIVO')->exists()) {
                throw new RuntimeException("No existe la asignatura activa {$subject}.");
            }
        }

        return [$parallel, $shift];
    }

    /**
     * @param  array<int, string>  $teachers
     * @return array<int, array<string, object>>
     */
    private function seedAcademicHistory(string $studentId, string $parallel, string $shift, array $teachers): array
    {
        $plansByYear = [];
        $studentToken = $this->studentToken($studentId);
        $periods = DB::table('periodo_evaluacion')
            ->where('est_pev', 'ACTIVO')
            ->orderBy('ord_pev')
            ->get();

        if ($periods->count() < 3) {
            throw new RuntimeException('Se requieren tres periodos de evaluacion activos.');
        }

        DB::table('calificacion')->where('cod_est', $studentId)->whereNull('cod_pas')->delete();

        foreach (range(2021, 2026) as $year) {
            $grade = $year - 2020;
            $management = $year === 2026 ? self::CURRENT_MANAGEMENT : "GEA_ENT_{$year}";
            $course = 'CUR_'.str_pad((string) $grade, 4, '0', STR_PAD_LEFT);

            DB::table('gestion_academica')->updateOrInsert(
                ['cod_gea' => $management],
                [
                    'ani_gea' => $year,
                    'fii_gea' => "{$year}-02-01",
                    'ffi_gea' => "{$year}-11-30",
                    'est_gea' => $year === 2026 ? 'ACTIVO' : 'CERRADO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $existingEnrollment = DB::table('inscripcion_estudiante')
                ->where('cod_est', $studentId)
                ->where('cod_gea', $management)
                ->first();
            $enrollmentId = $existingEnrollment?->cod_ins ?? "INS_ENT_{$year}_{$studentToken}";
            DB::table('inscripcion_estudiante')->updateOrInsert(
                ['cod_est' => $studentId, 'cod_gea' => $management],
                [
                    'cod_ins' => $enrollmentId,
                    'cod_cur' => $course,
                    'cod_par' => $parallel,
                    'cod_tur' => $shift,
                    'fei_ins' => "{$year}-02-05",
                    'tip_ins' => 'REGULAR',
                    'con_ins' => 'NORMAL',
                    'est_ins' => $year === 2026 ? 'ACTIVA' : 'ARCHIVADA',
                    'pro_ins' => 'INSCRITO',
                    'obs_ins' => $year === 2026
                        ? 'Inscripcion vigente confirmada para la gestion academica 2026.'
                        : "Gestion {$year} concluida y consolidada en el historial academico.",
                    'mot_obs_ins' => null,
                    'doc_com_ins' => true,
                    'sob_aut_ins' => false,
                    'sie_ins' => true,
                    'fec_sie_ins' => "{$year}-02-15 10:00:00",
                    'cod_esp_tec' => $grade >= 4 ? self::SPECIALTY : null,
                    'est_esp_tec_ins' => $grade >= 4 ? 'ASIGNADA' : 'NO_APLICA',
                    'obs_esp_tec_ins' => $grade >= 4
                        ? 'Especialidad tecnica elegida y confirmada: Sistemas Informaticos.'
                        : null,
                    'fec_con_ins' => "{$year}-02-16 09:00:00",
                    'updated_at' => now(),
                    'created_at' => $existingEnrollment?->created_at ?? now(),
                ]
            );

            foreach (self::SUBJECTS as $subjectIndex => $subject) {
                $existingPlan = DB::table('plan_asignatura')
                    ->where('cod_asi', $subject)
                    ->where('cod_cur', $course)
                    ->where('cod_par', $parallel)
                    ->where('cod_tur', $shift)
                    ->where('cod_gea', $management)
                    ->where('est_pas', 'ACTIVO')
                    ->first();
                $planId = $existingPlan?->cod_pas
                    ?? 'PEN_'.substr((string) $year, -2).'_'.str_pad((string) ($subjectIndex + 1), 2, '0', STR_PAD_LEFT);
                $teacher = $existingPlan?->cod_doc ?? $teachers[($grade + $subjectIndex) % count($teachers)];

                DB::table('plan_asignatura')->updateOrInsert(
                    ['cod_pas' => $planId],
                    [
                        'cod_asi' => $subject,
                        'cod_doc' => $teacher,
                        'cod_cur' => $course,
                        'cod_par' => $parallel,
                        'cod_tur' => $shift,
                        'cod_gea' => $management,
                        'hor_pas' => in_array($subject, ['ASI_0001', 'ASI_0002', 'ASI_0014'], true) ? 5 : 4,
                        'est_pas' => 'ACTIVO',
                        'created_at' => $existingPlan?->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );

                $plansByYear[$year][$subject] = (object) ['cod_pas' => $planId, 'cod_doc' => $teacher];
                $periodLimit = $year === 2026 ? 2 : 3;

                foreach ($periods->take($periodLimit) as $periodIndex => $period) {
                    $score = $this->gradeScore($grade, $subjectIndex, $periodIndex);
                    $gradeId = 'CEN'.substr((string) $year, -2)
                        .str_pad((string) ($subjectIndex + 1), 2, '0', STR_PAD_LEFT)
                        .'P'.($periodIndex + 1).'_'.$studentToken;

                    DB::table('calificacion')->updateOrInsert(
                        ['cod_est' => $studentId, 'cod_pas' => $planId, 'cod_pev' => $period->cod_pev],
                        [
                            'cod_cal' => $gradeId,
                            'cod_est' => $studentId,
                            'cod_asi' => $subject,
                            'cod_pas' => $planId,
                            'cod_pev' => $period->cod_pev,
                            'not_cal' => $score,
                            'obs_cal' => $this->gradeObservation($score),
                            'est_cal' => 'ACTIVO',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        return $plansByYear;
    }

    /** @param array<string, object> $currentPlans */
    private function seedVirtualClassroom(string $studentId, array $currentPlans): void
    {
        $studentToken = $this->studentToken($studentId);
        $subjects = ['ASI_0002', 'ASI_0005', 'ASI_0008', 'ASI_0014'];
        $titles = [
            'ASI_0002' => 'Matematica - 6to A',
            'ASI_0005' => 'Fisica - 6to A',
            'ASI_0008' => 'Ingles - 6to A',
            'ASI_0014' => 'Sistemas Informaticos - 6to A',
        ];
        $taskNames = [
            ['Ejercicios de aplicacion', 'Informe de avance', 'Proyecto de cierre de gestion'],
            ['Problemas de movimiento', 'Practica de laboratorio', 'Informe de energia y potencia'],
            ['Reading comprehension', 'Presentacion oral', 'Proyecto final en ingles'],
            ['Modelo de base de datos', 'Prototipo de aplicacion', 'Documentacion del proyecto tecnico'],
        ];
        $scores = [[78, 84], [76, 82], [88, 85], [86, 90]];

        foreach ($subjects as $classIndex => $subject) {
            $plan = $currentPlans[$subject];
            $classId = 'CLA_ENT_'.str_pad((string) ($classIndex + 1), 2, '0', STR_PAD_LEFT);

            DB::table('clase_virtual')->updateOrInsert(
                ['cod_cla' => $classId],
                [
                    'cod_pas' => $plan->cod_pas,
                    'nom_cla' => $titles[$subject],
                    'des_cla' => 'Aula de seguimiento para actividades, materiales y evaluacion continua de la gestion 2026.',
                    'fec_ini_cla' => '2026-02-09',
                    'fec_fin_cla' => '2026-11-27',
                    'est_cla' => 'ACTIVA',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('clase_estudiante')->updateOrInsert(
                ['cod_cla' => $classId, 'cod_est' => $studentId],
                [
                    'cod_cla_est' => 'CLE_ENT_'.str_pad((string) ($classIndex + 1), 2, '0', STR_PAD_LEFT).'_'.$studentToken,
                    'fec_inc_cla_est' => '2026-02-09',
                    'fec_ret_cla_est' => null,
                    'ult_acc_cla_est' => '2026-09-30 18:25:00',
                    'ult_act_cla_est' => '2026-09-29 20:10:00',
                    'cant_acc_cla_est' => 34 + ($classIndex * 7),
                    'est_cla_est' => 'ACTIVO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            foreach (range(0, 2) as $taskIndex) {
                $taskId = 'TAR_ENT_'.($classIndex + 1).'_'.($taskIndex + 1);
                $deadline = match ($taskIndex) {
                    0 => '2026-08-21 18:00:00',
                    1 => '2026-09-18 18:00:00',
                    default => '2026-10-'.str_pad((string) (8 + $classIndex), 2, '0', STR_PAD_LEFT).' 18:00:00',
                };

                DB::table('tarea')->updateOrInsert(
                    ['cod_tar' => $taskId],
                    [
                        'cod_cla' => $classId,
                        'cod_doc' => $plan->cod_doc,
                        'tit_tar' => $taskNames[$classIndex][$taskIndex],
                        'des_tar' => 'Actividad con consigna, criterios de evaluacion y entrega individual.',
                        'tip_tar' => $taskIndex === 2 ? 'PROYECTO' : 'PRACTICA',
                        'fec_pub_tar' => Carbon::parse($deadline)->subDays(10),
                        'fec_lim_tar' => $deadline,
                        'pun_max_tar' => 100,
                        'perm_ent_tardia' => true,
                        'est_tar' => 'PUBLICADA',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                if ($taskIndex === 2) {
                    continue;
                }

                $deliveryId = 'ENT_ENT_'.($classIndex + 1).'_'.($taskIndex + 1).'_'.$studentToken;
                $existingDeliveryId = DB::table('entrega_tarea')
                    ->where('cod_tar', $taskId)
                    ->where('cod_est', $studentId)
                    ->value('cod_ent');
                $deliveryValues = [
                    'fec_ent' => Carbon::parse($deadline)->subHours(3),
                    'tex_ent' => 'Actividad desarrollada con procedimiento, resultados y una breve conclusion.',
                    'est_ent' => 'CALIFICADO',
                    'obs_ent' => 'Entrega revisada dentro del plazo establecido.',
                    'updated_at' => now(),
                ];
                if ($existingDeliveryId) {
                    $deliveryId = (string) $existingDeliveryId;
                    DB::table('entrega_tarea')->where('cod_ent', $deliveryId)->update($deliveryValues);
                } else {
                    DB::table('entrega_tarea')->insert($deliveryValues + [
                        'cod_ent' => $deliveryId,
                        'cod_tar' => $taskId,
                        'cod_est' => $studentId,
                        'created_at' => now(),
                    ]);
                }

                $gradeValues = [
                    'cod_tar' => $taskId,
                    'cod_est' => $studentId,
                    'cod_doc' => $plan->cod_doc,
                    'pun_obt' => $scores[$classIndex][$taskIndex],
                    'pun_max' => 100,
                    'com_cal' => $scores[$classIndex][$taskIndex] >= 85
                        ? 'Buen dominio del contenido y presentacion ordenada.'
                        : 'Cumple la consigna; puede reforzar la justificacion del procedimiento.',
                    'fec_cal' => Carbon::parse($deadline)->addDay(),
                    'est_cal' => 'REGISTRADO',
                    'updated_at' => now(),
                ];
                if (DB::table('calificacion_tarea')->where('cod_ent', $deliveryId)->exists()) {
                    DB::table('calificacion_tarea')->where('cod_ent', $deliveryId)->update($gradeValues);
                } else {
                    DB::table('calificacion_tarea')->insert($gradeValues + [
                        'cod_cal_tar' => 'CTA_ENT_'.($classIndex + 1).'_'.($taskIndex + 1).'_'.$studentToken,
                        'cod_ent' => $deliveryId,
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedAttendance(string $studentId, string $fallbackUserId): void
    {
        $studentToken = $this->studentToken($studentId);
        $states = [
            'EAS_PRESENTE' => ['Presente', 'P', '#16a34a', 100, false],
            'EAS_TARDANZA' => ['Tardanza', 'T', '#f59e0b', 100, true],
            'EAS_FALTA' => ['Falta', 'F', '#dc2626', 0, true],
            'EAS_JUSTIFICADA' => ['Justificada', 'J', '#2563eb', 100, true],
        ];

        foreach ($states as $code => [$name, $short, $color, $value, $requiresObservation]) {
            DB::table('estado_asistencia')->updateOrInsert(
                ['cod_est_asi' => $code],
                [
                    'nom_est_asi' => $name,
                    'abr_est_asi' => $short,
                    'des_est_asi' => "Estado {$name} para el control de asistencia.",
                    'color_est_asi' => $color,
                    'valor_porcentual' => $value,
                    'afecta_asistencia' => true,
                    'requiere_observacion' => $requiresObservation,
                    'est_est_asi' => 'ACTIVO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $stateSequence = [
            'EAS_PRESENTE', 'EAS_PRESENTE', 'EAS_PRESENTE', 'EAS_PRESENTE',
            'EAS_TARDANZA', 'EAS_PRESENTE', 'EAS_PRESENTE', 'EAS_JUSTIFICADA',
            'EAS_PRESENTE', 'EAS_PRESENTE', 'EAS_FALTA', 'EAS_PRESENTE',
        ];
        $dates = ['2026-08-10', '2026-08-17', '2026-08-24', '2026-08-31', '2026-09-07', '2026-09-14', '2026-09-21', '2026-09-28', '2026-08-12', '2026-08-26', '2026-09-09', '2026-09-23'];

        foreach ($stateSequence as $index => $state) {
            $classNumber = ($index % 4) + 1;
            $classId = 'CLA_ENT_'.str_pad((string) $classNumber, 2, '0', STR_PAD_LEFT);
            $plan = DB::table('clase_virtual')
                ->join('plan_asignatura', 'plan_asignatura.cod_pas', '=', 'clase_virtual.cod_pas')
                ->where('clase_virtual.cod_cla', $classId)
                ->select('plan_asignatura.cod_doc')
                ->first();
            $registrar = $this->teacherUser($plan?->cod_doc) ?? $fallbackUserId;
            $sessionId = 'ACL_ENT_'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            DB::table('asistencia_clase')->updateOrInsert(
                ['cod_asi_cla' => $sessionId],
                [
                    'cod_cla' => $classId,
                    'cod_doc' => $plan->cod_doc,
                    'cod_hbl' => null,
                    'cod_usu_reg' => $registrar,
                    'fec_asi_cla' => $dates[$index],
                    'hor_ini_asi_cla' => '08:00:00',
                    'hor_fin_asi_cla' => '09:30:00',
                    'tip_asi_cla' => $index % 5 === 0 ? 'PRACTICA' : 'CLASE',
                    'tit_asi_cla' => 'Control de asistencia de la sesion',
                    'obs_asi_cla' => 'Registro cerrado por el docente de la asignatura.',
                    'ori_asi_cla' => 'MANUAL',
                    'est_asi_cla' => 'CERRADA',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('asistencia_estudiante')->updateOrInsert(
                ['cod_asi_cla' => $sessionId, 'cod_est' => $studentId],
                [
                    'cod_asi_est' => 'AES_ENT_'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'_'.$studentToken,
                    'cod_est_asi' => $state,
                    'cod_usu_reg' => $registrar,
                    'min_retraso' => $state === 'EAS_TARDANZA' ? 8 : 0,
                    'obs_asi_est' => match ($state) {
                        'EAS_TARDANZA' => 'Ingreso registrado ocho minutos despues del inicio.',
                        'EAS_FALTA' => 'Inasistencia registrada sin respaldo presentado.',
                        'EAS_JUSTIFICADA' => 'Inasistencia justificada con respaldo revisado.',
                        default => null,
                    },
                    'fec_reg_asi_est' => $dates[$index].' 10:00:00',
                    'est_asi_est' => 'REGISTRADO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedOrientation(string $studentId, bool $complete): void
    {
        $activity = OrientacionActividad::query()
            ->where('cod_est', $studentId)
            ->where('cod_gea', self::CURRENT_MANAGEMENT)
            ->whereNull('riasec_public')
            ->first();

        if (! $activity) {
            $activity = OrientacionActividad::create([
                'cod_est' => $studentId,
                'cod_gea' => self::CURRENT_MANAGEMENT,
            ]);
        }

        $activity->forceFill([
            'estado' => $complete ? 'finalizado' : 'en_proceso',
            'avance' => $complete ? 100 : 87,
            'iniciado_at' => now()->subDays(5),
            'finalizado_at' => $complete ? now() : null,
            'revisado_por' => null,
        ])->save();

        DB::table('orientacion_respuestas')->where('orientacion_actividad_id', $activity->id)->delete();
        $questions = DB::table('orientacion_preguntas')
            ->where('visible', true)
            ->orderBy('orden')
            ->limit($complete ? 30 : 26)
            ->get();

        foreach ($questions as $index => $question) {
            DB::table('orientacion_respuestas')->insert([
                'orientacion_actividad_id' => $activity->id,
                'orientacion_pregunta_id' => $question->id,
                'cod_est' => $studentId,
                'valor_likert' => [4, 5, 4, 3, 5, 4][$index % 6],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function teacherUser(?string $teacherId): ?string
    {
        if (! $teacherId) {
            return null;
        }

        return DB::table('docente')
            ->join('personal_institucional', 'personal_institucional.cod_pin', '=', 'docente.cod_pin')
            ->join('users', 'users.cod_per', '=', 'personal_institucional.cod_per')
            ->where('docente.cod_doc', $teacherId)
            ->where('users.est_usu', 'ACTIVO')
            ->value('users.cod_usu');
    }

    private function studentToken(string $studentId): string
    {
        $token = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $studentId));

        if ($token === '') {
            throw new RuntimeException('No se pudo generar un identificador seguro para el estudiante de entrenamiento.');
        }

        return $token;
    }

    private function gradeScore(int $grade, int $subjectIndex, int $periodIndex): int
    {
        $subjectBase = [72, 68, 76, 74, 67, 70, 79, 82][$subjectIndex];
        $periodAdjustment = [-2, 1, 3][$periodIndex];
        $variation = (($grade * 7 + $subjectIndex * 3 + $periodIndex) % 5) - 2;

        return max(58, min(94, $subjectBase + (($grade - 1) * 2) + $periodAdjustment + $variation));
    }

    private function gradeObservation(int $score): string
    {
        return match (true) {
            $score >= 86 => 'Desempeno destacado y participacion constante durante el periodo.',
            $score >= 76 => 'Rendimiento favorable; cumple actividades y demuestra progreso sostenido.',
            $score >= 66 => 'Alcanza los aprendizajes esperados y requiere reforzar algunos contenidos.',
            default => 'Requiere acompanamiento y practica adicional en los contenidos del periodo.',
        };
    }
}
