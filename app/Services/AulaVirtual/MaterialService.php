<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\MaterialClase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MaterialService
{
    public function crear(array $datos, User $user, ?UploadedFile $archivo = null): MaterialClase
    {
        if ($archivo) {
            $ruta = $archivo->store('aula-virtual/materiales');
            $datos['rut_mat'] = $ruta;
            $datos['mime_mat'] = $archivo->getMimeType();
            $datos['tam_mat'] = $archivo->getSize();
        }

        $datos['cod_usu'] = $user->cod_usu;

        return MaterialClase::create($datos);
    }

    public function descargar(MaterialClase $material)
    {
        abort_if(! $material->rut_mat || ! Storage::exists($material->rut_mat), 404);

        return Storage::download($material->rut_mat, $material->nom_mat);
    }
}
