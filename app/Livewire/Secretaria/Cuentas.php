<?php

namespace App\Livewire\Secretaria;

use App\Models\User;
use App\Services\OperationalAccountService;
use Livewire\Component;
use Livewire\WithPagination;

class Cuentas extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $selected = null;
    public bool $editing = false;
    public array $form = [];

    public function boot(OperationalAccountService $service): void { $service->authorize(auth()->user(), 'usuarios.ver.institucional'); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function nuevo(): void { $this->resetValidation(); $this->selected = null; $this->form = []; $this->editing = true; }
    public function editar(string $id, OperationalAccountService $service): void
    {
        $service->authorize(auth()->user(), 'usuarios.editar');
        $user = $service->scope(User::query())->findOrFail($id);
        $this->resetValidation(); $this->selected = $id; $this->form = ['email' => $user->email]; $this->editing = true;
    }
    public function guardar(OperationalAccountService $service): void
    {
        $service->save(auth()->user(), $this->form, $this->selected);
        $this->form = []; $this->selected = null; $this->editing = false;
        session()->flash('status', 'Cuenta guardada y registrada en bitácora.');
    }
    public function estado(string $id, bool $active, OperationalAccountService $service): void
    {
        $service->changeState(auth()->user(), $id, $active);
        session()->flash('status', 'Estado actualizado.');
    }
    public function render(OperationalAccountService $service)
    {
        return view('livewire.secretaria.cuentas', ['accounts' => $service->scope(User::with('persona', 'roles'))
            ->when($this->search !== '', fn ($q) => $q->where('email', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
            ->orderBy('email')->paginate(20)]);
    }
}
