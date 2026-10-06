<?php

namespace App\Livewire\Admin;

use App\Services\Academico\EstudioCalendarioMinisterial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class EstudioCalendario extends Component
{
    #[Reactive]
    public string $anio = '';
    #[Locked]
    public array $estudio = [];
    #[Locked]
    public bool $suscrito = false;

    public function mount(): void
    {
        $this->actualizar();
    }

    private function anioValido(): int
    {
        Gate::authorize('Gestion_Academica');
        $this->validate(['anio' => ['required', 'integer', 'min:2020', 'max:2100']]);
        return (int) $this->anio;
    }

    public function investigar(): void
    {
        $anio = $this->anioValido();
        $clave = 'estudio-calendario:'.Auth::id().':'.$anio;
        if (RateLimiter::tooManyAttempts($clave, 1)) {
            $this->dispatch('toast', type: 'info', message: 'La solicitud anterior está registrada. Espera un momento antes de repetirla.');
            $this->actualizar();
            return;
        }
        RateLimiter::hit($clave, 60);
        $this->estudio = app(EstudioCalendarioMinisterial::class)->solicitar($anio);
    }

    public function avisarme(): void
    {
        $anio = $this->anioValido();
        app(EstudioCalendarioMinisterial::class)->suscribir((string) Auth::id(), $anio);
        $this->suscrito = true;
        if (! $this->estudio) {
            $this->investigar();
        }
    }

    public function actualizar(): void
    {
        $anio = $this->anioValido();
        $servicio = app(EstudioCalendarioMinisterial::class);
        $this->estudio = $servicio->leer($anio);
        if ($this->estudio && ($this->estudio['estado'] ?? '') !== 'RESULTADO') {
            $servicio->asegurarTrabajador();
        }
        $aviso = $servicio->aviso((string) Auth::id(), $anio);
        $this->suscrito = (bool) ($aviso['avisar'] ?? false);
        if ($this->suscrito && ($this->estudio['estado'] ?? '') === 'RESULTADO' && ($this->estudio['consultado'] ?? 0) > ($aviso['visto'] ?? 0)) {
            $this->dispatch('toast', type: 'success', message: 'El estudio del calendario '.$anio.' está listo. Revisa sus fechas y la fuente en Nueva gestión.');
            $servicio->marcarAvisado((string) Auth::id(), $anio, $this->estudio['consultado']);
        }
    }

    public function aplicar(): void
    {
        $anio = $this->anioValido();
        $this->dispatch('calendario-ministerial-aplicar', anio: $anio);
    }

    public function render()
    {
        Gate::authorize('Gestion_Academica');
        return view('livewire.admin.academica.estudio-calendario');
    }
}
