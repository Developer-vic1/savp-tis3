<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GeneradorZipReportesService
{
    public function __construct(
        protected DatosReporteAcademicoService   $datosAcademico,
        protected DatosReporteAdministrativoService $datosAdministrativo,
        protected DatosReporteVocacionalService  $datosVocacional,
        protected GeneradorMpdfService           $mpdf,
        protected GeneradorSqlAcademicoService   $sql,
    ) {}

    /**
     * Genera el paquete ZIP completo con todos los reportes.
     * Retorna la ruta relativa dentro del disco 'private'.
     */
    public function generar(): string
    {
        $timestamp = now()->format('Ymd-His');
        $nombreZip = "paquete-reportes-gestion-{$timestamp}.zip";
        $rutaZip   = "reportes/zip/{$nombreZip}";
        $pathZip   = Storage::disk('local')->path($rutaZip);

        $observaciones = [];
        $archivosTemp  = [];

        // ── 1. Reporte Académico General ──────────────────────────────────────
        try {
            $datos   = $this->datosAcademico->obtener();
            $archivosTemp['academicos']['01-reporte-academico-general.pdf']
                = $this->mpdf->generarAcademicoGeneral($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte académico general: ' . $e->getMessage();
            Log::warning('[ZIP] Falló reporte académico general.', ['e' => $e->getMessage()]);
        }

        // ── 2. Reporte Calificaciones ─────────────────────────────────────────
        try {
            $datos   = $this->datosAcademico->obtener();
            $archivosTemp['academicos']['02-reporte-calificaciones.pdf']
                = $this->mpdf->generarCalificaciones($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte calificaciones: ' . $e->getMessage();
        }

        // ── 3. Reporte Estudiantes en Riesgo ──────────────────────────────────
        try {
            $datos   = $this->datosAcademico->obtener();
            $archivosTemp['academicos']['03-reporte-estudiantes-en-riesgo.pdf']
                = $this->mpdf->generarEstudiantesRiesgo($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte estudiantes en riesgo: ' . $e->getMessage();
        }

        // ── 4. Reporte Administrativo ─────────────────────────────────────────
        try {
            $datos   = $this->datosAdministrativo->obtener();
            $archivosTemp['administrativos']['04-reporte-administrativo.pdf']
                = $this->mpdf->generarAdministrativo($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte administrativo: ' . $e->getMessage();
        }

        // ── 5. Reporte Bitácora ───────────────────────────────────────────────
        try {
            $datos   = $this->datosAdministrativo->obtener();
            $archivosTemp['administrativos']['05-reporte-bitacora.pdf']
                = $this->mpdf->generarBitacora($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte bitácora: ' . $e->getMessage();
        }

        // ── 6. Reporte Vocacional RIASEC ──────────────────────────────────────
        try {
            $datos   = $this->datosVocacional->obtenerGeneral();
            $archivosTemp['vocacionales']['06-reporte-vocacional-riasec.pdf']
                = $this->mpdf->generarVocacionalRiasec($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte RIASEC: ' . $e->getMessage();
        }

        // ── 7. Reporte Compatibilidad de Carreras ─────────────────────────────
        try {
            $datos   = $this->datosVocacional->obtenerCompatibilidad();
            $archivosTemp['vocacionales']['07-reporte-compatibilidad-carreras.pdf']
                = $this->mpdf->generarCompatibilidadCarreras($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte compatibilidad carreras: ' . $e->getMessage();
        }

        // ── 8. Reporte Institucional Completo ─────────────────────────────────
        try {
            $datosAca  = $this->datosAcademico->obtener();
            $datosAdm  = $this->datosAdministrativo->obtener();
            $datosVoc  = $this->datosVocacional->obtenerCompatibilidad();
            $archivosTemp['completo']['08-reporte-institucional-completo.pdf']
                = $this->mpdf->generarInstitucionalCompleto(array_merge($datosAca, $datosAdm, $datosVoc));
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte institucional completo: ' . $e->getMessage();
        }

        // ── 9. Respaldo SQL ───────────────────────────────────────────────────
        try {
            $archivosTemp['sql']['09-respaldo-gestion-academica.sql']
                = $this->sql->generar();
        } catch (\Throwable $e) {
            $observaciones[] = 'Respaldo SQL: ' . $e->getMessage();
        }

        // ── Construir ZIP ─────────────────────────────────────────────────────
        $zip = new ZipArchive();

        if ($zip->open($pathZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("No se pudo crear el archivo ZIP en: {$pathZip}");
        }

        foreach ($archivosTemp as $carpeta => $archivos) {
            foreach ($archivos as $nombreEnZip => $rutaRelativa) {
                $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);
                if (file_exists($rutaAbsoluta)) {
                    $zip->addFile($rutaAbsoluta, "{$carpeta}/{$nombreEnZip}");
                } else {
                    $observaciones[] = "Archivo no encontrado: {$nombreEnZip}";
                }
            }
        }

        // Agregar archivo de observaciones si hay errores
        if (!empty($observaciones)) {
            $obs = "OBSERVACIONES DE GENERACIÓN\n";
            $obs .= "Fecha: " . now()->format('d/m/Y H:i:s') . "\n\n";
            foreach ($observaciones as $o) {
                $obs .= "- {$o}\n";
            }
            $zip->addFromString('OBSERVACIONES.txt', $obs);
        }

        $zip->close();

        return $rutaZip;
    }
}
