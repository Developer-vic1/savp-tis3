<?php

namespace App\Http\Controllers;

use App\Models\Oficial\Academico\ReferenciaDocumental;
use App\Services\ReferenciaDocumentalService;
use Illuminate\Support\Facades\Storage;

class ReferenciaDocumentalController extends Controller
{
    public function imagen(string $id)
    {
        $autoridad = app(ReferenciaDocumentalService::class)->autorizar();
        $referencia = ReferenciaDocumental::findOrFail($id);
        abort_unless(auth()->user()->hasRole('Administrador') || ($referencia->vigente &&
            ($referencia->tipo === 'SELLO' || $referencia->cod_pin === $autoridad['director']->cod_pin)), 403);
        abort_unless(Storage::disk('local')->exists($referencia->ruta), 404);
        $ruta = Storage::disk('local')->path($referencia->ruta);
        abort_unless(hash_equals($referencia->sha256, hash_file('sha256', $ruta)), 409);
        return response()->file($ruta, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
