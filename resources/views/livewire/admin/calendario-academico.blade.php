<div class="space-y-5">
    <section class="ui-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="ui-title">Calendario Académico</h1>@can('create', \App\Models\CalendarioEvento::class)<button type="button" wire:click="abrir" class="ui-btn-primary">Registrar evento</button>@endcan</div>
        <p class="ui-help">Feriados, descansos, contingencias y recuperaciones. Las prealertas no suspenden clases.</p>
        <div class="mt-4 flex gap-3">
            <select wire:model.live="gestion" class="ui-select" aria-label="Gestión"><option value="">Todas las gestiones</option>@foreach($gestiones as $item)<option value="{{ $item->cod_gea }}">{{ $item->ani_gea }}</option>@endforeach</select>
            <select wire:model.live="estado" class="ui-select" aria-label="Estado"><option value="">Todos los estados</option>@foreach($estados as $item)<option>{{ $item }}</option>@endforeach</select>
        </div>
    </section>
    <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Evento</th><th>Fechas</th><th>Tipo</th><th>Efecto</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        @forelse($eventos as $evento)<tr wire:key="evento-{{ $evento->cod_cae }}"><td>{{ $evento->nom_cae }}</td><td>{{ $evento->fii_cae->format('d/m/Y') }} – {{ $evento->ffi_cae->format('d/m/Y') }}</td><td>{{ $evento->tip_cae }}</td><td>{{ $evento->efe_cae }}</td><td>{{ $evento->est_cae }}</td><td><button type="button" wire:click="abrir('{{ $evento->cod_cae }}')" class="ui-btn-secondary">Editar / analizar</button></td></tr>
        @empty<tr><td colspan="6">No hay eventos registrados para los filtros seleccionados.</td></tr>@endforelse
    </tbody></table></div>{{ $eventos->links() }}
    @if($modalEvento)
        <div class="ui-modal-backdrop"></div><div class="ui-modal fixed inset-4 z-50 mx-auto max-w-3xl overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="evento-titulo">
            <div class="ui-modal-header"><h2 id="evento-titulo" class="ui-title">{{ $codigo ? 'Editar evento' : 'Registrar evento' }}</h2></div>
            <div class="space-y-4 p-5">
                @foreach($errors->all() as $error)<div class="ui-alert-danger">{{ $error }}</div>@endforeach
                <label class="ui-label">Nombre<input wire:model="form.nom_cae" class="ui-input"></label>
                <label class="ui-label">Gestión<select wire:model="form.cod_gea" class="ui-select"><option value="">Seleccionar</option>@foreach($gestiones as $item)<option value="{{ $item->cod_gea }}">{{ $item->ani_gea }}</option>@endforeach</select></label>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach(['tip_cae' => $tipos,'efe_cae' => $efectos,'est_cae' => $estados] as $campo => $opciones)
                        <label class="ui-label">{{ ['tip_cae'=>'Tipo','efe_cae'=>'Efecto','est_cae'=>'Estado'][$campo] }}<select wire:model="form.{{ $campo }}" class="ui-select">@foreach($opciones as $opcion)<option>{{ $opcion }}</option>@endforeach</select></label>
                    @endforeach
                    <label class="ui-label">Inicio<input type="date" wire:model="form.fii_cae" class="ui-input"></label><label class="ui-label">Fin<input type="date" wire:model="form.ffi_cae" class="ui-input"></label>
                    <label class="ui-label">Desde hora<input type="time" wire:model="form.hoi_cae" class="ui-input"></label><label class="ui-label">Hasta hora<input type="time" wire:model="form.hof_cae" class="ui-input"></label>
                    @foreach(['cod_cur'=>'cursos','cod_par'=>'paralelos','cod_tur'=>'turnos'] as $campo => $catalogo)
                        <label class="ui-label">{{ ucfirst($catalogo) }}<select wire:model="form.{{ $campo }}" class="ui-select"><option value="">Todos</option>@foreach($catalogos[$catalogo] as $item)<option value="{{ $item[$campo] }}">{{ $item['nombre'] ?? $item[$campo] }}</option>@endforeach</select></label>
                    @endforeach
                </div>
                <label class="ui-label">Código del horario específico (opcional)<input wire:model="form.cod_hde" class="ui-input"></label>
                <label class="ui-label">Evento original para recuperación<input wire:model="form.cod_cae_ori" class="ui-input"></label>
                <label class="ui-label"><input type="checkbox" wire:model="form.com_cae"> Computa como jornada efectiva por decisión institucional</label>
                <label class="ui-label">Motivo<textarea wire:model="form.mot_cae" class="ui-textarea"></textarea></label>
                <label class="ui-label">Fuente o comunicado<textarea wire:model="form.fue_cae" class="ui-textarea"></textarea></label>
                <details class="ui-card p-3"><summary class="ui-label">Autoridad y disposición normativa</summary>
                    <div class="grid gap-3 md:grid-cols-2 mt-3">
                        <label class="ui-label">Entidad emisora<input wire:model="form.ent_cae" class="ui-input" maxlength="180"></label>
                        <label class="ui-label">Nivel<select wire:model="form.niv_cae" class="ui-select"><option value="INSTITUCIONAL">Institucional</option><option value="DISTRITAL">Distrital</option><option value="DEPARTAMENTAL">Departamental</option><option value="NACIONAL">Nacional</option></select></label>
                        <label class="ui-label">Tipo de documento<input wire:model="form.tip_doc_cae" class="ui-input"></label>
                        <label class="ui-label">Número<input wire:model="form.num_doc_cae" class="ui-input"></label>
                        <label class="ui-label">Emisión<input type="date" wire:model="form.fec_emi_cae" class="ui-input"></label>
                        <label class="ui-label">Publicación<input type="date" wire:model="form.fec_pub_cae" class="ui-input"></label>
                        <label class="ui-label">URL oficial<input type="url" wire:model="form.url_cae" class="ui-input"></label>
                        <label class="ui-label">Certeza<select wire:model="form.cer_cae" class="ui-select"><option>INFORMATIVO</option><option>PROBABLE</option><option>ALTA_PROBABILIDAD</option><option>CONFIRMADO</option></select></label>
                        <label class="ui-label">Código del evento que sustituye<input wire:model="form.cod_cae_ant" class="ui-input"></label>
                    </div>
                </details>
                @if($impacto)
                    <div class="ui-alert-info">Horarios afectados: {{ count($impacto['impacto']['horarios'] ?? []) }}. Cursos: {{ implode(', ', $impacto['impacto']['cursos'] ?? []) }}. Docentes: {{ $impacto['impacto']['docentes'] ?? 0 }}. Estudiantes: {{ $impacto['impacto']['estudiantes'] ?? 0 }}. Tareas: {{ count($impacto['impacto']['tareas'] ?? []) }}. Evaluaciones: {{ count($impacto['impacto']['evaluaciones'] ?? []) }}.</div>
                    @foreach($impacto['advertencias'] ?? [] as $advertencia)<div class="ui-alert-warning">{{ $advertencia }}</div>@endforeach
                @endif
            </div>
            <div class="ui-modal-footer flex flex-wrap justify-end gap-2"><button type="button" wire:click="$set('modalEvento', false)" class="ui-btn-secondary">Cancelar</button><button type="button" wire:click="analizarImpacto" class="ui-btn-secondary">Analizar impacto</button>@can('create', \App\Models\CalendarioEvento::class)<button type="button" wire:click="guardar" wire:loading.attr="disabled" class="ui-btn-primary">Guardar evento</button>@endcan</div>
        </div>
    @endif
</div>
