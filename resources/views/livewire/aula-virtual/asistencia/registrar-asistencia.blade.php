<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">
    <!-- Encabezado de Sesión y Horario -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Control de Asistencia Escolar
                </h1>
                @if($clase)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <strong>{{ $clase->planAsignatura->asignatura->nom_asi ?? 'Asignatura' }}</strong> — 
                        {{ $clase->planAsignatura->curso->nom_cur ?? '' }} {{ $clase->planAsignatura->paralelo->nom_par ?? '' }}
                        ({{ $clase->planAsignatura->turno->nom_tur ?? 'Turno' }})
                    </p>
                @endif
            </div>

            <!-- Progreso -->
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600 self-start md:self-auto">
                <div class="text-right">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Estudiantes evaluados</span>
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

        <!-- Parámetros y acciones de marcado -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div>
                <label for="fecha" class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de la sesión <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="fecha" 
                    wire:model.change="fecha"
                    max="{{ now()->format('Y-m-d') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                @error('fecha')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="tipoAsistencia" class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de Sesión <span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipoAsistencia" 
                    wire:model.change="tipoAsistencia"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="CLASE">Clase Regular</option>
                    <option value="LABORATORIO">Laboratorio / Taller</option>
                    <option value="PRACTICA">Práctica Guiada</option>
                    <option value="EVALUACION">Evaluación Continua</option>
                    <option value="ACTIVIDAD">Actividad Extraordinaria</option>
                </select>
            </div>

            <div class="flex items-end">
                <button 
                    type="button" 
                    wire:click="marcarPendientesComoPresentes"
                    wire:loading.attr="disabled"
                    class="w-full py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-300 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-1.5 border border-indigo-200 dark:border-indigo-800"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Marcar Pendientes Presentes
                </button>
            </div>

            <div class="flex items-end">
                <button 
                    type="button" 
                    wire:click="marcarTodosPresentes"
                    wire:loading.attr="disabled"
                    class="w-full py-2 px-3 bg-gray-50 hover:bg-gray-100 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-1.5 border border-gray-200 dark:border-gray-600"
                >
                    Marcar Todos Presentes
                </button>
            </div>
        </div>
    </div>

    <!-- BANNER DE SOPORTE INTELIGENTE -->
    @if(!empty($analisis['bloqueos']))
        <div class="bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Observaciones Críticas (Bloqueo)</h3>
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
                <svg class="h-5 w-5 text-amber-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Avisos Pedagógicos</h3>
                    <ul class="mt-1 text-sm text-amber-700 dark:text-amber-400 list-disc list-inside space-y-1">
                        @foreach($analisis['advertencias'] as $adv)
                            <li>{{ $adv }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Vista Desktop: Tabla -->
    <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Estudiante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">RUDE</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Estado <span class="text-red-500">*</span></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Observación / Justificación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($estudiantes as $index => $est)
                    @php
                        $estadoSeleccionado = $estadosAsistencia->firstWhere('cod_est_asi', $asistencias[$est->cod_est] ?? '');
                        $reqObs = $estadoSeleccionado?->requiere_observacion ?? false;
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition {{ empty($asistencias[$est->cod_est]) ? 'bg-amber-50/20' : '' }}">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $est->ape_pat_per }} {{ $est->ape_mat_per }} {{ $est->nom_per }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-xs font-mono text-gray-500">{{ $est->rud_est }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select 
                                wire:model.change="asistencias.{{ $est->cod_est }}"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ empty($asistencias[$est->cod_est]) ? 'border-amber-300 text-gray-400' : 'text-gray-900 dark:text-white font-medium' }}"
                            >
                                <option value="">— Seleccionar —</option>
                                @foreach($estadosAsistencia as $estado)
                                    <option value="{{ $estado->cod_est_asi }}">{{ $estado->nom_est_asi }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input 
                                type="text" 
                                wire:model.blur="observaciones.{{ $est->cod_est }}"
                                placeholder="{{ $reqObs ? 'Justificación obligatoria *' : 'Nota opcional...' }}"
                                maxlength="255"
                                class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $reqObs && empty($observaciones[$est->cod_est]) ? 'border-red-400 bg-red-50/20' : '' }}"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No hay estudiantes activos matriculados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Vista Móvil: Tarjetas individuales -->
    <div class="block md:hidden space-y-3">
        @forelse($estudiantes as $index => $est)
            @php
                $estadoSeleccionado = $estadosAsistencia->firstWhere('cod_est_asi', $asistencias[$est->cod_est] ?? '');
                $reqObs = $estadoSeleccionado?->requiere_observacion ?? false;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border {{ empty($asistencias[$est->cod_est]) ? 'border-amber-300 bg-amber-50/10' : 'border-gray-200 dark:border-gray-700' }}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs font-bold text-gray-400">#{{ $index + 1 }}</span>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $est->ape_pat_per }} {{ $est->ape_mat_per }} {{ $est->nom_per }}</h4>
                        <span class="text-xs font-mono text-gray-500">{{ $est->rud_est }}</span>
                    </div>
                </div>

                <div class="space-y-2 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Estado de Asistencia</label>
                        <select 
                            wire:model.change="asistencias.{{ $est->cod_est }}"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm shadow-sm"
                        >
                            <option value="">— Seleccionar —</option>
                            @foreach($estadosAsistencia as $estado)
                                <option value="{{ $estado->cod_est_asi }}">{{ $estado->nom_est_asi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Observación</label>
                        <input 
                            type="text" 
                            wire:model.blur="observaciones.{{ $est->cod_est }}"
                            placeholder="{{ $reqObs ? 'Justificación obligatoria *' : 'Nota opcional...' }}"
                            maxlength="255"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm {{ $reqObs && empty($observaciones[$est->cod_est]) ? 'border-red-400' : '' }}"
                        />
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-sm text-gray-500 bg-white dark:bg-gray-800 rounded-xl">No hay estudiantes activos.</div>
        @endforelse
    </div>

    <!-- Barra de Consolidación -->
    <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
            @if($marcados < $totalEstudiantes)
                <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    Faltan {{ $totalEstudiantes - $marcados }} estudiante(s) por registrar para poder consolidar.
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Todos los estudiantes han sido evaluados.
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
                Guardando sesión...
            </span>
        </button>
    </div>
</div>
