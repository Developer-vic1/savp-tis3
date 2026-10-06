<?php

namespace App\Policies;

use App\Models\Oficial\Academico\DocumentoInscripcionEstudiante;
use App\Models\Oficial\Sistema\User;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\Gate;

class DocumentoInscripcionEstudiantePolicy
{
    public function view(User $user, DocumentoInscripcionEstudiante $document): bool
    {
        return in_array(app(RoleDashboardResolver::class)->roleFor($user), ['Administrador', 'Secretaria'], true)
            && $user->can('Inscripciones') && ! in_array($document->est_die, ['ANULADO', 'NO_APLICA'], true)
            && $document->inscripcion && Gate::forUser($user)->allows('view', $document->inscripcion);
    }
}
