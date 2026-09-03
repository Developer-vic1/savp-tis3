<div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Encabezado de Sesión -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Registro de Asistencia
                </h1>
                @if($clase)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $clase->planAsignatura->asignatura->nom_asi ?? 'Asignatura' }} — 
                        {{ $clase->planAsignatura->curso->nom_cur ?? '' }} {{ $clase->planAsignatura->paralelo->nom_par ?? '' }}
                    </p>
                @endif
            </div>

            <!-- Contador de Alumnos Marcados -->
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="text-right">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Progreso de lista</span>
                    <p class="text-lg font-bold {{ $marcados === $totalEstudiantes && $totalEstudiantes > 0 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $marcados }} / {{ $totalEstudiantes }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $marcados === $totalEstudiantes && $totalEstudiantes > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                    @if($marcados === $totalEstudiantes && $totalEstudiantes > 0)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @endif
                </div>
            </div>
        </div>

        <!-- Parámetros de la sesión -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div>
                <label for="fecha" class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de la sesión <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="fecha" 
                    wire:model.live="fecha"
                    max="{{ now()->format('Y-m-d') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                @error('fecha')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="tipoAsistencia" class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de Actividad <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipoAsistencia" 
                    wire:model.live="tipoAsistencia"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="CLASE">Clase Regular</option>
                    <option value="LABORATORIO">Laboratorio / Taller BTH</option>
                    <option value="PRACTICA">Práctica Guiada</option>
                    <option value="EVALUACION">Evaluación Continua</option>
                    <option value="ACTIVIDAD">Actividad Institucional</option>
                </select>
            </div>

            <div class="flex items-end">
                <button 
                    type="button" 
                    wire:click="marcarTodosPresentes"
                    wire:loading.attr="disabled"
                    class="w-full py-2 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-300 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2 border border-indigo-200 dark:border-indigo-800"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Marcar todos Presentes
                </button>
            </div>
        </div>
    </div>

    <!-- BANNER DE SOPORTE INTELIGENTE -->
    @if(!empty($analisis['bloqueos']))
        <div class="bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Observaciones Críticas (Acción Bloqueada)</h3>
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
        <div class="bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Advertencias Pedagógicas / Estadísticas</h3>
                    <ul class="mt-1 text-sm text-amber-700 dark:text-amber-400 list-disc list-inside space-y-1">
                        @foreach($analisis['advertencias'] as $adv)
                            <li>{{ $adv }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla de Estudiantes -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código RUDE</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Asistencia <span class="text-red-500">*</span></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observación (Opcional)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($estudiantes as $index => $est)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition {{ empty($asistencias[$est->cod_est]) ? 'bg-amber-50/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $est->ape_pat_per }} {{ $est->ape_mat_per }} {{ $est->nom_per }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-gray-300">
                                {{ $est->rud_est }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select 
                                    wire:model.live="asistencias.{{ $est->cod_est }}"
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-medium shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ empty($asistencias[$est->cod_est]) ? 'text-gray-400 border-amber-300' : 'text-gray-900 dark:text-white' }}"
                                >
                                    <option value="">— Seleccionar estado —</option>
                                    @foreach($estadosAsistencia as $estado)
                                        <option value="{{ $estado->cod_est_asi }}">{{ $estado->nom_est_asi }} ({{ $estado->abr_est_asi }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input 
                                    type="text" 
                                    wire:model.lazy="observaciones.{{ $est->cod_est }}"
                                    placeholder="Justificación o nota..."
                                    maxlength="255"
                                    class="w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron estudiantes matriculados en esta clase virtual.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Barra inferior de acciones -->
        <div class="p-6 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($marcados < $totalEstudiantes)
                    <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Faltan {{ $totalEstudiantes - $marcados }} estudiante(s) por registrar para poder consolidar.
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Todos los estudiantes han sido marcados correctamente.
                    </span>
                @endif
            </div>

            <button 
                type="button"
                wire:click="guardarAsistencia"
                wire:loading.attr="disabled"
                @disabled(!($analisis['puede_guardar'] ?? false) || $marcados < $totalEstudiantes || $totalEstudiantes === 0)
                class="w-full sm:w-auto px-6 py-2.5 rounded-lg text-white font-semibold shadow-sm transition flex items-center justify-center gap-2 {{ ($analisis['puede_guardar'] ?? false) && $marcados === $totalEstudiantes && $totalEstudiantes > 0 ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-60' }}"
            >
                <span wire:loading.remove wire:target="guardarAsistencia">
                    Consolidar y Guardar Asistencia
                </span>
                <span wire:loading wire:target="guardarAsistencia" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Consolidando sesión...
                </span>
            </button>
        </div>
    </div>
</div>
