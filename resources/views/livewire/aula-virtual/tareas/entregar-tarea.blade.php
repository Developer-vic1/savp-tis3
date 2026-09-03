<div class="p-6 max-w-4xl mx-auto space-y-6">
    <!-- Ficha de la Actividad -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        @if($tarea)
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 mb-2">
                        {{ $tarea->tip_tar }}
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tarea->tit_tar }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $tarea->claseVirtual->planAsignatura->asignatura->nom_asi ?? '' }}
                    </p>
                </div>

                <div class="text-left md:text-right">
                    <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Puntaje Máximo</span>
                    <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ (int)$tarea->pun_max_tar }} pts</span>
                </div>
            </div>

            <!-- Vencimiento -->
            <div class="flex items-center gap-2 text-sm {{ now()->greaterThan($tarea->fec_lim_tar) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-600 dark:text-gray-300' }} mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Fecha límite: {{ \Carbon\Carbon::parse($tarea->fec_lim_tar)->translatedFormat('l, d \d\e F \d\e Y \a \l\a\s H:i') }}</span>
            </div>

            <!-- Instrucciones -->
            @if($tarea->des_tar)
                <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
                    {!! nl2br(e($tarea->des_tar)) !!}
                </div>
            @endif
        @endif
    </div>

    <!-- BANNER DE SOPORTE INTELIGENTE -->
    @if(!empty($analisis['bloqueos']))
        <div class="bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Entrega No Permitida</h3>
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
        <div class="bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 p-4 rounded-r-xl">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-amber-500 mt-0.5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Aviso de Entrega</h3>
                    <ul class="mt-1 text-sm text-amber-700 dark:text-amber-400 list-disc list-inside space-y-1">
                        @foreach($analisis['advertencias'] as $adv)
                            <li>{{ $adv }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulario de Entrega -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
            Tu Entrega
            @if($entrega)
                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $entrega->est_ent === 'CALIFICADO' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                    Estado: {{ $entrega->est_ent }}
                </span>
            @endif
        </h2>

        <form wire:submit.prevent="enviarEntrega" class="space-y-6">
            <!-- Texto de respuesta -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="texto" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Respuesta en texto (Opcional si adjuntas archivo)
                    </label>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ mb_strlen($texto) }} / 5000 caracteres
                    </span>
                </div>
                <textarea 
                    id="texto" 
                    wire:model.live.debounce.300ms="texto"
                    rows="5"
                    maxlength="5000"
                    placeholder="Escribe tu respuesta, conclusiones o enlace adicional aquí..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                ></textarea>
                @error('texto')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Archivo Adjunto -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Documento o Archivo Adjunto (Máx. 10 MB)
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition bg-gray-50/50 dark:bg-gray-700/20">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-2 flex text-sm justify-center text-gray-600 dark:text-gray-400">
                        <label for="archivo" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none">
                            <span>Seleccionar archivo</span>
                            <input id="archivo" type="file" wire:model="archivo" class="sr-only" accept=".pdf,.docx,.doc,.xlsx,.xls,.pptx,.ppt,.zip,.rar,.jpg,.jpeg,.png" />
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos admitidos: PDF, Word, Excel, PowerPoint, ZIP, Imágenes.</p>

                    <div wire:loading wire:target="archivo" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 font-semibold">
                        Cargando archivo en vista previa...
                    </div>

                    @if($archivo)
                        <div class="mt-3 p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg inline-flex items-center gap-2 text-sm text-indigo-700 dark:text-indigo-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Archivo listo: <strong>{{ $archivo->getClientOriginalName() }}</strong></span>
                        </div>
                    @endif
                </div>
                @error('archivo')
                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Archivos ya entregados previamente -->
            @if($entrega && $entrega->archivos->isNotEmpty())
                <div class="pt-2">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase block mb-2">Archivos adjuntos guardados:</span>
                    <ul class="space-y-1">
                        @foreach($entrega->archivos as $arc)
                            <li class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                <span>{{ $arc->nom_ear }} ({{ round($arc->tam_ear / 1024, 1) }} KB)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Botón de Envío -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    @disabled(!($analisis['puede_guardar'] ?? false))
                    class="px-6 py-2.5 rounded-lg text-white font-semibold shadow-sm transition flex items-center gap-2 {{ ($analisis['puede_guardar'] ?? false) ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-60' }}"
                >
                    <span wire:loading.remove wire:target="enviarEntrega">
                        {{ $entrega ? 'Actualizar Entrega' : 'Enviar Entrega Definitiva' }}
                    </span>
                    <span wire:loading wire:target="enviarEntrega" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
