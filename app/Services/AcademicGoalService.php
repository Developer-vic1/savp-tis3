<?php

namespace App\Services;

use App\Models\MetaAcademica;
use App\Models\User;
use App\Services\AulaVirtual\CursoVirtualService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AcademicGoalService
{
    public function available(): bool
    {
        return config('features.academic_goals', false) && Schema::hasTable('metas_academicas');
    }

    public function snapshot(User $user): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        if (! $this->available()) {
            return ['available' => false, 'goals' => null];
        }
        $student = app(CursoVirtualService::class)->estudianteDeUsuario($user);
        abort_unless($student, 403);

        return ['available' => true, 'goals' => MetaAcademica::where('cod_est', $student->cod_est)->latest()->paginate(15)];
    }

    public function save(User $user, array $data, ?string $id = null): MetaAcademica
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        abort_unless($this->available(), 409, 'El registro de metas requiere habilitación institucional.');
        $student = app(CursoVirtualService::class)->estudianteDeUsuario($user);
        abort_unless($student, 403);
        $data = $this->validateDraft($user, $data, $id !== null);

        return DB::transaction(function () use ($user, $student, $data, $id) {
            $goal = $id ? MetaAcademica::where('cod_est', $student->cod_est)->whereKey($id)->lockForUpdate()->firstOrFail() : new MetaAcademica;
            if ($id) {
                Gate::forUser($user)->authorize('update', $goal);
            }
            $before = $id ? $goal->only(['titulo', 'objetivo', 'accion', 'fecha_objetivo', 'estado']) : null;
            if (! $id) {
                abort_unless(in_array($data['estado'], ['BORRADOR', 'ACTIVA'], true), 422);
            }
            $allowed = match ($goal->estado) {
                'ACTIVA' => ['ACTIVA', 'COMPLETADA', 'CANCELADA'], 'COMPLETADA' => ['COMPLETADA'],
                'CANCELADA' => ['CANCELADA'], default => ['BORRADOR', 'ACTIVA', 'CANCELADA'],
            };
            if ($id) {
                abort_unless(in_array($data['estado'], $allowed, true), 422, 'La transición de estado requiere revisión.');
            }
            $goal->fill(collect($data)->except('motivo')->all());
            if (! $id) {
                $goal->forceFill(['id' => (string) Str::uuid(), 'cod_est' => $student->cod_est, 'created_by' => $user->cod_usu]);
            }
            $goal->save();
            DB::table('meta_academica_revisiones')->insert(['meta_id' => $goal->getKey(), 'cod_usu' => $user->cod_usu,
                'motivo' => filled(trim($data['motivo'] ?? '')) ? trim($data['motivo']) : 'Creación de meta personal.', 'datos' => json_encode(['antes' => $before, 'despues' => $goal->only(['titulo', 'objetivo', 'accion', 'fecha_objetivo', 'estado'])], JSON_THROW_ON_ERROR), 'created_at' => now()]);
            BitacoraService::registrar(accion: $id ? 'ACTUALIZAR_META_PROPIA' : 'CREAR_META_PROPIA', tabla: 'metas_academicas', registro: $goal->getKey(), modulo: 'Orientación', valoresNuevos: ['estado' => $goal->estado]);

            return $goal;
        });
    }

    public function find(User $user, string $id): MetaAcademica
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);
        abort_unless($this->available(), 409);
        $student = app(CursoVirtualService::class)->estudianteDeUsuario($user);
        abort_unless($student, 403);

        return MetaAcademica::where('cod_est', $student->cod_est)->findOrFail($id);
    }

    public function validateDraft(User $user, array $data, bool $revision = false): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Estudiante' && $user->can('Perfil_Academico'), 403);

        return validator($data, ['titulo' => 'required|string|max:180', 'objetivo' => 'required|string|max:4000',
            'accion' => 'nullable|string|max:4000', 'fecha_objetivo' => 'nullable|date',
            'estado' => $revision ? 'required|in:BORRADOR,ACTIVA,COMPLETADA,CANCELADA' : 'required|in:BORRADOR,ACTIVA',
            'motivo' => ($revision ? 'required' : 'nullable').'|string|max:2000'])->validate();
    }
}
