<?php

declare(strict_types=1);

namespace App\Support\Modelos;

use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\GrupoAcademico;
use App\Models\Oficial\Academico\Paralelo;
use App\Models\Oficial\Academico\Turno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/** Contexto obtenido del grupo; no duplica sus atributos en el plan/horario. */
trait ContextoGrupoAcademico
{
    public function gestionAcademica(): HasOneThrough
    {
        return $this->hasOneThrough(GestionAcademica::class, GrupoAcademico::class, 'cod_gac', 'cod_gea', 'cod_gac', 'cod_gea');
    }

    public function curso(): HasOneThrough
    {
        return $this->hasOneThrough(Curso::class, GrupoAcademico::class, 'cod_gac', 'cod_cur', 'cod_gac', 'cod_cur');
    }

    public function paralelo(): HasOneThrough
    {
        return $this->hasOneThrough(Paralelo::class, GrupoAcademico::class, 'cod_gac', 'cod_par', 'cod_gac', 'cod_par');
    }

    public function turno(): HasOneThrough
    {
        return $this->hasOneThrough(Turno::class, GrupoAcademico::class, 'cod_gac', 'cod_tur', 'cod_gac', 'cod_tur');
    }

    public function getCodGeaAttribute(): ?string
    {
        return $this->grupoAcademico?->cod_gea;
    }

    public function getCodCurAttribute(): ?string
    {
        return $this->grupoAcademico?->cod_cur;
    }

    public function getCodParAttribute(): ?string
    {
        return $this->grupoAcademico?->cod_par;
    }

    public function getCodTurAttribute(): ?string
    {
        return $this->grupoAcademico?->cod_tur;
    }

    public function scopeDeGestion(Builder $query, string $gestion): Builder
    {
        return $query->whereHas('grupoAcademico', fn (Builder $grupo) => $grupo->where('cod_gea', $gestion));
    }

    public function scopeDeCurso(Builder $query, string $curso): Builder
    {
        return $query->whereHas('grupoAcademico', fn (Builder $grupo) => $grupo->where('cod_cur', $curso));
    }
}
