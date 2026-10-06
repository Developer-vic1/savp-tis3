<?php

namespace App\Support\Academico;

class OrganizacionParalelosInteligente
{
    public static function revisar(array $grupos, ?int $anio): array
    {
        $alertas = [];
        $excedidos = collect($grupos)->filter(fn ($g) => $g['capacidad'] > 0 && $g['estudiantes'] > $g['capacidad']);
        if ($excedidos->isNotEmpty()) $alertas[] = ['tipo'=>'danger', 'titulo'=>'Capacidad registrada superada', 'mensaje'=>$excedidos->count().' grupos exceden su capacidad. Consulta las fichas y verifica aula y autorización antes de redistribuir.'];
        $porGrado = collect($grupos)->groupBy('curso');
        $masDeTres = $porGrado->filter(fn ($filas) => $filas->pluck('codigo')->unique()->count() > 3);
        if ($anio === 2026 && $masDeTres->isNotEmpty()) $alertas[] = ['tipo'=>'warning', 'titulo'=>'Revisar organización frente a la norma 2026', 'mensaje'=>$masDeTres->count().' grados tienen más de tres letras activas. El art. 21.III limita la creación de paralelos. Verifica el expediente de la organización existente; no se elimina ni fusiona historia automáticamente.'];
        $diferencias = collect($grupos)->groupBy(fn ($g) => $g['curso'].' · '.$g['turno'])->filter(fn ($filas) => $filas->count() > 1 && $filas->max('estudiantes') - $filas->min('estudiantes') >= 10);
        if ($diferencias->isNotEmpty()) $alertas[] = ['tipo'=>'info', 'titulo'=>'Distribución que merece revisión', 'mensaje'=>$diferencias->count().' combinaciones de grado y turno presentan diferencias de al menos 10 estudiantes. Es una señal interna de revisión; no autoriza traslados ni indica incumplimiento por sí sola.'];
        return $alertas;
    }
}
