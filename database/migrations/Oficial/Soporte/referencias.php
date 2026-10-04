<?php

declare(strict_types=1);

/** FK explícitas; tipo de claves existente preservado y ON DELETE RESTRICT. */
return [
    [
        'tabla' => 'users',
        'columnas' => [
            'cod_per',
        ],
        'madre' => 'persona',
        'referencias' => [
            'cod_per',
        ],
        'nombre' => 'ofi_fk_users_persona',
    ],
    [
        'tabla' => 'sessions',
        'columnas' => [
            'user_id',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_sessions_user',
    ],
    [
        'tabla' => 'model_has_roles',
        'columnas' => [
            'role_id',
        ],
        'madre' => 'roles',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_mhr_role',
    ],
    [
        'tabla' => 'model_has_permissions',
        'columnas' => [
            'permission_id',
        ],
        'madre' => 'permissions',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_mhp_permission',
    ],
    [
        'tabla' => 'role_has_permissions',
        'columnas' => [
            'permission_id',
        ],
        'madre' => 'permissions',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_rhp_permission',
    ],
    [
        'tabla' => 'role_has_permissions',
        'columnas' => [
            'role_id',
        ],
        'madre' => 'roles',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_rhp_role',
    ],
    [
        'tabla' => 'personal_institucional',
        'columnas' => [
            'cod_per',
        ],
        'madre' => 'persona',
        'referencias' => [
            'cod_per',
        ],
        'nombre' => 'ofi_fk_pin_persona',
    ],
    [
        'tabla' => 'vinculo_personal',
        'columnas' => [
            'cod_pin',
        ],
        'madre' => 'personal_institucional',
        'referencias' => [
            'cod_pin',
        ],
        'nombre' => 'ofi_fk_vpe_pin',
    ],
    [
        'tabla' => 'vinculo_personal',
        'columnas' => [
            'cod_cai',
        ],
        'madre' => 'cargo_institucional',
        'referencias' => [
            'cod_cai',
        ],
        'nombre' => 'ofi_fk_vpe_cargo',
    ],
    [
        'tabla' => 'requisito_documento_cargo',
        'columnas' => [
            'cod_cai',
        ],
        'madre' => 'cargo_institucional',
        'referencias' => [
            'cod_cai',
        ],
        'nombre' => 'ofi_fk_rdc_cargo',
    ],
    [
        'tabla' => 'requisito_documento_cargo',
        'columnas' => [
            'cod_tdp',
        ],
        'madre' => 'tipo_documento_personal',
        'referencias' => [
            'cod_tdp',
        ],
        'nombre' => 'ofi_fk_rdc_tipo',
    ],
    [
        'tabla' => 'documento_personal',
        'columnas' => [
            'cod_pin',
        ],
        'madre' => 'personal_institucional',
        'referencias' => [
            'cod_pin',
        ],
        'nombre' => 'ofi_fk_dpe_pin',
    ],
    [
        'tabla' => 'documento_personal',
        'columnas' => [
            'cod_tdp',
        ],
        'madre' => 'tipo_documento_personal',
        'referencias' => [
            'cod_tdp',
        ],
        'nombre' => 'ofi_fk_dpe_tipo',
    ],
    [
        'tabla' => 'documento_personal',
        'columnas' => [
            'cod_vpe',
        ],
        'madre' => 'vinculo_personal',
        'referencias' => [
            'cod_vpe',
        ],
        'nombre' => 'ofi_fk_dpe_vinculo',
    ],
    [
        'tabla' => 'documento_personal',
        'columnas' => [
            'val_dpe',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_dpe_validador',
    ],
    [
        'tabla' => 'docente',
        'columnas' => [
            'cod_pin',
        ],
        'madre' => 'personal_institucional',
        'referencias' => [
            'cod_pin',
        ],
        'nombre' => 'ofi_fk_doc_pin',
    ],
    [
        'tabla' => 'formacion_docente',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_fdo_doc',
    ],
    [
        'tabla' => 'formacion_docente',
        'columnas' => [
            'cod_dpe',
        ],
        'madre' => 'documento_personal',
        'referencias' => [
            'cod_dpe',
        ],
        'nombre' => 'ofi_fk_fdo_dpe',
    ],
    [
        'tabla' => 'experiencia_docente',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_edo_doc',
    ],
    [
        'tabla' => 'experiencia_docente',
        'columnas' => [
            'cod_dpe',
        ],
        'madre' => 'documento_personal',
        'referencias' => [
            'cod_dpe',
        ],
        'nombre' => 'ofi_fk_edo_dpe',
    ],
    [
        'tabla' => 'capacitacion_docente',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_cdo_doc',
    ],
    [
        'tabla' => 'capacitacion_docente',
        'columnas' => [
            'cod_dpe',
        ],
        'madre' => 'documento_personal',
        'referencias' => [
            'cod_dpe',
        ],
        'nombre' => 'ofi_fk_cdo_dpe',
    ],
    [
        'tabla' => 'estudiante',
        'columnas' => [
            'cod_per',
        ],
        'madre' => 'persona',
        'referencias' => [
            'cod_per',
        ],
        'nombre' => 'ofi_fk_est_persona',
    ],
    [
        'tabla' => 'estudiante',
        'columnas' => [
            'cod_tve',
        ],
        'madre' => 'tipo_vinculacion_estudiante',
        'referencias' => [
            'cod_tve',
        ],
        'nombre' => 'ofi_fk_est_tve',
    ],
    [
        'tabla' => 'estudiante_responsable',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_ere_est',
    ],
    [
        'tabla' => 'estudiante_responsable',
        'columnas' => [
            'cod_per',
        ],
        'madre' => 'persona',
        'referencias' => [
            'cod_per',
        ],
        'nombre' => 'ofi_fk_ere_persona',
    ],
    [
        'tabla' => 'expediente_traslado',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_ext_est',
    ],
    [
        'tabla' => 'expediente_traslado',
        'columnas' => [
            'cod_ipe',
        ],
        'madre' => 'institucion_procedencia',
        'referencias' => [
            'cod_ipe',
        ],
        'nombre' => 'ofi_fk_ext_ipe',
    ],
    [
        'tabla' => 'documento_traslado',
        'columnas' => [
            'cod_ext',
        ],
        'madre' => 'expediente_traslado',
        'referencias' => [
            'cod_ext',
        ],
        'nombre' => 'ofi_fk_dtr_ext',
    ],
    [
        'tabla' => 'nota_traslado',
        'columnas' => [
            'cod_ext',
        ],
        'madre' => 'expediente_traslado',
        'referencias' => [
            'cod_ext',
        ],
        'nombre' => 'ofi_fk_ntr_ext',
    ],
    [
        'tabla' => 'nota_traslado',
        'columnas' => [
            'cod_cur',
        ],
        'madre' => 'curso',
        'referencias' => [
            'cod_cur',
        ],
        'nombre' => 'ofi_fk_ntr_cur',
    ],
    [
        'tabla' => 'nota_traslado',
        'columnas' => [
            'cod_asi',
        ],
        'madre' => 'asignatura',
        'referencias' => [
            'cod_asi',
        ],
        'nombre' => 'ofi_fk_ntr_asi',
    ],
    [
        'tabla' => 'nota_traslado',
        'columnas' => [
            'cod_pev',
        ],
        'madre' => 'periodo_evaluacion',
        'referencias' => [
            'cod_pev',
        ],
        'nombre' => 'ofi_fk_ntr_pev',
    ],
    [
        'tabla' => 'configuracion_calendario_gestion',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_ccg_gea',
    ],
    [
        'tabla' => 'configuracion_calendario_gestion',
        'columnas' => [
            'cod_pev',
        ],
        'madre' => 'periodo_evaluacion',
        'referencias' => [
            'cod_pev',
        ],
        'nombre' => 'ofi_fk_ccg_pev',
    ],
    [
        'tabla' => 'grupo_academico',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_gac_gea',
    ],
    [
        'tabla' => 'grupo_academico',
        'columnas' => [
            'cod_cur',
        ],
        'madre' => 'curso',
        'referencias' => [
            'cod_cur',
        ],
        'nombre' => 'ofi_fk_gac_cur',
    ],
    [
        'tabla' => 'grupo_academico',
        'columnas' => [
            'cod_par',
        ],
        'madre' => 'paralelo',
        'referencias' => [
            'cod_par',
        ],
        'nombre' => 'ofi_fk_gac_par',
    ],
    [
        'tabla' => 'grupo_academico',
        'columnas' => [
            'cod_tur',
        ],
        'madre' => 'turno',
        'referencias' => [
            'cod_tur',
        ],
        'nombre' => 'ofi_fk_gac_tur',
    ],
    [
        'tabla' => 'plan_asignatura',
        'columnas' => [
            'cod_asi',
        ],
        'madre' => 'asignatura',
        'referencias' => [
            'cod_asi',
        ],
        'nombre' => 'ofi_fk_pas_asi',
    ],
    [
        'tabla' => 'plan_asignatura',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_pas_doc',
    ],
    [
        'tabla' => 'plan_asignatura',
        'columnas' => [
            'cod_gac',
        ],
        'madre' => 'grupo_academico',
        'referencias' => [
            'cod_gac',
        ],
        'nombre' => 'ofi_fk_pas_gac',
    ],
    [
        'tabla' => 'plan_especialidad',
        'columnas' => [
            'cod_esp',
        ],
        'madre' => 'especialidad_tecnica',
        'referencias' => [
            'cod_esp',
        ],
        'nombre' => 'ofi_fk_pes_esp',
    ],
    [
        'tabla' => 'plan_especialidad',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_pes_doc',
    ],
    [
        'tabla' => 'plan_especialidad',
        'columnas' => [
            'cod_gac',
        ],
        'madre' => 'grupo_academico',
        'referencias' => [
            'cod_gac',
        ],
        'nombre' => 'ofi_fk_pes_gac',
    ],
    [
        'tabla' => 'plantilla_horaria',
        'columnas' => [
            'cod_tur',
        ],
        'madre' => 'turno',
        'referencias' => [
            'cod_tur',
        ],
        'nombre' => 'ofi_fk_pho_tur',
    ],
    [
        'tabla' => 'horario_bloque',
        'columnas' => [
            'cod_pho',
        ],
        'madre' => 'plantilla_horaria',
        'referencias' => [
            'cod_pho',
        ],
        'nombre' => 'ofi_fk_hbl_pho',
    ],
    [
        'tabla' => 'horario',
        'columnas' => [
            'cod_gac',
        ],
        'madre' => 'grupo_academico',
        'referencias' => [
            'cod_gac',
        ],
        'nombre' => 'ofi_fk_hor_gac',
    ],
    [
        'tabla' => 'horario',
        'columnas' => [
            'cod_pho',
        ],
        'madre' => 'plantilla_horaria',
        'referencias' => [
            'cod_pho',
        ],
        'nombre' => 'ofi_fk_hor_pho',
    ],
    [
        'tabla' => 'horario_detalle',
        'columnas' => [
            'cod_hor',
        ],
        'madre' => 'horario',
        'referencias' => [
            'cod_hor',
        ],
        'nombre' => 'ofi_fk_hde_hor',
    ],
    [
        'tabla' => 'horario_detalle',
        'columnas' => [
            'cod_hbl',
        ],
        'madre' => 'horario_bloque',
        'referencias' => [
            'cod_hbl',
        ],
        'nombre' => 'ofi_fk_hde_hbl',
    ],
    [
        'tabla' => 'horario_detalle',
        'columnas' => [
            'cod_pas',
        ],
        'madre' => 'plan_asignatura',
        'referencias' => [
            'cod_pas',
        ],
        'nombre' => 'ofi_fk_hde_pas',
    ],
    [
        'tabla' => 'horario_detalle',
        'columnas' => [
            'cod_pes',
        ],
        'madre' => 'plan_especialidad',
        'referencias' => [
            'cod_pes',
        ],
        'nombre' => 'ofi_fk_hde_pes',
    ],
    [
        'tabla' => 'horario_detalle',
        'columnas' => [
            'cod_aul',
        ],
        'madre' => 'aula',
        'referencias' => [
            'cod_aul',
        ],
        'nombre' => 'ofi_fk_hde_aul',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_cae_gea',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_tur',
        ],
        'madre' => 'turno',
        'referencias' => [
            'cod_tur',
        ],
        'nombre' => 'ofi_fk_cae_tur',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_cur',
        ],
        'madre' => 'curso',
        'referencias' => [
            'cod_cur',
        ],
        'nombre' => 'ofi_fk_cae_cur',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_par',
        ],
        'madre' => 'paralelo',
        'referencias' => [
            'cod_par',
        ],
        'nombre' => 'ofi_fk_cae_par',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_hde',
        ],
        'madre' => 'horario_detalle',
        'referencias' => [
            'cod_hde',
        ],
        'nombre' => 'ofi_fk_cae_hde',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_cae_ori',
        ],
        'madre' => 'calendario_evento',
        'referencias' => [
            'cod_cae',
        ],
        'nombre' => 'ofi_fk_cae_ori',
    ],
    [
        'tabla' => 'calendario_evento',
        'columnas' => [
            'cod_cae_ant',
        ],
        'madre' => 'calendario_evento',
        'referencias' => [
            'cod_cae',
        ],
        'nombre' => 'ofi_fk_cae_ant',
    ],
    [
        'tabla' => 'sesion_academica',
        'columnas' => [
            'cod_hde',
        ],
        'madre' => 'horario_detalle',
        'referencias' => [
            'cod_hde',
        ],
        'nombre' => 'ofi_fk_ses_hde',
    ],
    [
        'tabla' => 'sesion_academica',
        'columnas' => [
            'cod_cae',
        ],
        'madre' => 'calendario_evento',
        'referencias' => [
            'cod_cae',
        ],
        'nombre' => 'ofi_fk_ses_cae',
    ],
    [
        'tabla' => 'sesion_academica',
        'columnas' => [
            'cod_ses_ori',
        ],
        'madre' => 'sesion_academica',
        'referencias' => [
            'cod_ses',
        ],
        'nombre' => 'ofi_fk_ses_ori',
    ],
    [
        'tabla' => 'inscripcion_estudiante',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_ins_est',
    ],
    [
        'tabla' => 'inscripcion_estudiante',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_ins_gea',
    ],
    [
        'tabla' => 'inscripcion_vigencia',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_ivg_ins',
    ],
    [
        'tabla' => 'inscripcion_vigencia',
        'columnas' => [
            'cod_gac',
        ],
        'madre' => 'grupo_academico',
        'referencias' => [
            'cod_gac',
        ],
        'nombre' => 'ofi_fk_ivg_gac',
    ],
    [
        'tabla' => 'inscripcion_vigencia',
        'columnas' => [
            'cod_esp_tec',
        ],
        'madre' => 'especialidad_tecnica',
        'referencias' => [
            'cod_esp',
        ],
        'nombre' => 'ofi_fk_ivg_esp',
    ],
    [
        'tabla' => 'documento_inscripcion_estudiante',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_die_ins',
    ],
    [
        'tabla' => 'novedad_estudiante',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_nes_ins',
    ],
    [
        'tabla' => 'seguimiento_academico',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_seg_ins',
    ],
    [
        'tabla' => 'seguimiento_academico',
        'columnas' => [
            'cod_usu_res',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_seg_usuario',
    ],
    [
        'tabla' => 'resultado_anual',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_ran_ins',
    ],
    [
        'tabla' => 'resultado_anual',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_ran_usuario',
    ],
    [
        'tabla' => 'clase_virtual',
        'columnas' => [
            'cod_pas',
        ],
        'madre' => 'plan_asignatura',
        'referencias' => [
            'cod_pas',
        ],
        'nombre' => 'ofi_fk_cla_pas',
    ],
    [
        'tabla' => 'clase_virtual',
        'columnas' => [
            'cod_pes',
        ],
        'madre' => 'plan_especialidad',
        'referencias' => [
            'cod_pes',
        ],
        'nombre' => 'ofi_fk_cla_pes',
    ],
    [
        'tabla' => 'clase_estudiante',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_ces_cla',
    ],
    [
        'tabla' => 'clase_estudiante',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_ces_est',
    ],
    [
        'tabla' => 'seccion_clase',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_sec_cla',
    ],
    [
        'tabla' => 'publicacion_clase',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_pub_cla',
    ],
    [
        'tabla' => 'publicacion_clase',
        'columnas' => [
            'cod_sec',
            'cod_cla',
        ],
        'madre' => 'seccion_clase',
        'referencias' => [
            'cod_sec',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_pub_sec_cla',
    ],
    [
        'tabla' => 'publicacion_clase',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_pub_usuario',
    ],
    [
        'tabla' => 'material_clase',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_mat_cla',
    ],
    [
        'tabla' => 'material_clase',
        'columnas' => [
            'cod_sec',
            'cod_cla',
        ],
        'madre' => 'seccion_clase',
        'referencias' => [
            'cod_sec',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_mat_sec_cla',
    ],
    [
        'tabla' => 'material_clase',
        'columnas' => [
            'cod_pub',
            'cod_cla',
        ],
        'madre' => 'publicacion_clase',
        'referencias' => [
            'cod_pub',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_mat_pub_cla',
    ],
    [
        'tabla' => 'material_clase',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_mat_usuario',
    ],
    [
        'tabla' => 'tarea',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_tar_cla',
    ],
    [
        'tabla' => 'tarea',
        'columnas' => [
            'cod_sec',
            'cod_cla',
        ],
        'madre' => 'seccion_clase',
        'referencias' => [
            'cod_sec',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_tar_sec_cla',
    ],
    [
        'tabla' => 'tarea',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_tar_doc',
    ],
    [
        'tabla' => 'tarea',
        'columnas' => [
            'cod_pev',
        ],
        'madre' => 'periodo_evaluacion',
        'referencias' => [
            'cod_pev',
        ],
        'nombre' => 'ofi_fk_tar_pev',
    ],
    [
        'tabla' => 'tarea_material',
        'columnas' => [
            'cod_tar',
        ],
        'madre' => 'tarea',
        'referencias' => [
            'cod_tar',
        ],
        'nombre' => 'ofi_fk_tma_tar',
    ],
    [
        'tabla' => 'entrega_tarea',
        'columnas' => [
            'cod_tar',
        ],
        'madre' => 'tarea',
        'referencias' => [
            'cod_tar',
        ],
        'nombre' => 'ofi_fk_ent_tar',
    ],
    [
        'tabla' => 'entrega_tarea',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_ent_est',
    ],
    [
        'tabla' => 'entrega_archivo',
        'columnas' => [
            'cod_ent',
        ],
        'madre' => 'entrega_tarea',
        'referencias' => [
            'cod_ent',
        ],
        'nombre' => 'ofi_fk_ear_ent',
    ],
    [
        'tabla' => 'calificacion_tarea',
        'columnas' => [
            'cod_ent',
        ],
        'madre' => 'entrega_tarea',
        'referencias' => [
            'cod_ent',
        ],
        'nombre' => 'ofi_fk_cta_ent',
    ],
    [
        'tabla' => 'calificacion_tarea',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_cta_doc',
    ],
    [
        'tabla' => 'retroalimentacion_archivo',
        'columnas' => [
            'cod_cal_tar',
        ],
        'madre' => 'calificacion_tarea',
        'referencias' => [
            'cod_cal_tar',
        ],
        'nombre' => 'ofi_fk_raf_cta',
    ],
    [
        'tabla' => 'cuestionario',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_cue_cla',
    ],
    [
        'tabla' => 'cuestionario',
        'columnas' => [
            'cod_sec',
            'cod_cla',
        ],
        'madre' => 'seccion_clase',
        'referencias' => [
            'cod_sec',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_cue_sec_cla',
    ],
    [
        'tabla' => 'cuestionario',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_cue_doc',
    ],
    [
        'tabla' => 'cuestionario',
        'columnas' => [
            'cod_pev',
        ],
        'madre' => 'periodo_evaluacion',
        'referencias' => [
            'cod_pev',
        ],
        'nombre' => 'ofi_fk_cue_pev',
    ],
    [
        'tabla' => 'pregunta_cuestionario',
        'columnas' => [
            'cod_cue',
        ],
        'madre' => 'cuestionario',
        'referencias' => [
            'cod_cue',
        ],
        'nombre' => 'ofi_fk_prc_cue',
    ],
    [
        'tabla' => 'opcion_pregunta',
        'columnas' => [
            'cod_prc',
        ],
        'madre' => 'pregunta_cuestionario',
        'referencias' => [
            'cod_prc',
        ],
        'nombre' => 'ofi_fk_opr_prc',
    ],
    [
        'tabla' => 'intento_cuestionario',
        'columnas' => [
            'cod_cue',
        ],
        'madre' => 'cuestionario',
        'referencias' => [
            'cod_cue',
        ],
        'nombre' => 'ofi_fk_inc_cue',
    ],
    [
        'tabla' => 'intento_cuestionario',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_inc_est',
    ],
    [
        'tabla' => 'respuesta_cuestionario',
        'columnas' => [
            'cod_inc',
        ],
        'madre' => 'intento_cuestionario',
        'referencias' => [
            'cod_inc',
        ],
        'nombre' => 'ofi_fk_rcu_inc',
    ],
    [
        'tabla' => 'respuesta_cuestionario',
        'columnas' => [
            'cod_prc',
        ],
        'madre' => 'pregunta_cuestionario',
        'referencias' => [
            'cod_prc',
        ],
        'nombre' => 'ofi_fk_rcu_prc',
    ],
    [
        'tabla' => 'respuesta_cuestionario',
        'columnas' => [
            'doc_rcu',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_rcu_doc',
    ],
    [
        'tabla' => 'respuesta_opcion',
        'columnas' => [
            'cod_rcu',
        ],
        'madre' => 'respuesta_cuestionario',
        'referencias' => [
            'cod_rcu',
        ],
        'nombre' => 'ofi_fk_rop_rcu',
    ],
    [
        'tabla' => 'respuesta_opcion',
        'columnas' => [
            'cod_opr',
        ],
        'madre' => 'opcion_pregunta',
        'referencias' => [
            'cod_opr',
        ],
        'nombre' => 'ofi_fk_rop_opr',
    ],
    [
        'tabla' => 'foro_clase',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_for_cla',
    ],
    [
        'tabla' => 'foro_clase',
        'columnas' => [
            'cod_sec',
            'cod_cla',
        ],
        'madre' => 'seccion_clase',
        'referencias' => [
            'cod_sec',
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_for_sec_cla',
    ],
    [
        'tabla' => 'foro_clase',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_for_doc',
    ],
    [
        'tabla' => 'foro_tema',
        'columnas' => [
            'cod_for',
        ],
        'madre' => 'foro_clase',
        'referencias' => [
            'cod_for',
        ],
        'nombre' => 'ofi_fk_fte_for',
    ],
    [
        'tabla' => 'foro_tema',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_fte_usuario',
    ],
    [
        'tabla' => 'foro_mensaje',
        'columnas' => [
            'cod_fte',
        ],
        'madre' => 'foro_tema',
        'referencias' => [
            'cod_fte',
        ],
        'nombre' => 'ofi_fk_fme_fte',
    ],
    [
        'tabla' => 'foro_mensaje',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_fme_usuario',
    ],
    [
        'tabla' => 'foro_mensaje',
        'columnas' => [
            'pad_fme',
        ],
        'madre' => 'foro_mensaje',
        'referencias' => [
            'cod_fme',
        ],
        'nombre' => 'ofi_fk_fme_pad',
    ],
    [
        'tabla' => 'foro_archivo',
        'columnas' => [
            'cod_fme',
        ],
        'madre' => 'foro_mensaje',
        'referencias' => [
            'cod_fme',
        ],
        'nombre' => 'ofi_fk_far_fme',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'cod_cla',
        ],
        'madre' => 'clase_virtual',
        'referencias' => [
            'cod_cla',
        ],
        'nombre' => 'ofi_fk_rac_cla',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_rac_est',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_rac_usuario',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'pub_rac',
        ],
        'madre' => 'publicacion_clase',
        'referencias' => [
            'cod_pub',
        ],
        'nombre' => 'ofi_fk_rac_pub',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'mat_rac',
        ],
        'madre' => 'material_clase',
        'referencias' => [
            'cod_mat',
        ],
        'nombre' => 'ofi_fk_rac_mat',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'tar_rac',
        ],
        'madre' => 'tarea',
        'referencias' => [
            'cod_tar',
        ],
        'nombre' => 'ofi_fk_rac_tar',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'cue_rac',
        ],
        'madre' => 'cuestionario',
        'referencias' => [
            'cod_cue',
        ],
        'nombre' => 'ofi_fk_rac_cue',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'for_rac',
        ],
        'madre' => 'foro_clase',
        'referencias' => [
            'cod_for',
        ],
        'nombre' => 'ofi_fk_rac_for',
    ],
    [
        'tabla' => 'registro_actividad_clase',
        'columnas' => [
            'men_rac',
        ],
        'madre' => 'foro_mensaje',
        'referencias' => [
            'cod_fme',
        ],
        'nombre' => 'ofi_fk_rac_fme',
    ],
    [
        'tabla' => 'asistencia_clase',
        'columnas' => [
            'cod_ses',
        ],
        'madre' => 'sesion_academica',
        'referencias' => [
            'cod_ses',
        ],
        'nombre' => 'ofi_fk_acl_ses',
    ],
    [
        'tabla' => 'asistencia_clase',
        'columnas' => [
            'cod_doc',
        ],
        'madre' => 'docente',
        'referencias' => [
            'cod_doc',
        ],
        'nombre' => 'ofi_fk_acl_doc',
    ],
    [
        'tabla' => 'asistencia_clase',
        'columnas' => [
            'cod_usu_reg',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_acl_usu',
    ],
    [
        'tabla' => 'asistencia_estudiante',
        'columnas' => [
            'cod_asi_cla',
        ],
        'madre' => 'asistencia_clase',
        'referencias' => [
            'cod_asi_cla',
        ],
        'nombre' => 'ofi_fk_aes_acl',
    ],
    [
        'tabla' => 'asistencia_estudiante',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_aes_est',
    ],
    [
        'tabla' => 'asistencia_estudiante',
        'columnas' => [
            'cod_est_asi',
        ],
        'madre' => 'estado_asistencia',
        'referencias' => [
            'cod_est_asi',
        ],
        'nombre' => 'ofi_fk_aes_eas',
    ],
    [
        'tabla' => 'asistencia_estudiante',
        'columnas' => [
            'cod_usu_reg',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_aes_usu',
    ],
    [
        'tabla' => 'asistencia_estudiante',
        'columnas' => [
            'cod_nes',
        ],
        'madre' => 'novedad_estudiante',
        'referencias' => [
            'cod_nes',
        ],
        'nombre' => 'ofi_fk_aes_nes',
    ],
    [
        'tabla' => 'calificacion',
        'columnas' => [
            'cod_ins',
        ],
        'madre' => 'inscripcion_estudiante',
        'referencias' => [
            'cod_ins',
        ],
        'nombre' => 'ofi_fk_cal_ins',
    ],
    [
        'tabla' => 'calificacion',
        'columnas' => [
            'cod_pas',
        ],
        'madre' => 'plan_asignatura',
        'referencias' => [
            'cod_pas',
        ],
        'nombre' => 'ofi_fk_cal_pas',
    ],
    [
        'tabla' => 'calificacion',
        'columnas' => [
            'cod_pes',
        ],
        'madre' => 'plan_especialidad',
        'referencias' => [
            'cod_pes',
        ],
        'nombre' => 'ofi_fk_cal_pes',
    ],
    [
        'tabla' => 'calificacion',
        'columnas' => [
            'cod_pev',
        ],
        'madre' => 'periodo_evaluacion',
        'referencias' => [
            'cod_pev',
        ],
        'nombre' => 'ofi_fk_cal_pev',
    ],
    [
        'tabla' => 'bitacora',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_bit_usu',
    ],
    [
        'tabla' => 'reportes_generados',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_rep_usu',
    ],
    [
        'tabla' => 'respaldo_gestion_academica',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_rga_gea',
    ],
    [
        'tabla' => 'respaldo_gestion_academica',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_rga_usu',
    ],
    [
        'tabla' => 'user_status_logs',
        'columnas' => [
            'cod_usu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_usl_usu',
    ],
    [
        'tabla' => 'user_status_logs',
        'columnas' => [
            'cod_usu_admin',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_usl_admin',
    ],
    [
        'tabla' => 'regente_asignaciones',
        'columnas' => [
            'cod_vpe',
        ],
        'madre' => 'vinculo_personal',
        'referencias' => [
            'cod_vpe',
        ],
        'nombre' => 'ofi_fk_ras_vpe',
    ],
    [
        'tabla' => 'regente_asignaciones',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_ras_gea',
    ],
    [
        'tabla' => 'regente_asignaciones',
        'columnas' => [
            'cod_cur',
        ],
        'madre' => 'curso',
        'referencias' => [
            'cod_cur',
        ],
        'nombre' => 'ofi_fk_ras_cur',
    ],
    [
        'tabla' => 'instrumento_pregunta',
        'columnas' => [
            'cod_ior',
        ],
        'madre' => 'instrumento_orientacion',
        'referencias' => [
            'cod_ior',
        ],
        'nombre' => 'ofi_fk_ipr_ior',
    ],
    [
        'tabla' => 'instrumento_pregunta',
        'columnas' => [
            'pre_ipr',
        ],
        'madre' => 'orientacion_preguntas',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_ipr_pre',
    ],
    [
        'tabla' => 'orientacion_actividades',
        'columnas' => [
            'cod_est',
        ],
        'madre' => 'estudiante',
        'referencias' => [
            'cod_est',
        ],
        'nombre' => 'ofi_fk_oac_est',
    ],
    [
        'tabla' => 'orientacion_actividades',
        'columnas' => [
            'cod_gea',
        ],
        'madre' => 'gestion_academica',
        'referencias' => [
            'cod_gea',
        ],
        'nombre' => 'ofi_fk_oac_gea',
    ],
    [
        'tabla' => 'orientacion_actividades',
        'columnas' => [
            'cod_ior',
        ],
        'madre' => 'instrumento_orientacion',
        'referencias' => [
            'cod_ior',
        ],
        'nombre' => 'ofi_fk_oac_ior',
    ],
    [
        'tabla' => 'orientacion_actividades',
        'columnas' => [
            'revisado_por',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_oac_revisor',
    ],
    [
        'tabla' => 'orientacion_respuestas',
        'columnas' => [
            'orientacion_actividad_id',
        ],
        'madre' => 'orientacion_actividades',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_ore_oac',
    ],
    [
        'tabla' => 'orientacion_respuestas',
        'columnas' => [
            'cod_ipr',
        ],
        'madre' => 'instrumento_pregunta',
        'referencias' => [
            'cod_ipr',
        ],
        'nombre' => 'ofi_fk_ore_ipr',
    ],
    [
        'tabla' => 'orientacion_resultados',
        'columnas' => [
            'orientacion_actividad_id',
        ],
        'madre' => 'orientacion_actividades',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_ors_oac',
    ],
    [
        'tabla' => 'orientacion_carreras_sugeridas',
        'columnas' => [
            'orientacion_resultado_id',
        ],
        'madre' => 'orientacion_resultados',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_ocs_ors',
    ],
    [
        'tabla' => 'orientacion_carreras_sugeridas',
        'columnas' => [
            'cod_car',
        ],
        'madre' => 'carrera',
        'referencias' => [
            'cod_car',
        ],
        'nombre' => 'ofi_fk_ocs_car',
    ],
    [
        'tabla' => 'sede_universidad',
        'columnas' => [
            'cod_uni',
        ],
        'madre' => 'universidad',
        'referencias' => [
            'cod_uni',
        ],
        'nombre' => 'ofi_fk_sed_uni',
    ],
    [
        'tabla' => 'oferta_academica',
        'columnas' => [
            'cod_sed',
        ],
        'madre' => 'sede_universidad',
        'referencias' => [
            'cod_sed',
        ],
        'nombre' => 'ofi_fk_ofa_sed',
    ],
    [
        'tabla' => 'oferta_academica',
        'columnas' => [
            'cod_car',
        ],
        'madre' => 'carrera',
        'referencias' => [
            'cod_car',
        ],
        'nombre' => 'ofi_fk_ofa_car',
    ],
    [
        'tabla' => 'version_oferta_academica',
        'columnas' => [
            'cod_ofa',
        ],
        'madre' => 'oferta_academica',
        'referencias' => [
            'cod_ofa',
        ],
        'nombre' => 'ofi_fk_vof_ofa',
    ],
    [
        'tabla' => 'orientacion_ofertas_sugeridas',
        'columnas' => [
            'sug_oos',
        ],
        'madre' => 'orientacion_carreras_sugeridas',
        'referencias' => [
            'id',
        ],
        'nombre' => 'ofi_fk_oos_ocs',
    ],
    [
        'tabla' => 'orientacion_ofertas_sugeridas',
        'columnas' => [
            'cod_vof',
        ],
        'madre' => 'version_oferta_academica',
        'referencias' => [
            'cod_vof',
        ],
        'nombre' => 'ofi_fk_oos_vof',
    ],
    [
        'tabla' => 'recurso_fuente',
        'columnas' => [
            'cod_uni',
        ],
        'madre' => 'universidad',
        'referencias' => [
            'cod_uni',
        ],
        'nombre' => 'ofi_fk_rfu_uni',
    ],
    [
        'tabla' => 'recurso_fuente',
        'columnas' => [
            'cod_sed',
        ],
        'madre' => 'sede_universidad',
        'referencias' => [
            'cod_sed',
        ],
        'nombre' => 'ofi_fk_rfu_sed',
    ],
    [
        'tabla' => 'captura_fuente',
        'columnas' => [
            'cod_rfu',
        ],
        'madre' => 'recurso_fuente',
        'referencias' => [
            'cod_rfu',
        ],
        'nombre' => 'ofi_fk_cfu_rfu',
    ],
    [
        'tabla' => 'captura_fuente',
        'columnas' => [
            'val_cfu',
        ],
        'madre' => 'users',
        'referencias' => [
            'cod_usu',
        ],
        'nombre' => 'ofi_fk_cfu_validador',
    ],
    [
        'tabla' => 'evidencia_academica',
        'columnas' => [
            'cod_vof',
        ],
        'madre' => 'version_oferta_academica',
        'referencias' => [
            'cod_vof',
        ],
        'nombre' => 'ofi_fk_eac_vof',
    ],
    [
        'tabla' => 'evidencia_academica',
        'columnas' => [
            'cod_cfu',
        ],
        'madre' => 'captura_fuente',
        'referencias' => [
            'cod_cfu',
        ],
        'nombre' => 'ofi_fk_eac_cfu',
    ],
];
