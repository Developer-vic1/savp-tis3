<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\MaterialClase;
use App\Models\User;
use App\Services\BitacoraService;
use App\Support\PrivateFilePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MaterialService
{
    public function crear(array $datos, User $user, ?UploadedFile $archivo = null): MaterialClase
    {
        $class = ClaseVirtual::findOrFail($datos['cod_cla'] ?? '');
        Gate::forUser($user)->authorize('manage', $class);
        abort_unless($class->est_cla === 'ACTIVA' && $user->can('Materiales_Aula'), 403);
        $datos = validator($datos, [
            'cod_cla' => ['required', 'string'],
            'nom_mat' => ['required', 'string', 'max:180'],
            'tip_mat' => ['required', 'in:ARCHIVO,ENLACE,PDF,VIDEO,IMAGEN,DOCUMENTO,OTRO'],
            'url_mat' => ['nullable', 'url:http,https', 'max:500'],
            'est_mat' => ['required', 'in:ACTIVO,OCULTO'],
        ])->validate();
        validator(['archivo' => $archivo], ['archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,mp4,txt']])->validate();
        if (! $archivo && empty($datos['url_mat'])) {
            throw ValidationException::withMessages(['url_mat' => 'Adjunta un archivo o indica un enlace.']);
        }
        $path = null;
        try {
            if ($archivo) {
                $path = $archivo->store('aula-virtual/materiales', 'local');
                abort_unless($path, 503, 'No fue posible guardar el archivo.');
                $datos += ['rut_mat' => $path, 'mime_mat' => $archivo->getMimeType(), 'tam_mat' => $archivo->getSize()];
            }

            return DB::transaction(function () use ($datos, $user) {
                $class = ClaseVirtual::lockForUpdate()->findOrFail($datos['cod_cla']);
                Gate::forUser($user)->authorize('manage', $class);
                abort_unless($class->est_cla === 'ACTIVA', 422, 'El curso está cerrado para cambios.');
                $material = MaterialClase::create($datos + ['cod_usu' => $user->cod_usu, 'cod_mat' => 'MAT_'.Str::upper(Str::random(16))]);
                BitacoraService::registrar(accion: 'CREAR_MATERIAL', tabla: 'material_clase', registro: $material->cod_mat,
                    modulo: 'Aula Virtual', valoresNuevos: $material->only(['cod_cla', 'tip_mat', 'est_mat']));

                return $material;
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
    }

    public function state(MaterialClase $material, string $state, User $user): void
    {
        Gate::forUser($user)->authorize('update', $material);
        abort_unless($material->claseVirtual->est_cla === 'ACTIVA' && $user->can('Materiales_Aula'), 403);
        abort_unless(in_array($state, ['ACTIVO', 'OCULTO'], true), 422);
        DB::transaction(function () use ($material, $state, $user) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($material->cod_cla);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $locked = MaterialClase::lockForUpdate()->findOrFail($material->cod_mat);
            Gate::forUser($user)->authorize('update', $locked);
            $before = $locked->only('est_mat');
            $locked->update(['est_mat' => $state]);
            BitacoraService::registrar(accion: 'ESTADO_MATERIAL', tabla: 'material_clase', registro: $locked->cod_mat,
                modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: ['est_mat' => $state]);
        });
    }

    public function actualizar(MaterialClase $material, array $data, User $user): void
    {
        Gate::forUser($user)->authorize('update', $material);
        abort_unless($material->claseVirtual->est_cla === 'ACTIVA' && $user->can('Materiales_Aula'), 403);
        $data = validator($data, [
            'nom_mat' => ['required', 'string', 'max:180'],
            'tip_mat' => ['required', 'in:ARCHIVO,ENLACE,PDF,VIDEO,IMAGEN,DOCUMENTO,OTRO'],
            'url_mat' => ['nullable', 'url:http,https', 'max:500'],
            'est_mat' => ['required', 'in:ACTIVO,OCULTO'],
        ])->validate();
        DB::transaction(function () use ($material, $data, $user) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($material->cod_cla);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $locked = MaterialClase::lockForUpdate()->findOrFail($material->cod_mat);
            Gate::forUser($user)->authorize('update', $locked);
            $before = $locked->only(['tip_mat', 'est_mat']);
            if (! $locked->rut_mat && empty($data['url_mat'])) {
                throw ValidationException::withMessages(['url_mat' => 'El material necesita un archivo o un enlace.']);
            }
            $locked->update($data);
            BitacoraService::registrar(accion: 'EDITAR_MATERIAL', tabla: 'material_clase', registro: $locked->cod_mat,
                modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: $locked->only(['tip_mat', 'est_mat']));
        });
    }

    public function descargar(MaterialClase $material)
    {
        Gate::authorize('view', $material);
        abort_unless(PrivateFilePath::valid($material->rut_mat, 'aula-virtual/materiales'), 404);
        abort_unless(Storage::disk('local')->exists($material->rut_mat), 404);

        return Storage::disk('local')->download($material->rut_mat, basename($material->rut_mat), ['X-Content-Type-Options' => 'nosniff']);
    }
}
