<?php

namespace App\Livewire\Admin;

use App\Services\RolePermissionService;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisos extends Component
{
    public ?int $selectedRoleId = null;

    public array $selectedPermissions = [];

    public string $search = '';

    public string $domain = '';
    public string $action = '';
    public string $scope = '';
    public string $selection = '';

    public function selectVisible(bool $selected): void
    {
        $names = $this->visiblePermissions()->pluck('name')->all();
        $this->selectedPermissions = $selected
            ? array_values(array_unique(array_merge($this->selectedPermissions, $names)))
            : array_values(array_diff($this->selectedPermissions, $names));
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);
        $this->selectedRoleId = Role::query()->where('name', 'Administrador')->value('id')
            ?? Role::query()->orderBy('name')->value('id');
        $this->loadRole();
    }

    public function updatedSelectedRoleId(): void
    {
        $this->loadRole();
    }

    public function loadRole(): void
    {
        $role = Role::query()->where('guard_name', 'web')->find($this->selectedRoleId);
        $this->selectedPermissions = $role?->permissions()->pluck('name')->all() ?? [];
    }

    public function save(RolePermissionService $service): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);

        $role = Role::query()->findOrFail($this->selectedRoleId);
        $service->sync($role, $this->selectedPermissions, auth()->user());

        session()->flash('status', 'Permisos guardados y registrados en bitácora.');

        $this->dispatch('swal:success', title: 'Permisos actualizados', text: 'La matriz del rol fue guardada y registrada en bitácora.');
    }

    private function visiblePermissions()
    {
        return Permission::query()->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->filter(function ($permission) {
                $label = \App\Support\PermissionLabel::describe($permission->name);
                return ($this->search === '' || str_contains(mb_strtolower($permission->name.' '.$label['label']), mb_strtolower($this->search)))
                    && ($this->domain === '' || $this->domain === $label['domain'])
                    && ($this->action === '' || $this->action === $label['action'])
                    && ($this->scope === '' || $this->scope === $label['scope'])
                    && ($this->selection === '' || ($this->selection === 'selected') === in_array($permission->name, $this->selectedPermissions, true));
            });
    }

    public function render()
    {
        $labels = Permission::query()->where('guard_name', 'web')->pluck('name')->map(fn ($name) => \App\Support\PermissionLabel::describe($name));
        $current = Role::query()->where('guard_name', 'web')->find($this->selectedRoleId)?->permissions()->pluck('name')->all() ?? [];

        return view('livewire.admin.roles-permisos', [
            'roles' => Role::query()->where('guard_name', 'web')->withCount('users')->orderBy('name')->get(),
            'permissionGroups' => $this->visiblePermissions()->groupBy(fn ($permission) => \App\Support\PermissionLabel::describe($permission->name)['domain']),
            'domains' => $labels->pluck('domain')->unique()->sort(),
            'actions' => $labels->pluck('action')->unique()->sort(),
            'scopes' => $labels->pluck('scope')->filter()->unique()->sort(),
            'added' => array_diff($this->selectedPermissions, $current),
            'removed' => array_diff($current, $this->selectedPermissions),
        ]);
    }
}
