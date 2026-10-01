<?php

namespace App\Livewire\Shared;

use App\Services\RoleDashboardResolver;
use App\Services\StudentContextService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentDrawer extends Component
{
    #[Locked]
    public ?string $curso = null;

    #[Locked]
    public ?string $studentId = null;

    public function mount(?string $curso = null): void
    {
        $this->curso = $curso;
    }

    #[On('open-student')]
    public function open(string $id): void
    {
        abort_unless(strlen($id) <= 30, 422);
        app(StudentContextService::class)->details(auth()->user(), $id, $this->curso);
        $this->studentId = $id;
    }

    public function close(): void
    {
        $this->studentId = null;
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user), 403);
        $detail = $this->studentId ? app(StudentContextService::class)->details($user, $this->studentId, $this->curso) : null;

        return view('livewire.shared.student-drawer', compact('detail'));
    }
}
