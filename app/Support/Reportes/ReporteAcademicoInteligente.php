<?php

namespace App\Support\Reportes;

use App\Support\Evaluacion\CalificacionInteligente;

class ReporteAcademicoInteligente
{
    public function clasificar(float $nota): string
    {
        return app(CalificacionInteligente::class)->clasificar($nota);
    }

    public function orientacionPorEspecialidad(?string $especialidad): array
    {
        $texto = mb_strtolower((string) $especialidad);

        return match (true) {
            str_contains($texto, 'sistemas') => ['Tecnología digital', ['Ingeniería de Sistemas', 'Informática', 'Ciencia de Datos']],
            str_contains($texto, 'electr') => ['Electrónica y telecomunicaciones', ['Ingeniería Electrónica', 'Telecomunicaciones', 'Mecatrónica']],
            str_contains($texto, 'contab') => ['Administración y finanzas', ['Contaduría Pública', 'Administración de Empresas', 'Economía']],
            str_contains($texto, 'gastronom') => ['Servicios y producción gastronómica', ['Gastronomía', 'Turismo', 'Administración de Servicios']],
            str_contains($texto, 'mec') => ['Industria y mantenimiento', ['Ingeniería Mecánica', 'Ingeniería Industrial', 'Mecatrónica']],
            str_contains($texto, 'textil') => ['Diseño y producción textil', ['Diseño Textil', 'Diseño de Modas', 'Ingeniería Industrial']],
            default => ['Formación técnica y producción', ['Área técnica relacionada', 'Emprendimiento productivo']],
        };
    }
}
