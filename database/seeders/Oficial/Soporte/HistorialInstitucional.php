<?php

declare(strict_types=1);

namespace Database\Seeders\Oficial\Soporte;

use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Hash;

/** Escenario sintético reproducible; nunca reconstruye hechos institucionales reales. */
final class HistorialInstitucional
{
    private array $origen;

    private array $formatos;

    private array $columnas = [];

    private array $contadores = [];

    private array $catalogos = [];

    private array $personas = [];

    private array $trayectos = [];

    private array $actual = [];

    private array $planes = [];

    private array $docUsuarios = [];

    private array $grupos = [];

    private array $preguntas = [];

    private array $ofertas = [];

    private array $buffer = [];

    private array $totales = [];

    private array $correosUsados = [];

    private string $actor;

    private string $pdf;

    private string $hashPdf;

    private int $tamPdf;

    private array $cal;

    private array $licencias = [];

    private array $estadosAsistencia = [];

    private array $fuentesAula = [];

    private array $miembros = [];

    private array $horarios = [];

    private array $instituciones = [];

    public function __construct(private Connection $db, private mixed $comando = null)
    {
        $this->formatos = require __DIR__.'/formatos_codigos.php';
    }

    private function codigo(string $tabla): string
    {
        $f = $this->formatos[$tabla] ?? throw new \LogicException('Falta formato de '.$tabla);
        if (! isset($this->contadores[$tabla])) {
            $mayor = 0;
            foreach ($this->db->table($tabla)->select($f['clave'])->cursor() as $fila) {
                if (preg_match('/^'.$f['prefijo'].'_?([0-9]+)$/D', $fila->{$f['clave']}, $m)) {
                    $mayor = max($mayor, (int) $m[1]);
                }
            }
            foreach ($this->origen[$tabla] ?? [] as $fila) {
                if (preg_match('/^'.$f['prefijo'].'_?([0-9]+)$/D', (string) ($fila[$f['clave']] ?? ''), $m)) {
                    $mayor = max($mayor, (int) $m[1]);
                }
            }
            $this->contadores[$tabla] = $mayor;
        }

        return $f['prefijo'].$f['separador'].str_pad((string) ++$this->contadores[$tabla], $f['digitos'], '0', STR_PAD_LEFT);
    }

    private function campos(string $tabla): array
    {
        return $this->columnas[$tabla] ??= $this->db->getSchemaBuilder()->getColumnListing($tabla);
    }

