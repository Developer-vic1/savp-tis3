<section id="cuestionario" class="intereses-panel" aria-labelledby="cuestionario-titulo">
    <div class="intereses-cabecera"><div><p class="intereses-etiqueta">Cuestionario RIASEC</p><h2 id="cuestionario-titulo" class="mt-2">¿Qué te gustaría hacer?</h2><p class="intereses-texto mt-2">Piensa si disfrutarías la actividad, aunque todavía no sepas hacerla. No hay respuestas correctas o incorrectas.</p></div><span class="intereses-chip">30 actividades</span></div>
    @if($instrument?->available)
        <div>
            <form method="POST" action="{{ route('estudiante.intereses.guardar') }}" x-data="crearCuestionarioIntereses({{ \Illuminate\Support\Js::from(['ids' => $instrumentItems->pluck('item_id')->all(), 'respuestas' => (object) $formResponses, 'respuestasGuardadas' => (object) $storedResponses, 'urlBorrador' => route('estudiante.intereses.borrador'), 'borradorGuardado' => (bool) $borradorCompatible, 'guardadoEn' => $borradorCompatible ? $draft->updated_at?->toIso8601String() : null]) }})" @submit.prevent="guardar($event)" :aria-busy="enviando">
                @csrf
                <input type="hidden" name="instrument_version" value="{{ data_get($instrument->data, 'instrument_version') }}">
                <div class="intereses-borrador" :data-pendiente="cambiosPendientes ? 'true' : null">
                    <i class="ph-duotone ph-floppy-disk" aria-hidden="true"></i>
                    <div><h3>Tu avance puede esperar hasta mañana</h3><p role="status" aria-live="polite" x-text="estadoBorrador">{{ $borradorCompatible ? 'Tu borrador está guardado en tu cuenta. Continúa por las actividades pendientes.' : 'Guarda las respuestas que avances y retómalas en tu cuenta otro día.' }}</p><p class="intereses-texto">El borrador conserva tus respuestas; el perfil se genera al completar las 30 actividades.</p></div>
                    <button class="ui-btn-secondary" type="submit" formnovalidate formaction="{{ route('estudiante.intereses.borrador') }}" @click.prevent="guardarBorrador()" :disabled="enviando || !contestadas || !cambiosPendientes || {{ $storageReady ? 'false' : 'true' }}" @disabled(!$storageReady)><span x-text="guardandoBorrador ? 'Guardando borrador…' : 'Guardar borrador'">Guardar borrador</span></button>
                </div>
                <div class="intereses-progreso">
                    <div class="intereses-progreso-cabecera"><span role="status" aria-live="polite" x-text="contestadas + ' de ' + ids.length + ' respondidas'">{{ count($formResponses) }} de 30 respondidas</span><span x-text="porcentaje + '%'"></span></div>
                    <progress max="30" value="{{ count($formResponses) }}" :value="contestadas" aria-label="Avance del cuestionario"></progress>
                    <nav class="intereses-bloques" aria-label="Bloques del cuestionario">@for($b = 0; $b < 6; $b++)<button type="button" @click="ir({{ $b }})" :disabled="enviando" :aria-current="bloque === {{ $b }} && !revisando ? 'step' : null" :class="{ 'is-completo': respondidasBloque({{ $b }}) === 5 }" :aria-label="'Bloque {{ $b + 1 }} de 6, ' + respondidasBloque({{ $b }}) + ' de 5 respondidas'">{{ $b + 1 }}</button>@endfor</nav>
                </div>
                <div class="intereses-bloque-titulo" tabindex="-1" x-ref="tituloBloque"><span x-text="revisando ? 'Todas tus respuestas' : 'Bloque ' + (bloque + 1) + ' de ' + totalBloques">Bloque 1 de 6</span><button class="text-sm underline underline-offset-4" type="button" @click="revisando = !revisando" :disabled="enviando" x-text="revisando ? 'Volver a bloques' : 'Ver todas'"></button></div>
                <div class="intereses-aviso mb-3" x-ref="aviso" tabindex="-1" x-show="aviso" x-cloak role="alert"><h3 x-text="aviso?.title"></h3><p x-text="aviso?.message"></p><p x-text="aviso?.preserved"></p><p x-text="aviso?.next_step"></p></div>
                @foreach($instrumentItems as $indice => $item)
                    @php $itemId = data_get($item, 'item_id'); $currentValue = data_get($formResponses, (string) $itemId); @endphp
                    <fieldset class="intereses-pregunta" x-show="visible({{ $indice }})" :disabled="enviando" :aria-describedby="errores['responses.{{ $itemId }}'] ? 'error-interes-{{ $itemId }}' : null">
                        <legend><span class="intereses-pregunta-numero">{{ str_pad($itemId, 2, '0', STR_PAD_LEFT) }}</span>{{ data_get($item, 'text') }}</legend>
                        <div class="intereses-opciones">
                            @foreach($responseScale as $option)
                                @php $optionValue = data_get($option, 'value'); $optionId = 'interes-'.$itemId.'-'.$optionValue; @endphp
                                <label class="intereses-opcion" for="{{ $optionId }}"><input class="sr-only" id="{{ $optionId }}" type="radio" required name="responses[{{ $itemId }}]" value="{{ $optionValue }}" x-model="respuestas[{{ $itemId }}]" @checked((string) $currentValue === (string) $optionValue)><i class="ph-duotone ph-{{ [1 => 'minus-circle', 2 => 'minus', 3 => 'question', 4 => 'plus', 5 => 'plus-circle'][$optionValue] }}" aria-hidden="true"></i><span>{{ data_get($option, 'label') }}</span></label>
                            @endforeach
                        </div>
                        <p class="intereses-error" id="error-interes-{{ $itemId }}" x-show="errores['responses.{{ $itemId }}']" x-text="errores['responses.{{ $itemId }}']?.join(' ')" x-cloak></p>
                    </fieldset>
                @endforeach
                <div class="intereses-botones">
                    <button class="ui-btn-secondary" type="button" @click="ir(bloque - 1)" :disabled="bloque === 0 || enviando" x-show="!revisando"><i class="ph-duotone ph-arrow-left" aria-hidden="true"></i>Anterior</button>
                    <button class="ui-btn-primary" type="button" @click="ir(bloque + 1)" :disabled="enviando" x-show="!revisando && bloque < totalBloques - 1">Siguiente bloque<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button>
                    <button class="ui-btn-primary" type="submit" :disabled="enviando || !completa || {{ $storageReady ? 'false' : 'true' }}" @disabled(!$storageReady) x-show="revisando || bloque === totalBloques - 1"><i class="ph-duotone ph-floppy-disk" aria-hidden="true"></i><span x-text="enviando ? 'Guardando tu perfil…' : 'Guardar mis intereses'">Guardar mis intereses</span></button>
                </div>
                <button class="ui-btn-secondary mt-3" type="submit" formnovalidate formaction="{{ route('estudiante.intereses.borrador') }}" @click.prevent="guardarBorrador()" :disabled="enviando || !contestadas || !cambiosPendientes || {{ $storageReady ? 'false' : 'true' }}" @disabled(!$storageReady)>Guardar borrador y continuar después<i class="ph-duotone ph-clock" aria-hidden="true"></i></button>
                <div x-show="!completa && (revisando || bloque === totalBloques - 1)" class="mt-4"><p class="intereses-texto" x-text="'Faltan ' + (ids.length - contestadas) + ' actividades. Respondemos todas para obtener un perfil completo.'"></p><button class="ui-btn-secondary mt-2" type="button" @click="revisarPendientes()">Ir a la primera pendiente</button></div>
                @if(!$storageReady)<p class="intereses-error mt-3">Todavía no podemos guardar el cuestionario. La administración necesita habilitar esta función; conserva esta pestaña abierta.</p>@endif
                <div class="intereses-carga" x-show="enviando" x-cloak role="status" aria-live="polite"><span class="intereses-spinner" aria-hidden="true"></span><div><h3 x-text="guardandoBorrador ? 'Estamos guardando tu avance' : 'Estamos preparando tu perfil'">Estamos preparando tu perfil</h3><p class="intereses-texto" x-text="guardandoBorrador ? 'Esperamos la confirmación del guardado en tu cuenta. Puedes salir cuando aparezca Borrador guardado.' : 'Procesamos tus respuestas y esperamos confirmación del guardado. No cierres esta pestaña.'">Procesamos tus respuestas y esperamos confirmación del guardado. No cierres esta pestaña.</p></div></div>
            </form>
            <x-plegable-institucional etiqueta="De dónde vienen estas 30 actividades" descripcion="Consulta el instrumento, su versión y la fuente oficial." icono="ph-book-open-text"><p class="intereses-texto mt-2">{{ data_get($instrument->data, 'title') }} · {{ data_get($instrument->data, 'instrument_version') }}</p><p class="intereses-texto mt-2">{{ data_get($instrument->data, 'source_attribution') }}</p>@if($enlaceSeguro(data_get($instrument->data, 'source_url')))<a class="ui-btn-secondary mt-3" href="{{ data_get($instrument->data, 'source_url') }}" target="_blank" rel="noopener noreferrer">Ver instrumento oficial<span class="sr-only">(abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endif<p class="intereses-texto mt-3">Conservamos las actividades y la escala oficiales. La equivalencia psicométrica para estudiantes bolivianos todavía no se ha validado.</p></x-plegable-institucional>
        </div>
    @else
        <div class="intereses-aviso mt-4" role="status"><h3>El cuestionario no está disponible en este momento</h3><p>El servicio no pudo entregar las actividades oficiales. Tus resultados anteriores siguen guardados. Recarga para volver a comprobar la conexión.</p><a href="{{ route('estudiante.intereses') }}" class="ui-btn-secondary mt-3">Volver a comprobar</a></div>
    @endif
</section>
