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

        estadosValidos: @js(array_keys($estadosGestion ?? [])),

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
            if (this.duracionEstado === 'BLOQUEADO') return 'text-rose-600 dark:text-rose-300 border-rose-200 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-950';
            if (this.duracionEstado === 'ADVERTENCIA' || this.duracionEstado === 'EXTENDIDA') return 'text-amber-600 dark:text-amber-300 border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-950';
            if (this.duracionEstado === 'COHERENTE') return 'text-emerald-700 dark:text-emerald-300 border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-950';

            return 'text-slate-500 dark:text-slate-400 border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900';
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
            if (this.bloqueadoPorPlanificacionAnticipada) return 'Planificación anticipada bloqueada';
            if (this.bloqueadoPorGestionActivaDuplicada) return 'Ya existe una gestión activa';
            if (this.bloqueadoPorDuracion) return 'Duración institucional inválida';
            if (!this.anioValido) return 'Año inválido';
            if (!this.nombreValido) return 'Nombre incompleto';
            if (!this.fechaInicioValida || !this.fechaFinValida) return 'Fechas incompletas';
            if (!this.fechaFinValida) return 'Rango de fechas inválido';
            if (!this.estadoValido) return 'Estado inválido';
            if (!this.descripcionValida) return 'Descripción demasiado extensa';
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
                && !this.bloqueadoPorPlanificacionAnticipada
                && !this.bloqueadoPorGestionActivaDuplicada;
        }
    }"
    x-on:keydown.escape.window="
        if (modalNueva) modalNueva = false;
        if (drawerDetalle) drawerDetalle = false;
        if (modalCierre) modalCierre = false;
    "
    class="space-y-6">

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
                        confirmButtonColor: '#059669',
                        background: document.documentElement.classList.contains('dark') ? '#020617' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#0f172a',
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
        ];

        $activeStatus = $gestionActiva['estado'] ?? null;
        $activeStatusName = $statusLabel[$activeStatus] ?? ($activeStatus ?: 'Sin gestión activa');
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        .ga-card {
            border: 2px solid rgb(226 232 240);
            background: rgb(255 255 255);
            box-shadow: 0 14px 32px rgba(15, 23, 42, .055);
        }

        .dark .ga-card {
            border-color: rgb(30 41 59);
            background: rgb(2 6 23);
            box-shadow: 0 22px 45px rgba(0, 0, 0, .30);
        }

        .ga-soft {
            border: 2px solid rgb(226 232 240);
            background: rgb(248 250 252);
            box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        }

        .dark .ga-soft {
            border-color: rgb(30 41 59);
            background: rgb(15 23 42);
            box-shadow: 0 16px 30px rgba(0, 0, 0, .20);
        }

        .ga-input {
            width: 100%;
            border: 2px solid rgb(203 213 225);
            background: rgb(255 255 255);
            color: rgb(15 23 42);
            border-radius: 1rem;
            padding: .78rem 1rem;
            font-size: .875rem;
            font-weight: 700;
            outline: none;
            transition: all .22s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }

        .ga-input:focus {
            border-color: rgb(16 185 129);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
            transform: translateY(-1px);
        }

        .ga-input::placeholder {
            color: rgb(148 163 184);
        }

        .dark .ga-input {
            border-color: rgb(51 65 85);
            background: rgb(15 23 42);
            color: rgb(248 250 252);
        }

        .dark .ga-input:focus {
            border-color: rgb(52 211 153);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, .12);
        }

        .ga-overlay {
            background: rgba(15, 23, 42, .38);
            backdrop-filter: blur(16px);
        }

        .dark .ga-overlay {
            background: rgba(2, 6, 23, .70);
            backdrop-filter: blur(16px);
        }

        .ga-grid-pattern {
            background-image:
                linear-gradient(rgba(15, 23, 42, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, .04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .dark .ga-grid-pattern {
            background-image:
                linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
        }
    </style>

    @unless ($tablaDisponible)
        <section class="rounded-[2rem] border-2 border-amber-300 bg-amber-50 p-5 text-amber-950 shadow-sm ring-1 ring-amber-200 dark:border-amber-500/40 dark:bg-amber-950 dark:text-amber-100 dark:ring-amber-500/20">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.16em]">
                        Configuración pendiente
                    </p>

                    <h3 class="mt-2 text-xl font-black">
                        Gestión académica aún no disponible
                    </h3>

                    <p class="mt-2 max-w-4xl text-sm leading-7">
                        No existe la tabla principal de gestión académica. El módulo no mostrará datos simulados.
                    </p>
                </div>

                <div class="rounded-2xl border-2 border-amber-300 bg-white px-4 py-3 text-sm font-black text-amber-950 shadow-sm dark:border-amber-500/40 dark:bg-amber-900 dark:text-amber-100">
                    Sin registros disponibles
                </div>
            </div>
        </section>
    @endunless

    {{-- CABECERA --}}
    <section class="ga-card overflow-hidden rounded-[2rem]">
        <div class="border-b-2 border-slate-100 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/80 sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('dashboard') }}" class="text-slate-500 transition hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-300">
                        Inicio
                    </a>

                    <span class="text-slate-400 dark:text-slate-600">/</span>

                    <span class="font-black text-emerald-700 dark:text-emerald-300">
                        Gestión Académica
                    </span>

                    <span class="text-slate-400 dark:text-slate-600">/</span>

                    <span class="font-bold text-slate-600 dark:text-slate-300">
                        {{ $vistaLabel[$vista] ?? 'Vista general' }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border-2 border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                        SAVP-TIS3
                    </span>

                    <span class="rounded-full border-2 border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                        BTH
                    </span>

                    <span class="rounded-full border-2 border-violet-200 bg-violet-50 px-3 py-1 text-xs font-black text-violet-700 shadow-sm dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300">
                        Control anual
                    </span>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden ga-grid-pattern">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.20),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(124,58,237,0.14),transparent_34%),radial-gradient(circle_at_center,rgba(245,158,11,0.07),transparent_38%)]"></div>

            <div class="relative grid gap-6 p-5 sm:p-7 xl:grid-cols-[1.22fr,0.78fr] xl:items-stretch">
                <div class="flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border-2 border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-emerald-800 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                <svg class="h-4 w-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Control académico normativo
                            </span>

                            <span class="inline-flex rounded-full border-2 border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                Unidad Educativa Técnico Humanístico
                            </span>

                            <span class="inline-flex rounded-full border-2 border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 shadow-sm dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                Cierre recuperable
                            </span>
                        </div>

                        <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 dark:text-white sm:text-4xl">
                            Gestión Académica
                        </h1>

                        <p class="mt-3 max-w-4xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            Administra cada ciclo académico anual con validación inteligente: inscripción, planificación,
                            desarrollo curricular, periodos de evaluación, seguimiento, cierre institucional y preparación
                            de respaldo histórico.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-xs font-black uppercase tracking-[0.13em] text-slate-500 dark:text-slate-400">
                                Ciclo educativo
                            </p>
                            <p class="mt-1 text-sm font-black text-slate-950 dark:text-white">
                                Inscripción · Planificación · Cierre
                            </p>
                        </div>

                        <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950">
                            <p class="text-xs font-black uppercase tracking-[0.13em] text-emerald-700 dark:text-emerald-300">
                                Desarrollo curricular
                            </p>
                            <p class="mt-1 text-sm font-black text-emerald-900 dark:text-emerald-100">
                                200 días hábiles referenciales
                            </p>
                        </div>

                        <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950">
                            <p class="text-xs font-black uppercase tracking-[0.13em] text-amber-700 dark:text-amber-300">
                                Descanso pedagógico
                            </p>
                            <p class="mt-1 text-sm font-black text-amber-900 dark:text-amber-100">
                                10 días hábiles referenciales
                            </p>
                        </div>
                    </div>
                </div>

                <aside class="rounded-[1.6rem] border-2 border-slate-200 bg-white/95 p-5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                Estado actual
                            </p>

                            <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                {{ $activeStatusName }}
                            </h2>
                        </div>

                        <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ $this->badgeEstadoClass($activeStatus ?? 'SIN_ESTADO') }}">
                            {{ $activeStatusName }}
                        </span>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Gestión activa</span>
                            <span class="text-sm font-black text-slate-950 dark:text-white">{{ $gestionActiva['anio'] ?? 'N/D' }}</span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Inicio</span>
                            <span class="text-sm font-black text-slate-950 dark:text-white">{{ $formatDate($gestionActiva['fecha_inicio'] ?? null) }}</span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-300">Cierre</span>
                            <span class="text-sm font-black text-slate-950 dark:text-white">{{ $formatDate($gestionActiva['fecha_fin'] ?? null) }}</span>
                        </div>

                        @if ($gestionActiva)
                            <div class="rounded-2xl border-2 border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">
                                        Progreso
                                    </span>

                                    <span class="text-sm font-black text-emerald-700 dark:text-emerald-300">
                                        {{ $gestionActiva['progreso'] ?? 0 }}%
                                    </span>
                                </div>

                                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-700"
                                        style="width: {{ $gestionActiva['progreso'] ?? 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 grid gap-2">
                        <button type="button"
                            wire:click="abrirNuevaGestion"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nueva gestión
                        </button>

                        <button type="button"
                            wire:click="exportarGestion('{{ $gestionActiva['id'] ?? '' }}')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 21h15" />
                            </svg>
                            Preparar respaldo
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- RESUMEN --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($resumen as $card)
            <article class="ga-card rounded-[1.5rem] p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            {{ $card['titulo'] }}
                        </p>

                        <p class="mt-3 text-3xl font-black text-slate-950 dark:text-white">
                            {{ $card['valor'] }}
                        </p>

                        <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            {{ $card['descripcion'] }}
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 {{ $this->colorClass($card['color']) }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 9.75h7.5M8.25 14.25h4.5" />
                        </svg>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    {{-- NAVEGACIÓN --}}
    <section class="ga-card rounded-[1.5rem] p-2">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            @foreach ([
                'general' => ['Vista general', 'Resumen operativo'],
                'anios' => ['Gestiones', 'Historial anual'],
                'periodos' => ['Trimestres', 'Evaluación'],
                'estructura' => ['Estructura', 'Base académica'],
                'inscripciones' => ['Inscripciones', 'Estudiantes'],
                'reportes' => ['Respaldos', 'Exportación'],
                'cierre' => ['Cierre', 'Auditoría'],
            ] as $key => [$label, $sub])
                <button type="button"
                    wire:click="cambiarVista('{{ $key }}')"
                    class="rounded-2xl px-4 py-3 text-left transition
                    {{ $vista === $key
                        ? 'border-2 border-emerald-300 bg-emerald-50 text-emerald-800 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-200'
                        : 'border-2 border-transparent text-slate-500 hover:border-slate-200 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-400 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white' }}">
                    <span class="block text-sm font-black">{{ $label }}</span>
                    <span class="mt-0.5 block text-[11px] font-semibold opacity-75">{{ $sub }}</span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- FILTROS --}}
    <section class="ga-card rounded-[2rem] p-5">
        <div class="grid gap-4 lg:grid-cols-[1fr,180px,210px,auto]">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                    Buscar gestión
                </label>

                <input type="text"
                    wire:model.live.debounce.400ms="busqueda"
                    class="ga-input"
                    placeholder="Buscar por año, estado o código..." />
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                    Año
                </label>

                <select wire:model.live="filtroAnio" class="ga-input">
                    <option value="">Todos</option>
                    @foreach ($aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                    Estado
                </label>

                <select wire:model.live="filtroEstado" class="ga-input">
                    <option value="">Todos</option>
                    @foreach ($estadosGestion as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex w-full items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>

    {{-- CONTENIDO --}}
    <section class="space-y-6">

        @if ($vista === 'general')
            <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
                <div class="space-y-6">
                    <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                                    Vista general
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                    Estado operativo de la gestión académica
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Resumen del ciclo académico vigente, avance temporal y principales relaciones institucionales.
                                </p>
                            </div>

                            @if ($gestionActiva)
                                <button type="button"
                                    wire:click="abrirDetalle('{{ $gestionActiva['id'] }}')"
                                    class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                    Abrir expediente activo
                                </button>
                            @endif
                        </div>

                        @if ($gestionActiva)
                            <div class="grid gap-4 lg:grid-cols-4">
                                <div class="ga-soft rounded-2xl p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                        Días transcurridos
                                    </p>

                                    <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                        {{ $gestionActiva['dias_transcurridos'] }} días
                                    </p>
                                </div>

                                <div class="ga-soft rounded-2xl p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                        Progreso temporal
                                    </p>

                                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-700"
                                            style="width: {{ $gestionActiva['progreso'] }}%">
                                        </div>
                                    </div>

                                    <p class="mt-2 text-sm font-black text-emerald-700 dark:text-emerald-300">
                                        {{ $gestionActiva['progreso'] }}%
                                    </p>
                                </div>

                                <div class="ga-soft rounded-2xl p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                        Días restantes
                                    </p>

                                    <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                        {{ $gestionActiva['dias_restantes'] }} días
                                    </p>
                                </div>

                                <div class="rounded-2xl border-2 {{ $activeStatus === 'EN_CIERRE' ? 'border-amber-300 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-950' : 'border-emerald-300 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-950' }} p-4 shadow-sm">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] {{ $activeStatus === 'EN_CIERRE' ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                        Estado institucional
                                    </p>

                                    <p class="mt-2 text-2xl font-black {{ $activeStatus === 'EN_CIERRE' ? 'text-amber-800 dark:text-amber-200' : 'text-emerald-800 dark:text-emerald-200' }}">
                                        {{ $activeStatusName }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 border-t-2 border-slate-100 pt-5 dark:border-slate-800 sm:grid-cols-2 xl:grid-cols-4">
                                <button type="button"
                                    wire:click="cambiarVista('periodos')"
                                    class="rounded-2xl border-2 border-violet-200 bg-violet-50 px-4 py-3 text-sm font-black text-violet-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-violet-100 hover:shadow-md dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300">
                                    Ver trimestres
                                </button>

                                <button type="button"
                                    wire:click="cambiarVista('estructura')"
                                    class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                    Ver estructura
                                </button>

                                <button type="button"
                                    wire:click="exportarGestion('{{ $gestionActiva['id'] }}')"
                                    class="rounded-2xl border-2 border-teal-200 bg-teal-50 px-4 py-3 text-sm font-black text-teal-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-100 hover:shadow-md dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-300">
                                    Preparar respaldo
                                </button>

                                <button type="button"
                                    wire:click="prepararCierre('{{ $gestionActiva['id'] }}')"
                                    class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                    Revisar cierre
                                </button>
                            </div>
                        @else
                            <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                                <p class="text-sm font-black text-slate-950 dark:text-white">
                                    No existe una gestión activa.
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Crea o activa una gestión para iniciar el control institucional anual.
                                </p>

                                <button type="button"
                                    wire:click="abrirNuevaGestion"
                                    class="mt-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                    Crear gestión
                                </button>
                            </div>
                        @endif
                    </section>

                    <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                        <div class="mb-5">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-violet-700 dark:text-violet-300">
                                Trimestres sugeridos
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                Referencia evaluativa de la gestión
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                El sistema muestra los tres trimestres recomendados para una gestión regular.
                            </p>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-3">
                            @foreach ($periodos as $periodo)
                                <article class="ga-soft rounded-[1.5rem] p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-black text-slate-950 dark:text-white">
                                                {{ $periodo['nombre'] }}
                                            </h3>

                                            <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                                                Orden {{ $periodo['orden'] ?? 'N/D' }}
                                            </p>
                                        </div>

                                        <span class="rounded-full border-2 px-3 py-1 text-[10px] font-black {{ $this->badgeEstadoClass($periodo['estado']) }}">
                                            {{ $periodo['estado'] }}
                                        </span>
                                    </div>

                                    <div class="mt-4 space-y-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                            <span>Inicio</span>
                                            <span class="font-black">{{ $formatDate($periodo['fecha_inicio'] ?? null) }}</span>
                                        </div>

                                        <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                            <span>Cierre</span>
                                            <span class="font-black">{{ $formatDate($periodo['fecha_fin'] ?? null) }}</span>
                                        </div>

                                        <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                            <span>Días hábiles ref.</span>
                                            <span class="font-black">{{ $periodo['dias_habiles_referencia'] ?? 0 }}</span>
                                        </div>
                                    </div>

                                    @if (($periodo['incluye_descanso_pedagogico'] ?? false) === true)
                                        <div class="mt-3 rounded-2xl border-2 border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold leading-5 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                            Incluye descanso pedagógico de {{ $periodo['descanso_pedagogico_dias_habiles'] ?? 10 }} días hábiles.
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                        <div class="mb-5">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">
                                Control de cierre
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                                Pendientes institucionales
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Revisión rápida de procesos que pueden bloquear el cierre definitivo.
                            </p>
                        </div>

                        <div class="space-y-3">
                            @foreach ($pendientesCierre as $pendiente)
                                <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                        {{ $pendiente['titulo'] }}
                                    </p>

                                    <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ $this->colorClass($pendiente['color']) }}">
                                        {{ $pendiente['valor'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <button type="button"
                            wire:click="prepararCierre('{{ $gestionActiva['id'] ?? '' }}')"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                            Revisar cierre académico
                        </button>
                    </section>
                </div>
            </section>
        @endif

        @if ($vista === 'anios')
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                            Gestiones registradas
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                            Expedientes anuales
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Consulta, activa, revisa o prepara el respaldo de cada gestión académica registrada.
                        </p>
                    </div>

                    <span class="rounded-full border-2 border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        {{ $gestiones->total() }} registros
                    </span>
                </div>

                <div class="grid gap-4">
                    @forelse ($gestiones as $gestion)
                        <article class="ga-soft rounded-[1.5rem] p-5 transition hover:border-emerald-300 hover:bg-white hover:-translate-y-0.5 hover:shadow-md dark:hover:border-emerald-500/40 dark:hover:bg-slate-950">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border-2 border-slate-200 bg-white text-emerald-700 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-emerald-300">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.19c.597 0 1.17.237 1.591.659l1.06 1.06c.422.421.995.659 1.591.659H18A2.25 2.25 0 0 1 20.25 9.128V17.25A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-black text-slate-950 dark:text-white">
                                                {{ $gestion['nombre'] }}
                                            </h3>

                                            <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ $this->badgeEstadoClass($gestion['estado']) }}">
                                                {{ $statusLabel[$gestion['estado']] ?? $gestion['estado'] }}
                                            </span>

                                            <span class="rounded-full border-2 border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400">
                                                Ciclo {{ $gestion['anio'] }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            {{ $gestion['descripcion'] }}
                                        </p>

                                        <div class="mt-3 grid gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 sm:grid-cols-2 xl:grid-cols-4">
                                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 dark:border-slate-700 dark:bg-slate-950">
                                                Inscripciones: {{ $gestion['estudiantes'] }}
                                            </span>

                                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 dark:border-slate-700 dark:bg-slate-950">
                                                Planes: {{ $gestion['planes_asignatura'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 dark:border-slate-700 dark:bg-slate-950">
                                                Horarios: {{ $gestion['horarios'] ?? 0 }}
                                            </span>

                                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 dark:border-slate-700 dark:bg-slate-950">
                                                Actualizado: {{ $gestion['ultima_actualizacion'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                    <button type="button"
                                        wire:click="abrirDetalle('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-800">
                                        Expediente
                                    </button>

                                    <button type="button"
                                        wire:click="exportarGestion('{{ $gestion['id'] }}')"
                                        class="rounded-2xl border-2 border-teal-200 bg-teal-50 px-4 py-2 text-sm font-black text-teal-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-100 hover:shadow-md dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-300">
                                        Respaldo
                                    </button>

                                    @if ($gestion['estado'] === 'PLANIFICADA')
                                        <button type="button"
                                            wire:click="activarGestion('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                            Activar
                                        </button>
                                    @endif

                                    @if (in_array($gestion['estado'], ['ACTIVA', 'EN_CIERRE'], true))
                                        <button type="button"
                                            wire:click="prepararCierre('{{ $gestion['id'] }}')"
                                            class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-2 text-sm font-black text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                            Cierre
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                            <p class="text-sm font-black text-slate-950 dark:text-white">
                                No existen gestiones académicas registradas.
                            </p>

                            <button type="button"
                                wire:click="abrirNuevaGestion"
                                class="mt-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
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
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-violet-700 dark:text-violet-300">
                        Periodos de evaluación
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        Trimestres académicos y descanso pedagógico
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Configuración orientada a una gestión regular: tres trimestres, 200 días hábiles curriculares y descanso pedagógico de invierno.
                    </p>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($periodos as $periodo)
                        <article class="ga-soft rounded-[1.5rem] p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-black text-slate-950 dark:text-white">
                                        {{ $periodo['nombre'] }}
                                    </h3>

                                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                                        Orden {{ $periodo['orden'] ?? 'N/D' }}
                                    </p>
                                </div>

                                <span class="rounded-full border-2 px-3 py-1 text-[10px] font-black {{ $this->badgeEstadoClass($periodo['estado']) }}">
                                    {{ $periodo['estado'] }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                    <span>Inicio</span>
                                    <span class="font-black">{{ $formatDate($periodo['fecha_inicio'] ?? null) }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                    <span>Cierre</span>
                                    <span class="font-black">{{ $formatDate($periodo['fecha_fin'] ?? null) }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950">
                                    <span>Días hábiles ref.</span>
                                    <span class="font-black">{{ $periodo['dias_habiles_referencia'] ?? 0 }}</span>
                                </div>
                            </div>

                            @if (($periodo['incluye_descanso_pedagogico'] ?? false) === true)
                                <div class="mt-3 rounded-2xl border-2 border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold leading-5 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                    Incluye descanso pedagógico referencial de {{ $periodo['descanso_pedagogico_dias_habiles'] ?? 10 }} días hábiles.
                                </div>
                            @endif

                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-emerald-500 transition-all duration-700"
                                    style="width: {{ $periodo['progreso'] ?? 0 }}%">
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($vista === 'estructura')
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-teal-700 dark:text-teal-300">
                        Estructura académica anual
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        Organización institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Indicadores reales de cursos, paralelos, turnos, asignaturas, especialidades, planes y horarios.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($estructura as $item)
                        <article class="ga-soft rounded-2xl p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                {{ $item['titulo'] }}
                            </p>

                            <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">
                                {{ $item['valor'] }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                {{ $item['detalle'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($vista === 'inscripciones')
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">
                        Inscripciones
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        Estado de estudiantes inscritos
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Vista de control general para la gestión activa. La administración detallada debe conectarse con el módulo de inscripciones.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border-2 border-sky-200 bg-sky-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-sky-500/30 dark:bg-sky-950">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-sky-700 dark:text-sky-300">Gestión activa</p>
                        <p class="mt-2 text-3xl font-black text-sky-900 dark:text-sky-100">{{ $gestionActiva['anio'] ?? 'N/D' }}</p>
                    </article>

                    <article class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700 dark:text-emerald-300">Estudiantes inscritos</p>
                        <p class="mt-2 text-3xl font-black text-emerald-900 dark:text-emerald-100">{{ $gestionActiva['estudiantes'] ?? 0 }}</p>
                    </article>

                    <article class="rounded-2xl border-2 border-violet-200 bg-violet-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-violet-500/30 dark:bg-violet-950">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-violet-700 dark:text-violet-300">Cursos activos</p>
                        <p class="mt-2 text-3xl font-black text-violet-900 dark:text-violet-100">{{ $gestionActiva['cursos'] ?? 0 }}</p>
                    </article>

                    <article class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950">
                        <p class="text-xs font-black uppercase tracking-[0.12em] text-amber-700 dark:text-amber-300">Pendientes cierre</p>
                        <p class="mt-2 text-3xl font-black text-amber-900 dark:text-amber-100">{{ $gestionActiva ? array_sum(array_column($pendientesCierre, 'valor')) : 0 }}</p>
                    </article>
                </div>

                <div class="mt-5 rounded-2xl border-2 border-slate-200 bg-slate-50 p-5 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    La gestión académica no reemplaza el módulo de inscripción. Aquí se muestra el resumen institucional para validar cierre, reportes y respaldo de la gestión.
                </div>
            </section>
        @endif

        @if ($vista === 'reportes')
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-teal-700 dark:text-teal-300">
                        Respaldos y exportación
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        Preparación de expediente institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
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
                            wire:click="exportarGestion('{{ $gestionActiva['id'] ?? '' }}', '{{ $tipo }}')"
                            class="rounded-2xl border-2 border-slate-200 bg-slate-50 p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:bg-white hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-500/40 dark:hover:bg-slate-950">
                            <p class="text-sm font-black text-slate-950 dark:text-white">{{ $titulo }}</p>
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $detalle }}</p>
                            <span class="mt-4 inline-flex rounded-full border-2 border-teal-200 bg-teal-50 px-3 py-1 text-xs font-black text-teal-800 dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-300">
                                Preparar
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($vista === 'cierre')
            <section class="ga-card rounded-[2rem] p-5 sm:p-6">
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">
                        Cierre académico
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">
                        Auditoría previa al cierre institucional
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        El cierre definitivo se bloquea si existen procesos académicos pendientes o inconsistencias críticas.
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($pendientesCierre as $pendiente)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $pendiente['titulo'] }}
                            </p>

                            <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ $this->colorClass($pendiente['color']) }}">
                                {{ $pendiente['valor'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button"
                        wire:click="prepararCierre('{{ $gestionActiva['id'] ?? '' }}')"
                        class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-5 py-3 text-sm font-black text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                        Revisar cierre
                    </button>

                    <button type="button"
                        wire:click="exportarGestion('{{ $gestionActiva['id'] ?? '' }}')"
                        class="rounded-2xl border-2 border-teal-200 bg-teal-50 px-5 py-3 text-sm font-black text-teal-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-100 hover:shadow-md dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-300">
                        Preparar respaldo
                    </button>
                </div>
            </section>
        @endif
    </section>

    {{-- MODAL NUEVA GESTIÓN --}}
    <div
        x-show="modalNueva"
        x-cloak
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center ga-overlay p-4">

        <section
            x-show="modalNueva"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="relative max-h-[92vh] w-full max-w-7xl overflow-hidden rounded-[2rem] border-2 border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-950">

            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-400/10 blur-3xl"></div>
                <div class="absolute -bottom-24 right-0 h-72 w-72 rounded-full bg-violet-400/10 blur-3xl"></div>
                <div class="absolute right-1/3 top-0 h-40 w-40 rounded-full bg-amber-300/10 blur-3xl"></div>
            </div>

            <div class="relative max-h-[92vh] overflow-y-auto">
                <header class="sticky top-0 z-20 border-b-2 border-slate-100 bg-white/92 px-6 py-5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/92">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-4xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border-2 border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-emerald-800 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300">
                                    <svg class="h-4 w-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Validación institucional
                                </span>

                                <span class="inline-flex rounded-full border-2 border-violet-200 bg-violet-50 px-3 py-1 text-xs font-black text-violet-800 shadow-sm dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300">
                                    Gestión anual
                                </span>

                                <span class="inline-flex rounded-full border-2 border-amber-200 bg-amber-50 px-3 py-1 text-xs font-black text-amber-800 shadow-sm dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                    200 días hábiles ref.
                                </span>

                                <span
                                    class="inline-flex rounded-full border-2 px-3 py-1 text-xs font-black shadow-sm transition-all duration-300"
                                    x-bind:class="puedeGuardarGestion
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300'">
                                    <span x-text="estadoFormularioTexto"></span>
                                </span>
                            </div>

                            <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                                Registrar gestión académica
                            </h2>

                            <p class="mt-2 max-w-4xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                                Registra un ciclo institucional anual para inscripción, planificación, desarrollo curricular,
                                periodos de evaluación, cierre académico y respaldo histórico. El sistema bloquea fechas
                                inconsistentes, duplicidad de gestión activa y planificación anticipada fuera de la etapa institucional.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="cerrarModal"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Cerrar
                        </button>
                    </div>
                </header>

                <form wire:submit.prevent="crearGestionAcademica" class="p-6">
                    <div
                        x-show="bloqueadoPorPlanificacionAnticipada"
                        x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mb-6 overflow-hidden rounded-[1.7rem] border-2 border-rose-300 bg-rose-50 shadow-sm ring-1 ring-rose-100 dark:border-rose-500/40 dark:bg-rose-950 dark:ring-rose-500/20">

                        <div class="border-b-2 border-rose-200 bg-rose-100/70 px-5 py-4 dark:border-rose-500/30 dark:bg-rose-900/40">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-rose-300 bg-white text-rose-700 shadow-sm dark:border-rose-500/40 dark:bg-rose-900 dark:text-rose-200">
                                        <svg class="h-6 w-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">
                                            Creación bloqueada
                                        </p>

                                        <h3 class="mt-1 text-xl font-black text-rose-950 dark:text-rose-100">
                                            Aún no corresponde planificar la siguiente gestión
                                        </h3>
                                    </div>
                                </div>

                                <span class="inline-flex rounded-full border-2 border-rose-300 bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-rose-800 shadow-sm dark:border-rose-500/40 dark:bg-rose-900 dark:text-rose-100">
                                    Bloqueo preventivo
                                </span>
                            </div>
                        </div>

                        <div class="p-5">
                            <p class="max-w-5xl text-sm leading-7 text-rose-900 dark:text-rose-200">
                                Ya existe una gestión académica activa. Para conservar coherencia institucional, la siguiente
                                gestión solo debe planificarse en noviembre o diciembre, cuando la gestión vigente se encuentra
                                próxima al cierre curricular o cierre institucional.
                            </p>

                            <div class="mt-5 grid gap-3 md:grid-cols-3">
                                <div class="rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-rose-500/30 dark:bg-rose-900">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-rose-600 dark:text-rose-300">
                                        Gestión vigente
                                    </p>

                                    <p class="mt-1 text-lg font-black text-rose-950 dark:text-white">
                                        <span x-text="gestionActivaAnio || 'Sin dato'"></span>
                                    </p>
                                </div>

                                <div class="rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-rose-500/30 dark:bg-rose-900">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-rose-600 dark:text-rose-300">
                                        Mes actual
                                    </p>

                                    <p class="mt-1 text-lg font-black text-rose-950 dark:text-white">
                                        <span x-text="mesActual"></span>
                                    </p>
                                </div>

                                <div class="rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-rose-500/30 dark:bg-rose-900">
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-rose-600 dark:text-rose-300">
                                        Permitido desde
                                    </p>

                                    <p class="mt-1 text-lg font-black text-rose-950 dark:text-white">
                                        Noviembre / Diciembre
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-900 shadow-sm dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                Recomendación: continúa operando la gestión actual. Cuando llegue la etapa de cierre institucional,
                                podrás registrar la siguiente gestión como <strong>PLANIFICADA</strong>, no como <strong>ACTIVA</strong>.
                            </div>
                        </div>
                    </div>

                    <div
                        x-show="bloqueadoPorGestionActivaDuplicada"
                        x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mb-6 rounded-[1.5rem] border-2 border-amber-300 bg-amber-50 p-5 shadow-sm ring-1 ring-amber-100 dark:border-amber-500/40 dark:bg-amber-950 dark:ring-amber-500/20">

                        <div class="flex gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-amber-300 bg-white text-amber-700 shadow-sm dark:border-amber-500/40 dark:bg-amber-900 dark:text-amber-200">
                                <svg class="h-6 w-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">
                                    Estado no permitido
                                </p>

                                <h3 class="mt-2 text-xl font-black text-amber-950 dark:text-amber-100">
                                    No puede existir más de una gestión activa
                                </h3>

                                <p class="mt-2 text-sm leading-7 text-amber-900 dark:text-amber-200">
                                    Si ya existe una gestión en curso, la nueva gestión debe registrarse como
                                    <strong>PLANIFICADA</strong>. La activación corresponde después del cierre o transición
                                    institucional de la gestión vigente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[1fr,0.94fr]">
                        <div class="space-y-5">
                            <section class="rounded-[1.7rem] border-2 border-slate-200 bg-slate-50 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                            Datos principales
                                        </p>

                                        <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                            Identificación de la gestión
                                        </h3>

                                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            Define el año, nombre, rango institucional, modalidad y estado inicial.
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex rounded-full border-2 px-3 py-1 text-xs font-black transition-all duration-300"
                                        x-bind:class="puedeGuardarGestion
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300'">
                                        <span x-text="puedeGuardarGestion ? 'Habilitado' : 'Bloqueado'"></span>
                                    </span>
                                </div>

                                <div class="mt-5 grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Año de gestión
                                        </label>

                                        <input
                                            type="number"
                                            x-model="anio"
                                            min="2020"
                                            max="2100"
                                            class="ga-input"
                                            placeholder="2027" />

                                        <p x-show="!anioValido" x-cloak class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">
                                            El año debe estar entre 2020 y 2100.
                                        </p>

                                        @error('form.anio')
                                            <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Nombre de gestión
                                        </label>

                                        <input
                                            type="text"
                                            x-model="nombre"
                                            class="ga-input"
                                            placeholder="Gestión Académica 2027" />

                                        <p x-show="!nombreValido" x-cloak class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">
                                            El nombre debe ser más descriptivo.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Fecha inicio
                                        </label>

                                        <input
                                            type="date"
                                            x-model="fechaInicio"
                                            class="ga-input" />

                                        @error('form.fecha_inicio')
                                            <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Fecha fin
                                        </label>

                                        <input
                                            type="date"
                                            x-model="fechaFin"
                                            class="ga-input" />

                                        <p x-show="fechaInicio && fechaFin && !fechaFinValida" x-cloak class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">
                                            La fecha final debe ser posterior a la fecha inicial.
                                        </p>

                                        @error('form.fecha_fin')
                                            <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Modalidad
                                        </label>

                                        <input
                                            type="text"
                                            x-model="modalidad"
                                            class="ga-input"
                                            placeholder="Técnico Humanístico" />
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Estado inicial
                                        </label>

                                        <select x-model="estado" class="ga-input">
                                            @foreach ($estadosGestion as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        <p x-show="bloqueadoPorGestionActivaDuplicada" x-cloak class="mt-2 text-xs font-bold text-amber-700 dark:text-amber-300">
                                            Ya existe una gestión activa. Usa PLANIFICADA.
                                        </p>

                                        @error('form.estado')
                                            <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-100">
                                            Descripción institucional
                                        </label>

                                        <textarea
                                            x-model="descripcion"
                                            rows="4"
                                            maxlength="500"
                                            class="ga-input resize-none"
                                            placeholder="Ciclo académico orientado a inscripción, planificación, desarrollo curricular, evaluación y cierre institucional."></textarea>

                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                Describe el propósito institucional de la gestión.
                                            </p>

                                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                <span x-text="(descripcion || '').length"></span>/500
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-[1.7rem] border-2 border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                                            Fechas recomendadas
                                        </p>

                                        <h3 class="mt-1 text-lg font-black text-slate-950 dark:text-white">
                                            Selección rápida del calendario
                                        </h3>

                                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            Usa el rango institucional para cubrir inscripción, planificación, desarrollo curricular y cierre.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    <button
                                        type="button"
                                        wire:click="aplicarFechasInstitucionales"
                                        x-bind:disabled="bloqueadoPorPlanificacionAnticipada"
                                        x-bind:class="bloqueadoPorPlanificacionAnticipada
                                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-600'
                                            : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-md dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300'"
                                        class="rounded-2xl border-2 px-4 py-3 text-sm font-black shadow-sm transition">
                                        Usar rango institucional sugerido
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="aplicarFechasCurriculares"
                                        x-bind:disabled="bloqueadoPorPlanificacionAnticipada"
                                        x-bind:class="bloqueadoPorPlanificacionAnticipada
                                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-600'
                                            : 'border-violet-200 bg-violet-50 text-violet-800 hover:-translate-y-0.5 hover:bg-violet-100 hover:shadow-md dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300'"
                                        class="rounded-2xl border-2 px-4 py-3 text-sm font-black shadow-sm transition">
                                        Usar rango curricular sugerido
                                    </button>
                                </div>
                            </section>

                            <section class="grid gap-3 md:grid-cols-2">
                                <label
                                    class="flex items-start gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                                    x-bind:class="bloqueadoPorPlanificacionAnticipada ? 'cursor-not-allowed opacity-60 hover:translate-y-0 hover:shadow-sm' : 'cursor-pointer'">

                                    <input
                                        type="checkbox"
                                        x-model="crearPeriodos"
                                        x-bind:disabled="bloqueadoPorPlanificacionAnticipada"
                                        class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                                    <span>
                                        <span class="block text-sm font-black text-slate-950 dark:text-white">
                                            Crear periodos base si no existen
                                        </span>

                                        <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                            Creará Primer, Segundo y Tercer trimestre si la tabla está vacía.
                                        </span>
                                    </span>
                                </label>

                                <label class="flex cursor-not-allowed items-start gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 p-4 opacity-80 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <input
                                        type="checkbox"
                                        x-model="copiarEstructura"
                                        disabled
                                        class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                                    <span>
                                        <span class="block text-sm font-black text-slate-950 dark:text-white">
                                            Copiar estructura anterior
                                        </span>

                                        <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                            Reservado para una siguiente fase con control de duplicidad y auditoría.
                                        </span>
                                    </span>
                                </label>
                            </section>
                        </div>

                        <aside class="space-y-4">
                            <section class="rounded-[1.7rem] border-2 border-slate-200 bg-slate-50 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                                            Análisis inteligente
                                        </p>

                                        <h3 class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                                            Coherencia de gestión
                                        </h3>
                                    </div>

                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 shadow-sm transition"
                                        x-bind:class="puedeGuardarGestion
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-300'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path x-show="puedeGuardarGestion" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75" />
                                            <path x-show="!puedeGuardarGestion" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3">
                                    <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-950">
                                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
                                            Duración calculada
                                        </p>

                                        <div class="mt-2 flex items-end justify-between gap-3">
                                            <p class="text-3xl font-black text-slate-950 dark:text-white">
                                                <span x-text="duracionDias"></span>
                                                <span class="text-sm font-bold text-slate-500 dark:text-slate-400">días</span>
                                            </p>

                                            <p class="rounded-full border-2 px-3 py-1 text-xs font-black transition" x-bind:class="duracionColor">
                                                <span x-text="duracionEstado"></span>
                                            </p>
                                        </div>

                                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                            <div
                                                class="h-full rounded-full transition-all duration-700"
                                                x-bind:style="'width:' + progresoDuracion + '%'"
                                                x-bind:class="duracionEstado === 'COHERENTE'
                                                    ? 'bg-gradient-to-r from-emerald-500 to-teal-500'
                                                    : duracionEstado === 'BLOQUEADO'
                                                        ? 'bg-gradient-to-r from-rose-500 to-red-500'
                                                        : 'bg-gradient-to-r from-amber-500 to-orange-500'">
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-2xl border-2 p-4 shadow-sm transition-all duration-300"
                                        x-bind:class="puedeGuardarGestion
                                            ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-950'
                                            : 'border-rose-200 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-950'">

                                        <p
                                            class="text-xs font-black uppercase tracking-[0.12em]"
                                            x-bind:class="puedeGuardarGestion
                                                ? 'text-emerald-700 dark:text-emerald-300'
                                                : 'text-rose-700 dark:text-rose-300'">
                                            Estado del formulario
                                        </p>

                                        <p
                                            class="mt-2 text-sm font-bold leading-6"
                                            x-bind:class="puedeGuardarGestion
                                                ? 'text-emerald-900 dark:text-emerald-100'
                                                : 'text-rose-900 dark:text-rose-100'">
                                            <span x-show="puedeGuardarGestion">
                                                La gestión cumple las condiciones mínimas para enviarse a validación del sistema.
                                            </span>

                                            <span x-show="!puedeGuardarGestion">
                                                La gestión todavía está bloqueada por fechas, estado, duración o planificación anticipada.
                                            </span>
                                        </p>
                                    </div>

                                    @if (!empty($analisisCreacion))
                                        <div class="rounded-2xl border-2 {{ ($analisisCreacion['puede_continuar'] ?? false) ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-950' : 'border-rose-200 bg-rose-50 dark:border-rose-500/30 dark:bg-rose-950' }} p-4 shadow-sm">
                                            <p class="text-xs font-black uppercase tracking-[0.12em] {{ ($analisisCreacion['puede_continuar'] ?? false) ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                                                {{ $analisisCreacion['estado_inteligente'] ?? 'SIN_DATOS' }}
                                            </p>

                                            <p class="mt-2 text-sm font-bold leading-6 {{ ($analisisCreacion['puede_continuar'] ?? false) ? 'text-emerald-900 dark:text-emerald-100' : 'text-rose-900 dark:text-rose-100' }}">
                                                {{ $analisisCreacion['mensaje'] ?? 'Esperando datos.' }}
                                            </p>
                                        </div>

                                        @foreach (($analisisCreacion['bloqueos'] ?? []) as $bloqueo)
                                            <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold leading-5 text-rose-900 shadow-sm dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-200">
                                                {{ $bloqueo }}
                                            </div>
                                        @endforeach

                                        @foreach (($analisisCreacion['advertencias'] ?? []) as $advertencia)
                                            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-5 text-amber-900 shadow-sm dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                                {{ $advertencia }}
                                            </div>
                                        @endforeach

                                        @foreach (($analisisCreacion['sugerencias'] ?? []) as $sugerencia)
                                            <div class="rounded-2xl border-2 border-teal-200 bg-teal-50 px-4 py-3 text-xs font-bold leading-5 text-teal-900 shadow-sm dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-200">
                                                {{ $sugerencia }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </section>

                            <section class="rounded-[1.7rem] border-2 border-violet-200 bg-violet-50 p-5 shadow-sm dark:border-violet-500/30 dark:bg-violet-950">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-violet-700 dark:text-violet-300">
                                            Periodos sugeridos
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-violet-900 dark:text-violet-100">
                                            Referencia de trimestres para una gestión regular.
                                        </p>
                                    </div>

                                    <span class="rounded-full border-2 border-violet-200 bg-white px-3 py-1 text-xs font-black text-violet-800 shadow-sm dark:border-violet-500/30 dark:bg-slate-950 dark:text-violet-300">
                                        3 periodos
                                    </span>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @foreach ($periodosSugeridos as $periodo)
                                        <div class="rounded-2xl border-2 border-white bg-white p-3 text-xs font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-violet-500/30 dark:bg-slate-950 dark:text-slate-200">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-black">{{ $periodo['nombre'] }}</span>
                                                <span>{{ $periodo['dias_habiles_referencia'] }} días</span>
                                            </div>

                                            <p class="mt-1 text-slate-500 dark:text-slate-400">
                                                {{ $formatDate($periodo['fecha_inicio']) }} - {{ $formatDate($periodo['fecha_fin']) }}
                                            </p>

                                            @if (($periodo['incluye_descanso_pedagogico'] ?? false) === true)
                                                <p class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                                                    Incluye descanso pedagógico.
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="rounded-[1.7rem] border-2 border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                                <p class="text-sm font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                    Reglas aplicadas
                                </p>

                                <div class="mt-4 space-y-3">
                                    @foreach ([
                                        'No se permite crear una segunda gestión activa.',
                                        'La siguiente gestión solo se puede planificar desde noviembre o diciembre.',
                                        'El rango debe representar una gestión anual coherente, no un periodo corto.',
                                        'Una gestión cerrada queda como expediente histórico institucional.',
                                    ] as $regla)
                                        <div class="rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold leading-5 text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                                            {{ $regla }}
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        </aside>
                    </div>

                    <footer class="sticky bottom-0 z-20 mt-6 border-t-2 border-slate-100 bg-white/92 pt-5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/92">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm font-black text-slate-950 dark:text-white">
                                    Guardar gestión académica
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    El botón se habilita únicamente si la gestión respeta el rango anual, el flujo institucional y la ventana de planificación.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    wire:click="cerrarModal"
                                    class="rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    x-bind:disabled="!puedeGuardarGestion"
                                    wire:loading.attr="disabled"
                                    x-bind:class="puedeGuardarGestion
                                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                                        : 'cursor-not-allowed border-2 border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-600'"
                                    class="inline-flex min-w-[190px] items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition">

                                    <svg
                                        wire:loading
                                        wire:target="crearGestionAcademica"
                                        class="h-4 w-4 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                    </svg>

                                    <span wire:loading.remove wire:target="crearGestionAcademica">
                                        Guardar gestión
                                    </span>

                                    <span wire:loading wire:target="crearGestionAcademica">
                                        Validando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </footer>
                </form>
            </div>
        </section>
    </div>

    {{-- MODAL CIERRE --}}
    <div x-show="modalCierre" x-cloak class="fixed inset-0 z-50 flex items-center justify-center ga-overlay p-4">
        <section x-show="modalCierre" x-transition class="w-full max-w-4xl rounded-[2rem] border-2 border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-950">
            <div class="flex items-start justify-between gap-4 border-b-2 border-slate-100 pb-5 dark:border-slate-800">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Revisión de cierre</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Cierre de gestión académica</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">El cierre definitivo convierte la gestión en expediente histórico institucional.</p>
                </div>

                <button type="button" wire:click="cerrarModalCierre" class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                    Cerrar
                </button>
            </div>

            <div class="mt-6 space-y-5">
                <div class="ga-soft rounded-2xl p-4">
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Gestión revisada</p>
                    <p class="mt-2 text-lg font-black text-slate-950 dark:text-white">{{ $revisionCierre['gestion'] ?? 'Sin gestión seleccionada' }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $revisionCierre['mensaje'] ?? 'Sin análisis disponible.' }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        'inscripciones_pendientes' => 'Inscripciones pendientes',
                        'planes_asignatura_incompletos' => 'Plan asignatura incompleto',
                        'planes_especialidad_incompletos' => 'Plan especialidad incompleto',
                    ] as $key => $label)
                        <div class="ga-soft rounded-2xl p-4">
                            <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-black {{ ($revisionCierre[$key] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                                {{ $revisionCierre[$key] ?? 0 }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if (!empty($revisionCierre['pendientes_cierre'] ?? []))
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($revisionCierre['pendientes_cierre'] as $titulo => $valor)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $titulo }}</span>
                                <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ ((int) $valor) > 0 ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300' : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-300' }}">
                                    {{ $valor }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach (($revisionCierre['bloqueos'] ?? []) as $bloqueo)
                    <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-6 text-rose-900 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-200">
                        {{ $bloqueo }}
                    </div>
                @endforeach

                @foreach (($revisionCierre['advertencias'] ?? []) as $advertencia)
                    <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-900 dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-200">
                        {{ $advertencia }}
                    </div>
                @endforeach

                <div class="flex flex-col gap-3 border-t-2 border-slate-100 pt-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-black text-slate-950 dark:text-white">Acción institucional</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Primero puede pasar a EN_CIERRE; después, si no hay pendientes, se cierra definitivamente.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="cerrarModalCierre" class="rounded-2xl border-2 border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                            Cancelar
                        </button>

                        <button type="button" wire:click="iniciarCierreGestion" class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-5 py-3 text-sm font-black text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                            Pasar a EN_CIERRE
                        </button>

                        <button type="button"
                            wire:click="confirmarCierreGestion"
                            @disabled(($revisionCierre['puede_cerrar'] ?? false) !== true)
                            class="rounded-2xl px-5 py-3 text-sm font-black transition
                            {{ ($revisionCierre['puede_cerrar'] ?? false) === true
                                ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                                : 'cursor-not-allowed border-2 border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-600' }}">
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
            <section x-show="drawerDetalle" x-transition class="h-full w-full max-w-4xl overflow-y-auto border-l-2 border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-950">
                @if ($gestionSeleccionada)
                    <div class="flex items-start justify-between gap-4 border-b-2 border-slate-100 pb-5 dark:border-slate-800">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border-2 px-3 py-1 text-xs font-black {{ $this->badgeEstadoClass($gestionSeleccionada['estado']) }}">
                                    {{ $statusLabel[$gestionSeleccionada['estado']] ?? $gestionSeleccionada['estado'] }}
                                </span>

                                <span class="rounded-full border-2 border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                                    Ciclo {{ $gestionSeleccionada['anio'] }}
                                </span>
                            </div>

                            <h2 class="mt-4 text-2xl font-black text-slate-950 dark:text-white">
                                Expediente de {{ $gestionSeleccionada['nombre'] }}
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Resumen institucional del ciclo académico seleccionado.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarDetalle" class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                            Cerrar
                        </button>
                    </div>

                    <div class="mt-6 space-y-6">
                        <section class="ga-soft rounded-[1.5rem] p-5">
                            <h3 class="text-sm font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                                Datos principales
                            </h3>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach ([
                                    'Año' => $gestionSeleccionada['anio'],
                                    'Estado' => $statusLabel[$gestionSeleccionada['estado']] ?? $gestionSeleccionada['estado'],
                                    'Inicio' => $formatDate($gestionSeleccionada['fecha_inicio']),
                                    'Cierre' => $formatDate($gestionSeleccionada['fecha_fin']),
                                ] as $label => $value)
                                    <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-950">
                                        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
                                        <p class="mt-1 text-sm font-black text-slate-950 dark:text-white">{{ $value }}</p>
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
                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
                                    <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $value }}</p>
                                </div>
                            @endforeach
                        </section>

                        <section class="grid gap-3 sm:grid-cols-3">
                            <button type="button" wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}', 'COMPLETA')" class="rounded-2xl border-2 border-teal-200 bg-teal-50 px-4 py-3 text-sm font-black text-teal-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-100 hover:shadow-md dark:border-teal-500/30 dark:bg-teal-950 dark:text-teal-300">
                                Respaldo completo
                            </button>

                            <button type="button" wire:click="exportarGestion('{{ $gestionSeleccionada['id'] }}', 'INSCRIPCIONES')" class="rounded-2xl border-2 border-violet-200 bg-violet-50 px-4 py-3 text-sm font-black text-violet-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-violet-100 hover:shadow-md dark:border-violet-500/30 dark:bg-violet-950 dark:text-violet-300">
                                Inscripciones
                            </button>

                            @if (in_array($gestionSeleccionada['estado'], ['ACTIVA', 'EN_CIERRE'], true))
                                <button type="button" wire:click="prepararCierre('{{ $gestionSeleccionada['id'] }}')" class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-md dark:border-amber-500/30 dark:bg-amber-950 dark:text-amber-300">
                                    Revisar cierre
                                </button>
                            @endif
                        </section>
                    </div>
                @else
                    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                        <p class="text-sm font-black text-slate-950 dark:text-white">
                            No existe gestión seleccionada.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>