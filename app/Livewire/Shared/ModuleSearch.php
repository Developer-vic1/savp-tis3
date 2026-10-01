<?php

namespace App\Livewire\Shared;

use App\Services\RoleDashboardResolver;
use App\Support\WorkspaceNavigation;
use Livewire\Component;

class ModuleSearch extends Component
{
    public string $search = '';

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user), 403);
        $term = mb_strtolower(mb_substr(trim($this->search), 0, 100));
        $links = $term === '' ? [] : array_values(array_filter(
            app(WorkspaceNavigation::class)->for($user),
            fn ($link) => str_contains(mb_strtolower($link['label']), $term)
        ));

        return view('livewire.shared.module-search', ['links' => array_slice($links, 0, 8)]);
    }
}
