<?php

namespace App\Services\Reportes;

use App\Services\ReportAccessService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class GeneradorZipReportesService
{
    public function __construct(
        protected DatosReporteAcademicoService $datosAcademico,
        protected DatosReporteAdministrativoService $datosAdministrativo,
        protected DatosReporteVocacionalService $datosVocacional,
        protected GeneradorMpdfService $mpdf,
        protected GeneradorSqlAcademicoService $sql,
    ) {}

    /**
     * Genera el paquete ZIP completo con todos los reportes.
     * Retorna la ruta relativa dentro del disco 'private'.
     */
    public function generar(): string
    {
        app(ReportAccessService::class)->authorize(auth()->user(), ['Reportes_Academicos', 'Reportes_Administrativos', 'Bitacora', 'Gestion_Academica']);
        $timestamp = now()->format('Ymd-His').'-'.Str::random(12);
        $nombreZip = "paquete-reportes-gestion-{$timestamp}.zip";
        $rutaZip = "reportes/zip/{$nombreZip}";
        Storage::disk('local')->makeDirectory('reportes/zip');
        $pathZip = Storage::disk('local')->path($rutaZip);

        $observaciones = [];
        $archivosTemp = [];

        // ── 1. Reporte Académico General ──────────────────────────────────────
        try {
            $datos = $this->datosAcademico->obtener();
            $archivosTemp['academicos']['01-reporte-academico-general.pdf']
                = $this->mpdf->generarAcademicoGeneral($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte académico general: '.'No disponible.';
            Log::warning('[ZIP] Falló reporte académico general.', ['exception' => $e::class]);
        }

        // ── 2. Reporte Calificaciones ─────────────────────────────────────────
        try {
            $datos = $this->datosAcademico->obtener();
            $archivosTemp['academicos'] = array_merge($archivosTemp['academicos'] ?? [], $this->mpdf->generarCalificacionesPorPartes($datos));
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte calificaciones: '.'No disponible.';
        }

        // ── 3. Reporte Estudiantes en Riesgo ──────────────────────────────────
        try {
            $datos = $this->datosAcademico->obtener();
            $archivosTemp['academicos']['03-reporte-estudiantes-en-riesgo.pdf']
                = $this->mpdf->generarEstudiantesRiesgo($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte estudiantes en riesgo: '.'No disponible.';
        }

        // ── 4. Reporte Administrativo ─────────────────────────────────────────
        try {
            $datos = $this->datosAdministrativo->obtener();
            $archivosTemp['administrativos']['04-reporte-administrativo.pdf']
                = $this->mpdf->generarAdministrativo($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte administrativo: '.'No disponible.';
        }

        // ── 5. Reporte Bitácora ───────────────────────────────────────────────
        try {
            $datos = $this->datosAdministrativo->obtener();
            $archivosTemp['administrativos']['05-reporte-bitacora.pdf']
                = $this->mpdf->generarBitacora($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte bitácora: '.'No disponible.';
        }

        // ── 6. Reporte Vocacional RIASEC ──────────────────────────────────────
        try {
            $datos = $this->datosVocacional->obtenerGeneral();
            $archivosTemp['vocacionales']['06-reporte-vocacional-riasec.pdf']
                = $this->mpdf->generarVocacionalRiasec($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte RIASEC: '.'No disponible.';
        }

        // ── 7. Reporte Compatibilidad de Carreras ─────────────────────────────
        try {
            $datos = $this->datosVocacional->obtenerCompatibilidad();
            $archivosTemp['vocacionales']['07-reporte-compatibilidad-carreras.pdf']
                = $this->mpdf->generarCompatibilidadCarreras($datos);
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte compatibilidad carreras: '.'No disponible.';
        }

        // ── 8. Reporte Institucional Completo ─────────────────────────────────
        try {
            $datosAca = $this->datosAcademico->obtener();
            $datosAdm = $this->datosAdministrativo->obtener();
            $datosVoc = $this->datosVocacional->obtenerCompatibilidad();
            $archivosTemp['completo']['08-reporte-institucional-completo.pdf']
                = $this->mpdf->generarInstitucionalCompleto(array_merge($datosAca, $datosAdm, $datosVoc));
        } catch (\Throwable $e) {
            $observaciones[] = 'Reporte institucional completo: '.'No disponible.';
        }

        // ── 9. Respaldo SQL ───────────────────────────────────────────────────
        try {
            $archivosTemp['sql']['09-respaldo-gestion-academica.sql']
                = $this->sql->generar();
        } catch (\Throwable $e) {
            $observaciones[] = 'Respaldo SQL: '.'No disponible.';
        }

        // ── Construir ZIP ─────────────────────────────────────────────────────
        $zip = new ZipArchive;

        if ($zip->open($pathZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP.');
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
        if (! empty($observaciones)) {
            $obs = "OBSERVACIONES DE GENERACIÓN\n";
            $obs .= 'Fecha: '.now()->format('d/m/Y H:i:s')."\n\n";
            foreach ($observaciones as $o) {
                $obs .= "- {$o}\n";
            }
            $zip->addFromString('OBSERVACIONES.txt', $obs);
        }

        if (! $zip->close()) {
            throw new \RuntimeException('No se pudo completar el archivo ZIP.');
        }

        return $rutaZip;
    }
}
