<?php

namespace App\Support\Usuarios;

use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Str;

class CorreoInstitucional
{
    public function sugerir(Persona $persona): string
    {
        $normalizar = fn ($valor) => preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii((string) $valor)));
        $nombre = $normalizar($persona->nom_per);
        $paterno = $normalizar($persona->ape_pat_per);
        $materno = substr($normalizar($persona->ape_mat_per), 0, 2);
        if (! $nombre || ! $paterno || strlen($materno) < 2) {
            return '';
        }
        $local = 'uft3.'.$nombre.'.'.$paterno.'.'.$materno;

        return strlen($local) <= 64 ? $local.'@gmail.com' : '';
    }

    public function ocupado(string $correo, ?string $excepto = null): bool
    {
        // Gmail considera equivalentes los puntos del nombre de usuario.
        $canonico = fn ($valor) => str_ends_with(strtolower($valor), '@gmail.com')
            ? str_replace('.', '', explode('@', strtolower($valor))[0]).'@gmail.com' : strtolower($valor);

        return User::query()->when($excepto, fn ($q) => $q->where('cod_usu', '!=', $excepto))
            ->pluck('email')->filter()->contains(fn ($valor) => $canonico($valor) === $canonico($correo));
    }
}
