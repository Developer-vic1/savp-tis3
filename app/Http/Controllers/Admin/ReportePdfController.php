<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReporteGenerado;
use App\Services\Reportes\DatosReporteAcademicoService;
use App\Services\Reportes\DatosReporteAdministrativoService;
use App\Services\Reportes\DatosReporteVocacionalService;
use App\Services\Reportes\GeneradorMpdfService;
use App\Services\Reportes\GeneradorSqlAcademicoService;
use App\Services\Reportes\GeneradorZipReportesService;
use Illuminate\Support\Facades\Storage;

class ReportePdfController extends Controller
{
    public function __construct(
        protected DatosReporteAcademicoService     $datosAcademico,
        protected DatosReporteAdministrativoService $datosAdministrativo,
        protected DatosReporteVocacionalService    $datosVocacional,
        protected GeneradorMpdfService             $mpdf,
        protected GeneradorSqlAcademicoService     $sql,
        protected GeneradorZipReportesService      $zip,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. PDF ACADÉMICO GENERAL
    // ─────────────────────────────────────────────────────────────────────────

    public function academicoGeneral()
    {
        try {
            $datos = $this->datosAcademico->obtener();
            $ruta  = $this->mpdf->generarAcademicoGeneral($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte no pudo ser generado. Intente de nuevo.');
            }

            ReporteGenerado::registrar(
                'REP-ACA-' . now()->format('YmdHis'),
                'Reporte Académico General', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Academico-General.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte académico: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PDF CALIFICACIONES
    // ─────────────────────────────────────────────────────────────────────────

    public function calificaciones()
    {
        try {
            $datos = $this->datosAcademico->obtener();
            $ruta  = $this->mpdf->generarCalificaciones($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte de calificaciones no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-CAL-' . now()->format('YmdHis'),
                'Reporte de Calificaciones', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Calificaciones.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte de calificaciones: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. PDF ESTUDIANTES EN RIESGO
    // ─────────────────────────────────────────────────────────────────────────

    public function estudiantesRiesgo()
    {
        try {
            $datos = $this->datosAcademico->obtener();
            $ruta  = $this->mpdf->generarEstudiantesRiesgo($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte de estudiantes en riesgo no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-RIESGO-' . now()->format('YmdHis'),
                'Reporte Estudiantes en Riesgo', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Estudiantes-Riesgo.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte de estudiantes en riesgo: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. PDF ADMINISTRATIVO
    // ─────────────────────────────────────────────────────────────────────────

    public function administrativo()
    {
        try {
            $datos = $this->datosAdministrativo->obtener();
            $ruta  = $this->mpdf->generarAdministrativo($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte administrativo no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-ADM-' . now()->format('YmdHis'),
                'Reporte Administrativo', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Administrativo.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte administrativo: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. PDF BITÁCORA
    // ─────────────────────────────────────────────────────────────────────────

    public function bitacora()
    {
        try {
            $datos = $this->datosAdministrativo->obtener();
            $ruta  = $this->mpdf->generarBitacora($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte de bitácora no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-BIT-' . now()->format('YmdHis'),
                'Reporte Bitácora', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Bitacora.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte de bitácora: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. PDF VOCACIONAL RIASEC
    // ─────────────────────────────────────────────────────────────────────────

    public function vocacionalRiasec()
    {
        try {
            $datos = $this->datosVocacional->obtenerGeneral();
            $ruta  = $this->mpdf->generarVocacionalRiasec($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte vocacional RIASEC no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-RIASEC-' . now()->format('YmdHis'),
                'Reporte Vocacional RIASEC', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Vocacional-RIASEC.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte vocacional RIASEC: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. PDF COMPATIBILIDAD DE CARRERAS
    // ─────────────────────────────────────────────────────────────────────────

    public function compatibilidadCarreras()
    {
        try {
            $datos = $this->datosVocacional->obtenerCompatibilidad();
            $ruta  = $this->mpdf->generarCompatibilidadCarreras($datos);

            $path  = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte de compatibilidad de carreras no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-COMP-' . now()->format('YmdHis'),
                'Reporte Compatibilidad de Carreras', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Compatibilidad-Carreras.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte de compatibilidad de carreras: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. PDF INSTITUCIONAL COMPLETO
    // ─────────────────────────────────────────────────────────────────────────

    public function institucionalCompleto()
    {
        try {
            $datosAca = $this->datosAcademico->obtener();
            $datosAdm = $this->datosAdministrativo->obtener();
            $datosVoc = $this->datosVocacional->obtenerCompatibilidad();

            $ruta = $this->mpdf->generarInstitucionalCompleto(
                array_merge($datosAca, $datosAdm, $datosVoc)
            );

            $path = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El reporte institucional completo no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-INST-' . now()->format('YmdHis'),
                'Reporte Institucional Completo', 'pdf',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Reporte-Institucional-Completo.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el reporte institucional completo: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. RESPALDO SQL
    // ─────────────────────────────────────────────────────────────────────────

    public function respaldoSql()
    {
        try {
            $ruta = $this->sql->generar();

            $path = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El respaldo SQL no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-SQL-' . now()->format('YmdHis'),
                'Respaldo SQL Académico', 'sql',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Respaldo-Academico-' . now()->format('Ymd') . '.sql', [
                'Content-Type' => 'application/sql',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el respaldo SQL: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. PAQUETE ZIP
    // ─────────────────────────────────────────────────────────────────────────

    public function paqueteZip()
    {
        try {
            $ruta = $this->zip->generar();

            $path = Storage::disk('local')->path($ruta);
            if (! file_exists($path)) {
                return back()->with('error', 'El paquete ZIP no pudo ser generado.');
            }

            ReporteGenerado::registrar(
                'REP-ZIP-' . now()->format('YmdHis'),
                'Paquete ZIP de Reportes', 'zip',
                basename($path), $ruta, $path
            );

            return Storage::disk('local')->download($ruta, 'Paquete-Reportes-' . now()->format('Ymd') . '.zip', [
                'Content-Type' => 'application/zip',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Error al generar el paquete ZIP: ' . $e->getMessage());
        }
    }
}
