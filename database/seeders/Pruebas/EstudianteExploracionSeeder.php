<?php

namespace Database\Seeders\Pruebas;

use App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Prepara un estudiante existente para demostrar el inicio de la orientacion.
 *
 * El escenario conserva el historial, la gestion 2026 y el Aula Virtual del
 * seeder de presentacion, pero deja la actividad de orientacion sin respuestas
 * ni resultados oficiales. Solo se permite en la SQLite aislada de entrenamiento.
 *
 * Ejecucion intencional:
 * SAVP_TRAINING_DATA_ENABLED=true
 * SAVP_TRAINING_STUDENT_EMAIL="..."
 * SAVP_TRAINING_PASSWORD="..."
 * php artisan db:seed --class="Database\\Seeders\\Pruebas\\EstudianteExploracionSeeder"
 */
class EstudianteExploracionSeeder extends Seeder
{
    private const CURRENT_MANAGEMENT = 'GEA_2026';

    private const PRESENTATION_STUDENT_EMAIL = 'jose.luis.quispe.151@gmail.com';

    public function run(): void
    {
        $this->assertTrainingDatabase();

        $email = (string) env('SAVP_TRAINING_STUDENT_EMAIL', '');
        $password = (string) env('SAVP_TRAINING_PASSWORD', '');

        if ($email === '') {
            throw new RuntimeException('Define SAVP_TRAINING_STUDENT_EMAIL para seleccionar un estudiante existente.');
        }

        if (mb_strlen($password) < 12) {
            throw new RuntimeException('SAVP_TRAINING_PASSWORD debe tener al menos 12 caracteres.');
        }

        if (mb_strtolower($email) === self::PRESENTATION_STUDENT_EMAIL) {
            throw new RuntimeException('Usa un estudiante distinto al escenario de presentacion ya finalizado.');
        }

        $user = User::query()->where('email', $email)->where('est_usu', 'ACTIVO')->first();
        if (! $user || ! $user->hasRole('Estudiante')) {
            throw new RuntimeException('El correo indicado no corresponde a un usuario estudiante activo.');
        }

        $student = DB::table('estudiante')
            ->where('cod_per', $user->cod_per)
            ->where('est_est', 'ACTIVO')
            ->first();

        if (! $student) {
            throw new RuntimeException('El usuario seleccionado no tiene un estudiante activo vinculado.');
        }

        if (OrientacionActividad::query()->where('cod_est', $student->cod_est)->whereNotNull('riasec_public')->exists()) {
            throw new RuntimeException('El estudiante elegido ya cuenta con una orientacion oficial. Selecciona otro escenario de entrenamiento.');
        }

        $this->call(EstudiantePresentacionSeeder::class);

        DB::transaction(function () use ($student): void {
            $activity = OrientacionActividad::query()->firstOrCreate([
                'cod_est' => $student->cod_est,
                'cod_gea' => self::CURRENT_MANAGEMENT,
            ]);

            DB::table('orientacion_respuestas')->where('orientacion_actividad_id', $activity->id)->delete();
            DB::table('orientacion_resultados')->where('orientacion_actividad_id', $activity->id)->delete();

            $activity->forceFill([
                'estado' => 'pendiente',
                'avance' => 0,
                'iniciado_at' => null,
                'finalizado_at' => null,
                'revisado_por' => null,
                'riasec_public' => null,
                'riasec_score' => null,
                'analysis_snapshot' => null,
                'analysis_completed_at' => null,
                'riasec_input_hash' => null,
            ])->save();
        });

        $name = DB::table('persona')->where('cod_per', $user->cod_per)
            ->selectRaw("TRIM(COALESCE(nom_per, '') || ' ' || COALESCE(ape_pat_per, '') || ' ' || COALESCE(ape_mat_per, '')) AS nombre")
            ->value('nombre');

        $this->command?->info('Escenario de exploracion preparado sobre un estudiante existente.');
        $this->command?->line("Estudiante: {$name} ({$student->cod_est})");
        $this->command?->line("Acceso: {$user->email}");
        $this->command?->line('Orientacion: sin respuestas ni resultados; listo para iniciar y explicar el flujo.');
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
}
