<?php

namespace App\Livewire\AulaVirtual\Orientacion;

use App\Services\AulaVirtual\CursoVirtualService;
use App\Services\AulaVirtual\OrientacionService;
use Livewire\Component;

class ExploradorVocacional extends Component
{
    public bool $abierto = false;
    public bool $sinGuardar = false;
    public int $paso = 0;
    public array $preguntas = [];
    public array $respuestas = [];
    public ?int $actividadId = null;
    public ?int $resultadoId = null;
    public ?string $mensaje = null;

    public function mount(OrientacionService $orientacion, CursoVirtualService $cursos): void
    {
        $this->cargarEstado($orientacion, $cursos);
    }

    public function abrir(): void
    {
        $this->abierto = true;
        $this->mensaje = null;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }

    public function anterior(): void
    {
        $this->paso = max(0, $this->paso - 1);
    }

    public function siguiente(): void
    {
        if (! $this->respuestaActualValida()) {
            $this->addError('respuestaActual', 'Selecciona una opción para continuar.');
            return;
        }

        $this->paso = min(count($this->preguntas) - 1, $this->paso + 1);
    }

    public function updatedRespuestas(): void
    {
        $this->sinGuardar = true;
        $this->resetErrorBag('respuestaActual');
    }

    public function guardarAvance(OrientacionService $orientacion): void
    {
        $actividad = $this->actividad($orientacion);
        $actividad = $orientacion->guardarAvance($actividad, $this->respuestas);

        $this->actividadId = $actividad->id;
        $this->sinGuardar = false;
        $this->mensaje = 'Avance guardado correctamente.';
    }

    public function finalizar(OrientacionService $orientacion): void
    {
        if (count(array_filter($this->respuestas, fn ($valor) => in_array((int) $valor, [1, 2, 3, 4, 5], true))) < count($this->preguntas)) {
            $this->addError('respuestaActual', 'Responde las 30 preguntas antes de finalizar.');
            return;
        }

        $resultado = $orientacion->finalizar($this->actividad($orientacion), $this->respuestas);

        $this->resultadoId = $resultado->id;
        $this->sinGuardar = false;
        $this->mensaje = 'Resultado generado correctamente.';
    }

    public function render()
    {
        $resultado = null;

        if ($this->resultadoId) {
            $resultado = \App\Models\AulaVirtual\OrientacionResultado::with('carreras')->find($this->resultadoId);
        } elseif ($this->actividadId) {
            $resultado = \App\Models\AulaVirtual\OrientacionActividad::with('resultado.carreras')->find($this->actividadId)?->resultado;
        }

        return view('livewire.aula-virtual.orientacion.explorador-vocacional', [
            'preguntaActual' => $this->preguntas[$this->paso] ?? null,
            'total' => count($this->preguntas),
            'progreso' => count($this->preguntas) > 0 ? (int) round((($this->paso + 1) / count($this->preguntas)) * 100) : 0,
            'resultado' => $resultado,
            'dimensiones' => app(OrientacionService::class)->dimensiones(),
            'mensajeOrientativo' => OrientacionService::MENSAJE_ORIENTATIVO,
        ]);
    }

    private function cargarEstado(OrientacionService $orientacion, CursoVirtualService $cursos): void
    {
        $estudiante = $cursos->estudianteDeUsuario(auth()->user());

        if (! $estudiante) {
            return;
        }

        $actividad = $orientacion->actividadActual($estudiante);
        $preguntas = $orientacion->preguntas();

        $this->actividadId = $actividad->id;
        $this->resultadoId = $actividad->resultado?->id;
        $this->preguntas = $preguntas->map(fn ($pregunta) => [
            'id' => $pregunta->id,
            'texto' => $pregunta->texto,
            'orden' => $pregunta->orden,
        ])->values()->all();
        $this->respuestas = $orientacion->respuestasGuardadas($actividad);

        $respondidas = count(array_filter($this->respuestas));
        $this->paso = min(max(0, $respondidas), max(0, $preguntas->count() - 1));
    }

    private function actividad(OrientacionService $orientacion)
    {
        if ($this->actividadId) {
            return \App\Models\AulaVirtual\OrientacionActividad::findOrFail($this->actividadId);
        }

        $estudiante = app(CursoVirtualService::class)->estudianteDeUsuario(auth()->user());

        return $orientacion->actividadActual($estudiante);
    }

    private function respuestaActualValida(): bool
    {
        $pregunta = $this->preguntas[$this->paso] ?? null;

        return $pregunta && in_array((int) ($this->respuestas[$pregunta['id']] ?? 0), [1, 2, 3, 4, 5], true);
    }
}
