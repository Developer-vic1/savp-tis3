<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use App\Services\RoleRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoleRequestDocumentController extends Controller
{
    public function __invoke(Request $request, RoleRequest $roleRequest, RoleRequestService $service)
    {
        $service->authorize($request->user(), 'roles.documentos.ver');
        abort_unless(Storage::disk('local')->exists($roleRequest->document_path), 404);
        $extension = match ($roleRequest->document_mime) { 'application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', default => null };
        abort_unless($extension, 404);
        return Storage::disk('local')->download($roleRequest->document_path, 'autorizacion-'.$roleRequest->id.'.'.$extension,
            ['Content-Type' => $roleRequest->document_mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
