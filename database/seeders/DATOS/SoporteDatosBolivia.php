<?php

namespace Database\Seeders\DATOS;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Throwable;

class SoporteDatosBolivia
{
    public const GESTION = 'GEA_2026';
    public const TOTAL_ESTUDIANTES = 180;
    public const TOTAL_DOCENTES = 32;

    public static array $resumen = [
        'personas_estudiantes' => 0,
        'usuarios_estudiantes' => 0,
        'docentes' => 0,
        'administrativos' => 0,
        'estudiantes' => 0,
        'inscripciones' => 0,
        'planes' => 0,
        'calificaciones' => 0,
        'aulas' => 0,
        'materiales' => 0,
        'tareas' => 0,
        'entregas' => 0,
        'vocacionales' => 0,
    ];

    public static array $advertencias = [];

    public function info(string $mensaje): void
    {
        echo $mensaje . PHP_EOL;
    }

    public function advertir(string $mensaje): void
    {
        self::$advertencias[] = $mensaje;
        $this->info('ADVERTENCIA: ' . $mensaje);
    }

    public function existeTabla(string $tabla): bool
    {
        if (! Schema::hasTable($tabla)) {
            $this->advertir("Tabla {$tabla} omitida porque no existe.");
            return false;
        }

        return true;
    }

    public function columnas(string $tabla): array
    {
        return Schema::hasTable($tabla) ? Schema::getColumnListing($tabla) : [];
    }

    public function insertarSeguro(string $tabla, array $claveUnica, array $datos): bool
    {
        if (! Schema::hasTable($tabla)) {
            $this->advertir("No se insertaron datos en {$tabla} porque la tabla no existe.");
            return false;
        }

        $columnas = $this->columnas($tabla);
        $ahora = Carbon::now();

        if (in_array('created_at', $columnas, true)) {
            $datos['created_at'] = $datos['created_at'] ?? $ahora;
        }
        if (in_array('updated_at', $columnas, true)) {
            $datos['updated_at'] = $ahora;
        }

        $clave = array_intersect_key($claveUnica, array_flip($columnas));
        $datosFiltrados = array_intersect_key($datos, array_flip($columnas));

        if ($clave === []) {
            $this->advertir("No se pudo insertar en {$tabla}: clave unica sin columnas validas.");
            return false;
        }

        DB::table($tabla)->updateOrInsert($clave, $datosFiltrados);
        return true;
    }

    public function asegurarGestion2026(): ?string
    {
        if (! Schema::hasTable('gestion_academica')) {
            $this->advertir('Gestion academica 2026 omitida porque no existe la tabla gestion_academica.');
            return null;
        }

        $this->insertarSeguro('gestion_academica', ['cod_gea' => self::GESTION], [
            'cod_gea' => self::GESTION,
            'ani_gea' => 2026,
            'fii_gea' => '2026-02-03',
            'ffi_gea' => '2026-11-30',
            'est_gea' => 'ACTIVO',
        ]);

        return self::GESTION;
    }

    public function catalogosBase(): ?array
    {
        foreach (['institucion_procedencia', 'curso', 'paralelo', 'turno', 'asignatura', 'especialidad_tecnica', 'periodo_evaluacion', 'tipo_vinculacion_estudiante'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                $this->advertir("Catalogo base {$tabla} no existe; se omiten datos dependientes.");
                return null;
            }
        }

        $catalogos = [
            'instituciones' => DB::table('institucion_procedencia')->where('est_ipe', 'ACTIVO')->get(),
            'cursos' => DB::table('curso')->where('est_cur', 'ACTIVO')->orderBy('cod_cur')->get(),
            'paralelos' => DB::table('paralelo')->where('est_par', 'ACTIVO')->orderBy('nom_par')->get(),
            'turnos' => DB::table('turno')->where('est_tur', 'ACTIVO')->orderByRaw("CASE WHEN UPPER(nom_tur) LIKE '%MA%' THEN 1 WHEN UPPER(nom_tur) LIKE '%TAR%' THEN 2 ELSE 3 END")->get(),
            'asignaturas' => DB::table('asignatura')->where('est_asi', 'ACTIVO')->orderBy('cod_asi')->get(),
            'especialidades' => DB::table('especialidad_tecnica')->where('est_esp', 'ACTIVO')->orderBy('cod_esp')->get(),
            'periodos' => DB::table('periodo_evaluacion')->where('est_pev', 'ACTIVO')->orderBy('ord_pev')->get(),
            'vinculaciones' => DB::table('tipo_vinculacion_estudiante')->where('est_tve', 'ACTIVO')->orderBy('cod_tve')->get(),
        ];

        foreach ($catalogos as $nombre => $items) {
            if ($items->isEmpty()) {
                $this->advertir("No hay registros activos en {$nombre}; se omiten datos dependientes.");
                return null;
            }
        }

        $this->info('No se crearon instituciones de procedencia; se usaron unicamente las existentes.');

        return $catalogos;
    }

