<?php

namespace App\Services;

use App\Models\Director;
use Illuminate\Support\Collection;

class InstitutionalAuthorityService
{
    public function current(): array
    {
        $directors = Director::query()->with('personalInstitucional.persona')
            ->where('est_dir', 'ACTIVO')
            ->whereHas('personalInstitucional', fn ($q) => $q->where('est_pin', 'ACTIVO')->whereHas('persona'))
            ->limit(2)->get();

        return $this->resolve($directors);
    }

    public function resolve(Collection $directors): array
    {
        $directors = $directors->filter(fn ($director) => $director->est_dir === 'ACTIVO'
            && $director->personalInstitucional?->est_pin === 'ACTIVO'
            && $director->personalInstitucional?->persona !== null)->values();
        if ($directors->count() !== 1) {
            return ['status' => $directors->isEmpty() ? 'SIN_DIRECTOR' : 'CONFIGURACION_INSTITUCIONAL_AMBIGUA',
                'message' => $directors->isEmpty() ? 'No existe un Director activo configurado en SAVP.' : 'Se encontraron múltiples Directores activos. Corrija la configuración institucional antes de continuar.',
                'director' => null, 'name' => null];
        }

        $director = $directors->first();
        $person = $director->personalInstitucional->persona;
        return ['status' => 'ACTIVO', 'message' => 'Director activo', 'director' => $director,
            'name' => trim(implode(' ', array_filter([$person->nom_per, $person->ape_pat_per, $person->ape_mat_per])))];
    }
}
