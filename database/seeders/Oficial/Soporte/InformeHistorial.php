<?php

declare(strict_types=1);

namespace Database\Seeders\Oficial\Soporte;

use Illuminate\Database\Connection;

/** Consultas sobre hechos existentes: no estima ni crea registros. */
final class InformeHistorial
{
    public function __construct(private Connection $db) {}

    public function ambitos(int $anio): array
    {
        $gea = $this->db->getPdo()->quote((string) $this->db->table('gestion_academica')->where('ani_gea', $anio)->value('cod_gea'));
        $ins = "SELECT cod_ins FROM inscripcion_estudiante WHERE cod_gea={$gea}";
        $grupo = "SELECT cod_gac FROM grupo_academico WHERE cod_gea={$gea}";
        $pas = "SELECT cod_pas FROM plan_asignatura WHERE cod_gac IN ({$grupo})";
        $pes = "SELECT cod_pes FROM plan_especialidad WHERE cod_gac IN ({$grupo})";
        $cla = "SELECT cod_cla FROM clase_virtual WHERE cod_pas IN ({$pas}) OR cod_pes IN ({$pes})";
        $tar = "SELECT cod_tar FROM tarea WHERE cod_cla IN ({$cla})";
        $ent = "SELECT cod_ent FROM entrega_tarea WHERE cod_tar IN ({$tar})";
        $cue = "SELECT cod_cue FROM cuestionario WHERE cod_cla IN ({$cla})";
        $inc = "SELECT cod_inc FROM intento_cuestionario WHERE cod_cue IN ({$cue})";
        $prc = "SELECT cod_prc FROM pregunta_cuestionario WHERE cod_cue IN ({$cue})";
        $for = "SELECT cod_for FROM foro_clase WHERE cod_cla IN ({$cla})";
        $fte = "SELECT cod_fte FROM foro_tema WHERE cod_for IN ({$for})";
        $asi = "SELECT cod_asi_cla FROM asistencia_clase WHERE cod_cla IN ({$cla})";
        $hor = "SELECT cod_hor FROM horario WHERE cod_gac IN ({$grupo})";
        $hde = "SELECT cod_hde FROM horario_detalle WHERE cod_hor IN ({$hor})";
        $oac = "SELECT id FROM orientacion_actividades WHERE cod_gea={$gea}";
        $ors = "SELECT id FROM orientacion_resultados WHERE orientacion_actividad_id IN ({$oac})";
        $ext = "SELECT cod_ext FROM expediente_traslado WHERE extract(year from fec_ext)={$anio}";
        $ambitos = ['gestion_academica' => "cod_gea={$gea}", 'grupo_academico' => "cod_gea={$gea}", 'inscripcion_estudiante' => "cod_gea={$gea}", 'plan_asignatura' => "cod_gac IN ({$grupo})", 'plan_especialidad' => "cod_gac IN ({$grupo})", 'horario' => "cod_gac IN ({$grupo})", 'horario_detalle' => "cod_hor IN ({$hor})", 'sesion_academica' => "cod_hde IN ({$hde})", 'clase_virtual' => "cod_cla IN ({$cla})", 'calendario_evento' => "cod_gea={$gea}", 'configuracion_calendario_gestion' => "cod_gea={$gea}", 'respaldo_gestion_academica' => "cod_gea={$gea}", 'expediente_traslado' => "cod_ext IN ({$ext})", 'documento_traslado' => "cod_ext IN ({$ext})", 'nota_traslado' => "cod_ext IN ({$ext})", 'orientacion_actividades' => "cod_gea={$gea}", 'orientacion_respuestas' => "orientacion_actividad_id IN ({$oac})", 'orientacion_resultados' => "orientacion_actividad_id IN ({$oac})", 'orientacion_carreras_sugeridas' => "orientacion_resultado_id IN ({$ors})", 'orientacion_ofertas_sugeridas' => "sug_oos IN (SELECT id FROM orientacion_carreras_sugeridas WHERE orientacion_resultado_id IN ({$ors}))"];
        foreach (['inscripcion_vigencia', 'calificacion', 'resultado_anual', 'documento_inscripcion_estudiante', 'novedad_estudiante', 'seguimiento_academico'] as $tabla) {
            $ambitos[$tabla] = "cod_ins IN ({$ins})";
        }
        foreach (['clase_estudiante', 'seccion_clase', 'publicacion_clase', 'material_clase', 'tarea', 'cuestionario', 'foro_clase', 'asistencia_clase', 'registro_actividad_clase'] as $tabla) {
            $ambitos[$tabla] = "cod_cla IN ({$cla})";
        }
        foreach (['tarea_material', 'entrega_tarea', 'calificacion_tarea'] as $tabla) {
            $ambitos[$tabla] = "cod_tar IN ({$tar})";
        }
        $ambitos += ['entrega_archivo' => "cod_ent IN ({$ent})", 'retroalimentacion_archivo' => "cod_cal_tar IN (SELECT cod_cal_tar FROM calificacion_tarea WHERE cod_tar IN ({$tar}))", 'pregunta_cuestionario' => "cod_cue IN ({$cue})", 'opcion_pregunta' => "cod_prc IN ({$prc})", 'intento_cuestionario' => "cod_cue IN ({$cue})", 'respuesta_cuestionario' => "cod_inc IN ({$inc})", 'respuesta_opcion' => "cod_rcu IN (SELECT cod_rcu FROM respuesta_cuestionario WHERE cod_inc IN ({$inc}))", 'foro_tema' => "cod_for IN ({$for})", 'foro_mensaje' => "cod_fte IN ({$fte})", 'foro_archivo' => "cod_fme IN (SELECT cod_fme FROM foro_mensaje WHERE cod_fte IN ({$fte}))", 'asistencia_estudiante' => "cod_asi_cla IN ({$asi})", 'plantilla_horaria' => "extract(year from fec_ini_pho)={$anio}", 'horario_bloque' => "cod_pho IN (SELECT cod_pho FROM plantilla_horaria WHERE extract(year from fec_ini_pho)={$anio})"];

        return $ambitos;
    }

