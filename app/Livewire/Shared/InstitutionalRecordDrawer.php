<?php

namespace App\Livewire\Shared;

use App\Services\InstitutionalQueryService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class InstitutionalRecordDrawer extends Component
{
    #[Locked]
    public string $area;

    #[Locked]
    public ?string $recordId = null;

    #[Locked]
    public string $gestion = '';

    #[On('open-institutional-record')]
    public function open(string $id, string $gestion = ''): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $service = app(InstitutionalQueryService::class);
        $service->authorizeQuery($user, $this->area, 'secretaria');
        $service->secretaryDetail($user, $this->area, $id, $gestion);
        $this->recordId = $id;
        $this->gestion = $gestion;
    }

    public function close(): void
    {
        $this->recordId = null;
        $this->gestion = '';
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);
        app(InstitutionalQueryService::class)->authorizeQuery($user, $this->area, 'secretaria');
        abort_unless(in_array($this->area, ['cursos', 'turnos'], true), 403);
        $detail = $this->recordId ? app(InstitutionalQueryService::class)->secretaryDetail($user, $this->area, $this->recordId, $this->gestion) : null;

        return view('livewire.shared.institutional-record-drawer',compact('detail'));
    }
}
