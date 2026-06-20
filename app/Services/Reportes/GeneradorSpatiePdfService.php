<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Log;

/**
 * Servicio Spatie Laravel PDF.
 *
 * Queda preparado para reportes visuales cortos.
 * Si Chromium/Browsershot no está disponible, redirige a mPDF automáticamente.
 */
class GeneradorSpatiePdfService
{
    public function __construct(
        protected GeneradorMpdfService $mpdf,
    ) {}

    /**
     * Intenta generar con Spatie; si falla, usa mPDF como fallback.
     *
     * @param string $vista     Nombre de la vista Blade
     * @param array  $datos     Datos para la vista
     * @param string $archivo   Nombre del archivo de salida
     * @param string $subcarpeta Subcarpeta en storage
     * @return string           Ruta relativa del archivo generado
     */
    public function generar(string $vista, array $datos, string $archivo, string $subcarpeta): string
    {
        try {
            // Intentar con Spatie PDF (requiere Chromium instalado)
            $ruta = "reportes/{$subcarpeta}/{$archivo}";
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($ruta);

            \Spatie\LaravelPdf\Facades\Pdf::view($vista, $datos)
                ->format('a4')
                ->save($path);

            return $ruta;
        } catch (\Throwable $e) {
            // Fallback a mPDF si Spatie falla (Chromium no disponible)
            Log::warning('[GeneradorSpatiePdf] Spatie falló, usando mPDF como fallback.', [
                'error' => $e->getMessage(),
                'vista' => $vista,
            ]);

            return $this->mpdf->generar($vista, $datos, $archivo, $subcarpeta);
        }
    }
}
