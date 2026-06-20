<?php

namespace App\Services\AulaVirtual;

use App\Models\User;

class OrientacionService
{
    public function resumen(User $user): array
    {
        return [
            'estado' => 'En proceso',
            'avance' => 0,
            'perfil' => 'Información académica disponible según inscripción.',
            'compatibilidad_principal' => null,
            'carreras' => [],
            'mensaje' => 'Este resultado es orientativo y debe complementarse con tu rendimiento académico, tu formación técnica y el acompañamiento docente.',
        ];
    }

    public function dimensiones(): array
    {
        return [
            'tecnico_practico' => 'Técnico-práctico',
            'analitico_cientifico' => 'Analítico-científico',
            'creativo_expresivo' => 'Creativo-expresivo',
            'social_comunitario' => 'Social-comunitario',
            'liderazgo_emprendimiento' => 'Liderazgo-emprendimiento',
            'organizativo_administrativo' => 'Organizativo-administrativo',
        ];
    }
}