    public function crearPersonasBase(): void
    {
        if (! $this->existeTabla('persona') || ! $this->existeTabla('users')) {
            return;
        }

        $this->crearAdministrativos();

        foreach ($this->docentesBase() as $i => $docente) {
            $n = $i + 1;
            $this->crearPersonaUsuario(
                'PER_DOC_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'USU_DOC_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                $docente['nombres'],
                $docente['paterno'],
                $docente['materno'],
                '741' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                $docente['genero'],
                'docente',
                800 + $n,
                Carbon::create(1980 + ($n % 17), (($n % 12) + 1), (($n % 27) + 1))->format('Y-m-d')
            );
        }

        $nombresF = ['Camila', 'Valeria', 'Daniela', 'Mariana', 'Lucia', 'Fernanda', 'Gabriela', 'Andrea', 'Carla', 'Natalia', 'Noelia', 'Lizeth', 'Yessenia', 'Eliana', 'Paola', 'Monica', 'Anahi', 'Sofia', 'Roxana', 'Vanessa', 'Maria Fernanda', 'Katherine', 'Estefany', 'Claudia', 'Ruth', 'Fabiola', 'Dayana', 'Alejandra', 'Patricia', 'Laura', 'Ximena', 'Abigail', 'Lourdes', 'Cecilia', 'Miriam', 'Maribel', 'Karen', 'Pamela', 'Erika'];
        $nombresM = ['Mateo', 'Santiago', 'Diego', 'Rodrigo', 'Adrian', 'Sebastian', 'Mauricio', 'Javier', 'Franz', 'Alvaro', 'Brayan', 'Kevin', 'Cristian', 'Daniel', 'Luis', 'Marcelo', 'Leonardo', 'Gabriel', 'Nicolas', 'Rene', 'Jhonatan', 'Erick', 'Andres', 'Miguel', 'Carlos', 'Pablo', 'Ivan', 'Alejandro', 'Samuel', 'Victor', 'Jose Luis', 'Juan Carlos', 'Marco Antonio', 'Fernando', 'Ariel', 'Ronaldo', 'Joel', 'Milton', 'Edwin', 'Ramiro'];
        $apellidos = ['Quispe', 'Mamani', 'Choque', 'Condori', 'Apaza', 'Flores', 'Huanca', 'Laura', 'Callisaya', 'Calle', 'Vargas', 'Rojas', 'Paredes', 'Gutierrez', 'Mendoza', 'Fernandez', 'Sanchez', 'Copa', 'Ticona', 'Nina', 'Mollo', 'Aruquipa', 'Catari', 'Chambi', 'Villca', 'Poma', 'Cruz', 'Ramos', 'Aguilar', 'Aliaga'];

        for ($i = 1; $i <= self::TOTAL_ESTUDIANTES; $i++) {
            $genero = $i % 2 === 0 ? 'FEMENINO' : 'MASCULINO';
            $nombre = $genero === 'FEMENINO' ? $nombresF[($i - 1) % count($nombresF)] : $nombresM[($i - 1) % count($nombresM)];
            $paterno = $apellidos[($i - 1) % count($apellidos)];
            $materno = $apellidos[($i + 7) % count($apellidos)];
            $edad = 11 + intdiv($i - 1, 30);

            $this->crearPersonaUsuario(
                'PER_EST_' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'USU_EST_' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                $nombre,
                $paterno,
                $materno,
                '740' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                $genero,
                'estudiante',
                $i,
                Carbon::create(2026 - $edad, (($i % 12) + 1), (($i % 27) + 1))->format('Y-m-d')
            );
        }
    }

    private function crearPersonaUsuario(string $codPer, string $codUsu, string $nombres, string $paterno, string $materno, string $ci, string $genero, string $tipo, int $numero, string $fechaNacimiento): void
    {
        $correo = $this->correo($nombres, $paterno, $numero);

        $this->insertarSeguro('persona', ['cod_per' => $codPer], [
            'cod_per' => $codPer,
            'nom_per' => $nombres,
            'ape_pat_per' => $paterno,
            'ape_mat_per' => $materno,
            'ci_per' => $ci,
            'exp_per' => 'LP',
            'fec_nac_per' => $fechaNacimiento,
            'gen_per' => $genero,
            'tel_per' => $this->telefono($numero),
            'ema_per' => $correo,
            'dir_per' => $this->direccion($numero),
            'est_per' => true,
        ]);

        $this->insertarSeguro('users', ['cod_usu' => $codUsu], [
            'cod_usu' => $codUsu,
            'cod_per' => $codPer,
            'email' => $correo,
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('SavpTis3.2026'),
            'est_usu' => 'ACTIVO',
        ]);

        $rol = $tipo === 'docente' ? 'Docente' : ($tipo === 'estudiante' ? 'Estudiante' : null);
        if ($rol) {
            $this->asignarRol($codUsu, $rol);
        }

        if ($tipo === 'estudiante') {
            self::$resumen['personas_estudiantes']++;
            self::$resumen['usuarios_estudiantes']++;
        }
    }

    public function crearAdministrativos(): void
    {
        $administrativos = [
            ['Director', 'Silvia Gabriela', 'Rojas', 'Mendoza', 'FEMENINO'],
            ['Secretaria', 'Ana Maria', 'Vargas', 'Nina', 'FEMENINO'],
            ['Regente', 'Victor Hugo', 'Copa', 'Laura', 'MASCULINO'],
            ['Administrador', 'Jorge Ivan', 'Mendoza', 'Copa', 'MASCULINO'],
            ['Orientador', 'Carolina Vanessa', 'Ramos', 'Quispe', 'FEMENINO'],
            ['Coordinador', 'Alvaro Sebastian', 'Sanchez', 'Huanca', 'MASCULINO'],
        ];

        foreach ($administrativos as $i => [$cargo, $nombres, $paterno, $materno, $genero]) {
            $n = $i + 1;
            $codPer = 'PER_ADM_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            $codUsu = 'USU_ADM_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            $codPin = 'PIN_ADM_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);

            $this->crearPersonaUsuario($codPer, $codUsu, $nombres, $paterno, $materno, '742' . str_pad((string) $n, 4, '0', STR_PAD_LEFT), $genero, 'administrativo', 900 + $n, Carbon::create(1978 + $n, $n, 10 + $n)->format('Y-m-d'));
            $this->insertarSeguro('personal_institucional', ['cod_pin' => $codPin], [
                'cod_pin' => $codPin,
                'cod_per' => $codPer,
                'car_pin' => $cargo,
                'est_pin' => 'ACTIVO',
            ]);
            $this->asignarRol($codUsu, $cargo);
            self::$resumen['administrativos']++;
        }
    }

