<?php

namespace App\Livewire;

use App\Services\ReferenciaDocumentalService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentacionInstitucional extends Component
{
    use WithFileUploads;

    public $firma;
    public $sello;
    public string $mensaje = '';

    public function mount(): void
    {
        app(ReferenciaDocumentalService::class)->autorizar();
    }

    public function guardar(string $tipo): void
    {
        app(ReferenciaDocumentalService::class)->autorizar();
        abort_unless(in_array($tipo, ['FIRMA', 'SELLO'], true), 422);
        $campo = strtolower($tipo);
        $this->validate([$campo => 'required|image|mimes:png,jpg,jpeg|max:4096|dimensions:max_width=4000,max_height=4000']);
        app(ReferenciaDocumentalService::class)->guardar($tipo, $this->{$campo}->getRealPath());
        $this->{$campo} = null;
        $this->mensaje = ($tipo === 'FIRMA' ? 'Firma del Director' : 'Sello institucional').' registrado y conservado en la bitácora.';
    }

    public function render()
    {
        return view('livewire.documentacion-institucional', [
            'autoridad' => app(ReferenciaDocumentalService::class)->autorizar(),
            'referencias' => app(ReferenciaDocumentalService::class)->actuales(),
        ]);
    }
}
