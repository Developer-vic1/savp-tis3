<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class GeneradorMpdfService
{
    protected string $logoPath;
    protected string $institución = 'Unidad Educativa Técnico Humanístico "Franz Tamayo" N° 3';
    protected string $sistema     = 'Sistema Web de Orientación Académico-Vocacional (SAVP-TIS3)';

    public function __construct()
    {
        // Buscar logo
        $posibles = [
            public_path('image/LOGO FT3 A.jpg'),
            public_path('images/logo.png'),
            public_path('img/logo.png'),
        ];
        $this->logoPath = '';
        foreach ($posibles as $p) {
            if (file_exists($p)) {
                $this->logoPath = $p;
                break;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTODO GENÉRICO DE GENERACIÓN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera un PDF y lo guarda en storage. Retorna la ruta relativa.
     */
    public function generar(string $vista, array $datos, string $nombreArchivo, string $subcarpeta): string
    {
        $html = view($vista, $datos)->render();

        $mpdf = $this->crearInstancia();
        $mpdf->WriteHTML($this->estilosGlobales() . $html);

        $ruta = "reportes/{$subcarpeta}/{$nombreArchivo}";
        $path = Storage::disk('local')->path($ruta);

        $mpdf->Output($path, 'F');

        return $ruta;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF ACADÉMICO GENERAL
    // ─────────────────────────────────────────────────────────────────────────

    public function generarAcademicoGeneral(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-ACA-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.academico-general',
            $datos,
            'reporte-academico-general-' . now()->format('Ymd-His') . '.pdf',
            'academicos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF CALIFICACIONES
    // ─────────────────────────────────────────────────────────────────────────

    public function generarCalificaciones(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-CAL-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.calificaciones',
            $datos,
            'reporte-calificaciones-' . now()->format('Ymd-His') . '.pdf',
            'academicos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF ESTUDIANTES EN RIESGO
    // ─────────────────────────────────────────────────────────────────────────

    public function generarEstudiantesRiesgo(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-RIESGO-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.estudiantes-riesgo',
            $datos,
            'reporte-estudiantes-riesgo-' . now()->format('Ymd-His') . '.pdf',
            'academicos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF ADMINISTRATIVO
    // ─────────────────────────────────────────────────────────────────────────

    public function generarAdministrativo(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-ADM-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.administrativo',
            $datos,
            'reporte-administrativo-' . now()->format('Ymd-His') . '.pdf',
            'administrativos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF BITÁCORA
    // ─────────────────────────────────────────────────────────────────────────

    public function generarBitacora(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-BIT-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.bitacora',
            $datos,
            'reporte-bitacora-' . now()->format('Ymd-His') . '.pdf',
            'administrativos'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF VOCACIONAL RIASEC
    // ─────────────────────────────────────────────────────────────────────────

    public function generarVocacionalRiasec(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-RIASEC-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.vocacional-riasec',
            $datos,
            'reporte-vocacional-riasec-' . now()->format('Ymd-His') . '.pdf',
            'vocacionales'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF COMPATIBILIDAD DE CARRERAS
    // ─────────────────────────────────────────────────────────────────────────

    public function generarCompatibilidadCarreras(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-COMP-' . now()->format('Ymd') . '-001';

        return $this->generar(
            'pdf.reportes.compatibilidad-carreras',
            $datos,
            'reporte-compatibilidad-carreras-' . now()->format('Ymd-His') . '.pdf',
            'vocacionales'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF INSTITUCIONAL COMPLETO
    // ─────────────────────────────────────────────────────────────────────────

    public function generarInstitucionalCompleto(array $datos): string
    {
        $datos['logoPath']      = $this->logoPath;
        $datos['institucion']   = $this->institución;
        $datos['sistema']       = $this->sistema;
        $datos['fecha']         = now()->format('d/m/Y H:i');
        $datos['codigoReporte'] = 'REP-INST-' . now()->format('Ymd') . '-001';

        $mpdf = $this->crearInstancia(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Reporte Institucional Completo - SAVP-TIS3');

        $html = view('pdf.reportes.institucional-completo', $datos)->render();
        $mpdf->WriteHTML($this->estilosGlobales() . $html);

        $nombre = 'reporte-institucional-completo-' . now()->format('Ymd-His') . '.pdf';
        $ruta   = "reportes/completos/{$nombre}";
        $path   = Storage::disk('local')->path($ruta);

        $mpdf->Output($path, 'F');

        return $ruta;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────────────────────────────────

    protected function crearInstancia(array $extra = []): Mpdf
    {
        return new Mpdf(array_merge([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_left'       => 15,
            'margin_right'      => 15,
            'margin_top'        => 18,
            'margin_bottom'     => 18,
            'margin_header'     => 6,
            'margin_footer'     => 6,
            'default_font_size' => 9,
            'default_font'      => 'dejavusans',
            'tempDir'           => Storage::disk('local')->path('reportes/temp'),
        ], $extra));
    }

    protected function estilosGlobales(): string
    {
        return <<<'CSS'
        <style>
        body { font-family: dejavusans, Arial, sans-serif; font-size: 9pt; color: #0f172a; margin: 0; padding: 0; }

        /* ── HEADER ─────────────────────────────── */
        .pdf-header { width: 100%; border-bottom: 3px solid #059669; padding-bottom: 8px; margin-bottom: 14px; }
        .pdf-header-inner { display: flex; align-items: center; justify-content: space-between; }
        .pdf-logo { width: 52px; height: 52px; border-radius: 50%; object-fit: contain; border: 2px solid #a7f3d0; }
        .pdf-logo-fallback { width: 52px; height: 52px; border-radius: 50%; background: #059669; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14pt; font-weight: bold; text-align: center; line-height: 52px; }
        .pdf-inst { flex: 1; padding: 0 10px; }
        .pdf-inst-name { font-size: 11pt; font-weight: bold; color: #0f172a; }
        .pdf-inst-sub  { font-size: 8pt; color: #334155; margin-top: 2px; }
        .pdf-fecha { font-size: 7pt; color: #64748b; text-align: right; white-space: nowrap; }

        /* ── TÍTULOS ─────────────────────────────── */
        .pdf-title { font-size: 16pt; font-weight: bold; color: #059669; border-bottom: 2px solid #a7f3d0; padding-bottom: 5px; margin: 12px 0 8px 0; }
        .pdf-subtitle { font-size: 12pt; font-weight: bold; color: #0f172a; margin: 10px 0 5px 0; }
        .pdf-section { font-size: 10pt; font-weight: bold; color: #334155; margin: 12px 0 5px 0; padding: 4px 8px; background: #f1f5f9; border-left: 3px solid #059669; }
        .pdf-kicker { font-size: 7pt; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px; }
        .pdf-code { font-size: 7pt; color: #64748b; font-style: italic; }

        /* ── KPI CARDS ───────────────────────────── */
        .kpi-grid { width: 100%; margin: 10px 0; }
        .kpi-card { display: inline-block; width: 22%; margin: 0 1%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; vertical-align: top; }
        .kpi-label { font-size: 7pt; color: #64748b; font-weight: bold; }
        .kpi-value { font-size: 18pt; font-weight: bold; margin-top: 2px; }
        .kpi-green  { color: #059669; }
        .kpi-blue   { color: #0284c7; }
        .kpi-red    { color: #dc2626; }
        .kpi-violet { color: #7c3aed; }
        .kpi-amber  { color: #d97706; }

        /* ── TABLAS ──────────────────────────────── */
        table.pdf-table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8.5pt; }
        table.pdf-table th { background: #ecfdf5; color: #047857; font-weight: bold; padding: 6px 8px; border: 1px solid #a7f3d0; text-align: left; font-size: 8pt; }
        table.pdf-table td { padding: 5px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        table.pdf-table tr:nth-child(even) td { background: #f8fafc; }

        table.pdf-table-adm th { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
        table.pdf-table-adm tr:nth-child(even) td { background: #f0f9ff; }

        table.pdf-table-danger th { background: #fff1f2; color: #b91c1c; border-color: #fecdd3; }
        table.pdf-table-danger tr:nth-child(even) td { background: #fff1f2; }

        /* ── BADGES ──────────────────────────────── */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 7.5pt; font-weight: bold; }
        .badge-green  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-blue   { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-red    { background: #fff1f2; color: #dc2626; border: 1px solid #fecdd3; }
        .badge-amber  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-violet { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .badge-gray   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* ── BARRAS DE PROGRESO ───────────────────── */
        .bar-wrap { background: #e2e8f0; border-radius: 4px; height: 10px; width: 100%; margin: 4px 0; }
        .bar-fill { height: 10px; border-radius: 4px; }
        .bar-green  { background: #059669; }
        .bar-blue   { background: #0284c7; }
        .bar-red    { background: #dc2626; }
        .bar-amber  { background: #d97706; }
        .bar-violet { background: #7c3aed; }

        /* ── ALERTS ──────────────────────────────── */
        .alert { padding: 8px 12px; border-radius: 6px; margin: 6px 0; font-size: 8.5pt; }
        .alert-info    { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .alert-danger  { background: #fff1f2; color: #dc2626; border: 1px solid #fecdd3; }

        /* ── FOOTER ──────────────────────────────── */
        .pdf-footer { border-top: 1px solid #e2e8f0; padding-top: 5px; margin-top: 16px; font-size: 7pt; color: #64748b; }
        .pdf-empty { text-align: center; padding: 30px; font-size: 9pt; color: #64748b; font-style: italic; border: 1px dashed #e2e8f0; border-radius: 6px; margin: 10px 0; }

        /* ── RIASEC ──────────────────────────────── */
        .riasec-grid { width: 100%; }
        .riasec-item { display: inline-block; width: 30%; margin: 1%; vertical-align: top; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; }
        .riasec-letra { font-size: 22pt; font-weight: bold; color: #7c3aed; text-align: center; }
        .riasec-nombre { font-size: 9pt; font-weight: bold; color: #0f172a; text-align: center; }
        .riasec-desc { font-size: 7.5pt; color: #64748b; margin-top: 4px; }

        /* ── SALTOS ──────────────────────────────── */
        .page-break { page-break-after: always; }
        </style>
        CSS;
    }

    /**
     * Genera el encabezado HTML institucional.
     */
    public function htmlHeader(array $datos): string
    {
        $logoHtml = $datos['logoPath'] && file_exists($datos['logoPath'])
            ? '<img src="' . $datos['logoPath'] . '" class="pdf-logo" alt="Logo FT3">'
            : '<div class="pdf-logo-fallback">FT3</div>';

        $codigo = $datos['codigoReporte'] ?? '';
        $fecha  = $datos['fecha'] ?? now()->format('d/m/Y H:i');

        return <<<HTML
        <div class="pdf-header">
            <table style="width:100%; border:0; border-collapse:collapse;">
            <tr>
                <td style="width:60px; vertical-align:middle;">{$logoHtml}</td>
                <td style="padding: 0 10px; vertical-align:middle;">
                    <div class="pdf-inst-name">{$datos['institucion']}</div>
                    <div class="pdf-inst-sub">{$datos['sistema']}</div>
                    <div class="pdf-code">Código: {$codigo}</div>
                </td>
                <td style="text-align:right; vertical-align:middle;">
                    <div class="pdf-fecha">Generado el<br><strong>{$fecha}</strong></div>
                </td>
            </tr>
            </table>
        </div>
        HTML;
    }

    /**
     * Genera el pie de página HTML.
     */
    public function htmlFooter(string $sistema): string
    {
        return <<<HTML
        <div class="pdf-footer">
            <table style="width:100%; border:0; border-collapse:collapse;">
            <tr>
                <td>Generado por <strong>{$sistema}</strong></td>
                <td style="text-align:right; color:#64748b;">Documento confidencial · Uso institucional exclusivo</td>
            </tr>
            </table>
        </div>
        HTML;
    }
}
