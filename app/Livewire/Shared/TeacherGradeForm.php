<?php

namespace App\Livewire\Shared;

use App\Models\User;
use App\Services\GradeService;
use App\Services\RoleDashboardResolver;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TeacherGradeForm extends Component
{
    #[Locked]
    public string $curso;

    public array $form = ['cod_est' => '', 'cod_pev' => '', 'not_cal' => null, 'obs_cal' => ''];

    #[Locked]
    public array $analisis = [];

    #[Locked]
    public ?string $gradeId = null;

    public function mount(): void
    {
        foreach (array_keys($this->form) as $field) {
            $this->form[$field] = session()->getOldInput($field, $this->form[$field]);
        }
    }

    private function actor(): User
    {
        $user = auth()->user();
        abort_unless($user && app(RoleDashboardResolver::class)->roleFor($user) === 'Docente'
            && $user->can('calificaciones.gestionar.curso'), 403);

        return $user;
    }

    public function updatedForm(): void
    {
        $this->resetValidation();
        $this->analizar();
    }

    public function analizar(): void
    {
        $this->analisis = app(GradeService::class)->previewTeacherGrade($this->actor(), $this->curso, $this->form, $this->gradeId);
    }

    #[On('review-official-grade')]
    public function editar(string $id): void
    {
        $grade = app(GradeService::class)->teacherGrade($this->actor(), $this->curso, $id);
        $this->gradeId = $grade->cod_cal;
        $this->form = $grade->only(['cod_est', 'cod_pev', 'not_cal', 'obs_cal']);
        $this->resetValidation();
        $this->analizar();
    }

    public function cancelarEdicion(): void
    {
        $this->actor();
        $this->gradeId = null;
        $this->form = ['cod_est' => '', 'cod_pev' => '', 'not_cal' => null, 'obs_cal' => ''];
        $this->analisis = [];
        $this->resetValidation();
    }

    public function aplicarObservacion(): void
    {
        $this->analizar();
        if (is_numeric($this->form['not_cal'] ?? null) && $this->form['not_cal'] >= 0 && $this->form['not_cal'] <= 100) {
            $this->form['obs_cal'] = $this->analisis['datos']['obs_cal'] ?? '';
        }
    }

    public function guardar(): void
    {
        $this->analizar();
        if (! ($this->analisis['puede_guardar'] ?? false)) {
            $this->addError('form.not_cal', implode(' ', $this->analisis['bloqueos'] ?? ['Revisa los datos de la nota.']));

            return;
        }
        $data = $this->validate([
            'form.cod_est' => ['required', 'string', 'max:20'], 'form.cod_pev' => ['required', 'string', 'max:20'],
            'form.not_cal' => ['required', 'numeric', 'between:0,100'], 'form.obs_cal' => ['nullable', 'string', 'max:255'],
        ])['form'];
        $service = app(GradeService::class);
        $context = $service->teacherContext($this->actor(), $this->curso);
        $grade = $this->gradeId ? $service->teacherGrade($this->actor(), $this->curso, $this->gradeId) : null;
        $service->save($this->actor(), $context['course']->cod_pas, $data['cod_est'], $data['cod_pev'], (float) $data['not_cal'], $data['obs_cal'] ?? null, $grade);
        session()->flash('status', $grade ? 'Nota oficial revisada para la gestión de esta asignación.' : 'Nota oficial registrada para la gestión de esta asignación.');
        $this->redirectRoute('docente.cursos.calificaciones', $this->curso);
    }

    public function render()
    {
        return view('livewire.shared.teacher-grade-form', app(GradeService::class)->teacherContext($this->actor(), $this->curso));
    }
}
