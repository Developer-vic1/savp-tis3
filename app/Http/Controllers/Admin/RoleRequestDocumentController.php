<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoleRequestService;
use Illuminate\Http\Request;

class RoleRequestDocumentController extends Controller
{
    public function __invoke(Request $request, string $roleRequest, RoleRequestService $service)
    {
        $service->authorize($request->user(), 'roles.documentos.ver');
        abort_unless($service->disponible(),404);
        $s=\App\Models\Oficial\Sistema\SolicitudRol::findOrFail($roleRequest);
        $storage=\Illuminate\Support\Facades\Storage::disk('local');
        abort_unless($storage->exists($s->documento_ruta),404);
        abort_unless(hash_file('sha256',$storage->path($s->documento_ruta))===$s->documento_sha256,409);
        return response()->file($storage->path($s->documento_ruta),['Content-Type'=>'application/pdf','Content-Disposition'=>'inline; filename="autorizacion-rol.pdf"','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
}
