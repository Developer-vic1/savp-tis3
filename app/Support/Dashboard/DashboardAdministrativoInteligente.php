<?php

namespace App\Support\Dashboard;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardAdministrativoInteligente extends SoporteInteligenteBase
{
    /**
     * Sintetiza y prioriza los indicadores clave para la dirección y administración.
     */
    public function compilarIndicadores(): array
    {
        $alertas = [];
        $sugerencias = [];
        $hallazgos = [];
        $metricas = [];
        $fuentes = ['Gestión Administrativa y Padrón Escolar SAVP'];

        // 1. Usuarios sin rol
        if (Schema::hasTable('users') && Schema::hasTable('model_has_roles')) {
            $usuariosSinRol = DB::table('users')
                ->where('est_usu', 'ACTIVO')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.model_id', 'users.cod_usu');
                })
                ->count();

            $metricas['usuarios_sin_rol'] = $usuariosSinRol;
            if ($usuariosSinRol > 0) {
                $adv = "Existen {$usuariosSinRol} usuario(s) activo(s) sin rol asignado en el sistema.";
                $alertas[] = $adv;
                $this->registrarHallazgo($hallazgos, 'DASH_ADM_USR_SIN_ROL', self::TIPO_INTEGRIDAD, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO, ['cantidad' => $usuariosSinRol]);
            }
        }

        // 2. Docentes sin carga horaria activa
        if (Schema::hasTable('docente') && Schema::hasTable('plan_asignatura')) {
            $docentesSinCarga = DB::table('docente')
                ->where('est_doc', 'ACTIVO')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('plan_asignatura')
                        ->whereColumn('plan_asignatura.cod_doc', 'docente.cod_doc')
                        ->where('plan_asignatura.est_pas', 'ACTIVO');
                })
                ->count();

            $metricas['docentes_sin_carga'] = $docentesSinCarga;
            if ($docentesSinCarga > 0) {
                $sug = "Existen {$docentesSinCarga} docente(s) registrado(s) sin asignaturas asignadas en la gestión.";
                $sugerencias[] = $sug;
                $this->registrarHallazgo($hallazgos, 'DASH_ADM_DOC_SIN_CARGA', self::TIPO_PEDAGOGICA, self::COMP_SUGERENCIA, $sug, self::RIESGO_BAJO);
            }
        }

        // 3. Estudiantes activos
        if (Schema::hasTable('estudiante')) {
            $metricas['total_estudiantes_activos'] = DB::table('estudiante')->where('est_est', 'ACTIVO')->count();
        }

        return $this->construirResultado(
            puedeContinuar: true,
            puedeGuardar: true,
            estado: count($alertas) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK,
            nivelRiesgo: count($alertas) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO,
            advertencias: $alertas,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $metricas,
            fuentesRegla: $fuentes
        );
    }
}