    public function crearDocentes(): void
    {
        if (! $this->existeTabla('personal_institucional') || ! $this->existeTabla('docente')) {
            return;
        }

        $especialidades = Schema::hasTable('especialidad_tecnica') ? DB::table('especialidad_tecnica')->where('est_esp', 'ACTIVO')->pluck('nom_esp')->values() : collect();

        foreach ($this->docentesBase() as $i => $docente) {
            $n = $i + 1;
            $codPer = 'PER_DOC_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            $codPin = 'PIN_DOC_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            $codDoc = 'DOC_DAT_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            $esp = $especialidades->isNotEmpty() ? $especialidades[$i % $especialidades->count()] : $docente['area'];

            $this->insertarSeguro('personal_institucional', ['cod_pin' => $codPin], [
                'cod_pin' => $codPin,
                'cod_per' => $codPer,
                'car_pin' => 'Docente',
                'est_pin' => 'ACTIVO',
            ]);
            $this->insertarSeguro('docente', ['cod_doc' => $codDoc], [
                'cod_doc' => $codDoc,
                'cod_pin' => $codPin,
                'esp_doc' => $esp,
                'num_mod_doc' => 0,
                'est_doc' => 'ACTIVO',
            ]);
            self::$resumen['docentes']++;
        }
    }

    public function crearEstudiantes(): void
    {
        $catalogos = $this->catalogosBase();
        if (! $catalogos || ! $this->existeTabla('estudiante')) {
            return;
        }

        for ($i = 1; $i <= self::TOTAL_ESTUDIANTES; $i++) {
            $codEst = 'EST_DAT_' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $this->insertarSeguro('estudiante', ['cod_est' => $codEst], [
                'cod_est' => $codEst,
                'rud_est' => '2026' . str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'cod_per' => 'PER_EST_' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'cod_tve' => $catalogos['vinculaciones'][($i - 1) % $catalogos['vinculaciones']->count()]->cod_tve,
                'cod_ipe' => $catalogos['instituciones'][($i - 1) % $catalogos['instituciones']->count()]->cod_ipe,
                'cod_esp' => $catalogos['especialidades'][($i - 1) % $catalogos['especialidades']->count()]->cod_esp,
                'est_est' => 'ACTIVO',
            ]);
            self::$resumen['estudiantes']++;
        }
    }

    public function crearInscripciones(): void
    {
        $catalogos = $this->catalogosBase();
        if (! $catalogos || ! $this->asegurarGestion2026() || ! $this->existeTabla('inscripcion_estudiante')) {
            return;
        }

        $estudiantes = DB::table('estudiante')->where('cod_est', 'like', 'EST_DAT_%')->orderBy('cod_est')->get();
        if ($estudiantes->isEmpty()) {
            $this->advertir('No hay estudiantes institucionales para inscribir.');
            return;
        }

        foreach ($estudiantes as $i => $estudiante) {
            $n = $i + 1;
            $curso = $catalogos['cursos'][min(5, intdiv($i, 30)) % $catalogos['cursos']->count()];
            $paralelo = $catalogos['paralelos'][$i % min(4, $catalogos['paralelos']->count())];
            $turno = $catalogos['turnos'][$i % min(2, $catalogos['turnos']->count())];
            $tip = $n % 25 < 20 ? 'REGULAR' : ($n % 25 < 23 ? 'TRASLADO' : 'REGULAR');
            $con = $n % 20 < 15 ? 'NORMAL' : ($n % 20 < 19 ? 'CONDICIONAL' : 'OBSERVADA');

            $this->insertarSeguro('inscripcion_estudiante', ['cod_est' => $estudiante->cod_est, 'cod_gea' => self::GESTION], [
                'cod_ins' => 'INS_DAT_' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'cod_est' => $estudiante->cod_est,
                'cod_gea' => self::GESTION,
                'cod_cur' => $curso->cod_cur,
                'cod_par' => $paralelo->cod_par,
                'cod_tur' => $turno->cod_tur,
                'fei_ins' => Carbon::create(2026, 2, 3)->addDays($i % 18)->format('Y-m-d'),
                'tip_ins' => $tip,
                'con_ins' => $con,
                'est_ins' => 'ACTIVA',
                'pro_ins' => 'INSCRITO',
                'obs_ins' => $this->observacionInscripcion($n),
                'mot_obs_ins' => $con === 'NORMAL' ? null : 'Seguimiento administrativo registrado durante el proceso de inscripcion.',
                'doc_com_ins' => $n % 8 !== 0,
                'sob_aut_ins' => $n % 11 === 0,
                'sie_ins' => true,
                'fec_sie_ins' => Carbon::create(2026, 2, 20, 10, 0)->addDays($i % 10),
                'cod_esp_tec' => $estudiante->cod_esp,
                'est_esp_tec_ins' => $estudiante->cod_esp ? 'ASIGNADA' : 'NO_APLICA',
                'obs_esp_tec_ins' => 'Especialidad tecnica asignada segun disponibilidad institucional y perfil formativo.',
                'fec_con_ins' => Carbon::create(2026, 2, 21, 9, 0)->addDays($i % 5),
            ]);
            self::$resumen['inscripciones']++;
        }
    }

