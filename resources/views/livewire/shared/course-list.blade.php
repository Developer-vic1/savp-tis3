<div class="space-y-6">
    <section class="ui-panel">
        <nav class="ui-muted text-sm" aria-label="Ruta de navegación"><a class="underline" href="{{ route($teacher ? 'docente.dashboard' : 'estudiante.dashboard') }}">Inicio</a> / {{ $teacher ? 'Mis cursos' : 'Mis materias' }}</nav>
        <h1 class="ui-title mt-3 text-2xl font-black">{{ $teacher ? 'Mis cursos' : 'Mis materias' }}</h1>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="ui-label" for="course-search">Curso o asignatura<input id="course-search" class="ui-input" wire:model.live.debounce.350ms="search" maxlength="100" type="search"></label>
            <label class="ui-label" for="course-year">Año de gestión<input id="course-year" class="ui-input" wire:model.live.debounce.350ms="gestion" placeholder="Todas las gestiones autorizadas" inputmode="numeric" maxlength="4"></label>
        </div>
        @error('gestion')<p class="ui-error">{{ $message }}</p>@enderror
        @error('search')<p class="ui-error">{{ $message }}</p>@enderror
    </section>
    <p wire:loading wire:target="search,gestion,gotoPage,nextPage,previousPage" class="ui-alert-info" role="status">Buscando cursos autorizados…</p>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" wire:loading.remove>
        @forelse($cursos as $curso)
            <div wire:key="course-{{ $curso->cod_cla }}">@include('aula-virtual.componentes.course-card', ['curso'=>$curso, 'resumen'=>$service->cursoResumen($curso, $student), 'docente'=>$teacher, 'href'=>route($teacher ? 'docente.curso' : 'estudiante.materia', $curso->cod_cla)])</div>
        @empty
            <section class="ui-panel md:col-span-2 xl:col-span-3"><h2 class="ui-title font-bold">{{ $search || $gestion ? 'No hay cursos que coincidan con estos filtros.' : ($teacher ? 'No tienes cursos asignados.' : 'No hay aulas virtuales vinculadas a tu cuenta.') }}</h2><p class="ui-muted mt-2">{{ $search || $gestion ? 'Revisa el nombre o el año y vuelve a buscar.' : ($teacher ? 'Consulta con la institución para revisar tus asignaciones.' : 'Tu inscripción académica puede existir sin un aula virtual creada o asociada. Consulta con la institución para revisar la vinculación.') }}</p></section>
        @endforelse
    </div>
    {{ $cursos->links() }}
</div>
