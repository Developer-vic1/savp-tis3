<div class="p-6 max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Actividad Académica
                </h1>
                @if($clase)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $clase->planAsignatura->asignatura->nom_asi ?? 'Asignatura' }} — 
                        {{ $clase->planAsignatura->curso->nom_cur ?? '' }} {{ $clase->planAsignatura->paralelo->nom_par ?? '' }}
                    </p>
                @endif
            </div>
        </div>

        <!-- BANNER DE SOPORTE INTELIGENTE -->
        @if(!empty($analisis['bloqueos']))
            <div class="mb-6 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Observaciones Críticas</h3>
                        <ul class="mt-1 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                            @foreach($analisis['bloqueos'] as $bloqueo)
                                <li>{{ $bloqueo }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($analisis['advertencias']))
            <div class="mb-6 bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 p-4 rounded-r-xl">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-amber-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Alertas y Recomendaciones</h3>
                        <ul class="mt-1 text-sm text-amber-700 dark:text-amber-400 list-disc list-inside space-y-1">
                            @foreach($analisis['advertencias'] as $adv)
                                <li>{{ $adv }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="guardarCambios" class="space-y-6">
            <!-- Título -->
            <div>
                <label for="titulo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Título de la actividad <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="titulo" 
                    wire:model.live.debounce.300ms="titulo"
                    maxlength="180"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                />
                @error('titulo')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tipo y Puntaje -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tipo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Tipo de Actividad <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="tipo" 
                        wire:model.live="tipo"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="TAREA">Tarea Regular</option>
                        <option value="PRACTICA">Práctica / Ejercicios</option>
                        <option value="PROYECTO">Proyecto Formativo BTH</option>
                        <option value="INVESTIGACION">Investigación Documental</option>
                        <option value="LABORATORIO">Informe de Laboratorio</option>
                        <option value="EVALUACION">Evaluación Cuantitativa</option>
                    </select>
                </div>

                <div>
                    <label for="puntajeMaximo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Puntaje Máximo <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="puntajeMaximo" 
                        wire:model.live="puntajeMaximo"
                        min="1" 
                        max="100" 
                        step="1"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                    @error('puntajeMaximo')
                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fechaPublicacion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Fecha y Hora de Publicación <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="fechaPublicacion" 
                        wire:model.live="fechaPublicacion"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                    @error('fechaPublicacion')
                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="fechaLimite" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Fecha y Hora Límite de Entrega <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        id="fechaLimite" 
                        wire:model.live="fechaLimite"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                    @error('fechaLimite')
                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Instrucciones de la Actividad
                    </label>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ mb_strlen($descripcion) }} / 2000 caracteres
                    </span>
                </div>
                <textarea 
                    id="descripcion" 
                    wire:model.live.debounce.300ms="descripcion"
                    rows="5"
                    maxlength="2000"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                ></textarea>
                @error('descripcion')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Opciones y Estado -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-lg border border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="permiteEntregaTardia" 
                        wire:model.live="permiteEntregaTardia"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="permiteEntregaTardia" class="ml-2 text-sm text-gray-700 dark:text-gray-300 font-medium cursor-pointer">
                        Permitir entregas fuera de plazo
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <label for="estado" class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Estado:</label>
                    <select 
                        id="estado" 
                        wire:model.live="estado"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-medium"
                    >
                        <option value="PUBLICADA">Publicada</option>
                        <option value="BORRADOR">Borrador</option>
                        <option value="CERRADA">Cerrada</option>
                        <option value="ANULADA">Anulada</option>
                    </select>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    @disabled(!($analisis['puede_guardar'] ?? false))
                    class="px-6 py-2.5 rounded-lg text-white font-semibold shadow-sm transition flex items-center gap-2 {{ ($analisis['puede_guardar'] ?? false) ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-60' }}"
                >
                    <span wire:loading.remove wire:target="guardarCambios">
                        Actualizar Actividad
                    </span>
                    <span wire:loading wire:target="guardarCambios" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Guardando cambios...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
