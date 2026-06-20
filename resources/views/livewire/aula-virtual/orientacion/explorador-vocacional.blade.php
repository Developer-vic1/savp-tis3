<div>
    <button type="button" wire:click="abrir"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--savp-green)] px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-emerald-300/40">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18.75a6.75 6.75 0 1 0 0-13.5 6.75 6.75 0 0 0 0 13.5ZM12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3" />
        </svg>
        Explorador académico-vocacional
    </button>

    @if ($abierto)
        <div class="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6" role="dialog" aria-modal="true" aria-label="Explorador académico-vocacional">
            <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm"
                onclick="if (!@js($sinGuardar) || confirm('Hay cambios sin guardar. ¿Cerrar el explorador?')) { @this.cerrar(); }"></div>

            <section class="relative z-10 flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg border shadow-2xl"
                style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                <header class="border-b p-5" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="ui-kicker">Orientación académica-profesional</p>
                            <h2 class="ui-title mt-1 text-2xl font-black">Explorador académico-vocacional</h2>
                            <p class="ui-subtitle mt-2 text-sm leading-6">
                                Responde con sinceridad para generar una orientación académica complementaria.
                            </p>
                        </div>

                        <button type="button"
                            onclick="if (!@js($sinGuardar) || confirm('Hay cambios sin guardar. ¿Cerrar el explorador?')) { @this.cerrar(); }"
                            class="rounded-lg p-2 text-[var(--ui-muted)] transition hover:bg-[var(--ui-surface-muted)] focus:outline-none focus:ring-4 focus:ring-emerald-300/40"
                            aria-label="Cerrar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </header>

                <div class="ui-scrollbar overflow-y-auto p-5">
                    @if ($mensaje)
                        <div class="mb-4 rounded-lg border px-4 py-3 text-sm font-bold"
                            style="background: var(--savp-green-soft); border-color: var(--ui-primary-border); color: var(--savp-green);">
                            {{ $mensaje }}
                        </div>
                    @endif

                    @if ($resultado)
                        <div class="space-y-5">
                            <div class="rounded-lg border p-4" style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                <h3 class="ui-title text-xl font-black">Resultados por áreas</h3>
                                <p class="ui-subtitle mt-2 text-sm leading-7">{{ $mensajeOrientativo }}</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                @foreach ($dimensiones as $codigo => $nombre)
                                    @php $valor = (float) $resultado->{$codigo}; @endphp
                                    <div class="rounded-lg border p-4" style="border-color: var(--ui-border);">
                                        @include('aula-virtual.componentes.progress-bar', ['value' => (int) round($valor), 'label' => $nombre])
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <h3 class="ui-title text-xl font-black">Carreras sugeridas</h3>
                                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                    @foreach ($resultado->carreras as $carrera)
                                        <article class="rounded-lg border p-4" style="border-color: var(--ui-border);">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <h4 class="ui-title font-black">{{ $carrera->carrera }}</h4>
                                                    <p class="ui-muted mt-1 text-sm">{{ $carrera->area_profesional }}</p>
                                                </div>
                                                <span class="ui-badge-success">{{ $carrera->compatibilidad }}%</span>
                                            </div>
                                            <p class="ui-subtitle mt-3 text-sm leading-6">{{ $carrera->razon }}</p>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <p class="ui-muted text-xs font-black uppercase">Fortalezas</p>
                                                    <ul class="mt-2 list-disc space-y-1 pl-4 text-sm">
                                                        @foreach (($carrera->fortalezas ?? []) as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div>
                                                    <p class="ui-muted text-xs font-black uppercase">Áreas a fortalecer</p>
                                                    <ul class="mt-2 list-disc space-y-1 pl-4 text-sm">
                                                        @foreach (($carrera->areas_a_fortalecer ?? []) as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif ($preguntaActual)
                        <div class="space-y-6">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs font-bold">
                                    <span class="ui-muted">Pregunta {{ $paso + 1 }} de {{ $total }}</span>
                                    <span class="ui-title">{{ $progreso }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-[var(--ui-surface-muted)]">
                                    <div class="h-full rounded-full bg-[var(--savp-green)]" style="width: {{ $progreso }}%"></div>
                                </div>
                            </div>

                            <article class="rounded-lg border p-5" style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                <h3 class="ui-title text-xl font-black leading-8">{{ $preguntaActual['texto'] }}</h3>
                            </article>

                            <fieldset>
                                <legend class="ui-label">Escala de respuesta</legend>
                                <div class="grid gap-3 md:grid-cols-5">
                                    @foreach ([1 => 'No me representa', 2 => 'Me representa poco', 3 => 'Neutral', 4 => 'Me representa', 5 => 'Me representa mucho'] as $valor => $label)
                                        <label class="flex cursor-pointer flex-col gap-2 rounded-lg border p-3 text-sm font-bold transition focus-within:ring-4 focus-within:ring-emerald-300/40"
                                            style="border-color: var(--ui-border); background: {{ (int) ($respuestas[$preguntaActual['id']] ?? 0) === $valor ? 'var(--savp-green-soft)' : 'var(--ui-surface)' }};">
                                            <input type="radio" class="accent-[var(--savp-green)]"
                                                wire:model.live="respuestas.{{ $preguntaActual['id'] }}" value="{{ $valor }}">
                                            <span class="ui-title">{{ $valor }}</span>
                                            <span class="ui-muted leading-5">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('respuestaActual')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>
                    @else
                        @include('aula-virtual.componentes.empty-state', ['titulo' => 'Explorador académico-vocacional.', 'descripcion' => 'Las preguntas se habilitarán desde el banco institucional de orientación.'])
                    @endif
                </div>

                <footer class="flex flex-col gap-3 border-t p-5 sm:flex-row sm:items-center sm:justify-between"
                    style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                    <div class="text-sm font-bold ui-muted">
                        {{ $mensajeOrientativo }}
                    </div>
                    <div class="flex flex-wrap justify-end gap-2">
                        @if (! $resultado)
                            <button type="button" wire:click="anterior" @disabled($paso === 0)
                                class="ui-btn-secondary focus:outline-none focus:ring-4 focus:ring-emerald-300/40">Anterior</button>
                            <button type="button" wire:click="guardarAvance" wire:loading.attr="disabled"
                                class="ui-btn-secondary focus:outline-none focus:ring-4 focus:ring-emerald-300/40">Guardar avance</button>
                            @if ($paso + 1 < $total)
                                <button type="button" wire:click="siguiente" wire:loading.attr="disabled"
                                    class="ui-btn-primary focus:outline-none focus:ring-4 focus:ring-emerald-300/40">Siguiente</button>
                            @else
                                <button type="button" wire:click="finalizar" wire:loading.attr="disabled"
                                    class="ui-btn-primary focus:outline-none focus:ring-4 focus:ring-emerald-300/40">Finalizar</button>
                            @endif
                        @else
                            <button type="button" wire:click="cerrar"
                                class="ui-btn-primary focus:outline-none focus:ring-4 focus:ring-emerald-300/40">Cerrar</button>
                        @endif
                    </div>
                </footer>
            </section>
        </div>
    @endif
</div>
