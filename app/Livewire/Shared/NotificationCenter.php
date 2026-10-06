<?php

namespace App\Livewire\Shared;

use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class NotificationCenter extends Component
{
    use WithPagination;

    public string $filtro = 'TODAS';
    public string $origen = 'TODOS';
    public bool $abierta = false;

    #[On('notificaciones-actualizadas')]
    public function actualizarAvisos(): void
    {
        $this->resetPage('notificationsPage');
    }

    public function updatedFiltro(): void
    {
        $this->resetPage('notificationsPage');
    }

    public function updatedOrigen(): void
    {
        $this->resetPage('notificationsPage');
    }

    public function mark(string $id, bool $read): void
    {
        app(NotificationService::class)->mark(auth()->user(), $id, $read);
        $this->dispatch('toast', type: 'success', message: 'Estado de lectura actualizado.');
    }

    public function render(NotificationService $service)
    {
        abort_unless(auth()->user(), 403);

        return view('livewire.shared.notification-center', $service->snapshot(auth()->user(), $this->filtro, $this->origen) + ['service' => $service]);
    }

    public function archive(string $id, bool $archivar): void
    {
        app(NotificationService::class)->archive(auth()->user(), $id, $archivar);
        $this->resetPage('notificationsPage');
        $this->dispatch('toast', type: 'success', message: $archivar ? 'Aviso archivado. Puedes recuperarlo desde Archivadas.' : 'Aviso devuelto a tu bandeja.');
    }
}
