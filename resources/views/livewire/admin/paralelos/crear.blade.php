@teleport('body')
<div class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="paralelo-crear-titulo" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalCrear()">
    <div class="ui-modal-backdrop"></div>
    <div class="ui-modal flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden">
        <header class="ui-modal-header shrink-0 flex items-start justify-between gap-3"><div><h3 id="paralelo-crear-titulo" class="ui-title text-xl font-black">Registrar paralelo</h3><p class="ui-muted mt-1 text-sm">Paso {{ $faseCrear }} de 3 · {{ [1=>'Define el nombre', 2=>'Justifica y adjunta el respaldo', 3=>'Verifica y confirma'][$faseCrear] }}</p></div><button type="button" wire:click="cerrarModalCrear" class="ui-icon-btn" aria-label="Cerrar registro de paralelo"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        <x-fases-institucionales :actual="$faseCrear" :pasos="[1=>['titulo'=>'Nombre','icono'=>'ph-users-three'],2=>['titulo'=>'Motivo y PDF','icono'=>'ph-file-pdf'],3=>['titulo'=>'Verificar y confirmar','icono'=>'ph-shield-check']]" />
        <div class="min-h-0 overflow-y-auto ui-scrollbar space-y-4 p-4 sm:p-6" wire:key="fase-paralelo-{{ $faseCrear }}">
            @if ($faseCrear === 1)
                <div class="ui-alert-info text-sm">Completa únicamente la letra del catálogo. El grado, el turno y el aula pertenecen a la planificación de grupos.</div>
                <div><label for="crear-paralelo-nombre" class="ui-label">Nombre del paralelo *</label><input id="crear-paralelo-nombre" class="ui-input" wire:model.live.debounce.300ms="form.nom_par" maxlength="30" placeholder="Ej. E o Único">@error('form.nom_par')<p class="ui-error">{{ $message }}</p>@enderror</div>
                <div class="{{ $puedeGuardarCrear ? 'ui-alert-info' : 'ui-alert-warning' }}" role="status"><strong>{{ $puedeGuardarCrear ? 'Nombre reconocido' : 'Nombre pendiente de corregir' }}</strong><p class="mt-1 text-sm">{{ $analisisCrear['mensaje'] ?? 'Escribe una letra o Único. El texto sin significado no se considera válido.' }}</p><p class="mt-2 text-xs">Reconocer el nombre permite pasar al respaldo; todavía no autoriza el registro.</p></div>
                @if ($analisisCrear['puede_reactivar'] ?? false)<button type="button" wire:click="reactivarExistenteDesdeAnalisisCrear" class="ui-btn-secondary">Preparar reactivación del existente</button>@endif
            @elseif ($faseCrear === 2)
                <div class="grid gap-5 lg:grid-cols-[1.2fr_1fr]">
                    <div>@include('livewire.admin.paralelos.expediente', ['enFases'=>true])</div>
                    <x-requisitos-respaldo-academico tipo="paralelo" />
                </div>
            @else
                <div class="ui-alert-success text-sm"><strong>Coincidencias documentales verificadas</strong><p class="mt-1">El PDF pasó la revisión de contenido. Completa ahora la comprobación con la autoridad y confirma el alcance.</p></div>
                <dl class="ui-card-soft grid gap-3 p-4 text-sm sm:grid-cols-2">@foreach (['Paralelo'=>$form['nom_par'], 'Gestión solicitada'=>$gestionSolicitud, 'Documento'=>$numeroDocumento.' · '.$fechaDocumento, 'Autoridad'=>$autoridadDocumento] as $etiqueta=>$valor)<div><dt class="ui-muted text-xs">{{ $etiqueta }}</dt><dd class="ui-title font-bold">{{ $valor }}</dd></div>@endforeach</dl>
                <div><label for="verificacion-final-paralelo" class="ui-label">Comprobación con la autoridad *</label><textarea id="verificacion-final-paralelo" wire:model.live.debounce.300ms="referenciaVerificacion" class="ui-textarea" rows="2" maxlength="500" placeholder="Ej.: Comprobado el 04/10/{{ now()->year }} por el canal oficial, expediente [referencia]."></textarea><p class="ui-muted mt-1 text-xs">Escribe fecha, canal oficial y referencia de tu comprobación real (mínimo 15 caracteres). El PDF no completa esta confirmación.</p>@error('referenciaVerificacion')<p class="ui-error">{{ $message }}</p>@enderror</div>
                <div><x-selector-institucional modelo="form.est_par" identificador="estado-final-paralelo" etiqueta="Disponibilidad del catálogo" :requerido="true" :opciones="[['valor'=>'ACTIVO','etiqueta'=>'Activo para planificación'],['valor'=>'INACTIVO','etiqueta'=>'Inactivo, pendiente de habilitación']]" /></div>
                <div class="ui-alert-info text-sm"><strong>Qué se guardará</strong><p>El catálogo «{{ $form['nom_par'] }}», el motivo y su expediente. No se crean grupos, no se trasladan estudiantes ni se replica la incorporación en otras gestiones.</p>@if ((int)$gestionSolicitud > now()->year)<p class="mt-2">Para la próxima gestión debe permanecer inactivo y revisarse antes de habilitarlo.</p>@endif</div>
                <label class="ui-title flex gap-2 text-sm"><input type="checkbox" wire:model.live="confirmarImpacto" class="mt-1"> He comprobado el respaldo con la autoridad y confirmo el alcance del cambio.</label>@error('confirmarImpacto')<p class="ui-error">{{ $message }}</p>@enderror
                @error('documentoCambio')<p class="ui-error">{{ $message }}</p>@enderror
                @error('gestionSolicitud')<p class="ui-error">{{ $message }}</p>@enderror
            @endif
        </div>
        <footer class="ui-modal-footer shrink-0 flex flex-wrap items-center justify-between gap-3">
            <button type="button" wire:click="{{ $faseCrear > 1 ? 'volverFaseCreacion' : 'cerrarModalCrear' }}" wire:loading.attr="disabled" class="ui-btn-secondary">{{ $faseCrear > 1 ? 'Volver' : 'Cancelar' }}</button>
            @if ($faseCrear < 3)<button type="button" wire:click="continuarCreacion" wire:loading.attr="disabled" @disabled($faseCrear === 1 && !$puedeGuardarCrear) class="ui-btn-primary"><span wire:loading.remove wire:target="continuarCreacion">{{ $faseCrear === 2 ? 'Leer PDF y verificar' : 'Continuar con el respaldo' }}</span><span wire:loading wire:target="continuarCreacion">{{ $faseCrear === 2 ? 'Leyendo PDF…' : 'Revisando…' }}</span></button>
            @else<button type="button" wire:click="guardarParalelo" wire:loading.attr="disabled" @disabled(!$this->expedienteCompleto) class="ui-btn-primary"><span wire:loading.remove wire:target="guardarParalelo">Confirmar y guardar paralelo</span><span wire:loading wire:target="guardarParalelo">Guardando expediente…</span></button>@endif
        </footer>
    </div>
</div>
@endteleport