    public function crearPlanesAsignatura(): void
    {
        $catalogos = $this->catalogosBase();
        if (! $catalogos || ! $this->asegurarGestion2026() || ! $this->existeTabla('plan_asignatura')) {
            return;
        }

        $docentes = DB::table('docente')->where('est_doc', 'ACTIVO')->orderBy('cod_doc')->get();
        if ($docentes->isEmpty()) {
            $this->advertir('Planes de asignatura omitidos porque no hay docentes activos.');
            return;
        }

        $limiteParalelos = min(3, $catalogos['paralelos']->count());
        $limiteTurnos = min(2, $catalogos['turnos']->count());
        $contador = 1;

        foreach ($catalogos['cursos'] as $curso) {
            foreach ($catalogos['paralelos']->take($limiteParalelos) as $paralelo) {
                foreach ($catalogos['turnos']->take($limiteTurnos) as $turno) {
                    foreach ($catalogos['asignaturas'] as $a => $asignatura) {
                        if ($contador > 300) {
                            break 4;
                        }
                        $docente = $docentes[($contador + $a) % $docentes->count()];
                        $this->insertarSeguro('plan_asignatura', [
                            'cod_asi' => $asignatura->cod_asi,
                            'cod_doc' => $docente->cod_doc,
                            'cod_cur' => $curso->cod_cur,
                            'cod_par' => $paralelo->cod_par,
                            'cod_tur' => $turno->cod_tur,
                            'cod_gea' => self::GESTION,
                        ], [
                            'cod_pas' => 'PAS_DAT_' . str_pad((string) $contador, 4, '0', STR_PAD_LEFT),
                            'cod_asi' => $asignatura->cod_asi,
                            'cod_doc' => $docente->cod_doc,
                            'cod_cur' => $curso->cod_cur,
                            'cod_par' => $paralelo->cod_par,
                            'cod_tur' => $turno->cod_tur,
                            'cod_gea' => self::GESTION,
                            'hor_pas' => $asignatura->hor_asi ?? (($a % 3) + 2),
                            'est_pas' => 'ACTIVO',
                        ]);
                        $contador++;
                        self::$resumen['planes']++;
                    }
                }
            }
        }

        $this->crearPlanesEspecialidad($docentes, $catalogos);
    }

    private function crearPlanesEspecialidad($docentes, array $catalogos): void
    {
        if (! Schema::hasTable('plan_especialidad')) {
            $this->advertir('Relaciones docente especialidad omitidas porque no existe la tabla plan_especialidad.');
            return;
        }

        $contador = 1;
        foreach ($catalogos['especialidades'] as $e => $especialidad) {
            for ($j = 0; $j < 2; $j++) {
                $docente = $docentes[($e * 2 + $j) % $docentes->count()];
                $curso = $catalogos['cursos'][min(3 + ($e % 3), $catalogos['cursos']->count() - 1)];
                $paralelo = $catalogos['paralelos'][$j % $catalogos['paralelos']->count()];
                $turno = $catalogos['turnos'][$j % min(2, $catalogos['turnos']->count())];

                $this->insertarSeguro('plan_especialidad', [
                    'cod_esp' => $especialidad->cod_esp,
                    'cod_doc' => $docente->cod_doc,
                    'cod_cur' => $curso->cod_cur,
                    'cod_par' => $paralelo->cod_par,
                    'cod_tur' => $turno->cod_tur,
                    'cod_gea' => self::GESTION,
                ], [
                    'cod_pes' => 'PES_DAT_' . str_pad((string) $contador, 4, '0', STR_PAD_LEFT),
                    'cod_esp' => $especialidad->cod_esp,
                    'cod_doc' => $docente->cod_doc,
                    'cod_cur' => $curso->cod_cur,
                    'cod_par' => $paralelo->cod_par,
                    'cod_tur' => $turno->cod_tur,
                    'cod_gea' => self::GESTION,
                    'hor_pes' => 4,
                    'est_pes' => 'ACTIVO',
                ]);
                $contador++;
            }
        }
    }

    public function crearCalificaciones(): void
    {
        $catalogos = $this->catalogosBase();
        if (! $catalogos || ! $this->existeTabla('calificacion')) {
            return;
        }

        $inscripciones = DB::table('inscripcion_estudiante')->where('cod_gea', self::GESTION)->where('est_ins', 'ACTIVA')->orderBy('cod_est')->get();
        if ($inscripciones->isEmpty()) {
            $this->advertir('Calificaciones omitidas porque no existen inscripciones activas para 2026.');
            return;
        }

        $contador = 1;
        foreach ($inscripciones as $i => $inscripcion) {
            $perfil = $this->perfilRendimiento($i);
            foreach ($catalogos['asignaturas'] as $a => $asignatura) {
                foreach ($catalogos['periodos'] as $p => $periodo) {
                    $nota = $this->nota($perfil, $i, $a, $p);
                    $this->insertarSeguro('calificacion', [
                        'cod_est' => $inscripcion->cod_est,
                        'cod_asi' => $asignatura->cod_asi,
                        'cod_pev' => $periodo->cod_pev,
                    ], [
                        'cod_cal' => 'CALD' . str_pad((string) $contador, 8, '0', STR_PAD_LEFT),
                        'cod_est' => $inscripcion->cod_est,
                        'cod_asi' => $asignatura->cod_asi,
                        'cod_pev' => $periodo->cod_pev,
                        'not_cal' => $nota,
                        'obs_cal' => $this->observacionCalificacion($nota, $contador),
                        'est_cal' => 'ACTIVO',
                    ]);
                    $contador++;
                    self::$resumen['calificaciones']++;
                }
            }
        }
    }

