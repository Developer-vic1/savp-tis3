<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\ClaseVirtual;
use App\Models\AulaVirtual\PublicacionClase;
use App\Services\BitacoraService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicacionService
{
    public function guardar(string $curso, ?string $id, array $data): PublicacionClase
    {
        $user = auth()->user();
        abort_unless($user && $user->can('Materiales_Aula'), 403);
        $class = app(CursoVirtualService::class)->cursoParaDocente($user, $curso);
        abort_unless($class && $class->est_cla === 'ACTIVA', 403);
        Gate::authorize('manage', $class);
        $data = validator($data, [
            'tit_pub' => ['required', 'string', 'max:180'], 'con_pub' => ['required', 'string', 'max:10000'],
            'tip_pub' => ['required', 'in:ANUNCIO,AVISO,MATERIAL,RECORDATORIO,GENERAL'],
            'est_pub' => ['required', 'in:BORRADOR,PUBLICADO,OCULTO'], 'motivo' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use ($curso, $id, $data, $user) {
            $class = ClaseVirtual::lockForUpdate()->findOrFail($curso);
            Gate::forUser($user)->authorize('manage', $class);
            abort_unless($class->est_cla === 'ACTIVA', 422);
            $record = $id ? $class->publicaciones()->lockForUpdate()->findOrFail($id)
                : new PublicacionClase(['cod_pub' => 'PUB_'.Str::upper(Str::random(16)), 'cod_cla' => $curso, 'cod_usu' => $user->cod_usu]);
            if ($record->exists && $record->est_pub !== 'BORRADOR' && blank($data['motivo'] ?? null)) {
                throw ValidationException::withMessages(['motivo' => 'Describe el motivo del cambio en la publicación.']);
            }
            $before = $record->exists ? $record->only(['est_pub', 'tip_pub']) : [];
            $reason = $data['motivo'] ?? null;
            unset($data['motivo']);
            $record->fill($data);
            if ($record->est_pub === 'PUBLICADO' && ! $record->fec_pub) {
                $record->fec_pub = now();
            }
            $record->save();
            BitacoraService::registrar(accion: $before ? 'EDITAR_PUBLICACION' : 'CREAR_PUBLICACION', tabla: 'publicacion_clase', registro: $record->cod_pub,
                modulo: 'Aula Virtual', valoresAnteriores: $before, valoresNuevos: $record->only(['est_pub', 'tip_pub']) + ['motivo' => $reason]);

            return $record;
        });
    }
}