    private function insertar(string $tabla, array $fila, bool $lote = false): string|int|null
    {
        if (isset($this->formatos[$tabla])) {
            $clave = $this->formatos[$tabla]['clave'];
            $fila[$clave] ??= $this->codigo($tabla);
        } else {
            $clave = 'id';
            if (in_array('id', $this->campos($tabla), true)) {
                $this->contadores['id:'.$tabla] ??= 0;
                $fila['id'] ??= ++$this->contadores['id:'.$tabla];
            }
        }
        foreach ($fila as $columna => &$valor) {
            if (! in_array($columna, $this->campos($tabla), true)) {
                throw new \LogicException("Atributo ajeno al contrato: {$tabla}.{$columna}");
            }
            if (is_array($valor)) {
                $valor = json_encode($valor, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
        }
        unset($valor);
        if ($lote) {
            $this->buffer[$tabla][] = $fila;
            if ($tabla === 'asistencia_estudiante' && count($this->buffer[$tabla]) >= 500) {
                $this->vaciar($tabla);
            }
        } else {
            $this->db->table($tabla)->insert($fila);
        }
        $this->totales[$tabla] = ($this->totales[$tabla] ?? 0) + 1;

        return $fila[$clave] ?? null;
    }

    private function vaciar(?string $tabla = null): void
    {
        foreach ($tabla === null ? array_keys($this->buffer) : [$tabla] as $t) {
            if (! empty($this->buffer[$t])) {
                $this->db->table($t)->insert($this->buffer[$t]);
                $this->buffer[$t] = [];
            }
        }
    }

    private function conservar(string $tabla, array $fila): void
    {
        $fila = array_intersect_key($fila, array_flip($this->campos($tabla)));
        $clave = $this->formatos[$tabla]['clave'] ?? 'id';
        $actual = $this->db->table($tabla)->where($clave, $fila[$clave])->first();
        if ($actual) {
            foreach ($fila as $campo => $valor) {
                if ($campo === 'updated_at' || $campo === 'created_at') {
                    continue;
                }
                if ((string) $actual->$campo !== (string) $valor) {
                    throw new \RuntimeException("El registro oficial {$tabla}:{$fila[$clave]} difiere; no se sobrescribió.");
                }
            }

            return;
        }
        $this->db->table($tabla)->insert($fila);
    }

    private function aleatorio(string $clave, int $minimo, int $maximo): int
    {
        return $minimo + (int) (hexdec(substr(hash('sha256', 'SAVP-2020-2026:'.$clave), 0, 8)) % ($maximo - $minimo + 1));
    }

    private function texto(string $valor): string
    {
        return mb_strtoupper($valor, 'UTF-8');
    }

    public function preparar(): void
    {
        if (! $this->db->getSchemaBuilder()->hasColumn('calificacion', 'fea_cal')) {
            throw new \RuntimeException('Falta la migración aprobada de fecha académica; no se sustituye por created_at.');
        }
        if ($this->db->selectOne("SELECT to_regclass('public.ofi_bitacora_codigo_seq') AS secuencia")->secuencia === null) {
            throw new \RuntimeException('Falta revisar y aplicar la migración independiente del consecutivo de auditoría.');
        }
        $nullable = $this->db->selectOne("SELECT is_nullable FROM information_schema.columns WHERE table_schema='public' AND table_name='clase_virtual' AND column_name='cod_pas'");
        if ($nullable->is_nullable !== 'YES') {
            throw new \RuntimeException('Falta revisar y aplicar la corrección independiente de aulas técnicas.');
        }
        $manifiesto = json_decode(file_get_contents(__DIR__.'/../Fuentes/personal_oficial_manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        $ruta = storage_path('app/private/'.$manifiesto['ruta_privada']);
        if (! is_file($ruta) || ! hash_equals($manifiesto['sha256'], hash_file('sha256', $ruta))) {
            throw new \RuntimeException('Falta la copia privada verificada del personal oficial. No se generarán docentes.');
        }
        $this->origen = json_decode(file_get_contents($ruta), true, flags: JSON_THROW_ON_ERROR);
        $reales = __DIR__.'/../REALES/FUENTES/';
        $fuentes = json_decode(ltrim(file_get_contents(__DIR__.'/../REALES/MANIFIESTO_FUENTES.json'), "\xEF\xBB\xBF"), true, flags: JSON_THROW_ON_ERROR);
        foreach ($fuentes as $fuente) {
            $archivo = $reales.$fuente['archivo'];
            if (! is_file($archivo) || ! hash_equals($fuente['sha256'], hash_file('sha256', $archivo))) {
                throw new \RuntimeException('Fuente REAL ausente o modificada: '.$fuente['archivo']);
            }
        }
        $catalogosReales = require $reales.'catalogos_academicos_data.local.php';
        foreach (['curso', 'paralelo', 'turno', 'asignatura', 'especialidad_tecnica', 'tipo_vinculacion_estudiante', 'periodo_evaluacion'] as $t) {
            $this->origen[$t] = $catalogosReales[$t];
        }
        foreach (['plan_asignatura' => 'planes_asignatura_data.local.php', 'plan_especialidad' => 'planes_especialidad_data.local.php', 'horario' => 'horarios_data.local.php', 'horario_detalle' => 'horario_detalle_data.local.php'] as $t => $archivo) {
            $this->origen[$t] = require $reales.$archivo;
        }
        $plantillasReales = require $reales.'turnos_plantillas_bloques_data.local.php';
        $this->origen['plantilla_horaria'] = $plantillasReales['plantilla_horaria'];
        $this->origen['horario_bloque'] = $plantillasReales['horario_bloque'];
        if (count($this->origen['docente']) !== $manifiesto['docentes'] || count($this->origen['persona']) !== $manifiesto['personas']) {
            throw new \RuntimeException('El personal oficial no coincide con el manifiesto.');
        }
        foreach (['persona', 'users', 'personal_institucional', 'docente', 'roles'] as $t) {
            foreach ($this->origen[$t] as $fila) {
                $this->conservar($t, $fila);
            }
        }
        $permisosOficiales = require $reales.'roles_permisos_data.local.php';
        foreach ($permisosOficiales['permissions'] as $permiso) {
            $this->conservar('permissions', $permiso);
        }
        foreach ($permisosOficiales['role_has_permissions'] as $nombreRol => $nombresPermisos) {
            $rol = $this->db->table('roles')->where('name', $nombreRol)->where('guard_name', 'web')->value('id');
            if ($rol === null) {
                throw new \RuntimeException('Rol de la fuente REAL no encontrado: '.$nombreRol);
            }
            foreach ($nombresPermisos as $nombrePermiso) {
                $permiso = $this->db->table('permissions')->where('name', $nombrePermiso)->where('guard_name', 'web')->value('id');
                if ($permiso === null) {
                    throw new \RuntimeException('Permiso de la fuente REAL no encontrado: '.$nombrePermiso);
                }
                $vinculo = ['role_id' => $rol, 'permission_id' => $permiso];
                if (! $this->db->table('role_has_permissions')->where($vinculo)->exists()) {
                    $this->db->table('role_has_permissions')->insert($vinculo);
                }
            }
        }
        $usuarios = array_column($this->origen['users'], 'cod_usu');
        $claveUsuarioRol = config('permission.column_names.model_morph_key') ?: 'model_id';
        foreach ($this->origen['model_has_roles'] as $fila) {
            if (in_array($fila[$claveUsuarioRol], $usuarios, true) && ! $this->db->table('model_has_roles')->where($fila)->exists()) {
                $this->db->table('model_has_roles')->insert($fila);
            }
        }
        $this->actor = $usuarios[0];
        $personales = array_column($this->origen['personal_institucional'], 'cod_per', 'cod_pin');
        $porPersona = array_column($this->origen['users'], 'cod_usu', 'cod_per');
        foreach ($this->origen['docente'] as $docente) {
            $this->docUsuarios[$docente['cod_doc']] = $porPersona[$personales[$docente['cod_pin']]];
        }
        $this->db->select("SELECT set_config('app.cod_usu', ?, true)", [$this->actor]);
        foreach (['curso', 'paralelo', 'turno', 'asignatura', 'especialidad_tecnica', 'tipo_vinculacion_estudiante'] as $t) {
            foreach ($this->origen[$t] as $i => $fila) {
                $pk = $this->formatos[$t]['clave'];
                $anterior = $fila[$pk];
                if ($t === 'curso') {
                    $fila['ord_cur'] = $i + 1;
                }
                $this->conservar($t, $fila);
                $this->catalogos[$t][$anterior] = $anterior;
            }
        }
        foreach ($this->origen['periodo_evaluacion'] as $fila) {
            $this->conservar('periodo_evaluacion', $fila);
            $this->catalogos['periodo_evaluacion'][(int) $fila['ord_pev']] = $fila['cod_pev'];
        }
        foreach ($catalogosReales['estado_asistencia'] as $fila) {
            $this->conservar('estado_asistencia', $fila);
            $estado = ['P' => 'PRESENTE', 'F' => 'AUSENTE', 'T' => 'RETRASO', 'L' => 'LICENCIA', 'J' => 'JUSTIFICADO'][$fila['abr_est_asi']];
            $this->estadosAsistencia[$estado] = $fila['cod_est_asi'];
        }
        $instituciones = [
            ['cod_ipe' => 'IPE_0001', 'nom_ipe' => 'Unidad Educativa Franz Tamayo N° 3', 'tip_ipe' => 'Pública', 'ciu_ipe' => 'La Paz', 'est_ipe' => 'ACTIVO'],
            ['cod_ipe' => 'IPE_0002', 'nom_ipe' => 'Unidad Educativa Sagrado Corazón de Jesús', 'tip_ipe' => 'Convenio', 'ciu_ipe' => 'La Paz', 'est_ipe' => 'ACTIVO'],
            ['cod_ipe' => 'IPE_0003', 'nom_ipe' => 'Unidad Educativa Italia', 'tip_ipe' => 'Pública', 'ciu_ipe' => 'La Paz', 'est_ipe' => 'ACTIVO'],
            ['cod_ipe' => 'IPE_0004', 'nom_ipe' => 'Unidad Educativa Simón Bolívar', 'tip_ipe' => 'Pública', 'ciu_ipe' => 'La Paz', 'est_ipe' => 'ACTIVO'],
        ];
        foreach ($instituciones as $fila) {
            $this->conservar('institucion_procedencia', $fila);
            if ($fila['cod_ipe'] !== 'IPE_0001') {
                $this->instituciones[] = $fila['cod_ipe'];
            }
        }
        $this->pdf = 'integracion/DOCUMENTO_INSCRIPCION_Y_MATERIAL.pdf';
        if (! copy(__DIR__.'/../Documentos/ACTIVIDAD_2_CPM_PERT.pdf', storage_path('app/private/'.$this->pdf))) {
            throw new \RuntimeException('No se pudo incorporar el PDF proporcionado.');
        }
        $this->hashPdf = hash_file('sha256', storage_path('app/private/'.$this->pdf));
        $this->tamPdf = filesize(storage_path('app/private/'.$this->pdf));
        $this->crearPersonas();
        $this->crearInstrumentos();
        $this->universidades();
    }

    private function crearPersonas(): void
    {
        $apellidos = explode('|', 'QUISPE|MAMANI|CONDORI|FLORES|CHOQUE|APAZA|ROJAS|GUTIERREZ|VARGAS|HUANCA|CHAMBI|PACO|CALLISAYA|TORREZ|ARUQUIPA|YUJRA|COLQUE|LIMACHI|PLATA|RAMOS|AGUILAR|TICONA|LOPEZ|GONZALES|VILLEGAS|MENDOZA|PAREDES|CARRASCO|SALAZAR|VALDEZ|SORIA|HUARACHI');
        $masculinos = explode('|', 'LUIS FERNANDO|JOSE DANIEL|JUAN CARLOS|DIEGO ALEJANDRO|MARCO ANTONIO|DAVID ISMAEL|MIGUEL ANGEL|BRANDON JOSUE|OSCAR JAVIER|KEVIN RODRIGO|CRISTIAN EDUARDO|ANDRES SEBASTIAN|PABLO EMILIO|SERGIO GABRIEL|RENATO NICOLAS|VICTOR HUGO');
        $femeninos = explode('|', 'MARIA FERNANDA|ANA GABRIELA|DANIELA ALEJANDRA|ANDREA CAROLINA|PAOLA VALERIA|GABRIELA BELEN|CAMILA ANTONELLA|NATALIA ESTEFANIA|LUCIA ABIGAIL|JIMENA NICOLE|SOFIA ISABEL|VALERIA ELIZABETH|CARLA PATRICIA|DIANA ROCIO|MELISSA ALEJANDRA|FABIOLA ELENA');
        $calles = ['AVENIDA REPUBLICA', 'CALLE CHAPARE', 'CALLE MANURIPI', 'CALLE SORATA', 'CALLE ASUNCION', 'CALLE JULIA PAREDES', 'CALLE JOSE MARIA ASIN', 'AVENIDA KOLLASUYO'];
        $rol = $this->db->table('roles')->whereRaw('lower(name) = ?', ['estudiante'])->value('id');
        if (! $rol) {
            throw new \RuntimeException('Falta el rol institucional ESTUDIANTE.');
        }
        $credenciales = [];
        foreach (range(1, 612) as $i) {
            $inicio = $i <= 312 ? 2020 : 2021 + intdiv($i - 313, 50);
            $grado = $i <= 312 ? intdiv($i - 1, 52) + 1 : (($i % 13 === 0) ? $this->aleatorio('ingreso:'.$i, 2, 4) : 1);
            $sexo = $i % 2 ? 'M' : 'F';
            $nombres = $sexo === 'M' ? $masculinos : $femeninos;
            $nombre = $nombres[$this->aleatorio('nombre:'.$i, 0, 15)];
            $pat = $apellidos[($i - 1) % count($apellidos)];
            $mat = $apellidos[intdiv($i - 1, count($apellidos)) % count($apellidos)];
            $rude = '807'.str_pad((string) (290000000000 + $i * 7919), 12, '0', STR_PAD_LEFT);
            if ($i === 612) {
                $nombre = 'ANTONY MIGUEL';
                $pat = 'QUISPE';
                $mat = 'PLATA';
                $rude = '807301252018104';
                $grado = 1;
            }
            $fecha = ($inicio - 11 - $grado).'-'.str_pad((string) $this->aleatorio('mes:'.$i, 1, 12), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) $this->aleatorio('dia:'.$i, 1, 28), 2, '0', STR_PAD_LEFT);
            $correo = mb_strtolower(str_replace(' ', '', $nombre).'.'.$pat.($mat !== '' ? '.'.mb_substr($mat, 0, 2) : '').'@GMAIL.COM', 'UTF-8');
            if ($i !== 612) {
                $intento = 0;
                while (isset($this->correosUsados[$correo])) {
                    if (++$intento > count($nombres)) {
                        throw new \RuntimeException('No hay un nombre sintético disponible que conserve el formato del correo.');
                    }
                    $nombre = $nombres[($this->aleatorio('nombre:'.$i, 0, 15) + $intento) % count($nombres)];
                    $correo = mb_strtolower(str_replace(' ', '', $nombre).'.'.$pat.($mat !== '' ? '.'.mb_substr($mat, 0, 2) : '').'@GMAIL.COM', 'UTF-8');
                }
            }
            if (isset($this->correosUsados[$correo]) || $this->db->table('users')->whereRaw('lower(email) = ?', [strtolower($correo)])->exists()) {
                throw new \RuntimeException('El correo del escenario ya existe; no se modificó ninguna cuenta.');
            }
            $this->correosUsados[$correo] = true;
            $ci = (string) (10070000 + $i * 127);
            $per = $this->insertar('persona', ['nom_per' => $nombre, 'ape_pat_per' => $pat, 'ape_mat_per' => $mat, 'ci_per' => $ci, 'exp_per' => 'LP', 'fec_nac_per' => $fecha, 'gen_per' => $sexo, 'tel_per' => (string) (67080000 + $i * 83), 'ema_per' => $correo, 'dir_per' => $calles[$i % 8].' NRO. '.(180 + $i * 3).', VILLA VICTORIA, LA PAZ', 'est_per' => true, 'created_at' => $inicio.'-01-20 08:00:00']);
            $password = bin2hex(random_bytes(12));
            $usu = $this->insertar('users', ['cod_per' => $per, 'email' => $correo, 'password' => Hash::make($password), 'est_usu' => 'ACTIVO', 'auth_provider' => 'local', 'email_verified_at' => null, 'created_at' => $inicio.'-01-20 08:00:00']);
            $this->db->table('model_has_roles')->insert(['role_id' => $rol, 'model_type' => User::class, (config('permission.column_names.model_morph_key') ?: 'model_id') => $usu]);
            $est = $this->insertar('estudiante', ['cod_per' => $per, 'rud_est' => $rude, 'cod_tve' => ($grado > 1 && $inicio > 2020) ? 'TVE_0002' : 'TVE_0001', 'est_est' => 'ACTIVO']);
            $responsable = $this->insertar('persona', ['nom_per' => $femeninos[($i + 3) % count($femeninos)], 'ape_pat_per' => $mat, 'ape_mat_per' => $apellidos[($i + 11) % count($apellidos)], 'ci_per' => (string) (7000000 + $i * 157), 'exp_per' => 'LP', 'fec_nac_per' => ($inicio - 34 - $grado).'-'.substr($fecha, 5), 'gen_per' => 'F', 'tel_per' => (string) (72030000 + $i * 91), 'dir_per' => $calles[$i % 8].' NRO. '.(180 + $i * 3).', VILLA VICTORIA, LA PAZ', 'est_per' => true]);
            $this->insertar('estudiante_responsable', ['cod_est' => $est, 'cod_per' => $responsable, 'par_ere' => 'MADRE', 'pri_ere' => true, 'aut_ere' => true, 'con_ere' => true, 'fii_ere' => $inicio.'-01-20', 'obs_ere' => 'RESPONSABLE DEL ESCENARIO SINTETICO; NO ES UN REGISTRO REAL']);
            $this->personas[$i] = ['i' => $i, 'est' => $est, 'usu' => $usu, 'per' => $per, 'inicio' => $inicio, 'grado' => $grado, 'primer_grado' => $grado, 'pausado' => false, 'salida' => false, 'esp' => null];
            $credenciales[] = [$usu, $correo, $password];
        }
        $ruta = storage_path('app/private/integracion/ACCESOS_ESTUDIANTES.csv');
        $archivo = fopen($ruta, 'wb');
        fputcsv($archivo, ['CODIGO', 'CORREO', 'CONTRASENA'], ';');
        foreach ($credenciales as $fila) {
            fputcsv($archivo, $fila, ';');
        }
        fclose($archivo);
    }

    public function gestion(int $anio): void
    {
        $this->cal = json_decode(file_get_contents(__DIR__."/../{$anio}/calendario.json"), true, flags: JSON_THROW_ON_ERROR);
        $this->actual = [];
        $this->planes = [];
        $this->grupos = [];
        $this->licencias = [];
        $this->miembros = [];
        $this->fuentesAula = [];
        $this->horarios = [];
        $gestion = ['ani_gea' => $anio, 'fii_gea' => $this->cal['inicio'], 'ffi_gea' => $this->cal['fin'], 'est_gea' => 'ACTIVO'];
        if ($anio === 2026) {
            $gestion['cod_gea'] = $this->origen['gestion_academica'][0]['cod_gea'];
        }
        $this->cal['gea'] = $this->insertar('gestion_academica', $gestion);
        foreach ($this->cal['trimestres'] as $i => [$inicio, $fin]) {
            if ($anio === 2020) {
                if ($i > 0) {
                    continue;
                }
                $fin = '2020-08-03';
            }
            $this->insertar('configuracion_calendario_gestion', ['cod_gea' => $this->cal['gea'], 'cod_pev' => $this->catalogos['periodo_evaluacion'][$i + 1], 'fii_tri_ccg' => $inicio, 'ffi_tri_ccg' => $fin, 'est_ccg' => $anio < 2026 ? 'CERRADO' : ($i < 2 ? 'CERRADO' : 'PLANIFICADO')]);
        }
        $parejas = [];
        foreach (['plan_asignatura', 'plan_especialidad'] as $tabla) {
            foreach ($this->origen[$tabla] as $fila) {
                $key = implode('|', [$fila['cod_cur'], $fila['cod_par'], $fila['cod_tur']]);
                if (! isset($this->grupos[$key])) {
                    $this->grupos[$key] = $this->insertar('grupo_academico', ['cod_gea' => $this->cal['gea'], 'cod_cur' => $this->catalogos['curso'][$fila['cod_cur']], 'cod_par' => $this->catalogos['paralelo'][$fila['cod_par']], 'cod_tur' => $this->catalogos['turno'][$fila['cod_tur']], 'cap_gac' => 35, 'obs_gac' => 'ESCENARIO HISTORICO SINTETICO; COMPOSICION BASADA EN ASIGNACIONES OFICIALES 2026']);
                }
                $tipo = $tabla === 'plan_asignatura' ? 'pas' : 'pes';
                $materia = $fila[$tipo === 'pas' ? 'cod_asi' : 'cod_esp'];
                $datosPlan = [($tipo === 'pas' ? 'cod_asi' : 'cod_esp') => $this->catalogos[$tipo === 'pas' ? 'asignatura' : 'especialidad_tecnica'][$materia], 'cod_doc' => $fila['cod_doc'], 'cod_gac' => $this->grupos[$key], 'hor_'.$tipo => $fila['hor_'.$tipo], 'fii_'.$tipo => $this->cal['inicio'], 'ffi_'.$tipo => $this->cal['fin'], 'est_'.$tipo => $fila['est_'.$tipo]];
                if ($anio === 2026) {
                    $datosPlan['cod_'.$tipo] = $fila['cod_'.$tipo];
                }
                $plan = $this->insertar($tabla, $datosPlan);
                $this->planes[$fila['cod_'.$tipo]] = ['id' => $plan, 'tipo' => $tipo, 'materia' => $materia, 'doc' => $fila['cod_doc'], 'grupo' => $this->grupos[$key], 'key' => $key];
                $parejas[$key.'|'.$tipo.'|'.$materia] ??= $fila['cod_'.$tipo];
            }
        }
        $this->cal['principales'] = array_values($parejas);
        $this->horariosOficiales($anio);
        if ($anio === 2020) {
            $this->evento('SUSPENSION PRESENCIAL POR COVID-19', '2020-03-13', '2020-08-03', 'SIN_CLASES', 'ACTIVIDAD LIMITADA; SIN NOTAS OFICIALES. PROMOCION EXCEPCIONAL CONFIRMADA POR EL USUARIO.');
        } elseif ($this->cal['invierno']) {
            $this->evento('DESCANSO PEDAGOGICO', ...[$this->cal['invierno'][0], $this->cal['invierno'][1], 'SIN_CLASES', 'RECESO EN EL CALENDARIO SINTETICO DOCUMENTADO.']);
        }
    }

    private function evento(string $nombre, string $inicio, string $fin, string $efecto, string $motivo): void
    {
        $this->insertar('calendario_evento', ['cod_gea' => $this->cal['gea'], 'nom_cae' => $nombre, 'tip_cae' => 'CALENDARIO', 'fii_cae' => $inicio, 'ffi_cae' => $fin, 'est_cae' => 'FINALIZADO', 'efe_cae' => $efecto, 'mot_cae' => $motivo, 'created_by' => $this->actor]);
    }

    private function horariosOficiales(int $anio): void
    {
        $plantillas = [];
        $fechasPlantillas = [];
        $bloques = [];
        foreach ($this->origen['plantilla_horaria'] as $fila) {
            $id = $fila['cod_pho'];
            unset($fila['cod_pho'], $fila['created_at'], $fila['updated_at']);
            $fila['cod_tur'] = $this->catalogos['turno'][$fila['cod_tur']];
            if ($anio === 2026) {
                $fila['cod_pho'] = $id;
            } else {
                $fila['nom_pho'] = $this->texto($fila['nom_pho'].' '.$anio);
                $fila['act_pho'] = false;
                foreach (['fec_ini_pho', 'fec_fin_pho'] as $c) {
                    if ($fila[$c] ?? null) {
                        $limiteOrigen = $this->origen['gestion_academica'][0][$c === 'fec_ini_pho' ? 'fii_gea' : 'ffi_gea'];
                        $originalFecha = $fila[$c];
                        $fila[$c] = $originalFecha === $limiteOrigen ? $this->cal[$c === 'fec_ini_pho' ? 'inicio' : 'fin'] : $anio.substr($originalFecha, 4);
                        if ($c === 'fec_fin_pho' && $fila[$c] < $fila['fec_ini_pho']) {
                            $fila[$c] = $anio.substr($originalFecha, 4);
                        }
                    }
                }
            }
            $plantillas[$id] = $this->insertar('plantilla_horaria', $fila);
            $fechasPlantillas[$id] = [$fila['fec_ini_pho'], $fila['fec_fin_pho']];
        }
        foreach ($this->origen['horario_bloque'] as $fila) {
            $id = $fila['cod_hbl'];
            unset($fila['cod_hbl'], $fila['created_at'], $fila['updated_at']);
            $fila['cod_pho'] = $plantillas[$fila['cod_pho']];
            if ($anio === 2026) {
                $fila['cod_hbl'] = $id;
            }
            $bloques[$id] = $this->insertar('horario_bloque', $fila);
        }
        // Conserva TODAS las cabeceras, incluidas las técnicas sin detalles.
        $origenHorarios = array_column($this->origen['horario'], null, 'cod_hor');
        $bloquesOrigen = array_column($this->origen['horario_bloque'], null, 'cod_hbl');
        $mapaHorarios = [];
        $plantillasOrigen = array_column($this->origen['plantilla_horaria'], null, 'cod_pho');
        foreach ($origenHorarios as $original => $h) {
            $ph = $plantillasOrigen[$h['cod_pho']];
            $key = implode('|', [$h['cod_cur'], $h['cod_par'], $ph['cod_tur']]);
            $fii = max($this->cal['inicio'], $fechasPlantillas[$h['cod_pho']][0]);
            $ffi = min($this->cal['fin'], $fechasPlantillas[$h['cod_pho']][1]);
            // La clausura excepcional de 2020 impide materializar horarios posteriores.
            if ($fii > $ffi) {
                continue;
            }
            // Una cabecera oficial también define un grupo aunque aún no tenga un plan.
            $this->grupos[$key] ??= $this->insertar('grupo_academico', ['cod_gea' => $this->cal['gea'], 'cod_cur' => $this->catalogos['curso'][$h['cod_cur']], 'cod_par' => $this->catalogos['paralelo'][$h['cod_par']], 'cod_tur' => $this->catalogos['turno'][$ph['cod_tur']], 'cap_gac' => 35, 'obs_gac' => 'CONTEXTO CONSERVADO DESDE CABECERA HORARIA REAL; NO IMPLICA MATRICULA NI SESIONES GENERADAS']);
            $fila = ['cod_gac' => $this->grupos[$key], 'cod_pho' => $plantillas[$h['cod_pho']], 'fii_hor' => $fii, 'ffi_hor' => $ffi, 'obs_hor' => $h['obs_hor'], 'est_hor' => $h['est_hor']];
            if ($anio === 2026) {
                $fila['cod_hor'] = $original;
            }
            $mapaHorarios[$original] = ['id' => $this->insertar('horario', $fila), 'inicio' => $fii, 'fin' => $ffi];
        }
        foreach ($this->origen['horario_detalle'] as $detalle) {
            if (! isset($mapaHorarios[$detalle['cod_hor']])) {
                continue;
            }
            $hor = $mapaHorarios[$detalle['cod_hor']];
            $origen = $detalle['cod_pas'] ?: $detalle['cod_pes'];
            $plan = $this->planes[$origen];
            $fila = ['cod_hor' => $hor['id'], 'cod_hbl' => $bloques[$detalle['cod_hbl']], 'dia_hde' => $detalle['dia_hde'], 'cod_pas' => $plan['tipo'] === 'pas' ? $plan['id'] : null, 'cod_pes' => $plan['tipo'] === 'pes' ? $plan['id'] : null, 'aul_hde' => $detalle['aul_hde'], 'obs_hde' => $detalle['obs_hde'], 'est_hde' => $detalle['est_hde']];
            if ($anio === 2026) {
                $fila['cod_hde'] = $detalle['cod_hde'];
            }
            $hde = $this->insertar('horario_detalle', $fila);
            $this->horarios[] = ['id' => $hde, 'plan' => $origen, 'dia' => $detalle['dia_hde'], 'bloque' => $bloques[$detalle['cod_hbl']], 'horas' => $bloquesOrigen[$detalle['cod_hbl']], 'inicio' => $hor['inicio'], 'fin' => $hor['fin']];
        }
    }

    public function inscripciones(int $anio): void
    {
        $catalogoCur = array_keys($this->catalogos['curso']);
        foreach ($this->personas as $i => &$persona) {
            if ($persona['inicio'] > $anio || $persona['salida'] || $persona['grado'] > 6 || ($persona['pausado'] && $anio === 2023)) {
                continue;
            }
            $reincorporado = $persona['pausado'] && $anio === 2024;
            $grado = $persona['grado'];
            $cur = $catalogoCur[$grado - 1];
            $par = 'PAR_000'.(($i === 612) ? 2 : (($i + $anio) % 4 + 1));
            $key = $cur.'|'.$par.'|TUR_0001';
            $grupo = $this->grupos[$key];
            $retira = $anio === 2022 && $i % 71 === 0;
            $traslada = $anio === 2023 && $i % 97 === 0;
            $fin = ($retira || $traslada) ? $anio.'-06-15' : $this->cal['corte'];
            $tipo = ($persona['inicio'] === $anio && $grado > 1 && $anio > 2020) ? 'TRASLADO' : ($persona['inicio'] === $anio ? 'NUEVO' : 'REGULAR');
            $esp = null;
            // Las especialidades se asignan únicamente a grupos que ya tienen ese plan oficial.
            foreach ($this->planes as $p) {
                if ($p['tipo'] === 'pes' && $p['grupo'] === $grupo) {
                    $esp ??= $this->catalogos['especialidad_tecnica'][$p['materia']];
                }
            }
            $ins = $this->insertar('inscripcion_estudiante', ['cod_est' => $persona['est'], 'cod_gea' => $this->cal['gea'], 'cod_cur' => $this->catalogos['curso'][$cur], 'cod_par' => $this->catalogos['paralelo'][$par], 'cod_tur' => $this->catalogos['turno']['TUR_0001'], 'fei_ins' => $anio.'-01-20', 'tip_ins' => $tipo, 'est_ins' => 'ACTIVA', 'doc_com_ins' => true, 'cod_esp_tec' => $esp, 'est_esp_tec_ins' => $esp ? 'ASIGNADA' : 'NO_APLICA', 'obs_ins' => $reincorporado ? 'REINCORPORACION CON CONSERVACION DEL HISTORIAL' : null, 'fec_con_ins' => $anio.'-01-23 09:00:00']);
            $this->insertar('inscripcion_vigencia', ['cod_ins' => $ins, 'cod_gac' => $grupo, 'cod_esp_tec' => $esp, 'fii_ivg' => $this->cal['inicio'], 'ffi_ivg' => ($anio === 2026 && ! $retira && ! $traslada) ? null : $fin, 'tip_ivg' => $reincorporado ? 'REINCORPORACION' : 'INSCRIPCION', 'est_ivg' => $anio < 2026 || $retira || $traslada ? 'CERRADO' : 'ACTIVO']);
            foreach (['RUDE', 'CEDULA DE IDENTIDAD', 'CERTIFICADO DE NACIMIENTO', 'LIBRETA ESCOLAR'] as $documento) {
                $this->insertar('documento_inscripcion_estudiante', ['cod_ins' => $ins, 'nom_die' => $documento, 'tip_die' => $documento === 'RUDE' ? 'RUDE' : ($documento === 'LIBRETA ESCOLAR' ? 'ACADEMICO' : 'IDENTIDAD'), 'est_die' => 'PRESENTADO', 'obl_die' => true, 'fec_pre_die' => $anio.'-01-22', 'rut_die' => $this->pdf, 'for_die' => 'PDF', 'tam_die' => $this->tamPdf, 'has_die' => $this->hashPdf, 'obs_die' => 'ARCHIVO PROPORCIONADO POR EL USUARIO; NO ACREDITA IDENTIDAD DEL ESCENARIO SINTETICO']);
            }
            $this->actual[$i] = $persona + ['ins' => $ins, 'grupo' => $grupo, 'fin' => $fin, 'retira' => $retira, 'traslada' => $traslada, 'reincorporado' => $reincorporado];
            $this->trayectos[$i][$anio] = ['ins' => $ins, 'grupo' => $grupo, 'grado' => $grado];
            $tecnicos = array_filter($this->planes, fn ($p) => $p['tipo'] === 'pes' && explode('|', $p['key'])[0] === $cur && explode('|', $p['key'])[1] === $par);
            if ($tecnicos !== []) {
                $elegibles = array_values($tecnicos);
                $elegido = $elegibles[$this->aleatorio('especialidad:'.$i, 0, count($elegibles) - 1)];
                $especialidad = $this->catalogos['especialidad_tecnica'][$elegido['materia']];
                $this->insertar('inscripcion_vigencia', ['cod_ins' => $ins, 'cod_gac' => $elegido['grupo'], 'cod_esp_tec' => $especialidad, 'fii_ivg' => $this->cal['inicio'], 'ffi_ivg' => ($anio === 2026 && ! $retira && ! $traslada) ? null : $fin, 'tip_ivg' => 'FORMACION_TECNICA_COMPLEMENTARIA', 'est_ivg' => $anio < 2026 || $retira || $traslada ? 'CERRADO' : 'ACTIVO']);
                $this->actual[$i]['tecnica'] = ['grupo' => $elegido['grupo'], 'materia' => $elegido['materia'], 'inicio' => $this->cal['inicio'], 'fin' => $fin];
            }
            if (in_array($tipo, ['TRASLADO', 'EXTERIOR'], true)) {
                $this->traslado($this->actual[$i], $anio, $tipo);
            }
            if ($i % 9 === 0 && $anio > 2020) {
                $fecha = $anio.'-04-'.str_pad((string) $this->aleatorio('licencia:'.$i.$anio, 4, 20), 2, '0', STR_PAD_LEFT);
                $finLic = date('Y-m-d', strtotime($fecha.' +2 days'));
                $id = $this->insertar('novedad_estudiante', ['cod_ins' => $ins, 'tip_nes' => 'LICENCIA', 'fii_nes' => $fecha, 'ffi_nes' => $finLic, 'mot_nes' => $i % 18 === 0 ? 'RECUPERACION DE SALUD CON JUSTIFICATIVO FAMILIAR' : 'CITA MEDICA Y ACOMPANAMIENTO FAMILIAR', 'est_nes' => 'CERRADA', 'rut_res_nes' => $this->pdf]);
                $this->licencias[$persona['est']] = [$id, $fecha, $finLic];
            }
            if ($retira || $traslada || $reincorporado) {
                $this->insertar('novedad_estudiante', ['cod_ins' => $ins, 'tip_nes' => $retira ? 'RETIRO' : ($traslada ? 'TRASLADO' : 'REINCORPORACION'), 'fii_nes' => $retira || $traslada ? $fin : $this->cal['inicio'], 'mot_nes' => $retira ? 'INTERRUPCION POR SITUACION FAMILIAR' : ($traslada ? 'CAMBIO DE RESIDENCIA FUERA DEL DISTRITO' : 'RETORNO DESPUES DE INTERRUPCION CON HISTORIA CONSERVADA'), 'est_nes' => 'CERRADA']);
            }
        }
        unset($persona);
    }

    private function traslado(array $persona, int $anio, string $tipo): void
    {
        $ipe = $this->instituciones[$this->aleatorio('procedencia:'.$persona['i'], 0, count($this->instituciones) - 1)];
        $nombre = $this->db->table('institucion_procedencia')->where('cod_ipe', $ipe)->value('nom_ipe');
        $exp = $this->insertar('expediente_traslado', ['cod_est' => $persona['est'], 'cod_ipe' => $ipe, 'tip_ext' => 'INGRESO', 'fec_ext' => $anio.'-01-20', 'obs_ext' => 'EXPEDIENTE SINTETICO UNICO CON VARIAS GESTIONES; SIN DOCENTE LOCAL']);
        $this->insertar('documento_traslado', ['cod_ext' => $exp, 'tip_dtr' => 'LIBRETA DE TRASLADO', 'nom_dtr' => 'DOCUMENTO ADJUNTO PROPORCIONADO', 'fec_dtr' => $anio.'-01-20', 'rut_dtr' => $this->pdf, 'mim_dtr' => 'application/pdf', 'tam_dtr' => $this->tamPdf, 'has_dtr' => $this->hashPdf, 'obs_dtr' => 'ADJUNTO DEL DATASET SINTETICO; NO ACREDITA UN TRASLADO REAL']);
        $this->db->table('estudiante')->where('cod_est', $persona['est'])->update(['cod_ipe' => $ipe]);
        for ($grado = 1; $grado < $persona['grado']; $grado++) {
            $gestion = $anio - $persona['grado'] + $grado;
            if ($gestion === 2020) {
                continue;
            }
            foreach ($this->catalogos['asignatura'] as $original => $asi) {
                foreach ($this->catalogos['periodo_evaluacion'] as $n => $pev) {
                    $this->insertar('nota_traslado', ['cod_ext' => $exp, 'ani_ntr' => $gestion, 'cur_ntr' => 'SECUNDARIA '.$grado, 'mat_ntr' => $this->db->table('asignatura')->where('cod_asi', $asi)->value('nom_asi'), 'per_ntr' => 'TRIMESTRE '.$n, 'ori_ntr' => $nombre, 'not_ntr' => $this->aleatorio('traslado:'.$persona['i'].$gestion.$original.$n, 45, 94), 'esc_ntr' => '0-100', 'cod_asi' => $asi, 'cod_pev' => $pev]);
                }
            }
        }
    }

    public function aulas(int $anio): void
    {
        foreach ($this->cal['principales'] as $origen) {
            $plan = $this->planes[$origen];
            $materia = $this->db->table($plan['tipo'] === 'pas' ? 'asignatura' : 'especialidad_tecnica')->where($plan['tipo'] === 'pas' ? 'cod_asi' : 'cod_esp', $this->catalogos[$plan['tipo'] === 'pas' ? 'asignatura' : 'especialidad_tecnica'][$plan['materia']])->value($plan['tipo'] === 'pas' ? 'nom_asi' : 'nom_esp');
            $cla = $this->insertar('clase_virtual', ['cod_pas' => $plan['tipo'] === 'pas' ? $plan['id'] : null, 'cod_pes' => $plan['tipo'] === 'pes' ? $plan['id'] : null, 'nom_cla' => $materia.' '.$anio.' '.str_replace('|', ' ', $plan['key']), 'fec_ini_cla' => $this->cal['inicio'], 'fec_fin_cla' => $this->cal['fin'], 'est_cla' => 'ACTIVA', 'vis_cla' => true]);
            $this->fuentesAula[$origen] = $cla;
            foreach ($this->actual as $i => $alumno) {
                $regular = $plan['tipo'] === 'pas' && $alumno['grupo'] === $plan['grupo'];
                $tecnica = $plan['tipo'] === 'pes' && isset($alumno['tecnica']) && $alumno['tecnica']['grupo'] === $plan['grupo'] && $alumno['tecnica']['materia'] === $plan['materia'];
                if (! $regular && ! $tecnica) {
                    continue;
                }
                // Se cargan hechos y luego el cierre dentro de la misma transacción.
                $retiro = $anio < 2026 || $alumno['retira'] || $alumno['traslada'] ? date('Y-m-d', strtotime($alumno['fin'].' +1 day')) : null;
                $ultima = min($alumno['fin'], $this->cal['actividad_hasta']);
                $miembro = $this->insertar('clase_estudiante', ['cod_cla' => $cla, 'cod_est' => $alumno['est'], 'fec_inc_cla_est' => $this->cal['inicio'], 'fec_ret_cla_est' => $retiro, 'est_cla_est' => 'ACTIVO', 'cant_acc_cla_est' => $anio === 2020 ? 5 : $this->aleatorio('accesos:'.$i.$anio.$origen, 35, 180), 'ult_acc_cla_est' => $ultima.' 10:00:00', 'ult_act_cla_est' => $ultima.' 10:00:00']);
                $this->miembros[$cla][$i] = $alumno + ['miembro' => $miembro];
            }
            // Los planes sin alumnado se conservan; no se fabrican matrículas para llenarlos.
            if (empty($this->miembros[$cla])) {
                continue;
            }
            $usuario = $this->docUsuarios[$plan['doc']];
            $secciones = $anio === 2020 ? 1 : $this->cal['trimestres_registrados'];
            for ($trimestre = 1; $trimestre <= $secciones; $trimestre++) {
                [$inicio, $fin] = $this->cal['trimestres'][$trimestre - 1];
                $fin = min($fin, $this->cal['actividad_hasta']);
                $dias = $this->dias($inicio, $fin);
                $sec = $this->insertar('seccion_clase', ['cod_cla' => $cla, 'nom_sec' => 'TRIMESTRE '.$trimestre, 'ord_sec' => $trimestre]);
                $pub = $this->insertar('publicacion_clase', ['cod_cla' => $cla, 'cod_usu' => $usuario, 'cod_sec' => $sec, 'tip_pub' => 'MATERIAL', 'tit_pub' => 'PLAN DE ACTIVIDADES DE '.$materia, 'con_pub' => 'ORGANIZACION DE CONTENIDOS, EJERCICIOS, PARTICIPACION Y RETROALIMENTACION DEL TRIMESTRE '.$trimestre, 'fec_pub' => $inicio.' 08:00:00', 'est_pub' => 'PUBLICADO']);
                $mat = $this->insertar('material_clase', ['cod_cla' => $cla, 'cod_pub' => $pub, 'cod_usu' => $usuario, 'cod_sec' => $sec, 'nom_mat' => 'ARCHIVO ADJUNTO PROPORCIONADO', 'tip_mat' => 'ARCHIVO', 'rut_mat' => $this->pdf, 'mime_mat' => 'application/pdf', 'tam_mat' => $this->tamPdf, 'has_mat' => $this->hashPdf]);
                $temas = $this->temas($plan['materia'], $materia);
                $cantidad = $anio === 2020 ? 4 : $this->aleatorio('tareas:'.$origen.$anio.$trimestre, 15, 25);
                for ($n = 1; $n <= $cantidad; $n++) {
                    $pos = min(count($dias) - 4, (int) floor(($n - 1) * max(1, count($dias) - 5) / $cantidad));
                    $fecha = $dias[max(0, $pos)];
                    $limite = $dias[min(count($dias) - 2, max(0, $pos) + 2)];
                    $tema = $n === 1 ? 'PRESENTACION DE CARATULA Y ORGANIZACION DEL CUADERNO' : $temas[($n + $trimestre - 2) % count($temas)];
                    $tar = $this->insertar('tarea', ['cod_cla' => $cla, 'cod_doc' => $plan['doc'], 'cod_sec' => $sec, 'cod_pev' => $this->catalogos['periodo_evaluacion'][$trimestre], 'tit_tar' => 'TAREA #'.$n.' '.$tema, 'des_tar' => 'RESOLVER Y ARGUMENTAR LA ACTIVIDAD DE '.$materia.': '.$tema.'. PRESENTAR PROCEDIMIENTO, CONCLUSIONES Y REVISION DE ERRORES.', 'tip_tar' => $n % 7 === 0 ? 'PROYECTO' : ($n % 5 === 0 ? 'INVESTIGACION' : 'TAREA'), 'fec_pub_tar' => $fecha.' 08:00:00', 'ape_tar' => $fecha.' 08:00:00', 'fec_lim_tar' => $limite.' 18:00:00', 'cor_tar' => $dias[min(count($dias) - 1, $pos + 3)].' 23:00:00', 'int_tar' => 2, 'arc_tar' => 2, 'tam_tar' => 10485760, 'pun_max_tar' => 100, 'perm_ent_tardia' => true, 'est_tar' => 'CERRADA']);
                    $this->insertar('tarea_material', ['cod_tar' => $tar, 'nom_tar_mat' => 'ARCHIVO ADJUNTO PROPORCIONADO', 'tip_tar_mat' => 'PDF', 'rut_tar_mat' => $this->pdf, 'mime_tar_mat' => 'application/pdf', 'tam_tar_mat' => $this->tamPdf, 'has_tma' => $this->hashPdf], true);
                    foreach ($this->miembros[$cla] as $i => $alumno) {
                        if ($fecha > $alumno['fin']) {
                            continue;
                        }
                        $variacion = $this->aleatorio('entrega:'.$i.$anio.$origen.$trimestre.$n, 0, 99);
                        $pendiente = $variacion < ($i % 17 === 0 ? 16 : 4);
                        $tardia = ! $pendiente && $variacion > 86;
                        $entregaFecha = $tardia ? $dias[min(count($dias) - 1, $pos + 3)] : $limite;
                        if ($entregaFecha > $alumno['fin']) {
                            $pendiente = true;
                        }
                        $ent = $this->insertar('entrega_tarea', ['cod_tar' => $tar, 'cod_est' => $alumno['est'], 'ini_ent' => $fecha.' 09:00:00', 'int_ent' => 1, 'fec_ent' => $pendiente ? null : $entregaFecha.' '.($tardia ? '20:10:00' : '16:30:00'), 'tex_ent' => $pendiente ? null : 'DESARROLLO DE '.$tema.' CON EJEMPLOS Y CONCLUSIONES PERSONALES.', 'est_ent' => $pendiente ? 'PENDIENTE' : ($anio === 2020 ? ($tardia ? 'ENTREGADO_TARDE' : 'ENTREGADO') : 'CALIFICADO'), 'obs_ent' => $tardia ? 'ENTREGA POSTERIOR AL PLAZO, DENTRO DEL CIERRE PERMITIDO' : null], true);
                        if ($pendiente) {
                            continue;
                        }
                        $this->insertar('entrega_archivo', ['cod_ent' => $ent, 'nom_arc' => 'ACTIVIDAD PRESENTADA.pdf', 'rut_arc' => $this->pdf, 'mime_arc' => 'application/pdf', 'tam_arc' => $this->tamPdf, 'has_arc' => $this->hashPdf], true);
                        if ($anio !== 2020) {
                            $objetivo = $this->nota($alumno, $anio, $plan['materia'], $trimestre);
                            $puntaje = max(0, min(100, $objetivo + $this->aleatorio('puntaje:'.$i.$anio.$origen.$trimestre.$n, -17, 16) - ($tardia ? 5 : 0)));
                            $comentario = $puntaje < 51 ? 'REVISAR EL PROCEDIMIENTO DE '.$tema.'. COMPLETAR LOS EJERCICIOS OMITIDOS Y EXPLICAR CADA PASO.' : ($puntaje < 75 ? 'ARGUMENTACION ADECUADA EN '.$tema.'. CORREGIR LOS DETALLES SEÑALADOS Y AMPLIAR LOS EJEMPLOS.' : 'DESARROLLO CLARO DE '.$tema.'. MANTENER LA FUNDAMENTACION Y PROFUNDIZAR LAS CONCLUSIONES.');
                            $cal = $this->insertar('calificacion_tarea', ['cod_ent' => $ent, 'cod_tar' => $tar, 'cod_est' => $alumno['est'], 'cod_doc' => $plan['doc'], 'pun_obt' => $puntaje, 'pun_max' => 100, 'com_cal' => $comentario, 'fec_cal' => $entregaFecha.' 21:00:00', 'pub_cal' => $entregaFecha.' 21:30:00', 'est_cal' => 'REGISTRADO'], true);
                            if ($n === 2) {
                                $this->insertar('retroalimentacion_archivo', ['cod_cal_tar' => $cal, 'nom_raf' => 'ARCHIVO DE ACOMPAÑAMIENTO.pdf', 'rut_raf' => $this->pdf, 'mim_raf' => 'application/pdf', 'tam_raf' => $this->tamPdf, 'has_raf' => $this->hashPdf], true);
                            }
                        }
                    }
                    // Padres antes que hijos: no se suspenden FK, CHECK ni triggers.
                    foreach (['tarea_material', 'entrega_tarea', 'entrega_archivo', 'calificacion_tarea', 'retroalimentacion_archivo'] as $tabla) {
                        $this->vaciar($tabla);
                    }
                }
                if ($anio > 2020) {
                    $this->cuestionario($cla, $sec, $plan, $trimestre, $dias, $temas);
                    $this->foro($cla, $sec, $plan, $dias, $materia);
                    foreach ($this->miembros[$cla] as $alumno) {
                        if ($inicio > $alumno['fin']) {
                            continue;
                        }
                        $this->insertar('calificacion', ['cod_ins' => $alumno['ins'], 'cod_pas' => $plan['tipo'] === 'pas' ? $plan['id'] : null, 'cod_pes' => $plan['tipo'] === 'pes' ? $plan['id'] : null, 'cod_pev' => $this->catalogos['periodo_evaluacion'][$trimestre], 'not_cal' => $this->nota($alumno, $anio, $plan['materia'], $trimestre), 'fea_cal' => min($fin, $alumno['fin']), 'obs_cal' => ($alumno['i'] === 612 ? 'VALOR APORTADO POR EL USUARIO; ' : 'VALOR SINTETICO; ').'FECHA ACADEMICA SINTETICA DEL DATASET DE VALIDACION; NO ES FECHA DE ACTA INSTITUCIONAL NI PROMEDIO AUTOMATICO DE TAREAS.', 'est_cal' => 'VIGENTE', 'created_at' => min($fin, $alumno['fin']).' 18:00:00']);
                    }
                }
                foreach ($this->miembros[$cla] as $alumno) {
                    if ($inicio > $alumno['fin']) {
                        continue;
                    }
                    $this->insertar('registro_actividad_clase', ['cod_cla' => $cla, 'cod_est' => $alumno['est'], 'cod_usu' => $alumno['usu'], 'tip_rac' => 'CONSULTA_MATERIAL', 'mat_rac' => $mat, 'fec_rac' => $inicio.' 15:00:00'], true);
                }
                $this->vaciar('registro_actividad_clase');
            }
        }
    }

    private function nota(array $alumno, int $anio, string $materia, int $trimestre): int
    {
        $antony = ['ASI_0001' => [76, 93], 'ASI_0008' => [67, 45], 'ASI_0003' => [79, 55], 'ASI_0007' => [88, 97], 'ASI_0012' => [60, 51], 'ASI_0013' => [94, 64], 'ASI_0002' => [89, 78], 'ASI_0014' => [90, 97], 'ASI_0004' => [61, 62], 'ASI_0010' => [72, 82], 'ASI_0009' => [76, 68]];
        if ($alumno['i'] === 612 && $anio === 2026) {
            return $antony[$materia][$trimestre - 1] ?? throw new \RuntimeException('Materia no proporcionada para Antony: '.$materia);
        }
        if ($alumno['i'] % 17 === 0 && $anio === 2022 && $materia === 'ASI_0002') {
            return $this->aleatorio('retencion:'.$alumno['i'].$trimestre, 29, 47);
        }
        $base = $this->aleatorio('base:'.$alumno['i'].$materia, 53, 87);

        return max(51, min(100, $base + $this->aleatorio('nota:'.$alumno['i'].$anio.$materia.$trimestre, -11, 13)));
    }

    private function temas(string $codigo, string $nombre): array
    {
        $temas = [
            'ASI_0001' => ['COMPRENSION DE LECTURA', 'ESTRUCTURA DE LA ORACION', 'PRODUCCION DE TEXTOS NARRATIVOS', 'ORTOGRAFIA Y ACENTUACION', 'ANALISIS DE POEMAS', 'ARGUMENTACION ORAL', 'LECTURA DE AUTORES BOLIVIANOS', 'REDACCION DE UN ENSAYO'],
            'ASI_0002' => ['OPERACIONES CON NUMEROS ENTEROS', 'FRACCIONES Y PROPORCIONES', 'ECUACIONES Y VERIFICACION', 'GEOMETRIA Y MEDIDAS', 'INTERPRETACION DE GRAFICOS', 'RAZONAMIENTO ALGEBRAICO', 'ESTADISTICA DEL ENTORNO', 'RESOLUCION DE PROBLEMAS'],
            'ASI_0003' => ['HISTORIA DE BOLIVIA', 'GEOGRAFIA DEL TERRITORIO', 'DERECHOS Y RESPONSABILIDADES', 'PATRIMONIO DE LA PAZ', 'DIVERSIDAD CULTURAL', 'LINEA DE TIEMPO HISTORICA', 'ORGANIZACION COMUNITARIA', 'ANALISIS DE FUENTES'],
            'ASI_0004' => ['CELULA Y ORGANISMOS', 'ECOSISTEMAS DEL ALTIPLANO', 'CUIDADO DEL AGUA', 'SALUD Y NUTRICION', 'BIODIVERSIDAD BOLIVIANA', 'CLIMA Y RELIEVE', 'SISTEMAS DEL CUERPO', 'OBSERVACION CIENTIFICA'],
            'ASI_0005' => ['MAGNITUDES Y UNIDADES', 'MOVIMIENTO Y VELOCIDAD', 'FUERZAS Y EQUILIBRIO', 'ENERGIA Y TRABAJO', 'ELECTRICIDAD', 'PRESION Y DENSIDAD'],
            'ASI_0006' => ['ATOMOS Y ELEMENTOS', 'MEZCLAS Y SOLUCIONES', 'FORMULACION QUIMICA', 'REACCIONES Y BALANCEO', 'SEGURIDAD DE LABORATORIO', 'QUIMICA DEL ENTORNO'],
            'ASI_0007' => ['CALENTAMIENTO Y MOVILIDAD', 'COORDINACION MOTRIZ', 'REGLAS DE VOLEIBOL', 'TECNICA DE BALONCESTO', 'ATLETISMO', 'HABITOS DE VIDA SALUDABLE'],
            'ASI_0008' => ['PERSONAL INTRODUCTIONS', 'DAILY ROUTINES', 'VERB TO BE', 'READING COMPREHENSION', 'VOCABULARY AT SCHOOL', 'SIMPLE PRESENT', 'DESCRIBING MY COMMUNITY'],
            'ASI_0009' => ['RESPETO Y SOLIDARIDAD', 'DIALOGO INTERCULTURAL', 'VALORES COMUNITARIOS', 'CONVIVENCIA PACIFICA', 'ESPIRITUALIDAD Y DIVERSIDAD', 'RESPONSABILIDAD PERSONAL'],
            'ASI_0010' => ['COSMOVISION ANDINA', 'PENSAMIENTO FILOSOFICO', 'ETICA Y RESPONSABILIDAD', 'IDENTIDAD PERSONAL', 'ARGUMENTOS Y FALACIAS', 'REFLEXION SOBRE EL CONOCIMIENTO'],
            'ASI_0011' => ['EMOCIONES Y AUTOCONOCIMIENTO', 'APRENDIZAJE Y MEMORIA', 'DESARROLLO HUMANO', 'COMUNICACION ASERTIVA', 'SALUD MENTAL'],
            'ASI_0012' => ['RITMO Y PULSO', 'LECTURA MUSICAL', 'INSTRUMENTOS BOLIVIANOS', 'ENTONACION', 'PATRIMONIO MUSICAL', 'COMPOSICION RITMICA'],
            'ASI_0013' => ['DIBUJO Y PROPORCION', 'TEORIA DEL COLOR', 'COMPOSICION VISUAL', 'ARTE BOLIVIANO', 'MODELADO', 'AFICHE EDUCATIVO'],
            'ASI_0014' => ['HERRAMIENTAS Y SEGURIDAD', 'DISEÑO TECNOLOGICO', 'PROYECTO SOCIOPRODUCTIVO', 'RECICLAJE Y REUTILIZACION', 'PRESUPUESTO DE UN PROYECTO', 'PROCESOS PRODUCTIVOS'],
        ];

        return $temas[$codigo] ?? ['SEGURIDAD EN '.$nombre, 'PROCEDIMIENTOS DE '.$nombre, 'PLANIFICACION PRODUCTIVA DE '.$nombre, 'CONTROL DE CALIDAD EN '.$nombre];
    }

    private function cuestionario(string $cla, string $sec, array $plan, int $trimestre, array $dias, array $temas): void
    {
        $fecha = $dias[(int) floor(count($dias) * 0.6)];
        $cue = $this->insertar('cuestionario', ['cod_cla' => $cla, 'cod_sec' => $sec, 'cod_doc' => $plan['doc'], 'cod_pev' => $this->catalogos['periodo_evaluacion'][$trimestre], 'tit_cue' => 'REVISION DE CONCEPTOS DEL TRIMESTRE '.$trimestre, 'ape_cue' => $fecha.' 08:00:00', 'cie_cue' => $fecha.' 23:00:00', 'int_cue' => 2, 'dur_cue' => 40, 'met_cue' => 'MEJOR', 'max_cue' => 100, 'est_cue' => 'CERRADO']);
        $preguntas = [];
        foreach (range(1, 5) as $orden) {
            $tema = $temas[($trimestre + $orden - 2) % count($temas)];
            $tipo = $orden <= 3 ? 'DESARROLLO' : ($orden === 4 ? 'OPCION_UNICA' : 'VERDADERO_FALSO');
            $enunciados = [1 => 'EXPLICA '.$tema.' Y PRESENTA DOS EJEMPLOS DEL ENTORNO.', 2 => 'DESCRIBE UN PROCEDIMIENTO PARA ESTUDIAR '.$tema.' Y JUSTIFICA CADA PASO.', 3 => 'COMPARA DOS SITUACIONES RELACIONADAS CON '.$tema.'; SEÑALA UNA DIFERENCIA Y UNA CONCLUSION.', 4 => 'AL INVESTIGAR '.$tema.', ¿QUE PROCEDIMIENTO PERMITE FUNDAMENTAR UNA CONCLUSION?', 5 => 'EN EL ESTUDIO DE '.$tema.', UNA AFIRMACION DEBE CONTRASTARSE CON EVIDENCIA Y EXPLICAR SU RELACION CON LA CONCLUSION.'];
            $prc = $this->insertar('pregunta_cuestionario', ['cod_cue' => $cue, 'tip_prc' => $tipo, 'enu_prc' => $enunciados[$orden], 'pun_prc' => 20, 'ord_prc' => $orden]);
            $opciones = [];
            if ($orden >= 4) {
                $textos = $orden === 4 ? ['CONTRASTAR FUENTES, REGISTRAR OBSERVACIONES Y EXPLICAR EL RAZONAMIENTO', 'COPIAR UNA AFIRMACION SIN VERIFICARLA', 'ELEGIR EL RESULTADO ANTES DE REVISAR LOS DATOS', 'OMITIR LOS DATOS QUE CONTRADICEN LA HIPOTESIS'] : ['VERDADERO', 'FALSO'];
                foreach ($textos as $pos => $texto) {
                    $opciones[] = $this->insertar('opcion_pregunta', ['cod_prc' => $prc, 'tex_opr' => $texto, 'por_opr' => $pos === 0 ? 100 : 0, 'ord_opr' => $pos + 1]);
                }
            }
            $preguntas[] = ['id' => $prc, 'tema' => $tema, 'opciones' => $opciones];
        }
        foreach ($this->miembros[$cla] as $i => $alumno) {
            if ($fecha > $alumno['fin'] || $this->aleatorio('quiz-ausente:'.$i.$cue, 0, 99) < 7) {
                continue;
            }
            $cantidad = $i % 23 === 0 ? 2 : 1;
            for ($n = 1; $n <= $cantidad; $n++) {
                $base = $this->nota($alumno, (int) $this->cal['anio'], $plan['materia'], $trimestre);
                $respuestas = [];
                foreach ($preguntas as $pos => $pregunta) {
                    $opcion = null;
                    if ($pregunta['opciones'] !== []) {
                        $correcta = $this->aleatorio('quiz-opcion:'.$i.$cue.$n.$pos, 0, 99) < min(97, $base + ($n - 1) * 12);
                        $opcion = $pregunta['opciones'][$correcta ? 0 : $this->aleatorio('quiz-error:'.$i.$cue.$n.$pos, 1, count($pregunta['opciones']) - 1)];
                        $puntaje = $correcta ? 20 : 0;
                        $texto = null;
                    } else {
                        $puntaje = max(0, min(20, (int) round($base / 5) + $this->aleatorio('quiz-desarrollo:'.$i.$cue.$n.$pos, -6, 4) + ($n - 1)));
                        $conclusiones = ['RELACIONO LA OBSERVACION CON LOS DATOS DEL EJERCICIO Y EXPLICO EL RESULTADO', 'COMPARO LOS EJEMPLOS DEL CUADERNO CON UNA SITUACION DE MI COMUNIDAD', 'PRESENTO LOS PASOS DEL PROCEDIMIENTO Y REVISO LOS ERRORES ENCONTRADOS', 'IDENTIFICO LA IDEA PRINCIPAL Y JUSTIFICO LA DIFERENCIA ENTRE AMBOS CASOS'];
                        $texto = $pregunta['tema'].': '.$conclusiones[$this->aleatorio('quiz-texto:'.$i.$cue.$n.$pos, 0, 3)].'. EJEMPLO DE APLICACION NRO. '.($i + $pos + $n).'.';
                    }
                    $respuestas[] = ['pregunta' => $pregunta['id'], 'puntaje' => $puntaje, 'texto' => $texto, 'opcion' => $opcion];
                }
                $pun = array_sum(array_column($respuestas, 'puntaje'));
                $inc = $this->insertar('intento_cuestionario', ['cod_cue' => $cue, 'cod_est' => $alumno['est'], 'num_inc' => $n, 'ini_inc' => $fecha.' '.($n === 1 ? '09:00:00' : '15:00:00'), 'fin_inc' => $fecha.' '.($n === 1 ? '09:30:00' : '15:30:00'), 'pun_inc' => $pun, 'max_inc' => 100, 'est_inc' => 'CALIFICADO'], true);
                foreach ($respuestas as $respuesta) {
                    $rcu = $this->insertar('respuesta_cuestionario', ['cod_inc' => $inc, 'cod_prc' => $respuesta['pregunta'], 'tex_rcu' => $respuesta['texto'], 'pun_rcu' => $respuesta['puntaje'], 'cor_rcu' => $respuesta['puntaje'] >= 11, 'doc_rcu' => $plan['doc'], 'fec_rcu' => $fecha.' 18:00:00', 'obs_rcu' => $respuesta['puntaje'] < 11 ? 'REVISAR EL CONCEPTO, CONTRASTAR EL EJEMPLO Y EXPLICAR EL PROCEDIMIENTO' : 'RESPUESTA FUNDAMENTADA; PROFUNDIZAR LOS EJEMPLOS'], true);
                    if ($respuesta['opcion'] !== null) {
                        $this->insertar('respuesta_opcion', ['cod_rcu' => $rcu, 'cod_opr' => $respuesta['opcion']], true);
                    }
                }
            }
        }
        foreach (['intento_cuestionario', 'respuesta_cuestionario', 'respuesta_opcion'] as $tabla) {
            $this->vaciar($tabla);
        }
    }

    private function foro(string $cla, string $sec, array $plan, array $dias, string $materia): void
    {
        $fecha = $dias[min(8, count($dias) - 1)];
        $usu = $this->docUsuarios[$plan['doc']];
        $for = $this->insertar('foro_clase', ['cod_cla' => $cla, 'cod_sec' => $sec, 'cod_doc' => $plan['doc'], 'tit_for' => 'APLICACIONES DE '.$materia.' EN LA COMUNIDAD', 'des_for' => 'COMPARTIR EJEMPLOS, RESPONDER CON RESPETO Y FUNDAMENTAR LAS IDEAS.', 'ape_for' => $fecha.' 08:00:00', 'cie_for' => end($dias).' 23:00:00', 'est_for' => 'CERRADO']);
        $fte = $this->insertar('foro_tema', ['cod_for' => $for, 'cod_usu' => $usu, 'tit_fte' => 'OBSERVACION DEL ENTORNO DE VILLA VICTORIA', 'fec_fte' => $fecha.' 08:10:00']);
        foreach ($this->miembros[$cla] as $i => $alumno) {
            if ($i % 3 !== 0 || $fecha > $alumno['fin']) {
                continue;
            }
            $men = $this->insertar('foro_mensaje', ['cod_fte' => $fte, 'cod_usu' => $alumno['usu'], 'con_fme' => 'EN MI COMUNIDAD OBSERVO RELACIONES CON '.$materia.'. PROPONGO EXPLICARLAS CON LOS CONCEPTOS TRABAJADOS Y COMPARAR LOS EJEMPLOS DE MIS COMPAÑEROS.', 'fec_fme' => $fecha.' 15:00:00']);
            $this->insertar('foro_mensaje', ['cod_fte' => $fte, 'cod_usu' => $usu, 'pad_fme' => $men, 'con_fme' => 'AMPLIA EL EJEMPLO CON EVIDENCIA Y EXPLICA SU RELACION CON EL TEMA ESTUDIADO.', 'fec_fme' => $fecha.' 18:00:00']);
            if ($i % 12 === 0) {
                $this->insertar('foro_archivo', ['cod_fme' => $men, 'nom_far' => 'ARCHIVO ADJUNTO.pdf', 'rut_far' => $this->pdf, 'mim_far' => 'application/pdf', 'tam_far' => $this->tamPdf, 'has_far' => $this->hashPdf]);
            }
        }
    }

    private function dias(string $inicio, string $fin): array
    {
        $anio = (int) substr($inicio, 0, 4);
        $m = [2020 => ['02-24', '02-25', '04-10', '06-11'], 2021 => ['02-15', '02-16', '04-02', '06-03'], 2022 => ['02-28', '03-01', '04-15', '06-16'], 2023 => ['02-20', '02-21', '04-07', '06-08'], 2024 => ['02-12', '02-13', '03-29', '05-30'], 2025 => ['03-03', '03-04', '04-18', '06-19'], 2026 => ['02-16', '02-17', '04-03', '06-04']];
        $feriados = array_map(fn ($d) => $anio.'-'.$d, array_merge($m[$anio], ['05-01', '06-21', '07-16', '08-06', '11-02']));
        // DS 2750: traslado al lunes de Trabajo, Año Nuevo Andino e Independencia.
        foreach (['05-01', '06-21', '08-06'] as $dia) {
            $fechaFeriado = $anio.'-'.$dia;
            if ((int) date('N', strtotime($fechaFeriado)) === 7) {
                $feriados[] = date('Y-m-d', strtotime($fechaFeriado.' +1 day'));
            }
        }
        // Feriados excepcionales del Bicentenario y DS 5521; no alterar horarios.
        if ($anio === 2025) {
            $feriados[] = '2025-08-07';
        }
        if ($anio === 2026) {
            $feriados = array_merge($feriados, ['2026-06-05', '2026-08-07']);
        }
        $dias = [];
        for ($fecha = $inicio; $fecha <= $fin; $fecha = date('Y-m-d', strtotime($fecha.' +1 day'))) {
            if ((int) date('N', strtotime($fecha)) > 5 || in_array($fecha, $feriados, true)) {
                continue;
            }
            if ($this->cal['invierno'] && $fecha >= $this->cal['invierno'][0] && $fecha <= $this->cal['invierno'][1]) {
                continue;
            }
            $dias[] = $fecha;
        }
        if ($dias === []) {
            throw new \RuntimeException('Periodo sin días lectivos: '.$inicio.' '.$fin);
        }

        return $dias;
    }

    public function asistencias(int $anio): void
    {
        $dias = $this->dias($this->cal['inicio'], $this->cal['actividad_hasta']);
        $nombresDia = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES'];
        foreach ($dias as $fecha) {
            foreach ($this->horarios as $detalle) {
                if ($fecha < $detalle['inicio'] || $fecha > $detalle['fin']) {
                    continue;
                }
                if ($detalle['dia'] !== $nombresDia[(int) date('N', strtotime($fecha))]) {
                    continue;
                }
                $origen = $detalle['plan'];
                $cla = $this->fuentesAula[$origen] ?? null;
                // Codocencia: un horario puede apuntar al otro plan oficial de la misma materia.
                if ($cla === null) {
                    $p = $this->planes[$origen];
                    foreach ($this->cal['principales'] as $principal) {
                        $otro = $this->planes[$principal];
                        if ($otro['grupo'] === $p['grupo'] && $otro['materia'] === $p['materia'] && $otro['tipo'] === $p['tipo']) {
                            $cla = $this->fuentesAula[$principal];
                            $origen = $principal;
                            break;
                        }
                    }
                    // No se reinterpreta un horario del codocente como si fuera de otro plan.
                    if ($detalle['plan'] !== $origen) {
                        continue;
                    }
                }
                if ($cla === null || empty($this->miembros[$cla])) {
                    continue;
                }
                $plan = $this->planes[$origen];
                $ses = $this->insertar('sesion_academica', ['cod_hde' => $detalle['id'], 'fec_ses' => $fecha, 'hor_pla_ses' => 1, 'hor_rea_ses' => 1, 'est_ses' => 'REALIZADA']);
                $asi = $this->insertar('asistencia_clase', ['cod_cla' => $cla, 'cod_doc' => $plan['doc'], 'cod_hbl' => $detalle['bloque'], 'cod_usu_reg' => $this->docUsuarios[$plan['doc']], 'fec_asi_cla' => $fecha, 'hor_ini_asi_cla' => $detalle['horas']['hor_ini_hbl'], 'hor_fin_asi_cla' => $detalle['horas']['hor_fin_hbl'], 'tip_asi_cla' => 'CLASE', 'ori_asi_cla' => 'GENERADA', 'est_asi_cla' => 'CERRADA', 'cod_ses' => $ses]);
                foreach ($this->miembros[$cla] as $i => $alumno) {
                    if ($fecha > $alumno['fin']) {
                        continue;
                    }
                    $lic = $this->licencias[$alumno['est']] ?? null;
                    $valor = $this->aleatorio('asistencia:'.$i.$fecha.$detalle['id'], 0, 999);
                    $umbralFalta = $i % 17 === 0 ? 140 : 45;
                    $estado = $lic && $fecha >= $lic[1] && $fecha <= $lic[2] ? 'LICENCIA' : ($valor < $umbralFalta ? 'AUSENTE' : ($valor < $umbralFalta + 60 ? 'RETRASO' : 'PRESENTE'));
                    $this->insertar('asistencia_estudiante', ['cod_asi_cla' => $asi, 'cod_est' => $alumno['est'], 'cod_est_asi' => $this->estadosAsistencia[$estado], 'cod_usu_reg' => $this->docUsuarios[$plan['doc']], 'min_retraso' => $estado === 'RETRASO' ? $this->aleatorio('minutos:'.$i.$fecha, 3, 27) : 0, 'obs_asi_est' => $estado === 'PRESENTE' ? null : ($estado === 'LICENCIA' ? 'AUSENCIA RESPALDADA POR LICENCIA' : ($estado === 'RETRASO' ? 'INGRESO DESPUES DEL INICIO DEL BLOQUE' : 'AUSENCIA SIN JUSTIFICATIVO PRESENTADO')), 'fec_reg_asi_est' => $fecha.' '.$detalle['horas']['hor_fin_hbl'], 'est_asi_est' => 'REGISTRADO', 'cod_nes' => $estado === 'LICENCIA' ? $lic[0] : null], true);
                }
            }
        }
        $this->vaciar('asistencia_estudiante');
    }

    private function crearInstrumentos(): void
    {
        $dims = ['R' => 'tecnico_practico', 'I' => 'analitico_cientifico', 'A' => 'creativo_expresivo', 'S' => 'social_comunitario', 'E' => 'liderazgo_emprendimiento', 'C' => 'organizativo_administrativo'];
        $legacy = json_decode(file_get_contents(__DIR__.'/../Fuentes/preguntas_institucionales.json'), true, flags: JSON_THROW_ON_ERROR);
        $onet = json_decode(file_get_contents(__DIR__.'/../Fuentes/onet_mini_ip_v2_es.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach (['institucional' => $legacy, 'api_v1' => $onet['items']] as $tipo => $items) {
            $ior = $this->insertar('instrumento_orientacion', ['nom_ior' => $tipo === 'api_v1' ? $this->texto($onet['title']) : 'CUESTIONARIO INSTITUCIONAL DE INTERESES', 'ver_ior' => $tipo === 'api_v1' ? $onet['version'] : 'ORAV', 'tip_ior' => 'RIASEC', 'alg_ior' => 'SUMA_DIMENSION_0_20', 'fii_ior' => $tipo === 'api_v1' ? '2025-01-01' : '2020-01-01', 'est_ior' => 'ACTIVO', 'des_ior' => $tipo === 'api_v1' ? $onet['attribution'].' '.$onet['license_url'] : 'PREGUNTAS EXISTENTES DEL PROYECTO; APLICACIONES HISTORICAS SINTETICAS, SIN AFIRMAR VALIDACION PSICOMETRICA']);
            foreach ($items as $i => $item) {
                $dim = $tipo === 'api_v1' ? $item['code'] : array_search($item['dimension'], $dims, true);
                $pre = $this->insertar('orientacion_preguntas', ['codigo' => $tipo === 'api_v1' ? $onet['instrument_id'].':'.$item['item_id'] : $item['codigo'], 'dimension' => $dims[$dim], 'texto' => $tipo === 'api_v1' ? $item['text'] : $item['texto'], 'tipo' => 'likert', 'orden' => $i + 1, 'visible' => $tipo === 'api_v1', 'est_pre' => 'ACTIVA']);
                $ipr = $this->insertar('instrumento_pregunta', ['cod_ior' => $ior, 'pre_ipr' => $pre, 'dim_ipr' => $dim, 'ord_ipr' => $i + 1, 'pes_ipr' => 1, 'inv_ipr' => false, 'obl_ipr' => true, 'vis_ipr' => true]);
                $this->preguntas[$tipo][] = ['ior' => $ior, 'pre' => $pre, 'ipr' => $ipr, 'dim' => $dim, 'legacy' => $dims[$dim], 'item_id' => $tipo === 'api_v1' ? $item['item_id'] : $i + 1];
            }
        }
    }

    public function orientacion(int $anio): void
    {
        if ($anio === 2020) {
            return;
        }
        $tipo = $anio >= 2026 ? 'api_v1' : 'institucional';
        $preguntas = $this->preguntas[$tipo];
        foreach ($this->actual as $i => $alumno) {
            if ($alumno['grado'] < 4 || $alumno['retira'] || $alumno['traslada']) {
                continue;
            }
            $fecha = $anio.'-08-20';
            $intento = $this->db->table('orientacion_actividades')->where('cod_est', $alumno['est'])->where('cod_ior', $preguntas[0]['ior'])->count() + 1;
            $oac = $this->insertar('orientacion_actividades', ['cod_est' => $alumno['est'], 'cod_gea' => $this->cal['gea'], 'cod_ior' => $preguntas[0]['ior'], 'estado' => 'en_proceso', 'avance' => 0, 'iniciado_at' => $fecha.' 10:00:00', 'int_oac' => $intento]);
            $suma = array_fill_keys(str_split('RIASEC'), 0);
            $respuestas = [];
            foreach ($preguntas as $p) {
                $valor = $this->aleatorio('riasec:'.$i.$anio.$p['item_id'], 0, 4);
                $suma[$p['dim']] += $valor;
                $respuestas[] = ['item_id' => $p['item_id'], 'value' => $valor];
                $this->insertar('orientacion_respuestas', ['orientacion_actividad_id' => $oac, 'orientacion_pregunta_id' => $p['pre'], 'cod_est' => $alumno['est'], 'cod_ipr' => $p['ipr'], 'valor_likert' => $valor + 1]);
            }
            $orden = str_split('RIASEC');
            usort($orden, fn ($a, $b) => $suma[$b] <=> $suma[$a] ?: strpos('RIASEC', $a) <=> strpos('RIASEC', $b));
            $perfil = implode('', array_slice($orden, 0, 3));
            $resultado = ['orientacion_actividad_id' => $oac, 'cod_est' => $alumno['est'], 'perfil_predominante' => $perfil, 'interpretacion' => 'INTERESES EXPRESADOS EN EL PERFIL '.$perfil.'. CONSIDERAR EXPERIENCIAS, CONTEXTO Y ACOMPAÑAMIENTO; NO ES UNA DECISION AUTOMATICA DE CARRERA.', 'estado' => 'generado', 'mod_ors' => $tipo === 'api_v1' ? 'onet-mini-ip-2.0-es' : 'ORAV', 'ver_ors' => $tipo === 'api_v1' ? '2.0-es-2025' : 'ORAV'];
            foreach (['R' => ['tecnico_practico', 'rea_ors'], 'I' => ['analitico_cientifico', 'inv_ors'], 'A' => ['creativo_expresivo', 'art_ors'], 'S' => ['social_comunitario', 'soc_ors'], 'E' => ['liderazgo_emprendimiento', 'emp_ors'], 'C' => ['organizativo_administrativo', 'con_ors']] as $d => [$campo, $oficial]) {
                $resultado[$campo] = $suma[$d] * 5;
                $resultado[$oficial] = $suma[$d];
            }
            $this->insertar('orientacion_resultados', $resultado);
            $payload = ['instrument_id' => $tipo === 'api_v1' ? 'onet-mini-ip-2.0-es' : 'ORAV', 'version' => $tipo === 'api_v1' ? '2.0-es-2025' : 'ORAV', 'responses' => $respuestas];
            $this->db->table('orientacion_actividades')->where('id', $oac)->update(['estado' => 'finalizado', 'avance' => 100, 'finalizado_at' => $fecha.' 10:20:00', 'riasec_public' => json_encode($payload), 'riasec_score' => json_encode(['raw_scores' => $suma, 'holland_code' => $perfil]), 'riasec_input_hash' => hash('sha256', json_encode($payload))]);
        }
    }

    private function universidades(): void
    {
        $catalogo = json_decode(file_get_contents(__DIR__.'/../Fuentes/carreras.json'), true, flags: JSON_THROW_ON_ERROR);
        $universidades = [];
        $sedes = [];
        $carreras = [];
        foreach ($catalogo['universities'] as $u) {
            $sigla = explode('-', $u['university_id'])[1];
            $id = $this->insertar('universidad', ['nom_uni' => $this->texto($u['name']), 'sig_uni' => $sigla, 'pai_uni' => 'BO']);
            $universidades[$u['university_id']] = $id;
            $sedes[$u['university_id']] = $this->insertar('sede_universidad', ['cod_uni' => $id, 'nom_sed' => $this->texto($u['campus']), 'ciu_sed' => $this->texto($u['city']), 'dep_sed' => 'LA PAZ']);
        }
        foreach ($catalogo['careers'] as $c) {
            $nombre = $this->texto($c['name']);
            $car = $carreras[$nombre] ??= $this->insertar('carrera', ['nom_car' => $nombre, 'niv_car' => $this->texto($c['degree'] ?? 'LICENCIATURA')]);
            $ofa = $this->insertar('oferta_academica', ['cod_sed' => $sedes[$c['university_id']], 'cod_car' => $car]);
            $this->ofertas[] = $this->insertar('version_oferta_academica', ['cod_ofa' => $ofa, 'nom_vof' => $nombre, 'dur_vof' => $c['duration_semesters'] ?? null, 'uni_vof' => isset($c['duration_semesters']) ? 'SEMESTRES' : null, 'tit_vof' => $this->texto($c['degree'] ?? 'LICENCIATURA'), 'fii_vof' => '2026-01-01', 'est_vof' => 'ACTIVA', 'dat_vof' => ['catalog_version' => $catalogo['catalog_version'], 'career_id' => $c['career_id'], 'source_ids' => $c['source_ids'] ?? [], 'scope_note' => $catalogo['scope_note']]]);
        }
        $fuentes = json_decode(file_get_contents(__DIR__.'/../Fuentes/fuentes_universitarias.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach ($fuentes['sources'] ?? $fuentes as $f) {
            if (! is_array($f) || empty($f['url'])) {
                continue;
            }
            $uni = $universidades[$f['university_id'] ?? ''] ?? null;
            $this->insertar('recurso_fuente', ['cod_uni' => $uni, 'tip_rfu' => 'PAGINA_WEB', 'tit_rfu' => $this->texto($f['title'] ?? $f['source_id'] ?? 'FUENTE DEL CATALOGO EXISTENTE'), 'url_rfu' => $f['url'], 'dom_rfu' => parse_url($f['url'], PHP_URL_HOST), 'con_rfu' => 'OFICIAL', 'est_rfu' => 'ACTIVO']);
        }
        // No inventa capturas, validaciones humanas ni ofertas oficiales históricas.
    }

    public function cierre(int $anio): void
    {
        if ($anio < 2026) {
            $this->db->table('gestion_academica')->where('cod_gea', $this->cal['gea'])->update(['est_gea' => 'CERRADA']);
        }
        foreach ($this->actual as $i => $alumno) {
            if ($anio === 2026) {
                continue;
            }
            $retencion = $i % 17 === 0 && $anio === 2022;
            $resultado = $alumno['retira'] || $alumno['traslada'] ? 'RETIRADO' : ($retencion ? 'RETENIDO' : ($alumno['grado'] === 6 ? 'EGRESADO' : 'PROMOVIDO'));
            $motivo = $anio === 2020 ? 'PROMOCION EXCEPCIONAL POR PANDEMIA SIN NOTAS CARGADAS; ESCENARIO CONFIRMADO POR EL USUARIO' : ($retencion ? 'RETENCION DEL ESCENARIO SINTETICO; CONTINUA EN EL MISMO NIVEL' : ($alumno['traslada'] ? 'TRASLADO A OTRA UNIDAD EDUCATIVA; HISTORIAL INSTITUCIONAL CONSERVADO' : 'CIERRE ANUAL DEL ESCENARIO SINTETICO'));
            $this->insertar('resultado_anual', ['cod_ins' => $alumno['ins'], 'res_ran' => $resultado, 'fec_ran' => $alumno['retira'] || $alumno['traslada'] ? $alumno['fin'] : $this->cal['fin'], 'cod_usu' => $this->actor, 'ref_ran' => 'ESCENARIO SINTETICO DOCUMENTADO '.$anio, 'obs_ran' => $motivo]);
            if ($retencion || $alumno['retira']) {
                $this->insertar('seguimiento_academico', ['cod_ins' => $alumno['ins'], 'tip_seg' => $retencion ? 'APOYO_ACADEMICO' : 'CONTINUIDAD_ESCOLAR', 'mot_seg' => $motivo, 'est_seg' => 'CERRADO', 'fec_ape_seg' => $anio.'-05-20', 'fec_cie_seg' => $alumno['fin'], 'res_seg' => $retencion ? 'REFORZAMIENTO Y REPETICION DEL NIVEL' : 'ACOMPAÑAMIENTO PARA POSIBLE REINCORPORACION', 'cod_usu_res' => $this->actor]);
            }
            $persona = &$this->personas[$i];
            if ($alumno['retira']) {
                $persona['pausado'] = true;
            } elseif ($alumno['traslada'] || $resultado === 'EGRESADO') {
                $persona['salida'] = true;
            } elseif (! $retencion) {
                $persona['grado']++;
                $persona['pausado'] = false;
            }
            $activo = ! $persona['salida'] && ! $persona['pausado'];
            $this->db->table('estudiante')->where('cod_est', $alumno['est'])->update(['est_est' => $activo ? 'ACTIVO' : 'INACTIVO']);
            $this->db->table('users')->where('cod_usu', $alumno['usu'])->update(['est_usu' => $activo ? 'ACTIVO' : 'INACTIVO']);
            $this->db->table('inscripcion_estudiante')->where('cod_ins', $alumno['ins'])->update(['est_ins' => $alumno['retira'] || $alumno['traslada'] ? 'RETIRADA' : 'ARCHIVADA', 'fec_ret_ins' => $alumno['retira'] || $alumno['traslada'] ? $alumno['fin'].' 18:00:00' : null]);
            unset($persona);
        }
        if ($anio < 2026) {
            foreach ($this->fuentesAula as $cla) {
                $this->db->table('clase_estudiante')->where('cod_cla', $cla)->update(['est_cla_est' => 'INACTIVO']);
                $this->db->table('clase_virtual')->where('cod_cla', $cla)->update(['est_cla' => 'CERRADA']);
            }
        }
        $this->archivoAnual($anio);
    }

    private function archivoAnual(int $anio): void
    {
        $relativa = 'integracion/HISTORIAL_'.$anio.'.zip';
        (new InformeHistorial($this->db))->exportar($anio, storage_path('app/private/'.$relativa), ['gestion' => $anio, 'naturaleza' => 'ESCENARIO SINTETICO DOCUMENTADO', 'corte' => $this->cal['corte'], 'sin_notas_por_pandemia' => $anio === 2020, 'trimestres_entregados' => $this->cal['trimestres_registrados']], storage_path('app/private/'.$this->pdf));
        $archivo = storage_path('app/private/'.$relativa);
        $this->insertar('respaldo_gestion_academica', ['cod_gea' => $this->cal['gea'], 'tip_rga' => $anio === 2026 ? 'PRELIMINAR' : 'CIERRE', 'for_rga' => 'ZIP', 'rut_rga' => $relativa, 'tam_rga' => filesize($archivo), 'has_rga' => hash_file('sha256', $archivo), 'fec_rga' => now()->format('Y-m-d H:i:s'), 'est_rga' => 'GENERADO', 'cod_usu' => $this->actor, 'obs_rga' => 'EXPORTACION DEL ESCENARIO SINTETICO; EL ACCESO REQUIERE CONTROL INSTITUCIONAL']);
    }

    public function validar(): void
    {
        $this->vaciar();
        if ($this->db->table('estudiante')->count() !== 612 || $this->db->table('docente')->count() !== 48 || $this->db->table('personal_institucional')->count() !== 56) {
            throw new \RuntimeException('Conteos fuera del contrato');
        }
        $antony = $this->db->table('estudiante')->where('rud_est', '807301252018104')->value('cod_est');
        $ins = $this->db->table('inscripcion_estudiante')->where('cod_est', $antony)->value('cod_ins');
        if ($this->db->table('calificacion')->where('cod_ins', $ins)->count() !== 22) {
            throw new \RuntimeException('Antony no tiene exactamente las 22 notas aportadas');
        }
        $gea2026 = $this->db->table('gestion_academica')->where('ani_gea', 2026)->value('cod_gea');
        $pev3 = $this->catalogos['periodo_evaluacion'][3];
        if ($this->db->table('calificacion')->where('cod_pev', $pev3)->whereIn('cod_ins', $this->db->table('inscripcion_estudiante')->select('cod_ins')->where('cod_gea', $gea2026))->exists()) {
            throw new \RuntimeException('Notas indebidas de tercer trimestre 2026');
        }
        $gea2020 = $this->db->table('gestion_academica')->where('ani_gea', 2020)->value('cod_gea');
        if ($this->db->table('calificacion')->whereIn('cod_ins', $this->db->table('inscripcion_estudiante')->select('cod_ins')->where('cod_gea', $gea2020))->exists()) {
            throw new \RuntimeException('Se inventaron notas oficiales de 2020');
        }
        $this->db->statement('SET CONSTRAINTS ALL IMMEDIATE');
        foreach (['persona', 'users', 'personal_institucional', 'docente', 'roles'] as $tabla) {
            foreach ($this->origen[$tabla] as $fila) {
                $this->conservar($tabla, $fila);
            }
        }
        foreach (['asignatura', 'especialidad_tecnica', 'turno'] as $tabla) {
            foreach ($this->origen[$tabla] as $fila) {
                $this->conservar($tabla, $fila);
            }
        }
        foreach (['plan_asignatura' => 'pas', 'plan_especialidad' => 'pes'] as $tabla => $tipo) {
            foreach ($this->origen[$tabla] as $original) {
                $p = $this->db->table($tabla.' as p')->join('grupo_academico as g', 'g.cod_gac', '=', 'p.cod_gac')->where('p.cod_'.$tipo, $original['cod_'.$tipo])->first();
                foreach (['cod_doc', $tipo === 'pas' ? 'cod_asi' : 'cod_esp', 'hor_'.$tipo, 'cod_cur', 'cod_par', 'cod_tur'] as $campo) {
                    $igual = $p && ($campo === 'hor_'.$tipo
                        ? is_numeric($p->$campo) && is_numeric($original[$campo]) && (float) $p->$campo === (float) $original[$campo]
                        : (string) $p->$campo === (string) $original[$campo]);
                    if (! $igual) {
                        throw new \RuntimeException('Asignación REAL alterada: '.$original['cod_'.$tipo].'.'.$campo);
                    }
                }
            }
        }
        foreach ($this->origen['horario_detalle'] as $original) {
            $d = $this->db->table('horario_detalle')->where('cod_hde', $original['cod_hde'])->first();
            foreach (['cod_hor', 'cod_hbl', 'dia_hde', 'cod_pas', 'cod_pes'] as $campo) {
                if (! $d || ($d->$campo ?? null) !== ($original[$campo] ?? null)) {
                    throw new \RuntimeException('Detalle REAL alterado: '.$original['cod_hde'].'.'.$campo);
                }
            }
        }
        $resultado = ['estudiantes_distintos' => 612, 'docentes_preservados' => 48, 'personal_preservado' => 56, 'dataset' => 'SINTETICO PARA ESTUDIO Y VALIDACION; NO ES HISTORIAL REAL', 'por_gestion' => [], 'por_tabla' => []];
        $informe = new InformeHistorial($this->db);
        foreach (range(2020, 2026) as $anio) {
            $resultado['por_gestion'][$anio] = $informe->conteos($anio);
        }
        foreach ($this->db->select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename") as $t) {
            $tabla = $t->tablename;
            $resultado['por_tabla'][$tabla] = $this->db->table($tabla)->count();
            // Las identidades importadas conservan sus ID; el siguiente INSERT debe continuar.
            if (in_array('id', $this->campos($tabla), true)) {
                $secuencia = $this->db->selectOne('SELECT pg_get_serial_sequence(?, ?) AS nombre', ['public.'.$tabla, 'id'])->nombre;
                if ($secuencia !== null) {
                    $ultimo = $this->db->table($tabla)->max('id');
                    $this->db->select('SELECT setval(?::regclass, ?, ?::boolean)', [$secuencia, $ultimo ?? 1, $ultimo === null ? 'false' : 'true']);
                }
            }
        }
        file_put_contents(storage_path('app/private/integracion/RESULTADO_SEEDERS.json'), json_encode($resultado, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents(storage_path('app/private/integracion/FICHAS_ESTUDIANTES_COMPLETAS.json'), json_encode($informe->fichas(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