    public function crearAulaVirtual(): void
    {
        foreach (['clase_virtual', 'material_clase', 'tarea', 'entrega_tarea'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                $this->advertir('Aula virtual omitida porque no existen tablas completas.');
                return;
            }
        }

        $planes = DB::table('plan_asignatura')->where('cod_gea', self::GESTION)->where('est_pas', 'ACTIVO')->limit(80)->get();
        $estudiantes = DB::table('inscripcion_estudiante')->where('cod_gea', self::GESTION)->where('est_ins', 'ACTIVA')->pluck('cod_est')->values();
        if ($planes->isEmpty() || $estudiantes->isEmpty()) {
            $this->advertir('Aula virtual omitida porque faltan planes o estudiantes inscritos.');
            return;
        }

        $materiales = ['Guia de ejercicios de Matematica', 'Lectura complementaria de Comunicacion y Lenguaje', 'Practica de laboratorio de Fisica', 'Ficha de analisis de Quimica', 'Material de apoyo de Ciencias Sociales', 'Guia de vocabulario de Ingles', 'Actividad tecnica aplicada', 'Manual basico de herramientas digitales', 'Hoja de trabajo de programacion', 'Guia de contabilidad basica', 'Practica de circuitos electronicos', 'Protocolo de seguridad en taller'];
        $tareas = ['Resolucion de ejercicios del tema avanzado', 'Elaboracion de resumen de lectura', 'Desarrollo de practica aplicada', 'Investigacion breve sobre el tema asignado', 'Presentacion de trabajo grupal', 'Cuestionario de reforzamiento', 'Entrega de informe tecnico', 'Actividad de analisis de caso', 'Practica de laboratorio', 'Evaluacion de aplicacion de conocimientos'];
        $retro = ['Trabajo completo y bien estructurado.', 'Debe mejorar la explicacion del procedimiento.', 'Presenta avance adecuado, aunque requiere mayor precision.', 'Se recomienda revisar los conceptos principales.', 'Cumple con los criterios solicitados.', 'Actividad entregada fuera de plazo con contenido aceptable.', 'Requiere complementar el analisis con casos aplicados.', 'Excelente desarrollo y presentacion clara.'];

        $numTarea = 1;
        foreach ($planes as $i => $plan) {
            $codCla = 'CLA_DAT_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
            $this->insertarSeguro('clase_virtual', ['cod_cla' => $codCla], [
                'cod_cla' => $codCla,
                'cod_pas' => $plan->cod_pas,
                'nom_cla' => 'Aula virtual gestion 2026',
                'des_cla' => 'Espacio de trabajo academico para actividades, materiales y seguimiento de aprendizaje.',
                'fec_ini_cla' => '2026-02-10',
                'fec_fin_cla' => '2026-11-25',
                'est_cla' => 'ACTIVA',
            ]);
            self::$resumen['aulas']++;

            $this->insertarSeguro('material_clase', ['cod_mat' => 'MAT_DAT_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)], [
                'cod_mat' => 'MAT_DAT_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'cod_cla' => $codCla,
                'cod_usu' => $this->usuarioPorDocente($plan->cod_doc),
                'nom_mat' => $materiales[$i % count($materiales)],
                'tip_mat' => 'DOCUMENTO',
                'url_mat' => 'https://recursos.savp.local/material-' . ($i + 1),
                'mime_mat' => 'application/pdf',
                'tam_mat' => 256000 + ($i * 512),
                'est_mat' => 'ACTIVO',
            ]);
            self::$resumen['materiales']++;

            $cantidadTareas = $i < 20 ? 2 : 1;
            for ($j = 0; $j < $cantidadTareas; $j++) {
                $codTar = 'TAR_DAT_' . str_pad((string) $numTarea, 3, '0', STR_PAD_LEFT);
                $this->insertarSeguro('tarea', ['cod_tar' => $codTar], [
                    'cod_tar' => $codTar,
                    'cod_cla' => $codCla,
                    'cod_doc' => $plan->cod_doc,
                    'tit_tar' => $tareas[($numTarea - 1) % count($tareas)],
                    'des_tar' => 'Actividad academica con criterios de elaboracion, entrega y revision docente.',
                    'tip_tar' => ['TAREA', 'PRACTICA', 'PROYECTO', 'INVESTIGACION', 'LABORATORIO', 'EVALUACION'][($numTarea - 1) % 6],
                    'fec_pub_tar' => Carbon::create(2026, 3, 1, 8, 0)->addDays($numTarea),
                    'fec_lim_tar' => Carbon::create(2026, 3, 8, 18, 0)->addDays($numTarea),
                    'pun_max_tar' => 100,
                    'perm_ent_tardia' => true,
                    'est_tar' => 'PUBLICADA',
                ]);
                self::$resumen['tareas']++;

                foreach ($estudiantes->slice(($numTarea * 7) % max(1, $estudiantes->count() - 12), 10) as $k => $codEst) {
                    $estado = ['ENTREGADO', 'CALIFICADO', 'PENDIENTE', 'ENTREGADO_TARDE', 'DEVUELTO'][($numTarea + $k) % 5];
                    $codEnt = 'ENTG_' . str_pad((string) $numTarea, 3, '0', STR_PAD_LEFT) . '_' . str_pad((string) ($k + 1), 2, '0', STR_PAD_LEFT);
                    $this->insertarSeguro('entrega_tarea', ['cod_ent' => $codEnt], [
                        'cod_ent' => $codEnt,
                        'cod_tar' => $codTar,
                        'cod_est' => $codEst,
                        'fec_ent' => $estado === 'PENDIENTE' ? null : Carbon::create(2026, 3, 9, 17, 0)->addDays(($numTarea + $k) % 30),
                        'tex_ent' => 'Entrega desarrollada con avance, procedimiento y conclusiones del trabajo asignado.',
                        'est_ent' => $estado,
                        'obs_ent' => $retro[($numTarea + $k) % count($retro)],
                    ]);
                    self::$resumen['entregas']++;
                }
                $numTarea++;
            }
        }
    }

