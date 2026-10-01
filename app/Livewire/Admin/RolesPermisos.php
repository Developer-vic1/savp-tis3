<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\RoleRequest;
use App\Models\User;
use App\Services\InstitutionalAuthorityService;
use App\Services\RolePermissionService;
use App\Services\RoleRequestService;
use App\Support\PermissionLabel;
use App\Support\LegacyReadPermission;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RolesPermisos extends Component
{
    use WithFileUploads;

    public bool $showRequestModal = false;

    public int $requestStep = 1;

    public string $requestedName = '';

    public string $justification = '';

    public string $institutionalReason = '';

    public string $functions = '';

    public string $requestedScope = '';

    public string $observations = '';

    public array $requestedPermissions = [];

    public $document = null;

    public ?array $governanceResult = null;

    public ?int $activeRequestId = null;

    public string $reviewNote = '';

    public bool $directorMatches = false;

    public bool $documentReadable = false;

    public bool $signaturePresent = false;

    public bool $sealPresent = false;

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
        abort_unless(auth()->user()?->can('roles-permisos.ver'), 403);
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

    public function openRequest(): void
    {
        app(RoleRequestService::class)->authorize(auth()->user(), 'roles.solicitudes.crear');
        $this->reset(['requestedName', 'justification', 'institutionalReason', 'functions', 'requestedScope', 'observations', 'requestedPermissions', 'document', 'governanceResult', 'activeRequestId']);
        $this->search = $this->domain = $this->action = $this->scope = $this->selection = '';
        $this->requestStep = 1;
        $this->showRequestModal = true;
    }

    public function closeRequest(): void
    {
        $this->showRequestModal = false;
        $this->resetErrorBag();
    }

    public function nextRequestStep(): void
    {
        app(RoleRequestService::class)->authorize(auth()->user(), 'roles.solicitudes.crear');
        if ($this->requestStep === 1) {
            $this->validate(['document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png']);
        }
        if ($this->requestStep === 2) {
            $this->validate([
                'requestedName' => 'required|string|min:4|max:80', 'justification' => 'required|string|min:30|max:2000',
                'institutionalReason' => 'required|string|min:20|max:2000', 'requestedScope' => 'required|string|min:5|max:120',
            ]);
        }
        if ($this->requestStep === 3) {
            $this->validate(['functions' => 'required|string|min:30|max:3000']);
        }
        if ($this->requestStep === 4) {
            $this->validate(['requestedPermissions' => 'required|array|min:1']);
        }
        if ($this->requestStep === 5) {
            $this->analyzeRequest();

            return;
        }
        $this->requestStep = min(5, $this->requestStep + 1);
    }

    private function requestData(): array
    {
        return ['requested_name' => $this->requestedName, 'justification' => $this->justification,
            'institutional_reason' => $this->institutionalReason, 'functions' => $this->functions,
            'scope' => $this->requestedScope, 'observations' => $this->observations,
            'requested_permissions' => $this->requestedPermissions];
    }

    public function analyzeRequest(): void
    {
        app(RoleRequestService::class)->authorize(auth()->user(), 'roles.solicitudes.crear');
        $this->validate([
            'requestedName' => 'required|string|min:4|max:80', 'justification' => 'required|string|min:30|max:2000',
            'institutionalReason' => 'required|string|min:20|max:2000', 'requestedScope' => 'required|string|min:5|max:120',
            'functions' => 'required|string|min:30|max:3000', 'requestedPermissions' => 'required|array|min:1',
            'requestedPermissions.*' => 'string',
        ]);
        $this->governanceResult = app(RoleRequestService::class)->analyze($this->requestData());
        $this->requestStep = 5;
    }

    public function submitRequest(RoleRequestService $service): void
    {
        $service->authorize(auth()->user(), 'roles.solicitudes.crear');
        $this->validate([
            'document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'requestedName' => 'required|string|min:4|max:80', 'justification' => 'required|string|min:30|max:2000',
            'institutionalReason' => 'required|string|min:20|max:2000', 'functions' => 'required|string|min:30|max:3000',
            'requestedScope' => 'required|string|min:5|max:120', 'observations' => 'nullable|string|max:2000',
            'requestedPermissions' => 'required|array|min:1', 'requestedPermissions.*' => 'string',
        ]);
        $request = $service->submit(auth()->user(), $this->requestData(), $this->document);
        $this->closeRequest();
        $this->dispatch('swal:success', title: 'Solicitud registrada', text: "Solicitud #{$request->id} pendiente de revisión documental por otro administrador.");
    }

    public function viewRequest(int $id): void
    {
        app(RoleRequestService::class)->authorize(auth()->user(), 'roles.solicitudes.ver');
        RoleRequest::query()->findOrFail($id);
        $this->activeRequestId = $id;
        $this->reviewNote = '';
        $this->directorMatches = $this->documentReadable = $this->signaturePresent = $this->sealPresent = false;
    }

    public function reviewRequest(RoleRequestService $service, bool $approved): void
    {
        $service->authorize(auth()->user(), 'roles.solicitudes.analizar');
        $service->review(auth()->user(), $this->activeRequestId, $approved, $this->reviewNote,
            $this->directorMatches, $this->documentReadable, $this->signaturePresent, $this->sealPresent);
        $this->activeRequestId = null;
        $this->dispatch('swal:success', title: 'Revisión registrada', text: 'La decisión y la evidencia quedaron asentadas en Bitácora.');
    }

    public function createRole(RoleRequestService $service): void
    {
        $service->authorize(auth()->user(), 'roles.crear');
        $service->createRole(auth()->user(), $this->activeRequestId);
        $this->activeRequestId = null;
        $this->dispatch('swal:success', title: 'Rol institucional creado', text: 'El rol institucional fue creado correctamente a partir de una solicitud validada. Los permisos autorizados fueron registrados y la operación quedó asentada en Bitácora.');
    }

    public function cancelRequest(RoleRequestService $service): void
    {
        $service->cancel(auth()->user(), $this->activeRequestId);
        $this->activeRequestId = null;
        $this->dispatch('swal:success', title: 'Solicitud cancelada', text: 'La cancelación quedó registrada en Bitácora.');
    }

    private function visiblePermissions()
    {
        return Permission::query()->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->filter(function ($permission) {
                $label = PermissionLabel::describe($permission->name);

                return ($this->search === '' || str_contains(mb_strtolower($permission->name.' '.$label['label']), mb_strtolower($this->search)))
                    && ($this->domain === '' || $this->domain === $label['domain'])
                    && ($this->action === '' || $this->action === $label['action'])
                    && ($this->scope === '' || $this->scope === $label['scope'])
                    && ($this->selection === '' || ($this->selection === 'selected') === in_array($permission->name, $this->selectedPermissions, true));
            });
    }

    public function render()
    {
        $viewer = auth()->user();
        abort_unless($viewer?->can('roles-permisos.ver'), 403);
        $labels = Permission::query()->where('guard_name', 'web')->pluck('name')->map(fn ($name) => PermissionLabel::describe($name));
        $current = Role::query()->where('guard_name', 'web')->find($this->selectedRoleId)?->permissions()->pluck('name')->all() ?? [];
        $userCounts = DB::table('model_has_roles')->where('model_type', User::class)
            ->select('role_id', DB::raw('COUNT(DISTINCT cod_usu) AS total'))
            ->groupBy('role_id')->pluck('total', 'role_id');
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get()
            ->each(fn (Role $role) => $role->setAttribute('users_count', (int) ($userCounts[$role->id] ?? 0)));
        $selectedRole = $roles->firstWhere('id', $this->selectedRoleId);
        $roleWindows = collect(config('architecture_windows', []))
            ->where('actor', $selectedRole?->name)->values();
        $legacyReadGrants = collect(LegacyReadPermission::FALLBACKS)
            ->map(fn (array $actors, string $permission) => [
                'permission' => $permission,
                'legacy' => $actors[$selectedRole?->name] ?? null,
            ])
            ->filter(fn (array $grant) => $grant['legacy'] !== null && in_array($grant['legacy'], $current, true))
            ->values();

        return view('livewire.admin.roles-permisos', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'roleWindows' => $roleWindows,
            'legacyReadGrants' => $legacyReadGrants,
            'permissionGroups' => $this->visiblePermissions()->groupBy(fn ($permission) => PermissionLabel::describe($permission->name)['domain']),
            'domains' => $labels->pluck('domain')->unique()->sort(),
            'actions' => $labels->pluck('action')->unique()->sort(),
            'scopes' => $labels->pluck('scope')->filter()->unique()->sort(),
            'added' => array_diff($this->selectedPermissions, $current),
            'removed' => array_diff($current, $this->selectedPermissions),
            'authority' => app(InstitutionalAuthorityService::class)->current(),
            'requests' => $viewer->can('roles.solicitudes.ver') ? RoleRequest::query()->orderByDesc('id')->limit(30)->get() : collect(),
            'activeRequest' => $this->activeRequestId && $viewer->can('roles.solicitudes.ver') ? RoleRequest::query()->find($this->activeRequestId) : null,
        ]);
    }
}
