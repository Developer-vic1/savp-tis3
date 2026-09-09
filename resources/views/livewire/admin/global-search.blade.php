<div class="relative w-full" x-data="adminGlobalSearch($wire)"
    x-on:keydown.window.ctrl.k.prevent="
        open = true;
        $nextTick(() => $refs.searchInput.focus())
    "
    x-on:keydown.window.meta.k.prevent="
        open = true;
        $nextTick(() => $refs.searchInput.focus())
    ">
    {{-- ===================================================== --}}
    {{-- BUSCADOR --}}
    {{-- ===================================================== --}}

    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4" style="color: var(--ui-muted);">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
            </svg>
        </div>

        <input x-ref="searchInput" x-model="query" x-on:input="search()" x-on:focus="open = true"
            x-on:keydown.escape="
                open = false;
                query = '';
                $wire.clearSearch();
            "
            type="search" autocomplete="off" placeholder="Buscar persona, CI, docente o módulo..."
            class="
                h-12 w-full rounded-2xl border
                pl-12 pr-24 text-sm font-medium
                outline-none transition
                focus:ring-4
            "
            style="
                background: var(--ui-surface);
                border-color: var(--ui-border);
                color: var(--ui-text);
                --tw-ring-color: color-mix(in srgb, var(--ui-primary) 15%, transparent);
            ">

        <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-3">
            <button x-show="query.length" x-cloak type="button" x-on:click="clear()" class="ui-icon-btn h-8 w-8"
                title="Limpiar búsqueda">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            <span
                class="
                    hidden rounded-lg border px-2 py-1
                    text-[10px] font-black sm:inline-flex
                "
                style="
                    border-color: var(--ui-border);
                    color: var(--ui-muted);
                ">
                CTRL K
            </span>
        </div>
    </div>


    {{-- ===================================================== --}}
    {{-- RESULTADOS --}}
    {{-- ===================================================== --}}

    <div x-show="open && query.trim().length >= 2" x-cloak x-on:click.outside="open = false"
        class="
            absolute left-0 right-0 top-[calc(100%+0.65rem)]
            z-50 max-h-[70vh] overflow-y-auto
            rounded-[1.5rem] border p-2 shadow-2xl
        "
        style="
            background: var(--ui-surface);
            border-color: var(--ui-border);
        ">

        {{-- Navegación --}}
        <template x-if="filteredNavigation.length">
            <div class="mb-2">
                <div class="px-3 pb-2 pt-2 text-[11px] font-black uppercase tracking-[0.14em]"
                    style="color: var(--ui-muted);">
                    Ir a
                </div>

                <template x-for="item in filteredNavigation.slice(0, 6)" :key="item.href">
                    <button type="button" x-on:click="go(item.href)"
                        class="
                            flex w-full items-center gap-3
                            rounded-xl px-3 py-2.5 text-left
                            transition hover:bg-black/5
                            dark:hover:bg-white/5
                        ">
                        <div class="
                                flex h-9 w-9 shrink-0 items-center
                                justify-center rounded-xl
                            "
                            style="
                                background: var(--ui-surface-muted);
                                color: var(--ui-text-soft);
                            ">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3.75 12h16.5m-6-6 6 6-6 6" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold" style="color: var(--ui-text);" x-text="item.label">
                            </p>

                            <p class="truncate text-xs" style="color: var(--ui-muted);">
                                Abrir módulo
                            </p>
                        </div>
                    </button>
                </template>
            </div>
        </template>


        {{-- Personas --}}
        <div>
            <div class="flex items-center justify-between px-3 pb-2 pt-2">
                <span class="text-[11px] font-black uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                    Personas
                </span>

                <span wire:loading wire:target="query" class="text-xs font-semibold" style="color: var(--ui-muted);">
                    Buscando...
                </span>
            </div>

            @forelse ($this->people as $person)
                @php
                    $fullName = trim(
                        collect([$person->nom_per, $person->ape_pat_per, $person->ape_mat_per])
                            ->filter()
                            ->implode(' '),
                    );

                    $initials =
                        mb_strtoupper(mb_substr($person->nom_per ?? 'U', 0, 1)) .
                        mb_strtoupper(mb_substr($person->ape_pat_per ?? '', 0, 1));
                @endphp

                <button type="button" wire:click="selectPerson('{{ $person->cod_per }}')" x-on:click="open = false"
                    class="
                        group flex w-full items-center gap-3
                        rounded-xl px-3 py-3 text-left
                        transition hover:bg-black/5
                        dark:hover:bg-white/5
                    ">
                    <div class="
                            flex h-11 w-11 shrink-0 items-center
                            justify-center rounded-2xl
                            text-sm font-black
                        "
                        style="
                            background: var(--ui-surface-muted);
                            color: var(--ui-text-soft);
                        ">
                        {{ $initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                {{ $fullName }}
                            </p>

                            @if ($person->car_pin)
                                <span
                                    class="
                                        rounded-full px-2 py-0.5
                                        text-[10px] font-bold
                                    "
                                    style="
                                        background: var(--ui-surface-muted);
                                        color: var(--ui-text-soft);
                                    ">
                                    {{ $person->car_pin }}
                                </span>
                            @endif
                        </div>

                        <div class="
                                mt-1 flex flex-wrap gap-x-4
                                gap-y-1 text-xs
                            "
                            style="color: var(--ui-muted);">
                            @if ($person->ci_per)
                                <span>
                                    CI {{ $person->ci_per }}
                                </span>
                            @endif

                            @if ($person->esp_doc)
                                <span>
                                    {{ $person->esp_doc }}
                                </span>
                            @endif

                            @if ($person->email_usuario)
                                <span class="truncate">
                                    {{ $person->email_usuario }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <svg class="
                            h-4 w-4 shrink-0 transition
                            group-hover:translate-x-0.5
                        "
                        style="color: var(--ui-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                    </svg>
                </button>

            @empty

                @if (mb_strlen(trim($query)) >= 2)
                    <div class="px-4 py-8 text-center">
                        <div class="
                                mx-auto flex h-12 w-12
                                items-center justify-center
                                rounded-2xl
                            "
                            style="
                                background: var(--ui-surface-muted);
                                color: var(--ui-muted);
                            ">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15.75 15.75 21 21m-9-3.75A5.25 5.25 0 1 0 12 6.75a5.25 5.25 0 0 0 0 10.5Z" />
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-bold" style="color: var(--ui-text);">
                            Sin personas coincidentes
                        </p>

                        <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                            Prueba con nombre, apellido, CI,
                            teléfono o correo.
                        </p>
                    </div>
                @endif
            @endforelse
        </div>
    </div>


    {{-- ===================================================== --}}
    {{-- FICHA DETALLADA --}}
    {{-- ===================================================== --}}

    @if ($this->personDetail)
        @php($detail = $this->personDetail)

        <div x-data x-on:keydown.escape.window="$wire.closePerson()"
            class="
                fixed inset-0 z-[100]
                flex items-center justify-center
                bg-black/45 p-4 backdrop-blur-sm
            ">
            <button type="button" wire:click="closePerson" class="absolute inset-0" aria-label="Cerrar"></button>

            <section
                class="
                    relative z-10 max-h-[92vh] w-full
                    max-w-5xl overflow-y-auto
                    rounded-[2rem] border shadow-2xl
                "
                style="
                    background: var(--ui-surface);
                    border-color: var(--ui-border);
                ">
                {{-- Cabecera --}}
                <div class="
                        sticky top-0 z-10 flex items-start
                        justify-between gap-4 border-b
                        px-6 py-5 backdrop-blur-xl
                    "
                    style="
                        background:
                            color-mix(
                                in srgb,
                                var(--ui-surface) 92%,
                                transparent
                            );
                        border-color: var(--ui-border);
                    ">
                    <div>
                        <p class="ui-kicker">
                            Ficha institucional
                        </p>

                        <h2 class="mt-1 text-xl font-black sm:text-2xl" style="color: var(--ui-text);">
                            {{ $detail['nombre'] }}
                        </h2>

                        <div
                            class="
                                mt-2 flex flex-wrap
                                items-center gap-2
                            ">
                            @foreach ($detail['roles'] as $role)
                                <span
                                    class="
                                        rounded-full px-2.5 py-1
                                        text-xs font-bold
                                    "
                                    style="
                                        background: var(--ui-surface-muted);
                                        color: var(--ui-text-soft);
                                    ">
                                    {{ $role }}
                                </span>
                            @endforeach

                            @if ($detail['cargo'])
                                <span
                                    class="
                                        rounded-full px-2.5 py-1
                                        text-xs font-bold
                                    "
                                    style="
                                        background: var(--ui-surface-muted);
                                        color: var(--ui-text-soft);
                                    ">
                                    {{ $detail['cargo'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <button wire:click="closePerson" type="button" class="ui-icon-btn h-10 w-10" title="Cerrar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>


                <div class="grid gap-5 p-6 lg:grid-cols-3">

                    {{-- Identidad --}}
                    <article class="ui-card-soft rounded-[1.5rem] p-5">
                        <p class="ui-kicker">
                            Identidad
                        </p>

                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="ui-muted text-xs">
                                    Código persona
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['cod_per'] }}
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    CI
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['ci'] ?? 'No registrado' }}

                                    @if ($detail['complemento'])
                                        {{ $detail['complemento'] }}
                                    @endif

                                    @if ($detail['expedido'])
                                        · {{ $detail['expedido'] }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    Fecha de nacimiento
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['fecha_nacimiento'] ?? 'No registrada' }}

                                    @if ($detail['edad'] !== null)
                                        <span class="ui-muted font-medium">
                                            · {{ $detail['edad'] }} años
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    Género
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['genero'] ?? 'No definido' }}
                                </dd>
                            </div>
                        </dl>
                    </article>


                    {{-- Contacto --}}
                    <article class="ui-card-soft rounded-[1.5rem] p-5">
                        <p class="ui-kicker">
                            Contacto
                        </p>

                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="ui-muted text-xs">
                                    Teléfono
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['telefono'] ?? 'No registrado' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    Correo
                                </dt>
                                <dd class="mt-1 break-all font-bold">
                                    {{ $detail['email_usuario'] ?? ($detail['correo_personal'] ?? 'No registrado') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    Dirección
                                </dt>

                                <dd class="mt-1 font-bold">
                                    {{ $detail['direccion'] ?? 'No registrada' }}
                                </dd>

                                @if ($detail['direccion'])
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($detail['direccion']) }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="
                                            mt-2 inline-flex items-center
                                            gap-2 text-xs font-bold
                                        "
                                        style="color: var(--ui-primary);">
                                        Ver ubicación

                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </dl>
                    </article>


                    {{-- Institucional --}}
                    <article class="ui-card-soft rounded-[1.5rem] p-5">
                        <p class="ui-kicker">
                            Información institucional
                        </p>

                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="ui-muted text-xs">
                                    Usuario
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['cod_usu'] ?? 'Sin usuario' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="ui-muted text-xs">
                                    Personal institucional
                                </dt>
                                <dd class="mt-1 font-bold">
                                    {{ $detail['cod_pin'] ?? 'No aplica' }}
                                </dd>
                            </div>

                            @if ($detail['cod_doc'])
                                <div>
                                    <dt class="ui-muted text-xs">
                                        Código docente
                                    </dt>
                                    <dd class="mt-1 font-bold">
                                        {{ $detail['cod_doc'] }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="ui-muted text-xs">
                                        Materia / especialidad
                                    </dt>
                                    <dd class="mt-1 font-bold">
                                        {{ $detail['especialidad'] ?? 'No asignada' }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </article>
                </div>
            </section>
        </div>
    @endif
</div>