    public function crearOrientacionVocacional(): void
    {
        $tablas = ['orientacion_vocacional_resultado', 'orientacion_vocacional_respuesta'];
        if (! Schema::hasTable($tablas[0]) || ! Schema::hasTable($tablas[1])) {
            $this->advertir('Orientacion vocacional omitida porque no existen tablas especificas de respuestas y resultados.');
            return;
        }

        $inscripciones = DB::table('inscripcion_estudiante')
            ->join('curso', 'curso.cod_cur', '=', 'inscripcion_estudiante.cod_cur')
            ->where('inscripcion_estudiante.cod_gea', self::GESTION)
            ->where('inscripcion_estudiante.est_ins', 'ACTIVA')
            ->where(function ($q) {
                $q->where('curso.nom_cur', 'like', '4%')
                    ->orWhere('curso.nom_cur', 'like', '5%')
                    ->orWhere('curso.nom_cur', 'like', '6%');
            })
            ->select('inscripcion_estudiante.cod_est')
            ->limit(90)
            ->get();

        $perfiles = ['Analitico-cientifico y Social-comunitario', 'Tecnico-practico y Analitico-cientifico', 'Organizativo-administrativo y Liderazgo-emprendimiento', 'Creativo-expresivo y Social-comunitario', 'Tecnico-practico y Organizativo-administrativo', 'Liderazgo-emprendimiento y Social-comunitario'];

        foreach ($inscripciones as $i => $inscripcion) {
            $codRes = 'OVR_DAT_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
            $perfil = $perfiles[$i % count($perfiles)];
            $this->insertarSeguro('orientacion_vocacional_resultado', ['cod_est' => $inscripcion->cod_est, 'cod_gea' => self::GESTION], [
                'cod_res' => $codRes,
                'cod_est' => $inscripcion->cod_est,
                'cod_gea' => self::GESTION,
                'nom_cue' => 'Explorador de Intereses Academico-Vocacionales',
                'perfil_res' => $perfil,
                'int_res' => $this->interpretacionVocacional($i),
                'fec_res' => Carbon::create(2026, 8, 5)->addDays($i % 20),
                'est_res' => 'ACTIVO',
            ]);

            for ($p = 1; $p <= 42; $p++) {
                $tipo = $p <= 30 ? 'LIKERT' : ($p <= 36 ? 'CERRADA' : ($p <= 40 ? 'MULTIPLE' : 'ABIERTA'));
                $valor = $tipo === 'LIKERT' ? (string) (1 + (($i + $p) % 5)) : $this->respuestaVocacional($tipo, $p);
                $this->insertarSeguro('orientacion_vocacional_respuesta', ['cod_est' => $inscripcion->cod_est, 'cod_gea' => self::GESTION, 'num_pre' => $p], [
                    'cod_ore' => 'ORE_DAT_' . str_pad((string) (($i * 42) + $p), 5, '0', STR_PAD_LEFT),
                    'cod_est' => $inscripcion->cod_est,
                    'cod_gea' => self::GESTION,
                    'num_pre' => $p,
                    'tip_pre' => $tipo,
                    'res_pre' => $valor,
                    'est_ore' => 'ACTIVO',
                ]);
            }
            self::$resumen['vocacionales']++;
        }
    }

    public function imprimirResumen(): void
    {
        $this->info('');
        $this->info('Datos SAVP-TIS3 generados correctamente:');
        $this->info('* Personas estudiantes: ' . self::$resumen['personas_estudiantes']);
        $this->info('* Usuarios estudiantes: ' . self::$resumen['usuarios_estudiantes']);
        $this->info('* Docentes: ' . self::$resumen['docentes']);
        $this->info('* Administrativos: ' . self::$resumen['administrativos']);
        $this->info('* Estudiantes registrados: ' . self::$resumen['estudiantes']);
        $this->info('* Inscripciones activas: ' . self::$resumen['inscripciones']);
        $this->info('* Planes de asignatura: ' . self::$resumen['planes']);
        $this->info('* Calificaciones generadas: ' . self::$resumen['calificaciones']);
        $this->info('* Aulas virtuales generadas: ' . self::$resumen['aulas']);
        $this->info('* Materiales generados: ' . self::$resumen['materiales']);
        $this->info('* Tareas generadas: ' . self::$resumen['tareas']);
        $this->info('* Entregas generadas: ' . self::$resumen['entregas']);
        $this->info('* Resultados vocacionales generados: ' . self::$resumen['vocacionales']);

        $this->info('');
        $this->info('Validaciones finales:');
        $this->info('* Estudiantes sin inscripcion: ' . $this->contarEstudiantesSinInscripcion());
        $this->info('* Inscripciones sin estudiante: ' . $this->contarHuerfanos('inscripcion_estudiante', 'estudiante', 'cod_est'));
        $this->info('* Calificaciones sin estudiante: ' . $this->contarHuerfanos('calificacion', 'estudiante', 'cod_est'));
        $this->info('* Calificaciones sin asignatura: ' . $this->contarHuerfanos('calificacion', 'asignatura', 'cod_asi'));
        $this->info('* Calificaciones sin periodo: ' . $this->contarHuerfanos('calificacion', 'periodo_evaluacion', 'cod_pev'));
        $this->info('* Estudiantes sin institucion de procedencia: ' . (Schema::hasTable('estudiante') ? DB::table('estudiante')->where('cod_est', 'like', 'EST_DAT_%')->whereNull('cod_ipe')->count() : 0));
        $this->info('* Correos generados que no terminan en @gmail.com: ' . (Schema::hasTable('users') ? DB::table('users')->where(function ($q) {
            $q->where('cod_usu', 'like', 'USU_EST_%')->orWhere('cod_usu', 'like', 'USU_DOC_%')->orWhere('cod_usu', 'like', 'USU_ADM_%');
        })->where('email', 'not like', '%@gmail.com')->count() : 0));

        if (self::$advertencias !== []) {
            $this->info('');
            $this->info('Advertencias:');
            foreach (array_unique(self::$advertencias) as $advertencia) {
                $this->info('* ' . $advertencia);
            }
        }
    }