    public function conteos(int $anio): array
    {
        $resultado = [];
        foreach ($this->ambitos($anio) as $tabla => $condicion) {
            $resultado[$tabla] = $this->db->table($tabla)->whereRaw($condicion)->count();
        }
        $gea = $this->db->table('gestion_academica')->where('ani_gea', $anio)->value('cod_gea');
        $resultado['estudiantes_nuevos'] = $this->db->table('inscripcion_estudiante as i')->where('i.cod_gea', $gea)->whereNotExists(fn ($q) => $q->selectRaw('1')->from('inscripcion_estudiante as previa')->join('gestion_academica as gp', 'gp.cod_gea', '=', 'previa.cod_gea')->whereColumn('previa.cod_est', 'i.cod_est')->where('gp.ani_gea', '<', $anio))->count();

        return $resultado;
    }

    public function fichas(): array
    {
        $fichas = [];
        foreach ($this->db->table('estudiante')->orderBy('cod_est')->get() as $estudiante) {
            $usuario = $this->db->table('users')->where('cod_per', $estudiante->cod_per)->first(['cod_usu', 'email', 'est_usu', 'auth_provider', 'email_verified_at']);
            $inscripciones = $this->db->table('inscripcion_estudiante as i')->join('gestion_academica as g', 'g.cod_gea', '=', 'i.cod_gea')->where('i.cod_est', $estudiante->cod_est)->select('i.*', 'g.ani_gea')->orderBy('g.ani_gea')->get();
            $historia = [];
            foreach ($inscripciones as $inscripcion) {
                $historia[] = ['inscripcion' => $inscripcion, 'vigencias' => $this->db->table('inscripcion_vigencia')->where('cod_ins', $inscripcion->cod_ins)->orderBy('fii_ivg')->get(), 'notas' => $this->db->table('calificacion')->where('cod_ins', $inscripcion->cod_ins)->orderBy('fea_cal')->get(), 'resultado_anual' => $this->db->table('resultado_anual')->where('cod_ins', $inscripcion->cod_ins)->first(), 'documentos' => $this->db->table('documento_inscripcion_estudiante')->where('cod_ins', $inscripcion->cod_ins)->get(), 'novedades' => $this->db->table('novedad_estudiante')->where('cod_ins', $inscripcion->cod_ins)->get(), 'seguimientos' => $this->db->table('seguimiento_academico')->where('cod_ins', $inscripcion->cod_ins)->get()];
            }
            $fichas[] = ['estudiante' => $estudiante, 'persona' => $this->db->table('persona')->where('cod_per', $estudiante->cod_per)->first(), 'usuario_sin_secretos' => $usuario, 'responsables' => $this->db->table('estudiante_responsable as r')->join('persona as p', 'p.cod_per', '=', 'r.cod_per')->where('r.cod_est', $estudiante->cod_est)->select('r.*', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per', 'p.ci_per', 'p.fec_nac_per', 'p.gen_per', 'p.dir_per', 'p.tel_per', 'p.ema_per')->get(), 'historia_anual' => $historia, 'expedientes_traslado' => $this->db->table('expediente_traslado')->where('cod_est', $estudiante->cod_est)->get(), 'notas_traslado' => $this->db->table('nota_traslado')->whereIn('cod_ext', $this->db->table('expediente_traslado')->select('cod_ext')->where('cod_est', $estudiante->cod_est))->get()];
        }

        return $fichas;
    }

    public function exportar(int $anio, string $ruta, array $metadatos, string $pdf): void
    {
        $formatos = require __DIR__.'/formatos_codigos.php';
        $zip = new \ZipArchive;
        if ($zip->open($ruta, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el historial anual');
        }
        $temporales = [];
        try {
            $conteos = [];
            foreach ($this->ambitos($anio) as $tabla => $condicion) {
                if ($tabla === 'respaldo_gestion_academica') {
                    continue;
                }
                $temporal = tempnam(sys_get_temp_dir(), 'SAVP_ANUAL_');
                $temporales[] = $temporal;
                $archivo = fopen($temporal, 'wb');
                $conteos[$tabla] = 0;
                $cursor = 'savp_export_'.$tabla;
                try {
                    $clave = $formatos[$tabla]['clave'] ?? 'id';
                    // Un único plan SQL; FETCH limita memoria sin repetir uniones por página.
                    $this->db->statement('DECLARE "'.$cursor.'" NO SCROLL CURSOR FOR SELECT * FROM "'.$tabla.'" WHERE '.$condicion.' ORDER BY "'.$clave.'"');
                    try {
                        do {
                            $filas = $this->db->select('FETCH FORWARD 1000 FROM "'.$cursor.'"');
                            foreach ($filas as $fila) {
                                fwrite($archivo, json_encode($fila, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n");
                                $conteos[$tabla]++;
                            }
                        } while (count($filas) === 1000);
                    } finally {
                        $this->db->statement('CLOSE "'.$cursor.'"');
                    }
                } finally {
                    fclose($archivo);
                }
                $zip->addFile($temporal, $tabla.'.jsonl');
            }
            $estudiantes = $this->db->table('estudiante as e')->join('persona as p', 'p.cod_per', '=', 'e.cod_per')->whereIn('e.cod_est', $this->db->table('inscripcion_estudiante')->select('cod_est')->whereIn('cod_gea', $this->db->table('gestion_academica')->select('cod_gea')->where('ani_gea', $anio)))->select('e.*', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per', 'p.ci_per', 'p.fec_nac_per', 'p.dir_per', 'p.tel_per', 'p.ema_per')->get();
            $zip->addFromString('FICHAS_ESTUDIANTES.json', json_encode($estudiantes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $zip->addFile($pdf, 'DOCUMENTO_ADJUNTO_PROPORCIONADO.pdf');
            $zip->addFromString('MANIFIESTO.json', json_encode($metadatos + ['conteos' => $conteos, 'alcance' => 'HECHOS ACADEMICOS, LMS Y ORIENTACION DE ESTA GESTION; FICHAS SIN HASHES NI CONTRASEÑAS'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if (! $zip->close()) {
                throw new \RuntimeException('No se pudo cerrar el historial anual');
            }
        } finally {
            foreach ($temporales as $temporal) {
                if (is_file($temporal)) {
                    unlink($temporal);
                }
            }
        }
    }
}
