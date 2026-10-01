<?php

namespace App\Services\Kardex;

use App\Contracts\KardexRepository;
use App\Models\Estudiante;
use App\Models\User;
use App\Policies\KardexPolicy;
use App\Services\RoleDashboardResolver;
use App\Support\Academico\KardexInteligente;
use Illuminate\Validation\ValidationException;

class KardexService
{
    public function __construct(private readonly KardexRepository $repository, private readonly KardexPolicy $policy) {}

    public function available(): bool
    {
        return $this->repository->available();
    }

    public function timeline(User $user, Estudiante $student): array
    {
        abort_unless($this->policy->view($user, $student), 403);
        abort_unless($this->repository->available(), 409, 'El registro institucional de Kardex requiere catálogos y persistencia autorizados.');

        return $this->repository->timeline($user, $student);
    }

    public function register(User $user, Estudiante $student, array $data): never
    {
        abort_unless($this->policy->create($user, $student), 403);
        $analysis = $this->previewDraft($user, $data);
        if (! $analysis['puede_guardar']) {
            throw ValidationException::withMessages(['mot_seg' => $analysis['bloqueos']]);
        }
        abort(409, 'No está aprobada la escritura institucional de Kardex. No se registró ninguna observación.');
    }

    public function previewDraft(User $user, array $data): array
    {
        abort_unless(app(RoleDashboardResolver::class)->roleFor($user) === 'Docente' && $user->can('kardex.registrar.curso'), 403);
        // Valida la forma del transporte antes de invocar un contrato que recibe texto.
        validator($data, ['mot_seg' => 'nullable|string|max:2000', 'ori_seg' => 'nullable|string|max:100', 'pro_acc_seg' => 'nullable|string|max:2000'])->validate();
        $analysis = app(KardexInteligente::class)->analizar($data);

        // Sin consultas ni persistencia: un borrador válido aún requiere catálogos, Policy y contexto reales.
        return $analysis;
    }
}
