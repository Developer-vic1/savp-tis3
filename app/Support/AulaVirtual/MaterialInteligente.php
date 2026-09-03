<?php

namespace App\Support\AulaVirtual;

use App\Support\Core\SoporteInteligenteBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaterialInteligente extends SoporteInteligenteBase
{
    /**
     * Analiza la publicación de material didáctico en una clase virtual.
     */
    public function analizar(array $datos, ?string $ignorarCodMat = null): array
    {
        $bloqueos = [];
        $advertencias = [];
        $sugerencias = [];
        $hallazgos = [];
        $datosCalculados = [];
        $impacto = [];
        $fuentes = ['Gestión de Recursos Digitales SAVP LMS'];

        $codCla = trim((string) ($datos['cod_cla'] ?? ''));
        $titulo = trim((string) ($datos['tit_mat'] ?? $datos['nom_mat'] ?? ''));
        $url = trim((string) ($datos['url_mat'] ?? ''));
        $tieneArchivo = ! empty($datos['archivo']) || ! empty($datos['arc_mat']);

        // 1. Verificación de Clase
        if ($codCla === '') {
            $msg = 'Debe especificar la clase virtual a la que pertenece el material.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_MAT_CLASE_REQUERIDA', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        // 2. Título del material
        if ($titulo === '') {
            $msg = 'El título o nombre del material didáctico es obligatorio.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_MAT_TITULO_REQUERIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        } else {
            $tituloNorm = $this->normalizarTexto($titulo);
            $datosCalculados['titulo_normalizado'] = $tituloNorm;

            if ($codCla !== '' && Schema::hasTable('material_clase')) {
                $materialesExistentes = DB::table('material_clase')
                    ->where('cod_cla', $codCla)
                    ->when($ignorarCodMat, fn ($q) => $q->where('cod_mat', '!=', $ignorarCodMat))
                    ->pluck('nom_mat');

                foreach ($materialesExistentes as $matNom) {
                    $similitud = $this->calcularSimilitud($tituloNorm, (string) $matNom);
                    if ($similitud >= 92.0) {
                        $adv = "Ya existe un recurso con nombre muy similar ('{$matNom}') en esta clase.";
                        $advertencias[] = $adv;
                        $this->registrarHallazgo($hallazgos, 'AV_MAT_DUPLICADO', self::TIPO_PEDAGOGICA, self::COMP_ADVERTENCIA, $adv, self::RIESGO_MEDIO);
                        break;
                    }
                }
            }
        }

        // 3. Contenido (Archivo o URL)
        if (! $tieneArchivo && $url === '') {
            $msg = 'Debe adjuntar un archivo digital o proporcionar un enlace URL válido para el material.';
            $bloqueos[] = $msg;
            $this->registrarHallazgo($hallazgos, 'AV_MAT_SIN_CONTENIDO', self::TIPO_INTEGRIDAD, self::COMP_BLOQUEO, $msg, self::RIESGO_ALTO);
        }

        if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) {
            $adv = 'El formato del enlace URL no parece ser una dirección web estándar (e.g. https://...).';
            $advertencias[] = $adv;
            $this->registrarHallazgo($hallazgos, 'AV_MAT_URL_FORMATO', self::TIPO_INSTITUCIONAL, self::COMP_ADVERTENCIA, $adv, self::RIESGO_BAJO);
        }

        return $this->construirResultado(
            puedeContinuar: count($bloqueos) === 0,
            puedeGuardar: count($bloqueos) === 0,
            estado: count($bloqueos) > 0 ? self::ESTADO_BLOQUEADO : (count($advertencias) > 0 ? self::ESTADO_OBSERVADO : self::ESTADO_OK),
            nivelRiesgo: count($bloqueos) > 0 ? self::RIESGO_ALTO : (count($advertencias) > 0 ? self::RIESGO_MEDIO : self::RIESGO_BAJO),
            bloqueos: $bloqueos,
            advertencias: $advertencias,
            sugerencias: $sugerencias,
            hallazgos: $hallazgos,
            datosCalculados: $datosCalculados,
            impacto: $impacto,
            fuentesRegla: $fuentes
        );
    }
}