    private function contarEstudiantesSinInscripcion(): int
    {
        if (! Schema::hasTable('estudiante') || ! Schema::hasTable('inscripcion_estudiante')) {
            return 0;
        }

        return DB::table('estudiante')
            ->where('cod_est', 'like', 'EST_DAT_%')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inscripcion_estudiante')
                    ->whereColumn('inscripcion_estudiante.cod_est', 'estudiante.cod_est')
                    ->where('inscripcion_estudiante.cod_gea', self::GESTION);
            })
            ->count();
    }

    private function contarHuerfanos(string $tabla, string $referencia, string $columna): int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasTable($referencia)) {
            return 0;
        }

        return DB::table($tabla)
            ->leftJoin($referencia, "{$referencia}.{$columna}", '=', "{$tabla}.{$columna}")
            ->whereNull("{$referencia}.{$columna}")
            ->count();
    }

    private function asignarRol(string $codUsu, string $rol): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return;
        }

        try {
            $role = Role::where('name', $rol)->first();
            if (! $role) {
                $this->advertir("Rol {$rol} no existe; no se asigno al usuario {$codUsu}.");
                return;
            }

            $usuario = \App\Models\User::find($codUsu);
            if ($usuario && ! $usuario->hasRole($role->name)) {
                $usuario->assignRole($role->name);
            }
        } catch (Throwable $e) {
            $this->advertir("No se pudo asignar rol {$rol}: {$e->getMessage()}");
        }
    }

    private function correo(string $nombres, string $paterno, int $numero): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '.', trim($nombres . '.' . $paterno)));
        $base = trim((string) preg_replace('/\.+/', '.', $base), '.');
        return $base . '.' . str_pad((string) $numero, 3, '0', STR_PAD_LEFT) . '@gmail.com';
    }

    private function telefono(int $numero): string
    {
        return (($numero % 2 === 0) ? '7' : '6') . str_pad((string) (3000000 + ($numero * 37)), 7, '0', STR_PAD_LEFT);
    }

    private function direccion(int $numero): string
    {
        $zonas = ['Zona Villa Fatima, Calle 128', 'Zona Miraflores, Calle Diaz Romero 215', 'Zona Sopocachi, Avenida 20 de Octubre 640', 'Zona San Pedro, Calle Colombia 312', 'Zona Max Paredes, Calle Buenos Aires 455', 'Zona Villa Copacabana, Calle 9 180', 'Zona Alto Obrajes, Avenida del Maestro 520', 'Zona Villa Adela, Calle 3 290', 'Zona Ciudad Satelite, Avenida 6 de Marzo 420', 'Zona Rio Seco, Calle Illampu 96', 'Zona 16 de Julio, Avenida Alfonso Ugarte 310', 'Zona Munaypata, Calle Chorolque 205', 'Zona Los Andes, Calle 5 144', 'Zona Villa Dolores, Avenida Antofagasta 360', 'Zona Senkata, Calle 7 275', 'Zona Villa Bolivar, Avenida Civica 520', 'Zona Pampahasi, Calle 18 230', 'Zona Achachicala, Calle Litoral 118', 'Zona Bajo Tejar, Calle Apolo 175', 'Zona Villa Victoria, Calle Huyustus 390'];
        return $zonas[$numero % count($zonas)];
    }

    private function docentesBase(): array
    {
        $nombres = [
            ['Marcos Antonio', 'Mamani', 'Quispe', 'MASCULINO'], ['Roxana Patricia', 'Choque', 'Vargas', 'FEMENINO'], ['Juan Carlos', 'Condori', 'Flores', 'MASCULINO'], ['Silvia Gabriela', 'Rojas', 'Mendoza', 'FEMENINO'],
            ['Edwin Marcelo', 'Huanca', 'Apaza', 'MASCULINO'], ['Ana Maria', 'Vargas', 'Nina', 'FEMENINO'], ['Luis Fernando', 'Callisaya', 'Calle', 'MASCULINO'], ['Monica Lizeth', 'Ticona', 'Aguilar', 'FEMENINO'],
            ['Pedro Javier', 'Quenta', 'Mollo', 'MASCULINO'], ['Claudia Paola', 'Fernandez', 'Ramos', 'FEMENINO'], ['Victor Hugo', 'Copa', 'Laura', 'MASCULINO'], ['Daniela Andrea', 'Paredes', 'Sanchez', 'FEMENINO'],
            ['Rene Gonzalo', 'Aliaga', 'Vargas', 'MASCULINO'], ['Fabiola Mariela', 'Chambi', 'Choque', 'FEMENINO'], ['Mauricio Javier', 'Villca', 'Poma', 'MASCULINO'], ['Patricia Alejandra', 'Cruz', 'Catari', 'FEMENINO'],
            ['Oscar Daniel', 'Aruquipa', 'Nina', 'MASCULINO'], ['Mariela Ruth', 'Gutierrez', 'Flores', 'FEMENINO'], ['Jorge Ivan', 'Mendoza', 'Copa', 'MASCULINO'], ['Carolina Vanessa', 'Ramos', 'Quispe', 'FEMENINO'],
            ['Alvaro Sebastian', 'Sanchez', 'Huanca', 'MASCULINO'], ['Natalia Sofia', 'Aguilar', 'Mamani', 'FEMENINO'], ['Miguel Angel', 'Flores', 'Condori', 'MASCULINO'], ['Katherine Lucia', 'Nina', 'Paredes', 'FEMENINO'],
            ['Gustavo Adolfo', 'Mollo', 'Rojas', 'MASCULINO'], ['Eliana Gabriela', 'Catari', 'Ticona', 'FEMENINO'], ['Samuel Rodrigo', 'Poma', 'Callisaya', 'MASCULINO'], ['Dayana Noelia', 'Laura', 'Vargas', 'FEMENINO'],
            ['Ramiro Esteban', 'Calle', 'Quispe', 'MASCULINO'], ['Lourdes Beatriz', 'Mamani', 'Nina', 'FEMENINO'], ['Joel Andres', 'Choque', 'Aguilar', 'MASCULINO'], ['Pamela Gabriela', 'Vargas', 'Condori', 'FEMENINO'],
        ];

        return array_map(fn ($item) => [
            'nombres' => $item[0],
            'paterno' => $item[1],
            'materno' => $item[2],
            'genero' => $item[3],
            'area' => 'Formacion tecnico humanistica',
        ], $nombres);
    }

    private function observacionInscripcion(int $n): string
    {
        $items = [
            'Inscripcion registrada correctamente para la gestion academica 2026.',
            'Documentacion revisada con seguimiento administrativo.',
            'Inscripcion registrada con observacion documental menor.',
            'Estudiante incorporado por traslado con documentacion respaldatoria.',
            'Estudiante reincorporado con seguimiento academico inicial.',
        ];
        return $items[$n % count($items)];
    }

    private function perfilRendimiento(int $i): string
    {
        $m = $i % 100;
        return $m < 18 ? 'destacado' : ($m < 73 ? 'regular' : ($m < 93 ? 'riesgo' : 'critico'));
    }

    private function nota(string $perfil, int $i, int $a, int $p): float
    {
        $semilla = ($i * 11 + $a * 7 + $p * 5) % 16;
        return match ($perfil) {
            'destacado' => 85 + ($semilla % 16),
            'regular' => 61 + ($semilla % 24),
            'riesgo' => 45 + ($semilla % 16),
            default => 35 + ($semilla % 10),
        };
    }

    private function observacionCalificacion(float $nota, int $i): string
    {
        $items = $nota >= 85
            ? ['Presenta desempeno destacado, participacion constante y dominio adecuado de los contenidos.', 'Demuestra responsabilidad en evaluaciones y trabajos asignados.']
            : ($nota >= 70
                ? ['Mantiene un rendimiento favorable y cumple con las actividades academicas del periodo.', 'Mejora progresivamente en el desarrollo de actividades practicas.']
                : ($nota >= 61
                    ? ['Alcanza el nivel minimo esperado, aunque requiere reforzar algunos contenidos.', 'Participa en clase, pero debe mejorar la entrega puntual de actividades.']
                    : ['Requiere seguimiento academico y mayor constancia en la entrega de actividades.', 'Presenta dificultades significativas y requiere acompanamiento docente prioritario.', 'Necesita reforzar razonamiento logico y resolucion de ejercicios.', 'Requiere apoyo en comprension lectora y analisis de consignas.']));
        return $items[$i % count($items)];
    }

    private function usuarioPorDocente(string $codDoc): ?string
    {
        if (! Schema::hasTable('docente') || ! Schema::hasTable('personal_institucional')) {
            return null;
        }

        $pin = DB::table('docente')->where('cod_doc', $codDoc)->value('cod_pin');
        $per = $pin ? DB::table('personal_institucional')->where('cod_pin', $pin)->value('cod_per') : null;
        return $per && Schema::hasTable('users') ? DB::table('users')->where('cod_per', $per)->value('cod_usu') : null;
    }

    private function interpretacionVocacional(int $i): string
    {
        $items = [
            'Tus respuestas muestran afinidad por actividades de analisis, investigacion y resolucion de problemas.',
            'Tus respuestas muestran interes por actividades practicas, tecnicas y de aplicacion directa.',
            'Tus respuestas muestran preferencia por organizacion, registros, procesos y administracion de informacion.',
            'Tus respuestas muestran inclinacion por apoyar, orientar y trabajar con otras personas.',
            'Tus respuestas muestran interes por liderar proyectos, coordinar equipos y proponer iniciativas.',
            'Tus respuestas muestran afinidad por actividades creativas, expresivas y de comunicacion.',
        ];
        return $items[$i % count($items)];
    }

    private function respuestaVocacional(string $tipo, int $p): string
    {
        if ($tipo === 'ABIERTA') {
            return $p % 2 === 0
                ? 'Todavia tengo dudas entre una carrera tecnica y una carrera universitaria relacionada con mi especialidad.'
                : 'Me interesa estudiar una carrera relacionada con tecnologia porque me gusta resolver problemas con computadoras.';
        }

        return $tipo === 'MULTIPLE'
            ? 'Tecnologia; Salud; Administracion'
            : 'Prefiero actividades academicas con aplicacion practica y seguimiento docente.';
    }
}
