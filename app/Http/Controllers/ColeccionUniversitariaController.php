<?php

namespace App\Http\Controllers;

use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Services\AporteIngenieril\DTO\AporteResponse;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ColeccionUniversitariaController extends Controller
{
    public function mostrar(Request $request)
    {
        $this->autorizar($request);

        return response()->view('conocimiento.coleccion-unifranz')->header('Cache-Control', 'no-store');
    }

    public function consultar(Request $request, AporteIngenierilClient $cliente)
    {
        $this->autorizar($request);

        return $this->respuesta($cliente->unifranzCollection());
    }

    public function iniciar(Request $request, AporteIngenierilClient $cliente)
    {
        $this->autorizar($request);
        if ($request->except('_token') !== []) {
            throw ValidationException::withMessages(['fuente' => 'Esta colección usa el índice oficial de UNIFRANZ. No admite otra URL ni un estado enviado desde el navegador.']);
        }

        return $this->respuesta($cliente->unifranzCollection(true));
    }

    public function descargar(Request $request, string $hash, AporteIngenierilClient $cliente)
    {
        $this->autorizar($request);
        $documento = $cliente->documentoUnifranz($hash);
        abort_unless($documento->available, 503, 'No se pudo preparar la copia guardada. Consulta su estado e inténtalo de nuevo.');

        return response()->streamDownload(function () use ($documento) {
            echo $documento->data['content'];
        }, $documento->data['filename'], ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function analizar(Request $request, AporteIngenierilClient $cliente)
    {
        $this->autorizar($request);
        if ($request->except('_token') !== []) {
            throw ValidationException::withMessages(['fuente' => 'El análisis usa las mallas guardadas. No admite contenido ni estados enviados desde el navegador.']);
        }

        return $this->respuesta($cliente->analizarColeccionUnifranz());
    }

    private function autorizar(Request $request): void
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($request->user()) === 'Administrador'
            && $request->user()->can('conocimiento.revisar'), 403);
    }

    private function respuesta(AporteResponse $resultado)
    {
        return response()->json($resultado->available ? $resultado->data : [
            'message' => $resultado->status === 409
                ? 'La colección ya está en curso. Consulta su avance; no es necesario iniciarla otra vez.'
                : 'No pudimos consultar la colección. La extracción guardada se conserva; comprueba la conexión del Aporte.',
        ], $resultado->available ? 200 : ($resultado->status === 409 ? 409 : 503))->header('Cache-Control', 'no-store');
    }
}
