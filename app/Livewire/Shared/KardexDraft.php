<?php

namespace App\Livewire\Shared;

use App\Services\Kardex\KardexService;
use App\Services\RoleDashboardResolver;
use Livewire\Component;

class KardexDraft extends Component
{
    public array $form = ['mot_seg' => '', 'ori_seg' => '', 'pro_acc_seg' => ''];

    public array $analisis = [];

    public function updatedForm(): void
    {
        $this->analizar();
    }

    public function analizar(): void
    {
        $this->authorizeActor();
        $this->analisis = app(KardexService::class)->previewDraft(auth()->user(), $this->form);
        $this->resetValidation();
    }

    public function render()
    {
        $this->authorizeActor();

        return view('livewire.shared.kardex-draft');
    }

    private function authorizeActor(): void
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Docente' && $user->can('kardex.registrar.curso'), 403);
    }
}
