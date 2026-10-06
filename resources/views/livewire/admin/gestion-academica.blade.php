<div
    x-data="{
        modalNueva: @entangle('showCreateModal').live,
        drawerDetalle: @entangle('showDetailDrawer').live,
        modalCierre: @entangle('showCloseModal').live,

        anio: @entangle('form.anio').live,
        nombre: @entangle('form.nombre').live,
        fechaInicio: @entangle('form.fecha_inicio').live,
        fechaFin: @entangle('form.fecha_fin').live,
        modalidad: @entangle('form.modalidad').live,
        estado: @entangle('form.estado').live,
        descripcion: @entangle('form.descripcion').live,
        copiarEstructura: @entangle('form.copiar_estructura').live,
        crearPeriodos: @entangle('form.crear_periodos').live,
        analisisServidor: @entangle('analisisCreacion').live,
        aniosRegistrados: @js($aniosDisponibles),

        estadosValidos: @js(array_keys($estadosGestion ?? [])),

        init() {
            this.$watch('modalNueva', abierta => this.$nextTick(() => {
                if (abierta) this.$refs.modalGestion?.focus();
                else this.$refs.nuevaGestion?.focus();
            }));
        },

        mesActual: @js(now()->month),
        hayGestionActiva: @js(!empty($gestionActiva)),
        gestionActivaAnio: @js($gestionActiva['anio'] ?? null),
        gestionActivaEstado: @js($gestionActiva['estado'] ?? null),
        gestionActivaCierre: @js($gestionActiva['fecha_fin'] ?? null),

        get anioValido() {
            const value = parseInt(this.anio);
            return !isNaN(value) && value >= 2020 && value <= 2100;
        },

        get nombreValido() {
            return (this.nombre || '').trim().length >= 5;
        },

        get fechaInicioValida() {
            return Boolean(this.fechaInicio);
        },

        get fechaFinValida() {
            if (!this.fechaInicio || !this.fechaFin) return false;
            return new Date(this.fechaFin) > new Date(this.fechaInicio);
        },

        get modalidadValida() {
            return (this.modalidad || '').trim().length >= 3;
        },

        get estadoValido() {
            return this.estadosValidos.includes(this.estado);
        },

        get descripcionValida() {
            return (this.descripcion || '').length <= 500;
        },

        get duracionDias() {
            if (!this.fechaInicio || !this.fechaFin) return 0;

            const inicio = new Date(this.fechaInicio);
            const fin = new Date(this.fechaFin);
            const diff = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

            return diff > 0 ? diff : 0;
        },

        get duracionEstado() {
            if (this.duracionDias === 0) return 'SIN DATOS';
            if (this.duracionDias < 180) return 'BLOQUEADO';
            if (this.duracionDias < 270) return 'ADVERTENCIA';
            if (this.duracionDias <= 330) return 'COHERENTE';
            if (this.duracionDias <= 365) return 'EXTENDIDA';

            return 'BLOQUEADO';
        },

        get duracionColor() {
            if (this.duracionEstado === 'BLOQUEADO') return 'ga-texto-danger  ga-borde-danger ga-suave-danger  ';
            if (this.duracionEstado === 'ADVERTENCIA' || this.duracionEstado === 'EXTENDIDA') return 'ga-texto-warning  ga-borde-warning ga-suave-warning  ';
            if (this.duracionEstado === 'COHERENTE') return 'ga-texto-primary  ga-borde-primary ga-suave-primary  ';

            return 'ga-muted  ga-borde ga-fondo-suave  ';
        },

        get estaEnVentanaPlanificacion() {
            return this.mesActual >= 11;
        },

        get intentaCrearGestionPosterior() {
            const anioActual = parseInt(this.gestionActivaAnio || 0);
            const anioFormulario = parseInt(this.anio || 0);

            return this.hayGestionActiva && anioFormulario > anioActual;
        },

        get bloqueadoPorPlanificacionAnticipada() {
            return this.intentaCrearGestionPosterior && !this.estaEnVentanaPlanificacion;
        },

        get bloqueadoPorGestionActivaDuplicada() {
            return this.hayGestionActiva && this.estado === 'ACTIVA';
        },

        get bloqueadoPorDuracion() {
            return this.duracionDias > 0 && (this.duracionDias < 180 || this.duracionDias > 365);
        },

        get progresoDuracion() {
            if (this.duracionDias <= 0) return 0;
            return Math.min(100, Math.max(0, (this.duracionDias / 365) * 100));
        },

        get estadoFormularioTexto() {
            if (this.aniosRegistrados.includes(parseInt(this.anio))) return 'Esta gestión ya está registrada';
            if (this.bloqueadoPorPlanificacionAnticipada) return 'Planificación anticipada bloqueada';
            if (this.bloqueadoPorGestionActivaDuplicada) return 'Ya existe una gestión activa';
            if (this.bloqueadoPorDuracion) return 'Duración institucional inválida';
            if (!this.anioValido) return 'Año inválido';
            if (!this.nombreValido) return 'Nombre incompleto';
            if (!this.fechaInicioValida || !this.fechaFinValida) return 'Fechas incompletas';
            if (!this.fechaFinValida) return 'Rango de fechas inválido';
            if (!this.estadoValido) return 'Estado inválido';
            if (!this.descripcionValida) return 'Descripción demasiado extensa';
            if (!this.analisisServidor?.puede_continuar) return 'Revisa los campos y las condiciones';
            if (!this.puedeGuardarGestion) return 'Completa los datos requeridos';

            return 'Listo para validación';
        },

        get puedeGuardarGestion() {
            return this.anioValido
                && this.nombreValido
                && this.fechaInicioValida
                && this.fechaFinValida
                && this.modalidadValida
                && this.estadoValido
                && this.descripcionValida
                && this.duracionDias >= 180
                && this.duracionDias <= 365
                && !this.aniosRegistrados.includes(parseInt(this.anio))
                && new Date(this.fechaInicio).getUTCFullYear() === parseInt(this.anio)
                && new Date(this.fechaFin).getUTCFullYear() === parseInt(this.anio)
                && Boolean(this.analisisServidor?.puede_continuar)
                && !this.bloqueadoPorPlanificacionAnticipada
                && !this.bloqueadoPorGestionActivaDuplicada;
        }
    }"
    x-on:keydown.escape.window="
        if (modalNueva) modalNueva = false;
        if (drawerDetalle) drawerDetalle = false;
        if (modalCierre) modalCierre = false;
    "
    class="ga-pagina">

    @once
        <script>
            window.addEventListener('gestion-academica-alerta', event => {
                const data = event.detail || {};

                if (window.Swal) {
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || 'Información',
                        text: data.text || '',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--ui-primary').trim(),
                        background: getComputedStyle(document.documentElement).getPropertyValue('--ui-surface').trim(),
                        color: getComputedStyle(document.documentElement).getPropertyValue('--ui-text').trim(),
                        customClass: {
                            popup: 'rounded-3xl'
                        }
                    });
                }
            });
        </script>
    @endonce

    @php
        $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : 'Sin registro';

        $statusLabel = [
            'PLANIFICADA' => 'Planificada',
            'ACTIVA' => 'Activa',
            'EN_CIERRE' => 'En cierre',
            'CERRADA' => 'Cerrada',
            'ANULADA' => 'Anulada',
            'ACTIVO' => 'Activa',
            'PLANIFICADO' => 'Planificada',
            'CERRADO' => 'Cerrada',
            'ARCHIVADO' => 'Cerrada',
            'INACTIVO' => 'Anulada',
        ];

        $vistaLabel = [
            'general' => 'Vista general',
            'anios' => 'Gestiones',
            'periodos' => 'Trimestres',
            'estructura' => 'Estructura anual',
            'inscripciones' => 'Inscripciones',
            'reportes' => 'Respaldos',
            'cierre' => 'Cierre',
            'calendario' => 'Calendario e impacto',
            'alertas' => 'Alertas',
            'seguimiento' => 'Trayectorias y novedades',
            'comparativas' => 'Comparativas',
            'documentacion' => 'Documentación y descargas',
        ];

        $activeStatus = $gestionSeleccionada['estado'] ?? null;
        $activeStatusName = $statusLabel[$activeStatus] ?? ($activeStatus ?: 'Sin gestión activa');
    @endphp

    @include('livewire.admin.academica.estilos')


    @unless ($tablaDisponible)
        <section class="rounded-xl border ga-borde-warning ga-suave-warning p-5 ga-texto-warning shadow-sm ring-1 ga-borde-warning    ">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em]">
                        Configuración pendiente
                    </p>

                    <h3 class="mt-2 text-xl font-bold">
                        Gestión académica aún no disponible
                    </h3>

                    <p class="mt-2 max-w-4xl text-sm leading-7">
                        No existe la tabla principal de gestión académica. El módulo no mostrará datos simulados.
                    </p>
                </div>

                <div class="rounded-2xl border ga-borde-warning ga-superficie px-4 py-3 text-sm font-bold ga-texto-warning shadow-sm   ">
                    Sin registros disponibles
                </div>
            </div>
        </section>
    @endunless

    <header class="ui-card ga-cabecera">
        <div class="ga-identidad"><div class="ga-emblema"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i></div><div><p class="ui-kicker">Planificación y continuidad educativa</p><h1 class="ui-title">Gestión académica</h1><p class="ui-muted">Organiza el calendario, acompaña las trayectorias y prepara el expediente de cada gestión.</p></div></div>
        <div class="ga-cabecera-acciones"><button type="button" wire:click="cambiarVista('documentacion')" class="ui-btn ui-btn-secondary"><i class="ph-duotone ph-download-simple" aria-hidden="true"></i>Descargas</button><button type="button" x-ref="nuevaGestion" wire:click="abrirNuevaGestion" class="ui-btn ui-btn-primary"><i class="ph-duotone ph-plus" aria-hidden="true"></i>Nueva gestión</button></div>
    </header>

    <section class="ui-card ga-resumen" aria-label="Resumen de la gestión de consulta">
        @foreach($resumen as $i=>$card)<div><i class="ph-duotone {{ ['ph-student','ph-books','ph-calendar-check','ph-calendar-dots'][$i] }}" aria-hidden="true"></i><strong>{{ $card['valor'] }}</strong><span>{{ $card['titulo'] }}</span></div>@endforeach
    </section>

    <section class="ui-card ga-contexto">
        <div><p class="ui-kicker">Expediente que estás consultando</p><div class="ga-contexto-identidad"><h2 class="ui-title font-bold mt-1">{{ $gestionSeleccionada['nombre'] ?? 'Selecciona una gestión' }}</h2>@if($gestionSeleccionada)<span class="ui-badge-info">{{ $statusLabel[$gestionSeleccionada['estado']] ?? $gestionSeleccionada['estado'] }}</span>@endif</div><p class="ui-muted text-xs mt-1">Calendario, trimestres, seguimiento y descargas de este año.</p></div>
        <x-selector-institucional modelo="selectedGestionId" identificador="academica-contexto" etiqueta="Gestión de consulta" :opciones="array_merge([['valor'=>'','etiqueta'=>'Gestión activa']],$opcionesGestion)" />
    </section>
    @php
        $gruposAcademicos=[
            'Gestión'=>[['general','Vista general','ph-squares-four'],['anios','Gestiones','ph-clock-counter-clockwise'],['estructura','Estructura académica','ph-tree-structure']],
            'Calendario y prevención'=>[['periodos','Trimestres','ph-calendar-check'],['calendario','Calendario e impacto','ph-calendar-dots'],['alertas','Alertas','ph-warning-circle']],
            'Seguimiento'=>[['inscripciones','Inscripciones','ph-student'],['seguimiento','Trayectorias y novedades','ph-path'],['comparativas','Comparativas','ph-chart-donut']],
            'Expediente'=>[['documentacion','Documentación y descargas','ph-files'],['reportes','Respaldos','ph-archive'],['cierre','Revisión de cierre','ph-seal-check']],
        ];
        $grupoActual=collect($gruposAcademicos)->search(fn($opciones)=>in_array($vista,array_column($opciones,0),true))?:'Gestión';
    @endphp
    <nav class="ui-card pa-navegacion" x-data="{grupo:@js($grupoActual)}" wire:key="navegacion-academica-{{ $vista }}" aria-label="Apartados de gestión académica">
        <div class="pa-grupos" role="group" aria-label="Áreas del expediente">@foreach($gruposAcademicos as $grupo=>$opciones)<button type="button" x-on:click="grupo=@js($grupo)" :aria-pressed="grupo===@js($grupo)">{{ $grupo }}</button>@endforeach</div>
        @foreach($gruposAcademicos as $grupo=>$opciones)<div class="pa-subnavegacion" x-show="grupo===@js($grupo)" @if($grupo!==$grupoActual) x-cloak @endif>@foreach($opciones as [$clave,$etiqueta,$icono])<button type="button" class="ui-btn ui-btn-secondary" wire:click="cambiarVista('{{ $clave }}')" @if($vista===$clave) aria-current="page" @endif><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i>{{ $etiqueta }}@if($clave==='alertas' && count($panelAcademico['alertas'])+count($avisosSesion)) <small>({{ count($panelAcademico['alertas'])+count($avisosSesion) }})</small>@endif</button>@endforeach</div>@endforeach
    </nav>
    @if($vista==='general' && count($panelAcademico['alertas']))
    <section class="ui-card ga-contexto"><div><p class="ui-kicker">Revisión del expediente</p><h2 class="ui-title font-bold mt-1">{{ count($panelAcademico['alertas']) }} puntos para revisar en el expediente</h2><p class="ui-muted text-sm mt-1">{{ $panelAcademico['alertas'][0]['titulo'] }}. Revisa las causas y los próximos pasos.</p></div><div class="flex gap-2 flex-wrap"><button type="button" class="ui-btn ui-btn-primary" wire:click="cambiarVista('alertas')">Ver alertas</button><button type="button" class="ui-btn ui-btn-secondary" wire:click="cambiarVista('calendario')">Calendario e impacto</button></div></section>
    @endif

    {{-- FILTROS --}}
    @if($vista==='anios')
    <section class="ga-card rounded-xl p-5">
        <div class="grid gap-4 lg:grid-cols-[1fr_180px_210px_auto]">
            <div>
                <label class="mb-2 block text-sm font-bold ga-text ">
                    Buscar gestión
                </label>

                <input type="text"
                    wire:model.live.debounce.400ms="busqueda"
                    class="ga-input"
                    placeholder="Buscar por año o estado…" />
            </div>

            <div>
                <x-selector-institucional modelo="filtroAnio" identificador="academica-filtro-anio" etiqueta="Año de gestión" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los años']],collect($aniosDisponibles)->map(fn($anio)=>['valor'=>(string)$anio,'etiqueta'=>(string)$anio])->all())" />
            </div>

            <div>
                <x-selector-institucional modelo="filtroEstado" identificador="academica-filtro-estado" etiqueta="Estado de gestión" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los estados']],collect($estadosGestion)->map(fn($etiqueta,$valor)=>['valor'=>$valor,'etiqueta'=>$etiqueta])->values()->all())" />
            </div>

            <div class="flex items-end">
                <button type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex w-full items-center justify-center rounded-2xl border ga-borde ga-superficie px-5 py-3 text-sm font-bold ga-text shadow-sm transition  ga-interactivo     ">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>

    @endif

    {{-- CONTENIDO --}}
    <section class="space-y-6">
        @if(in_array($vista,['calendario','seguimiento','alertas','comparativas','documentacion'],true))
            @include('livewire.admin.academica.panel')
        @endif

        @if($vista === 'general')
            @include('livewire.admin.academica.resumen')
        @endif

        @if ($vista === 'anios')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-primary ">
                            Gestiones registradas
                        </p>

                        <h2 class="mt-2 text-2xl font-bold ga-text ">
                            Expedientes anuales
                        </h2>

                        <p class="mt-2 text-sm leading-6 ga-muted ">
                            Consulta, activa, revisa o prepara el respaldo de cada gestión académica registrada.
                        </p>
                    </div>

                    <span class="rounded-full border ga-borde ga-fondo-suave px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] ga-muted   ">
                        {{ $gestiones->total() }} registros
                    </span>
                </div>

                <div class="grid gap-4">
                    @forelse ($gestiones as $gestion)
                        <article class="ga-soft rounded-xl p-5 transition ga-interactivo ga-interactivo    ">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border ga-borde ga-superficie ga-texto-primary shadow-sm   ">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.597 0 1.17.237 1.591.659l1.06 1.06c.422.421.995.659 1.591.659H18A2.25 2.25 0 0 1 20.25 9.128V17.25A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-bold ga-text ">
                                                {{ $gestion['nombre'] }}
                                            </h3>

                                            <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $this->badgeEstadoClass($gestion['estado']) }}">
                                                {{ $statusLabel[$gestion['estado']] ?? $gestion['estado'] }}
                                            </span>

                                            <span class="rounded-full border ga-borde ga-superficie px-3 py-1 text-xs font-bold ga-muted   ">
                                                Ciclo {{ $gestion['anio'] }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 ga-muted ">
                                            {{ $gestion['descripcion'] }}
                                        </p>

                                        <div class="mt-3 grid gap-2 text-xs font-bold ga-muted  sm:grid-cols-2 xl:grid-cols-4">
                                            <span class="rounded-full border ga-borde ga-superficie px-3 py-1  ">
                                                Inscripciones: {{ $gestion['estudiantes'] }}
                                            </span>

                                            <span class="rounded-full border ga-borde ga-superficie px-3 py-1  ">
                                                Planes: {{ $gestion['planes_asignatura'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border ga-borde ga-superficie px-3 py-1  ">
                                                Horarios: {{ $gestion['horarios'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border ga-borde ga-superficie px-3 py-1  ">
                                                Actualizado: {{ $gestion['ultima_actualizacion'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                    <button type="button"
                                        wire:click="abrirDetalle('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border ga-borde ga-superficie px-4 py-2 text-sm font-bold ga-text shadow-sm transition  ga-interactivo     ">
                                        Expediente
                                    </button>

                                    <button type="button"
                                        wire:click="exportarGestion('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border ga-borde-primary ga-suave-primary px-4 py-2 text-sm font-bold ga-texto-primary shadow-sm transition  ga-interactivo    ">
                                        Respaldo
                                    </button>

                                    @if ($gestion['estado'] === 'PLANIFICADA')
                                        <button type="button"
                                            wire:click="activarGestion('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border ga-borde-primary ga-suave-primary px-4 py-2 text-sm font-bold ga-texto-primary shadow-sm transition  ga-interactivo    ">
                                            Activar
                                        </button>
                                    @endif

                                    @if (in_array($gestion['estado'], ['ACTIVA', 'EN_CIERRE'], true))
                                        <button type="button"
                                            wire:click="prepararCierre('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border ga-borde-warning ga-suave-warning px-4 py-2 text-sm font-bold ga-texto-warning shadow-sm transition  ga-interactivo    ">
                                            Cierre
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed ga-borde ga-fondo-suave p-8 text-center  ">
                            <p class="text-sm font-bold ga-text ">
                                No existen gestiones académicas registradas.
                            </p>

                            <button type="button"
                                wire:click="abrirNuevaGestion"
                                class="mt-5 rounded-2xl ga-relleno-primary px-5 py-3 text-sm font-bold text-white shadow-lg  transition  ">
                                Crear primera gestión
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $gestiones->links() }}
                </div>
            </section>
        @endif

        @if ($vista === 'periodos')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-violet ">
                        Periodos de evaluación
                    </p>

                    <h2 class="mt-2 text-2xl font-bold ga-text ">
                        Trimestres académicos y descanso pedagógico
                    </h2>

                    <p class="mt-2 text-sm leading-6 ga-muted ">
                        Configuración orientada a una gestión regular: tres trimestres, 200 días hábiles curriculares y descanso pedagógico de invierno.
                    </p>
                </div>

                <div class="ui-card p-4 mb-5"><div class="flex gap-3 justify-between flex-wrap items-center"><div><p class="ui-title font-semibold">Completar trimestres durante la planificación</p><p class="ui-muted text-sm mt-1">{{ $reglaPeriodos['mensaje'] }}</p></div>@can('Periodo_Evaluacion')@if($reglaPeriodos['puede_agregar'])<a class="ui-btn ui-btn-primary" href="{{ route('admin.periodo-evaluacion') }}">Agregar trimestre faltante</a>@else<button type="button" class="ui-btn ui-btn-secondary" disabled aria-describedby="academica-regla-periodos">Agregar trimestre faltante</button>@endif@endcan</div><p id="academica-regla-periodos" class="ui-muted text-xs mt-2">La RM 0001/2026 establece tres trimestres y 200 días efectivos. La incorporación inicial respeta esa estructura y las fechas de la gestión; una suspensión requiere revisión y recuperación, sin añadir un cuarto trimestre automáticamente.</p></div>
                @if($gestionSeleccionada)
                <x-distribucion-trimestres :periodos="$periodos" :anio="$gestionSeleccionada['anio']" />
                <x-plegable-institucional class="pa-tabla-detalle mt-5" icono="ph-calendar-dots"><x-slot:titulo>Consultar calendario por día</x-slot:titulo><x-calendario-academico :anio="$gestionSeleccionada['anio']" :periodos="$periodos" :eventos="$panelAcademico['eventos']" /></x-plegable-institucional>
                @else<p class="ui-muted text-sm">Selecciona una gestión para consultar sus trimestres.</p>@endif
            </section>
        @endif

        @if ($vista === 'estructura')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-primary ">
                        Estructura académica anual
                    </p>

                    <h2 class="mt-2 text-2xl font-bold ga-text ">
                        Organización institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 ga-muted ">
                        Indicadores reales de cursos, paralelos, turnos, asignaturas, especialidades, planes y horarios.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($estructura as $item)
                        <article class="ga-soft rounded-2xl p-4 transition  ">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] ga-muted ">
                                {{ $item['titulo'] }}
                            </p>

                            <p class="mt-2 text-3xl font-bold ga-text ">
                                {{ $item['valor'] }}
                            </p>

                            <p class="mt-1 text-xs font-semibold ga-muted ">
                                {{ $item['detalle'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($vista === 'inscripciones')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-info ">
                        Inscripciones
                    </p>

                    <h2 class="mt-2 text-2xl font-bold ga-text ">
                        Estado de estudiantes inscritos
                    </h2>

                    <p class="mt-2 text-sm leading-6 ga-muted ">
                        Vista de control general para la gestión activa. La administración detallada debe conectarse con el módulo de inscripciones.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border ga-borde-info ga-suave-info p-5 shadow-sm transition    ">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] ga-texto-info ">Gestión activa</p>
                        <p class="mt-2 text-3xl font-bold ga-texto-info ">{{ $gestionSeleccionada['anio'] ?? 'N/D' }}</p>
                    </article>

                    <article class="rounded-2xl border ga-borde-primary ga-suave-primary p-5 shadow-sm transition    ">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] ga-texto-primary ">Estudiantes inscritos</p>
                        <p class="mt-2 text-3xl font-bold ga-texto-primary ">{{ $gestionSeleccionada['estudiantes'] ?? 0 }}</p>
                    </article>

                    <article class="rounded-2xl border ga-borde-violet ga-suave-violet p-5 shadow-sm transition    ">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] ga-texto-violet ">Cursos activos</p>
                        <p class="mt-2 text-3xl font-bold ga-texto-violet ">{{ $gestionSeleccionada['cursos'] ?? 0 }}</p>
                    </article>

                    <article class="rounded-2xl border ga-borde-warning ga-suave-warning p-5 shadow-sm transition    ">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] ga-texto-warning ">Pendientes cierre</p>
                        <p class="mt-2 text-3xl font-bold ga-texto-warning ">{{ $gestionSeleccionada ? array_sum(array_column($pendientesCierre, 'valor')) : 0 }}</p>
                    </article>
                </div>

                <div class="mt-5 rounded-2xl border ga-borde ga-fondo-suave p-5 text-sm leading-7 ga-text   ">
                    La gestión académica no reemplaza el módulo de inscripción. Aquí se muestra el resumen institucional para validar cierre, reportes y respaldo de la gestión.
                </div>
            </section>
        @endif

        @if ($vista === 'reportes')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-primary ">
                        Respaldos y exportación
                    </p>

                    <h2 class="mt-2 text-2xl font-bold ga-text ">
                        Preparación de expediente institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 ga-muted ">
                        Esta vista valida qué información existe para preparar exportaciones futuras en PDF, Excel o ZIP.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['COMPLETA', 'Respaldo completo', 'Todo el expediente institucional'],
                        ['INSCRIPCIONES', 'Inscripciones', 'Estudiantes y cursos'],
                        ['PLANIFICACION', 'Planificación', 'Planes y horarios'],
                        ['CALIFICACIONES', 'Calificaciones', 'Resultados académicos'],
                    ] as [$tipo, $titulo, $detalle])
                        <button type="button"
                            wire:click="exportarGestion('{{ $gestionSeleccionada['id'] ?? '' }}', '{{ $tipo }}')"
                            class="rounded-2xl border ga-borde ga-fondo-suave p-5 text-left shadow-sm transition  ga-interactivo ga-interactivo     ">
                            <p class="text-sm font-bold ga-text ">{{ $titulo }}</p>
                            <p class="mt-2 text-xs leading-5 ga-muted ">{{ $detalle }}</p>
                            <span class="mt-4 inline-flex rounded-full border ga-borde-primary ga-suave-primary px-3 py-1 text-xs font-bold ga-texto-primary   ">
                                Preparar
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- RESPALDOS ACADÉMICOS --}}
                <div class="mt-8 border-t ga-borde pt-6 ">
                    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-violet ">
                                Respaldos académicos
                            </p>

                            <h3 class="mt-2 text-xl font-bold ga-text ">
                                Registro de respaldos institucionales
                            </h3>

                            <p class="mt-2 text-sm leading-6 ga-muted ">
                                Genera, valida y archiva respaldos académicos. Un respaldo validado es requisito para el cierre definitivo.
                            </p>
                        </div>

                        @if ($gestionSeleccionada)
                            <button type="button"
                                wire:click="generarRespaldo('{{ $gestionSeleccionada['id'] }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl ga-relleno-primary px-5 py-3 text-sm font-bold text-white shadow-lg  transition  ">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span wire:loading.remove wire:target="generarRespaldo">Generar respaldo</span>
                                <span wire:loading wire:target="generarRespaldo">Generando...</span>
                            </button>
                        @endif
                    </div>

                    @if (count($this->respaldosGestion) > 0)
                        <div class="overflow-hidden rounded-xl border ga-borde shadow-sm ">
                            <table class="min-w-full border-separate border-spacing-0">
                                <thead>
                                    <tr class="ga-fondo-suave ">
                                        <th class="border-b ga-borde px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] ga-muted  ">
                                            Tipo
                                        </th>
                                        <th class="border-b ga-borde px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] ga-muted  ">
                                            Formato
                                        </th>
                                        <th class="border-b ga-borde px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] ga-muted  ">
                                            Estado
                                        </th>
                                        <th class="border-b ga-borde px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.14em] ga-muted  ">
                                            Fecha
                                        </th>
                                        <th class="border-b ga-borde px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.14em] ga-muted  ">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->respaldosGestion as $respaldo)
                                        <tr class="transition ga-interactivo ">
                                            <td class="border-b ga-borde px-4 py-3 ">
                                                <span class="rounded-full border px-3 py-1 text-xs font-bold
                                                    {{ $respaldo['tipo'] === 'CIERRE'
                                                        ? 'ga-borde-warning ga-suave-warning ga-texto-warning   '
                                                        : 'ga-borde-violet ga-suave-violet ga-texto-violet   ' }}">
                                                    {{ $respaldo['tipo_label'] }}
                                                </span>
                                            </td>

                                            <td class="border-b ga-borde px-4 py-3 text-sm font-bold ga-text  ">
                                                {{ $respaldo['formato'] }}
                                            </td>

                                            <td class="border-b ga-borde px-4 py-3 ">
                                                @php
                                                    $badgeClass = match ($respaldo['estado']) {
                                                        'GENERADO' => 'ga-borde-info ga-suave-info ga-texto-info   ',
                                                        'VALIDADO' => 'ga-borde-primary ga-suave-primary ga-texto-primary   ',
                                                        'OBSERVADO' => 'ga-borde-warning ga-suave-warning ga-texto-warning   ',
                                                        'ARCHIVADO' => 'ga-borde-violet ga-suave-violet ga-texto-violet   ',
                                                        'ANULADO' => 'ga-borde-danger ga-suave-danger ga-texto-danger   ',
                                                        default => 'ga-borde ga-fondo-suave ga-text   ',
                                                    };
                                                @endphp

                                                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                                    {{ $respaldo['estado_label'] }}
                                                </span>
                                            </td>

                                            <td class="border-b ga-borde px-4 py-3 text-sm font-bold ga-muted  ">
                                                {{ $respaldo['fecha'] }}
                                            </td>

                                            <td class="border-b ga-borde px-4 py-3 ">
                                                <div class="flex justify-end gap-2">
                                                    @if ($respaldo['estado'] === 'GENERADO')
                                                        <button type="button"
                                                            wire:click="validarRespaldo('{{ $respaldo['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            class="rounded-xl border ga-borde-primary ga-suave-primary px-3 py-1.5 text-xs font-bold ga-texto-primary transition     ">
                                                            Validar
                                                        </button>

                                                        <button type="button"
                                                            wire:click="observarRespaldo('{{ $respaldo['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            class="rounded-xl border ga-borde-warning ga-suave-warning px-3 py-1.5 text-xs font-bold ga-texto-warning transition     ">
                                                            Observar
                                                        </button>
                                                    @endif

                                                    @if (in_array($respaldo['estado'], ['GENERADO', 'VALIDADO']))
                                                        <button type="button"
                                                            wire:click="archivarRespaldo('{{ $respaldo['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            class="rounded-xl border ga-borde-violet ga-suave-violet px-3 py-1.5 text-xs font-bold ga-texto-violet transition     ">
                                                            Archivar
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed ga-borde ga-fondo-suave p-8 text-center  ">
                            <svg class="mx-auto h-12 w-12 ga-muted " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>

                            <p class="mt-4 text-sm font-bold ga-text ">
                                No existen respaldos académicos
                            </p>

                            <p class="mt-2 text-sm leading-6 ga-muted ">
                                Genera un respaldo académico para documentar el estado de la gestión vigente.
                            </p>

                            @if ($gestionSeleccionada)
                                <button type="button"
                                    wire:click="generarRespaldo('{{ $gestionSeleccionada['id'] }}')"
                                    class="mt-5 rounded-2xl ga-relleno-primary px-5 py-3 text-sm font-bold text-white shadow-lg  transition  ">
                                    Generar primer respaldo
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($vista === 'cierre')
            <section class="ga-card rounded-xl p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-warning ">
                        Cierre académico
                    </p>

                    <h2 class="mt-2 text-2xl font-bold ga-text ">
                        Auditoría previa al cierre institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 ga-muted ">
                        El cierre definitivo se bloquea si existen procesos académicos pendientes, inconsistencias críticas o si no existe un respaldo académico validado.
                    </p>
                </div>

                {{-- ESTADO DEL RESPALDO PARA CIERRE --}}
                @php
                    $resumenRespaldos = $gestionSeleccionada
                        ? app(\App\Support\Academico\GestionAcademicaInteligente::class)->resumirRespaldos($gestionSeleccionada['id'])
                        : ['total' => 0, 'validados' => 0, 'tiene_cierre_validado' => false];
                    $tieneRespaldoValidado = ($resumenRespaldos['validados'] ?? 0) > 0 || ($resumenRespaldos['tiene_cierre_validado'] ?? false);
                @endphp

                <div class="mb-5 rounded-2xl border p-4 shadow-sm {{ $tieneRespaldoValidado
                    ? 'ga-borde-primary ga-suave-primary  '
                    : 'ga-borde-danger ga-suave-danger  ' }}">
                    <div class="flex items-center gap-3">
                        @if ($tieneRespaldoValidado)
                            <svg class="h-6 w-6 ga-texto-primary " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                            <div>
                                <p class="text-sm font-bold ga-texto-primary ">
                                    Respaldo académico validado
                                </p>
                                <p class="mt-1 text-xs font-semibold ga-texto-primary ">
                                    {{ $resumenRespaldos['validados'] ?? 0 }} respaldo(s) validado(s) · {{ $resumenRespaldos['total'] ?? 0 }} total(es)
                                </p>
                            </div>
                        @else
                            <svg class="h-6 w-6 ga-texto-danger " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>

                            <div>
                                <p class="text-sm font-bold ga-texto-danger ">
                                    Sin respaldo académico validado
                                </p>
                                <p class="mt-1 text-xs font-semibold ga-texto-danger ">
                                    El cierre definitivo requiere un respaldo validado. Ve a Respaldos para generarlo.
                                </p>
                            </div>

                            <button type="button"
                                wire:click="cambiarVista('reportes')"
                                class="ml-auto rounded-xl border ga-borde-danger ga-superficie px-3 py-1.5 text-xs font-bold ga-texto-danger transition     ">
                                Ir a Respaldos
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($pendientesCierre as $pendiente)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border ga-borde ga-fondo-suave px-4 py-3 shadow-sm transition    ">
                            <p class="text-sm font-bold ga-text ">
                                {{ $pendiente['titulo'] }}
                            </p>

                            <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $this->colorClass($pendiente['color']) }}">
                                {{ $pendiente['valor'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button"
                        wire:click="prepararCierre('{{ $gestionSeleccionada['id'] ?? '' }}')"
                        class="rounded-2xl border ga-borde-warning ga-suave-warning px-5 py-3 text-sm font-bold ga-texto-warning shadow-sm transition  ga-interactivo    ">
                        Revisar cierre
                    </button>

                    <button type="button"
                        wire:click="generarRespaldo('{{ $gestionSeleccionada['id'] ?? '' }}')"
                        class="rounded-2xl border ga-borde-violet ga-suave-violet px-5 py-3 text-sm font-bold ga-texto-violet shadow-sm transition  ga-interactivo    ">
                        Generar respaldo
                    </button>
                </div>
            </section>
        @endif
    </section>

    @include('livewire.admin.academica.nueva-gestion')

    {{-- MODAL CIERRE --}}
    <div x-show="modalCierre" x-cloak class="fixed inset-0 z-50 flex items-center justify-center ga-overlay p-4">
        <section x-show="modalCierre" x-transition class="w-full max-w-4xl rounded-xl border ga-borde ga-superficie p-6 shadow-2xl  ">
            <div class="flex items-start justify-between gap-4 border-b ga-borde pb-5 ">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] ga-texto-warning ">Revisión de cierre</p>
                    <h2 class="mt-2 text-2xl font-bold ga-text ">Cierre de gestión académica</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 ga-muted ">El cierre definitivo convierte la gestión en expediente histórico institucional.</p>
                </div>

                <button type="button" wire:click="cerrarModalCierre" class="rounded-2xl border ga-borde ga-superficie px-4 py-2 text-sm font-bold ga-text shadow-sm transition  ga-interactivo     ">
                    Cerrar
                </button>
            </div>

            <div class="mt-6 space-y-5">
                <div class="ga-soft rounded-2xl p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] ga-muted ">Gestión revisada</p>
                    <p class="mt-2 text-lg font-bold ga-text ">{{ $revisionCierre['gestion'] ?? 'Sin gestión seleccionada' }}</p>
                    <p class="mt-2 text-sm leading-6 ga-muted ">{{ $revisionCierre['mensaje'] ?? 'Sin análisis disponible.' }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        'inscripciones_pendientes' => 'Inscripciones pendientes',
                        'planes_asignatura_incompletos' => 'Plan asignatura incompleto',
                        'planes_especialidad_incompletos' => 'Plan especialidad incompleto',
                    ] as $key => $label)
                        <div class="ga-soft rounded-2xl p-4">
                            <p class="text-sm font-bold ga-muted ">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-bold {{ ($revisionCierre[$key] ?? 0) > 0 ? 'ga-texto-warning ' : 'ga-texto-primary ' }}">
                                {{ $revisionCierre[$key] ?? 0 }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if (!empty($revisionCierre['pendientes_cierre'] ?? []))
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($revisionCierre['pendientes_cierre'] as $titulo => $valor)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border ga-borde ga-fondo-suave px-4 py-3 shadow-sm  ">
                                <span class="text-sm font-bold ga-text ">{{ $titulo }}</span>
                                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ ((int) $valor) > 0 ? 'ga-borde-warning ga-suave-warning ga-texto-warning   ' : 'ga-borde-primary ga-suave-primary ga-texto-primary   ' }}">
                                    {{ $valor }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach (($revisionCierre['bloqueos'] ?? []) as $bloqueo)
                    <div class="rounded-2xl border ga-borde-danger ga-suave-danger px-4 py-3 text-sm font-bold leading-6 ga-texto-danger   ">
                        {{ $bloqueo }}
                    </div>
                @endforeach

                @foreach (($revisionCierre['advertencias'] ?? []) as $advertencia)
                    <div class="rounded-2xl border ga-borde-warning ga-suave-warning px-4 py-3 text-sm font-bold leading-6 ga-texto-warning   ">
                        {{ $advertencia }}
                    </div>
                @endforeach

                <div class="flex flex-col gap-3 border-t ga-borde pt-5  sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold ga-text ">Acción institucional</p>
                        <p class="mt-1 text-xs leading-5 ga-muted ">Primero puede pasar a EN_CIERRE; después, si no hay pendientes, se cierra definitivamente.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="cerrarModalCierre" class="rounded-2xl border ga-borde ga-superficie px-5 py-3 text-sm font-bold ga-text shadow-sm transition  ga-interactivo     ">
                            Cancelar
                        </button>

                        <button type="button" wire:click="iniciarCierreGestion" class="rounded-2xl border ga-borde-warning ga-suave-warning px-5 py-3 text-sm font-bold ga-texto-warning shadow-sm transition  ga-interactivo    ">
                            Pasar a EN_CIERRE
                        </button>

                        <button type="button"
                            wire:click="confirmarCierreGestion"
                            @disabled(($revisionCierre['puede_cerrar'] ?? false) !== true)
                            class="rounded-2xl px-5 py-3 text-sm font-bold transition
                            {{ ($revisionCierre['puede_cerrar'] ?? false) === true
                                ? 'ga-relleno-primary text-white shadow-lg   '
                                : 'cursor-not-allowed border ga-borde ga-fondo-suave ga-muted   ' }}">
                            Cerrar definitivamente
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- DRAWER DETALLE --}}
    <div x-show="drawerDetalle" x-cloak class="fixed inset-0 z-50 ga-overlay">
        <div class="absolute inset-y-0 right-0 flex w-full justify-end">
            <section x-show="drawerDetalle" x-transition class="h-full w-full max-w-4xl overflow-y-auto border-l-2 ga-borde ga-superficie p-6 shadow-2xl  ">
                @if ($gestionSeleccionada)
                    <div class="flex items-start justify-between gap-4 border-b ga-borde pb-5 ">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $this->badgeEstadoClass($gestionSeleccionada['estado']) }}">
                                    {{ $statusLabel[$gestionSeleccionada['estado']] ?? $gestionSeleccionada['estado'] }}
                                </span>

                                <span class="rounded-full border ga-borde ga-fondo-suave px-3 py-1 text-xs font-bold ga-muted   ">
                                    Ciclo {{ $gestionSeleccionada['anio'] }}
                                </span>
                            </div>

                            <h2 class="mt-4 text-2xl font-bold ga-text ">
                                Expediente de {{ $gestionSeleccionada['nombre'] }}
                            </h2>

                            <p class="mt-2 text-sm leading-6 ga-muted ">
                                Resumen institucional del ciclo académico seleccionado.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarDetalle" class="rounded-2xl border ga-borde ga-superficie px-4 py-2 text-sm font-bold ga-text shadow-sm transition  ga-interactivo     ">
                            Cerrar
                        </button>
                    </div>

                    <div class="mt-6 space-y-6">
                        <section class="ga-soft rounded-xl p-5">
                            <h3 class="text-sm font-bold uppercase tracking-[0.16em] ga-texto-primary ">
                                Datos principales
                            </h3>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach ([
                                    'Año' => $gestionSeleccionada['anio'],
                                    'Estado' => $statusLabel[$gestionSeleccionada['estado']] ?? $gestionSeleccionada['estado'],
                                    'Inicio' => $formatDate($gestionSeleccionada['fecha_inicio']),
                                    'Cierre' => $formatDate($gestionSeleccionada['fecha_fin']),
                                ] as $label => $value)
                                    <div class="rounded-2xl border ga-borde ga-superficie p-4  ">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] ga-muted ">{{ $label }}</p>
                                        <p class="mt-1 text-sm font-bold ga-text ">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                'Inscripciones' => $gestionSeleccionada['estudiantes'],
                                'Planes de asignatura' => $gestionSeleccionada['planes_asignatura'] ?? 0,
                                'Horarios' => $gestionSeleccionada['horarios'] ?? 0,
                                'Calificaciones' => $gestionSeleccionada['calificaciones'] ?? 0,
                                'Clases virtuales' => $gestionSeleccionada['clases_virtuales'] ?? 0,
                                'Asistencias' => $gestionSeleccionada['asistencias'] ?? 0,
                            ] as $label => $value)
                                <div class="ga-soft rounded-2xl p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] ga-muted ">{{ $label }}</p>
                                    <p class="mt-2 text-3xl font-bold ga-text ">{{ $value }}</p>
                                </div>
                            @endforeach
                        </section>

                        <section class="grid gap-3 sm:grid-cols-3">
                            <button type="button" wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}', 'COMPLETA')" class="rounded-2xl border ga-borde-primary ga-suave-primary px-4 py-3 text-sm font-bold ga-texto-primary shadow-sm transition  ga-interactivo    ">
                                Respaldo completo
                            </button>

                            <button type="button" wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}', 'INSCRIPCIONES')" class="rounded-2xl border ga-borde-violet ga-suave-violet px-4 py-3 text-sm font-bold ga-texto-violet shadow-sm transition  ga-interactivo    ">
                                Inscripciones
                            </button>

                            @if (in_array($gestionSeleccionada['estado'], ['ACTIVA', 'EN_CIERRE'], true))
                                <button type="button" wire:click="prepararCierre('{{ $gestionSeleccionada['id'] }}')" class="rounded-2xl border ga-borde-warning ga-suave-warning px-4 py-3 text-sm font-bold ga-texto-warning shadow-sm transition  ga-interactivo    ">
                                    Revisar cierre
                                </button>
                            @endif
                        </section>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed ga-borde ga-fondo-suave p-8 text-center  ">
                        <p class="text-sm font-bold ga-text ">
                            No existe gestión seleccionada.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
