<div class="p-6 max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        @if($entrega)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Calificar Entrega de Estudiante
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Estudiante: <strong>{{ $entrega->estudiante->persona->ape_pat_per ?? '' }} {{ $entrega->estudiante->persona->ape_mat_per ?? '' }} {{ $entrega->estudiante->persona->nom_per ?? '' }}</strong> (RUDE: {{ $entrega->estudiante->rud_est ?? '' }})
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $entrega->est_ent === 'ENTREGADO_TARDE' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' }}">
                        {{ $entrega->est_ent }}
                    </span>
                </div>
            </div>

            <!-- Contenido entregado por el estudiante -->
            <div class="space-y-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Trabajo presentado:</h3>

                @if($entrega->tex_ent)
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-100 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-200">
                        {!! nl2br(e($entrega->tex_ent)) !!}
                    </div>
                @endif

                @if($entrega->archivos->isNotEmpty())
                    <div class="space-y-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Archivos adjuntos:</span>
                        <ul class="space-y-1">
                            @foreach($entrega->archivos as $arc)
                                <li class="text-sm text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    <a href="{{ Storage::url($arc->url_ear) }}" target="_blank" class="hover:underline font-medium">
                                        {{ $arc->nom_ear }} ({{ round($arc->tam_ear / 1024, 1) }} KB)
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- BANNER DE SOPORTE INTELIGENTE -->
            @if(!empty($analisis['bloqueos']))
                <div class="mb-6 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                            @foreach($analisis['bloqueos'] as $bloqueo)
                                <li>{{ $bloqueo }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(!empty($analisis['sugerencias']))
                <div class="mb-6 bg-blue-50 dark:bg-blue-950/40 border-l-4 border-blue-500 p-4 rounded-r-xl">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                        <ul class="text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                            @foreach($analisis['sugerencias'] as $sug)
                                <li>{{ $sug }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="guardarCalificacion" class="space-y-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Puntaje -->
                <div class="max-w-xs">
                    <label for="puntaje" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Puntaje Obtenido (Máximo: {{ (int)$entrega->tarea->pun_max_tar }} pts) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <input 
                            type="number" 
                            id="puntaje" 
                            wire:model.live="puntaje"
                            min="0" 
                            max="{{ (int)$entrega->tarea->pun_max_tar }}" 
                            step="0.5"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-bold pr-12"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 dark:text-gray-400 font-semibold">
                            pts
                        </div>
                    </div>
                    @error('puntaje')
                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Retroalimentación -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="retroalimentacion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Comentarios y Retroalimentación Formativa
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ mb_strlen($retroalimentacion) }} / 1000 caracteres
                        </span>
                    </div>
                    <textarea 
                        id="retroalimentacion" 
                        wire:model.live.debounce.300ms="retroalimentacion"
                        rows="4"
                        maxlength="1000"
                        placeholder="Escribe comentarios sobre las fortalezas y aspectos a mejorar del trabajo del estudiante..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    ></textarea>
                    @error('retroalimentacion')
                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botón -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        @disabled(!($analisis['puede_guardar'] ?? false))
                        class="px-6 py-2.5 rounded-lg text-white font-semibold shadow-sm transition flex items-center gap-2 {{ ($analisis['puede_guardar'] ?? false) ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-60' }}"
                    >
                        <span wire:loading.remove wire:target="guardarCalificacion">
                            Guardar Calificación
                        </span>
                        <span wire:loading wire:target="guardarCalificacion" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Registrando nota...
                        </span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
