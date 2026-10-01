<?php

namespace App\Livewire\Shared;

use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public function mark(string $id, bool $read): void
    {
        validator(['id' => $id], ['id' => 'required|uuid'])->validate();
        app(NotificationService::class)->mark(auth()->user(), $id, $read);
        $this->dispatch('toast', type: 'success', message: 'Estado de lectura actualizado.');
    }

    public function render(NotificationService $service)
    {
        abort_unless(auth()->user(), 403);

        return view('livewire.shared.notification-center', $service->snapshot(auth()->user()) + ['service' => $service]);
    }
}
