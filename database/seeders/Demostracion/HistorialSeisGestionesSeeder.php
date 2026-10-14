<?php

declare(strict_types=1);

namespace Database\Seeders\Demostracion;

use App\Models\Oficial\Sistema\User;
use Database\Seeders\AulaVirtualPermissionSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Dataset sintético público: exclusivamente una instalación PostgreSQL vacía. */
final class HistorialSeisGestionesSeeder extends Seeder
{
    private int $nota = 0;

    public function run(): void
    {
        $db = DB::connection();
        $nombre = (string) $db->getDatabaseName();
        if (! app()->environment('local', 'testing') || ! config('demostracion.habilitada')
            || $db->getDriverName() !== 'pgsql'
            || ! preg_match('/\Asavp_demo_[a-z0-9_]+\z/i', $nombre)
            || config('demostracion.base_autorizada') !== $nombre
            || $db->selectOne('SELECT current_database() AS nombre')->nombre !== $nombre) {
            throw new RuntimeException('Demostración bloqueada: requiere una base PostgreSQL savp_demo_ autorizada y entorno local.');
        }
        $password = (string) config('demostracion.password');
        $ultimo = (int) config('demostracion.ultimo_anio');
        if (mb_strlen($password) < 12 || strlen($password) > 72 || $ultimo < 2005 || $ultimo > now('America/La_Paz')->year) {
            throw new RuntimeException('La contraseña debe tener 12 caracteres y la última gestión no puede ser futura.');
        }
        $db->transaction(function () use ($db, $password, $ultimo): void {
            $db->select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['SAVP:demostracion:seis-gestiones']);
            foreach (Schema::getTables() as $tabla) {
                if ($tabla['name'] !== 'migrations' && $db->table($tabla['name'])->exists()) {
                    throw new RuntimeException('Demostración bloqueada: ya hay registros. No se modificó el historial existente.');
                }
            }
            $this->call([RolSeeder::class, AulaVirtualPermissionSeeder::class]);
            $this->persona('PER_DEMO_ADMIN', 'Administrador', 'Demostración', 'DEMO-ADMIN');
            $this->cuenta('USU_DEMO_ADMIN', 'PER_DEMO_ADMIN', 'administrador@savp.test', $password, 'Administrador');
            $this->insertar('tipo_vinculacion_estudiante', ['cod_tve' => 'TVE_DEMO_REGULAR', 'nom_tve' => 'Regular', 'des_tve' => 'Dataset de demostración, sin registros personales reales.']);
            $materias = ['Matemática', 'Lengua y Literatura', 'Ciencias Naturales'];
            foreach ($materias as $indice => $materia) {
                $this->insertar('asignatura', ['cod_asi' => 'ASI_DEMO_'.($indice + 1), 'nom_asi' => $materia, 'hor_asi' => 10]);
            }
            $this->insertar('paralelo', ['cod_par' => 'PAR_DEMO_A', 'nom_par' => 'A']);
            for ($grado = 1; $grado <= 6; $grado++) {
                $this->insertar('curso', ['cod_cur' => "CUR_DEMO_{$grado}", 'nom_cur' => "{$grado}° de Secundaria", 'niv_cur' => 'SECUNDARIA', 'ord_cur' => $grado]);
            }
            for ($periodo = 1; $periodo <= 3; $periodo++) {
                $this->insertar('periodo_evaluacion', ['cod_pev' => "PEV_DEMO_{$periodo}", 'nom_pev' => ['Primer Trimestre', 'Segundo Trimestre', 'Tercer Trimestre'][$periodo - 1], 'ord_pev' => $periodo]);
            }
            foreach ([1 => ['Mañana', 8], 2 => ['Tarde', 14]] as $turno => [$etiqueta, $hora]) {
                $this->insertar('turno', ['cod_tur' => "TUR_DEMO_{$turno}", 'nom_tur' => $etiqueta, 'hor_ini_tur' => sprintf('%02d:00', $hora), 'hor_fin_tur' => sprintf('%02d:00', $hora + 5)]);
                $this->insertar('plantilla_horaria', ['cod_pho' => "PHO_DEMO_{$turno}", 'cod_tur' => "TUR_DEMO_{$turno}", 'nom_pho' => "Demostración {$etiqueta}", 'dur_blo_pho' => 45, 'act_pho' => true]);
                // Seis bloques de clase y un recreo: 4 h 50 min dentro del turno.
                $minutos = $hora * 60;
                for ($bloque = 1; $bloque <= 7; $bloque++) {
                    $duracion = $bloque === 4 ? 20 : 45;
                    $this->insertar('horario_bloque', ['cod_hbl' => "HBL_DEMO_{$turno}_{$bloque}", 'cod_pho' => "PHO_DEMO_{$turno}", 'num_hbl' => $bloque, 'hor_ini_hbl' => sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60), 'hor_fin_hbl' => sprintf('%02d:%02d', intdiv($minutos + $duracion, 60), ($minutos + $duracion) % 60), 'nom_hbl' => $bloque === 4 ? 'Recreo' : "Bloque {$bloque}", 'tip_hbl' => $bloque === 4 ? 'RECREO' : 'CLASE']);
                    $minutos += $duracion;
                }
                for ($grado = 1; $grado <= 6; $grado++) {
                    for ($materia = 1; $materia <= 3; $materia++) {
                        $token = "{$grado}_{$turno}_{$materia}";
                        $this->persona("PER_DEMO_D_{$token}", 'Docente', "Demostración {$token}", "DEMO-D-{$token}");
                        $this->insertar('personal_institucional', ['cod_pin' => "PIN_DEMO_{$token}", 'cod_per' => "PER_DEMO_D_{$token}"]);
                        $this->insertar('docente', ['cod_doc' => "DOC_DEMO_{$token}", 'cod_pin' => "PIN_DEMO_{$token}", 'esp_doc' => $materias[$materia - 1]]);
                    }
                    $token = "{$grado}_{$turno}";
                    $this->persona("PER_DEMO_E_{$token}", "Estudiante {$grado}", "Demostración {$turno}", "DEMO-E-{$token}");
                    $this->insertar('estudiante', ['cod_est' => "EST_DEMO_{$token}", 'cod_per' => "PER_DEMO_E_{$token}", 'rud_est' => "DEMO-RUDE-{$token}", 'cod_tve' => 'TVE_DEMO_REGULAR']);
                    $this->cuenta("USU_DEMO_{$token}", "PER_DEMO_E_{$token}", "estudiante{$grado}.".($turno === 1 ? 'm' : 't').'@savp.test', $password, 'Estudiante');
                }
            }
            foreach (range($ultimo - 5, $ultimo) as $anio) {
                $this->gestion($anio, $ultimo);
            }
            $db->statement('SET CONSTRAINTS ALL IMMEDIATE');
        });
        $this->command?->info('Demostración creada: seis gestiones, 12 estudiantes, 36 docentes, turnos, bloques y horarios. Datos sintéticos.');
    }

    private function gestion(int $anio, int $ultimo): void
    {
        $inicio = "{$anio}-02-02";
        $fin = "{$anio}-12-02";
        $actual = $anio === $ultimo;
        $this->insertar('gestion_academica', ['cod_gea' => "GEA_DEMO_{$anio}", 'ani_gea' => $anio, 'fii_gea' => $inicio, 'ffi_gea' => $fin, 'est_gea' => $actual ? 'ACTIVA' : 'CERRADA']);
        $periodos = [['02-02', '04-30'], ['05-01', '08-31'], ['09-01', '12-02']];
        foreach ($periodos as $indice => [$desde, $hasta]) {
            $this->insertar('configuracion_calendario_gestion', ['cod_ccg' => "CCG_D_{$anio}_{$indice}", 'cod_gea' => "GEA_DEMO_{$anio}", 'cod_pev' => 'PEV_DEMO_'.($indice + 1), 'fii_tri_ccg' => "{$anio}-{$desde}", 'ffi_tri_ccg' => "{$anio}-{$hasta}", 'est_ccg' => now('America/La_Paz')->toDateString() > "{$anio}-{$hasta}" ? 'CERRADO' : (now('America/La_Paz')->toDateString() >= "{$anio}-{$desde}" ? 'ACTIVO' : 'PLANIFICADO')]);
        }
        foreach ([1, 2] as $turno) {
            for ($grado = 1; $grado <= 6; $grado++) {
                $grupo = "GAC_D_{$anio}_{$grado}_{$turno}";
                $horario = "HOR_D_{$anio}_{$grado}_{$turno}";
                $this->insertar('grupo_academico', ['cod_gac' => $grupo, 'cod_gea' => "GEA_DEMO_{$anio}", 'cod_cur' => "CUR_DEMO_{$grado}", 'cod_par' => 'PAR_DEMO_A', 'cod_tur' => "TUR_DEMO_{$turno}", 'cap_gac' => 30, 'obs_gac' => 'Escenario sintético de demostración.']);
                $this->insertar('horario', ['cod_hor' => $horario, 'cod_gac' => $grupo, 'cod_pho' => "PHO_DEMO_{$turno}", 'fii_hor' => $inicio, 'ffi_hor' => $fin]);
                for ($materia = 1; $materia <= 3; $materia++) {
                    $plan = "PAS_D_{$anio}_{$grado}_{$turno}_{$materia}";
                    $clase = "CLA_D_{$anio}_{$grado}_{$turno}_{$materia}";
                    $this->insertar('plan_asignatura', ['cod_pas' => $plan, 'cod_gac' => $grupo, 'cod_asi' => "ASI_DEMO_{$materia}", 'cod_doc' => "DOC_DEMO_{$grado}_{$turno}_{$materia}", 'fii_pas' => $inicio, 'ffi_pas' => $fin, 'hor_pas' => 10]);
                    $this->insertar('clase_virtual', ['cod_cla' => $clase, 'cod_pas' => $plan, 'nom_cla' => "Demostración {$grado}° · Materia {$materia} · {$anio}", 'fec_ini_cla' => $inicio, 'fec_fin_cla' => $fin, 'est_cla' => $actual ? 'ACTIVA' : 'CERRADA', 'vis_cla' => true]);
                }
                foreach (['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'] as $dia => $nombre) {
                    foreach ([1, 2, 3, 5, 6, 7] as $indice => $bloque) {
                        $this->insertar('horario_detalle', ['cod_hde' => "HD_{$anio}_{$grado}_{$turno}_{$dia}_{$bloque}", 'cod_hor' => $horario, 'cod_hbl' => "HBL_DEMO_{$turno}_{$bloque}", 'dia_hde' => $nombre, 'cod_pas' => "PAS_D_{$anio}_{$grado}_{$turno}_".(intdiv($indice, 2) + 1), 'aul_hde' => "Aula demo {$grado}-{$turno}"]);
                    }
                }
                // Cada estudiante avanza un grado por año; sexto tiene las seis gestiones.
                $gradoFinal = $grado + $ultimo - $anio;
                if ($gradoFinal > 6) {
                    continue;
                }
                $estudiante = "EST_DEMO_{$gradoFinal}_{$turno}";
                $inscripcion = "INS_D_{$anio}_{$gradoFinal}_{$turno}";
                $this->insertar('inscripcion_estudiante', ['cod_ins' => $inscripcion, 'cod_est' => $estudiante, 'cod_gea' => "GEA_DEMO_{$anio}", 'cod_cur' => "CUR_DEMO_{$grado}", 'cod_par' => 'PAR_DEMO_A', 'cod_tur' => "TUR_DEMO_{$turno}", 'fei_ins' => $inicio, 'est_ins' => $actual ? 'ACTIVA' : 'ARCHIVADA', 'doc_com_ins' => true, 'obs_ins' => 'Datos sintéticos. Sin documentos personales reales.']);
                $this->insertar('inscripcion_vigencia', ['cod_ivg' => "IVG_D_{$anio}_{$gradoFinal}_{$turno}", 'cod_ins' => $inscripcion, 'cod_gac' => $grupo, 'fii_ivg' => $inicio, 'ffi_ivg' => $fin, 'est_ivg' => $actual ? 'ACTIVO' : 'CERRADO']);
                for ($materia = 1; $materia <= 3; $materia++) {
                    $this->insertar('clase_estudiante', ['cod_cla_est' => "CE_{$anio}_{$gradoFinal}_{$turno}_{$materia}", 'cod_cla' => "CLA_D_{$anio}_{$grado}_{$turno}_{$materia}", 'cod_est' => $estudiante, 'fec_inc_cla_est' => $inicio, 'fec_ret_cla_est' => $actual ? null : $fin, 'est_cla_est' => $actual ? 'ACTIVO' : 'INACTIVO']);
                    foreach ($periodos as $periodo => [$desde, $hasta]) {
                        if ("{$anio}-{$hasta}" > now('America/La_Paz')->toDateString()) {
                            continue;
                        }
                        $this->insertar('calificacion', ['cod_cal' => 'CAL_DEMO_'.str_pad((string) ++$this->nota, 6, '0', STR_PAD_LEFT), 'cod_ins' => $inscripcion, 'cod_pas' => "PAS_D_{$anio}_{$grado}_{$turno}_{$materia}", 'cod_pev' => 'PEV_DEMO_'.($periodo + 1), 'fea_cal' => "{$anio}-{$hasta}", 'not_cal' => 65 + (($anio + $grado + $materia + $turno + $periodo) % 30), 'obs_cal' => 'Calificación sintética de demostración.']);
                    }
                }
                if (! $actual) {
                    $this->insertar('resultado_anual', ['cod_ran' => "RAN_D_{$anio}_{$gradoFinal}_{$turno}", 'cod_ins' => $inscripcion, 'res_ran' => 'PROMOVIDO', 'fec_ran' => $fin, 'cod_usu' => 'USU_DEMO_ADMIN', 'ref_ran' => "DEMO-{$anio}-{$gradoFinal}-{$turno}", 'obs_ran' => 'Promoción sintética para demostrar una trayectoria; no es una decisión institucional.']);
                }
            }
        }
    }

    private function persona(string $codigo, string $nombre, string $apellido, string $ci): void
    {
        $this->insertar('persona', ['cod_per' => $codigo, 'nom_per' => $nombre, 'ape_pat_per' => $apellido, 'ci_per' => $ci]);
    }

    private function cuenta(string $codigo, string $persona, string $correo, string $password, string $rol): void
    {
        $user = User::create(['cod_usu' => $codigo, 'cod_per' => $persona, 'email' => $correo, 'password' => Hash::make($password), 'email_verified_at' => now(), 'est_usu' => 'ACTIVO', 'auth_provider' => 'local']);
        $user->assignRole($rol);
    }

    private function insertar(string $tabla, array $datos): void
    {
        DB::table($tabla)->insert($datos + ['created_at' => now(), 'updated_at' => now()]);
    }
}
