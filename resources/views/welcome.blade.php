<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Franz Tamayo N°3 | SAVP</title>
    <meta name="description"
        content="Landing institucional base de la Unidad Educativa Técnico Humanístico Franz Tamayo N°3.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|poppins:500,600,700,800"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Poppins', sans-serif;
        }

        .site-bg {
            position: relative;
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 8%, rgba(16, 185, 129, 0.12), transparent 22%),
                radial-gradient(circle at 88% 10%, rgba(14, 165, 233, 0.10), transparent 24%),
                linear-gradient(180deg, #f4fbf7 0%, #f7fafc 40%, #f5fbf8 100%);
        }

        .site-bg::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                linear-gradient(to right, rgba(15, 23, 42, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.06) 1px, transparent 1px),
                radial-gradient(circle at 16% 18%, rgba(16, 185, 129, 0.09), transparent 20%),
                radial-gradient(circle at 82% 14%, rgba(14, 165, 233, 0.08), transparent 22%),
                radial-gradient(circle at 50% 100%, rgba(16, 185, 129, 0.05), transparent 28%);
            background-size: 32px 32px, 32px 32px, auto, auto, auto;
            opacity: 1;
        }

        .site-bg::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.32));
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }

        .section-grid {
            position: relative;
            isolation: isolate;
        }

        .section-grid::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.46));
        }

        .glass {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .hero-shell {
            position: relative;
        }

        .hero-shell::before {
            content: '';
            position: absolute;
            inset: -40px -50px -20px -50px;
            border-radius: 48px;
            background:
                radial-gradient(circle at 18% 15%, rgba(16, 185, 129, 0.08), transparent 22%),
                radial-gradient(circle at 80% 25%, rgba(14, 165, 233, 0.08), transparent 24%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0.02));
            filter: blur(10px);
            z-index: -1;
        }

        .hero-shadow {
            box-shadow:
                0 30px 80px rgba(15, 23, 42, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.20);
        }

        .card-shadow {
            box-shadow:
                0 24px 55px rgba(15, 23, 42, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }

        .soft-panel {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .section-title {
            letter-spacing: 0.18em;
        }

        .scroll-reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity .8s ease, transform .8s ease;
            will-change: opacity, transform;
        }

        .scroll-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-reveal-left {
            opacity: 0;
            transform: translateX(-32px);
            transition: opacity .8s ease, transform .8s ease;
            will-change: opacity, transform;
        }

        .scroll-reveal-left.is-visible {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-reveal-right {
            opacity: 0;
            transform: translateX(32px);
            transition: opacity .8s ease, transform .8s ease;
            will-change: opacity, transform;
        }

        .scroll-reveal-right.is-visible {
            opacity: 1;
            transform: translateX(0);
        }

        @media (prefers-reduced-motion: reduce) {

            .scroll-reveal,
            .scroll-reveal-left,
            .scroll-reveal-right {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        .hero-shell {
            position: relative;
        }

        .hero-shell::before {
            content: '';
            position: absolute;
            inset: -40px -50px -20px -50px;
            border-radius: 48px;
            background:
                radial-gradient(circle at 18% 15%, rgba(16, 185, 129, 0.08), transparent 22%),
                radial-gradient(circle at 80% 25%, rgba(14, 165, 233, 0.08), transparent 24%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0.02));
            filter: blur(10px);
            z-index: -1;
        }

        .soft-panel {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .card-shadow {
            box-shadow:
                0 24px 55px rgba(15, 23, 42, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }
    </style>
</head>

<body class="site-bg text-slate-900 antialiased">
    <div class="min-h-screen content-layer">
        {{-- HEADER --}}
        <header
            class="fixed inset-x-0 top-0 z-50 border-b border-white/60 bg-white/72 glass shadow-sm shadow-slate-200/40">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="#inicio" class="flex items-center gap-3">
                    <div
                        class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md">
                        <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                            class="h-12 w-12 object-contain">
                    </div>

                    <div class="leading-tight">
                        <p class="font-display text-sm font-bold text-slate-950">Franz Tamayo N°3</p>
                        <p class="text-xs text-slate-500">Unidad Educativa Técnico Humanístico</p>
                    </div>
                </a>

                <nav class="hidden items-center gap-7 lg:flex">
                    <a href="#inicio"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Inicio</a>
                    <a href="#institucional"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Institucional</a>
                    <a href="#academico"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Académico</a>
                    <a href="#especialidades"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Especialidades</a>
                    <a href="#vida" class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Vida
                        estudiantil</a>
                    <a href="#sistema"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Sistema</a>
                    <a href="#contacto"
                        class="text-sm font-medium text-slate-600 transition hover:text-emerald-700">Contacto</a>
                </nav>

                <div class="hidden lg:block">
                    <a href="/login"
                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-700">
                        Ingresar
                    </a>
                </div>

                <button id="mobile-menu-button" type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden"
                    aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white px-6 py-5 lg:hidden">
                <div class="flex flex-col gap-4">
                    <a href="#inicio" class="text-sm font-medium text-slate-700">Inicio</a>
                    <a href="#institucional" class="text-sm font-medium text-slate-700">Institucional</a>
                    <a href="#academico" class="text-sm font-medium text-slate-700">Académico</a>
                    <a href="#especialidades" class="text-sm font-medium text-slate-700">Especialidades</a>
                    <a href="#vida" class="text-sm font-medium text-slate-700">Vida estudiantil</a>
                    <a href="#sistema" class="text-sm font-medium text-slate-700">Sistema</a>
                    <a href="#contacto" class="text-sm font-medium text-slate-700">Contacto</a>
                </div>
            </div>
        </header>

        <main>
            {{-- HERO / PRESENTACIÓN PRINCIPAL --}}
            <section id="inicio" class="section-grid overflow-hidden px-6 pb-16 pt-32 lg:px-8 lg:pb-24 lg:pt-30">
                <div class="hero-shell mx-auto max-w-7xl">
                    <div class="grid gap-13 lg:grid-cols-[1.02fr_.98fr] lg:items-center">

                        {{-- LEFT --}}
                        <div class="scroll-reveal-left">
                            <div
                                class="inline-flex items-center rounded-full border border-emerald-200/90 bg-white/90 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-700 shadow-sm shadow-emerald-100/60">
                                Formación técnica • BTH
                            </div>

                            <div
                                class="mt-6 max-w-[46rem] rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:p-10">
                                <p class="text-sm font-medium text-slate-500">
                                    Unidad Educativa Técnico Humanístico
                                </p>

                                <h1
                                    class="font-display mt-4 max-w-[11ch] text-4xl font-extrabold leading-[1.06] tracking-[-0.03em] text-slate-950 sm:text-[3.35rem] lg:text-[4.15rem]">
                                    Franz Tamayo N°3:
                                    <span
                                        class="bg-gradient-to-r from-emerald-600 to-sky-600 bg-clip-text text-transparent">
                                        formación con identidad
                                    </span>
                                    y visión de futuro
                                </h1>

                                <p class="mt-6 max-w-[34rem] text-[1.08rem] leading-8 text-slate-600">
                                    Descubre una institución con trayectoria, especialidades técnicas y una propuesta
                                    educativa
                                    que integra formación humanística, desarrollo práctico y proyección hacia la
                                    educación superior.
                                </p>

                                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                                    <a href="#especialidades"
                                        class="rounded-2xl bg-emerald-600 px-6 py-4 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                                        Ver especialidades
                                    </a>

                                    <a href="#institucional"
                                        class="rounded-2xl border border-slate-300 bg-white px-6 py-4 text-center text-sm font-semibold text-slate-700 transition hover:border-sky-400 hover:text-sky-700">
                                        Conocer el colegio
                                    </a>
                                </div>

                                {{-- MÉTRICAS --}}
                                <div class="mt-10 grid max-w-[33rem] grid-cols-2 gap-4 sm:grid-cols-4">
                                    <div
                                        class="rounded-2xl border border-slate-200/80 bg-white/92 px-4 py-5 text-center shadow-sm shadow-slate-100/80">
                                        <p class="text-[1.9rem] font-extrabold leading-none text-emerald-700">1957</p>
                                        <p class="mt-2 text-sm text-slate-500">Fundación</p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-slate-200/80 bg-white/92 px-4 py-5 text-center shadow-sm shadow-slate-100/80">
                                        <p class="text-[1.9rem] font-extrabold leading-none text-emerald-700">9</p>
                                        <p class="mt-2 text-sm text-slate-500">Especialidades</p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-slate-200/80 bg-white/92 px-4 py-5 text-center shadow-sm shadow-slate-100/80">
                                        <p class="text-[1.9rem] font-extrabold leading-none text-sky-700">BTH</p>
                                        <p class="mt-2 text-sm text-slate-500">Modalidad</p>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-slate-200/80 bg-white/92 px-4 py-5 text-center shadow-sm shadow-slate-100/80">
                                        <p class="text-[1.9rem] font-extrabold leading-none text-sky-700">2</p>
                                        <p class="mt-2 text-sm text-slate-500">Turnos</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT --}}
                        <div class="scroll-reveal-right relative lg:pl-6">
                            <div class="hero-shadow rounded-[2rem] border border-white/80 soft-panel p-5 lg:p-6">
                                <div
                                    class="relative overflow-hidden rounded-[1.7rem] bg-gradient-to-br from-slate-950 via-emerald-950 to-sky-900 p-8 text-white lg:p-9">

                                    {{-- capas visuales suaves --}}
                                    <div
                                        class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,189,248,0.10),transparent_26%),radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.12),transparent_28%)]">
                                    </div>

                                    <div class="relative z-10">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="max-w-[25rem]">
                                                <p class="text-sm text-emerald-200">Unidad Educativa</p>
                                                <h2 class="font-display mt-1 text-[2rem] font-bold leading-tight">
                                                    Franz Tamayo N°3
                                                </h2>
                                            </div>

                                            <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium">
                                                Villa Victoria
                                            </span>
                                        </div>

                                        <p class="mt-6 max-w-[30rem] text-[0.97rem] leading-7 text-slate-200/95">
                                            Una propuesta educativa que fortalece conocimientos, valores y habilidades
                                            técnicas
                                            dentro de una experiencia formativa integral.
                                        </p>

                                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                            <div
                                                class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                                <p class="text-xs uppercase tracking-[0.14em] text-emerald-200">
                                                    Identidad</p>
                                                <p class="mt-2 text-[1.35rem] font-bold leading-tight">
                                                    Bachillerato Técnico
                                                </p>
                                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                                    Formación humanística y técnica integrada.
                                                </p>
                                            </div>

                                            <div
                                                class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                                <p class="text-xs uppercase tracking-[0.14em] text-sky-200">Propuesta
                                                </p>
                                                <p class="mt-2 text-[1.35rem] font-bold leading-tight">
                                                    Especialidades
                                                </p>
                                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                                    Áreas técnicas con proyección profesional.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-8 flex flex-wrap gap-3 text-xs text-slate-300">
                                            <span class="rounded-full bg-white/10 px-3 py-1.5">Historia</span>
                                            <span class="rounded-full bg-white/10 px-3 py-1.5">Formación</span>
                                            <span
                                                class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-300">Futuro</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECCIÓN INSTITUCIONAL --}}
            <section id="institucional" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">

                    {{-- Encabezado --}}
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-title text-sm font-semibold uppercase text-emerald-700">
                            Institucional
                        </span>

                        <h2
                            class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                            Trayectoria, identidad educativa y una formación con compromiso social.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            La Unidad Educativa Técnico Humanístico Franz Tamayo N°3 se proyecta como una institución
                            con identidad técnica, formación humanística y visión de futuro, fortaleciendo el desarrollo
                            integral de sus estudiantes en un entorno educativo con historia y presencia institucional.
                        </p>
                    </div>

                    {{-- Bloque principal --}}
                    <div class="mt-12 grid gap-8 lg:grid-cols-[1.02fr_.98fr] lg:items-start">

                        {{-- Lado izquierdo: historia + identidad --}}
                        <div class="scroll-reveal-left space-y-6">

                            <div class="rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:p-9">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-school text-xl"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                            Historia institucional
                                        </p>

                                        <h3 class="font-display mt-2 text-2xl font-bold text-slate-950">
                                            Una institución con trayectoria en la educación paceña
                                        </h3>

                                        <p class="mt-4 text-[1rem] leading-8 text-slate-600">
                                            El colegio fue fundado el <strong>5 de abril de 1957</strong>, consolidando
                                            una presencia
                                            educativa de varias décadas dentro de la ciudad de La Paz. Su permanencia y
                                            evolución
                                            reflejan una identidad institucional sólida, orientada a formar estudiantes
                                            con preparación
                                            académica, valores y proyección técnica.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-6 card-shadow">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                        <i class="fa-solid fa-location-dot text-lg"></i>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-bold text-slate-950">
                                        Ubicación
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Villa Victoria, calle Virrey Toledo esquina Murguía s/n, en la ciudad de La Paz.
                                    </p>

                                    <p class="mt-2 text-sm leading-7 text-slate-600">
                                        Su localización la conecta con una comunidad educativa activa y un entorno donde
                                        la formación
                                        técnica adquiere valor práctico y social.
                                    </p>
                                </article>

                                <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-6 card-shadow">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-building text-lg"></i>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-bold text-slate-950">
                                        Presencia educativa
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        La institución se reconoce como <strong>Unidad Educativa Plena</strong> y además
                                        cumple
                                        un rol como <strong>Núcleo Tecnológico</strong>, fortaleciendo su propuesta
                                        formativa.
                                    </p>

                                    <p class="mt-2 text-sm leading-7 text-slate-600">
                                        Esta identidad la distingue como un espacio de formación con carácter técnico y
                                        visión de
                                        proyección educativa.
                                    </p>
                                </article>
                            </div>
                        </div>

                        {{-- Lado derecho: galería institucional --}}
                        <div class="scroll-reveal-right">
                            <div class="rounded-[2rem] border border-slate-200/90 soft-panel p-5 card-shadow lg:p-6">

                                <div class="relative overflow-hidden rounded-[1.8rem] group">
                                    <img src="{{ asset('image/infra-edificio1.jpg') }}"
                                        class="h-[18rem] w-full object-cover transition duration-500 group-hover:scale-105">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

                                    <div class="absolute bottom-4 left-4 text-white">
                                        <p class="text-sm">Infraestructura</p>
                                        <h3 class="font-bold text-lg">Edificio principal</h3>
                                    </div>
                                </div>

                                <div
                                    class="mt-5 rounded-[1.5rem] bg-gradient-to-r from-emerald-600 to-sky-600 p-5 text-white">
                                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-100">
                                        Infraestructura
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-bold">
                                        Espacios que fortalecen la experiencia educativa
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-white/90">
                                        La infraestructura institucional acompaña la formación académica y técnica,
                                        brindando ambientes que refuerzan la identidad del colegio y la experiencia
                                        integral del estudiante.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Misión y visión --}}
                    <div class="mt-12 grid gap-6 lg:grid-cols-2">
                        <article
                            class="scroll-reveal-left rounded-[2rem] bg-gradient-to-br from-emerald-600 to-emerald-500 p-8 text-white shadow-2xl shadow-emerald-600/20">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/20">
                                    <i class="fa-solid fa-bullseye text-lg"></i>
                                </div>
                                <h3 class="font-display text-2xl font-bold">Misión</h3>
                            </div>

                            <p class="mt-6 text-sm leading-8 text-white/95">
                                La Unidad Educativa Franz Tamayo Nro. 3 del distrito La Paz – 1 forma bachilleres
                                técnico-humanísticos idóneos, con valores humanos, sólida formación académica, capacidad
                                productiva
                                y vocación de servicio con calidad humana, respondiendo a las necesidades sociales y
                                fortaleciendo
                                el desarrollo productivo sostenible y la conciencia social.
                            </p>
                        </article>

                        <article
                            class="scroll-reveal-right rounded-[2rem] bg-gradient-to-br from-sky-700 to-sky-500 p-8 text-white shadow-2xl shadow-sky-600/20">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/20">
                                    <i class="fa-solid fa-eye text-lg"></i>
                                </div>
                                <h3 class="font-display text-2xl font-bold">Visión</h3>
                            </div>

                            <p class="mt-6 text-sm leading-8 text-white/95">
                                Ser una institución de formación con calidad y calidez, reconocida por su liderazgo en
                                la excelencia
                                académica a partir de una educación integral, holística, socio comunitaria, técnica,
                                tecnológica e
                                investigativa, promoviendo valores socio comunitarios, el vivir bien y el desarrollo
                                sostenible en el
                                Estado Plurinacional de Bolivia.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            {{-- SECCIÓN ACADÉMICA --}}
            <section id="academico" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">

                    {{-- Encabezado --}}
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-title text-sm font-semibold uppercase text-sky-700">
                            Académico
                        </span>

                        <h2
                            class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                            Una propuesta académica centrada en el Bachillerato Técnico Humanístico.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            La propuesta académica del colegio integra formación humanística, desarrollo técnico y
                            orientación
                            hacia decisiones futuras, brindando al estudiante una experiencia educativa más completa,
                            práctica y
                            conectada con su proyección profesional.
                        </p>
                    </div>

                    {{-- Bloque principal --}}
                    <div class="mt-12 grid gap-8 lg:grid-cols-[1.05fr_.95fr] lg:items-start">

                        {{-- Panel principal --}}
                        <div
                            class="scroll-reveal-left rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:p-9">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                                        Modalidad formativa
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-bold text-slate-950">
                                        Bachillerato Técnico Humanístico
                                    </h3>

                                    <p class="mt-4 text-[1rem] leading-8 text-slate-600">
                                        La institución desarrolla una formación académica que combina la base
                                        humanística con el
                                        fortalecimiento técnico, consolidando un perfil estudiantil con capacidades
                                        prácticas,
                                        pensamiento crítico y mayor claridad en su proyección educativa.
                                    </p>
                                </div>
                            </div>

                            {{-- Indicadores internos --}}
                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Enfoque
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-slate-950">
                                        Técnico + humanístico
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Turnos
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-slate-950">
                                        Mañana y tarde
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Proyección
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-slate-950">
                                        Educación superior
                                    </p>
                                </div>
                            </div>

                            {{-- Recorrido académico --}}
                            <div class="mt-10 rounded-[1.8rem] bg-slate-50/80 p-6 ring-1 ring-slate-200">
                                <div class="max-w-2xl">
                                    <h4 class="font-display text-xl font-bold text-slate-950">
                                        Recorrido académico del estudiante
                                    </h4>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        La experiencia académica puede entenderse como un proceso que integra
                                        conocimientos generales,
                                        orientación técnica, especialización progresiva y proyección hacia la toma de
                                        decisiones futuras.
                                    </p>
                                </div>

                                <div class="mt-8 grid gap-4 md:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 font-bold text-emerald-700">
                                                1
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                                    Paso 1
                                                </p>
                                                <h5 class="mt-2 font-display text-xl font-bold text-slate-950">
                                                    Base académica
                                                </h5>
                                                <p class="mt-3 text-sm leading-7 text-slate-600">
                                                    Consolidación de conocimientos generales y desarrollo formativo.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 font-bold text-sky-700">
                                                2
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">
                                                    Paso 2
                                                </p>
                                                <h5 class="mt-2 font-display text-xl font-bold text-slate-950">
                                                    Orientación técnica
                                                </h5>
                                                <p class="mt-3 text-sm leading-7 text-slate-600">
                                                    Acercamiento a áreas técnicas y fortalecimiento de habilidades
                                                    prácticas.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 font-bold text-emerald-700">
                                                3
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                                    Paso 3
                                                </p>
                                                <h5 class="mt-2 font-display text-xl font-bold text-slate-950">
                                                    Especialización
                                                </h5>
                                                <p class="mt-3 text-sm leading-7 text-slate-600">
                                                    Desarrollo de competencias dentro del enfoque técnico elegido.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-4">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 font-bold text-sky-700">
                                                4
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">
                                                    Paso 4
                                                </p>
                                                <h5 class="mt-2 font-display text-xl font-bold text-slate-950">
                                                    Proyección futura
                                                </h5>
                                                <p class="mt-3 text-sm leading-7 text-slate-600">
                                                    Vinculación con aspiraciones académicas y decisiones hacia la
                                                    educación superior.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel lateral --}}
                        <div class="scroll-reveal-right space-y-6">

                            <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-7 card-shadow">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        Organización académica
                                    </h3>
                                </div>

                                <p class="mt-4 text-sm leading-7 text-slate-600">
                                    El colegio organiza su propuesta mediante horarios, calendario académico, estructura
                                    docente,
                                    turnos y una oferta formativa que respalda la continuidad del proceso educativo.
                                </p>
                            </article>

                            <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-7 card-shadow">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 6.253v11.494m-5.747-8.62h11.494M5.253 16.747h13.494" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        Formación integral
                                    </h3>
                                </div>

                                <p class="mt-4 text-sm leading-7 text-slate-600">
                                    La formación académica no se limita al contenido técnico. También fortalece valores,
                                    capacidades
                                    personales, visión social y una preparación más sólida para desenvolverse en
                                    contextos futuros.
                                </p>
                            </article>

                            <div
                                class="rounded-[2rem] bg-gradient-to-br from-sky-700 to-emerald-600 p-7 text-white shadow-2xl shadow-sky-600/20">
                                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-sky-100">
                                    Proyección educativa
                                </p>

                                <h3 class="font-display mt-3 text-2xl font-bold">
                                    Una base que conecta aprendizaje, técnica y futuro.
                                </h3>

                                <p class="mt-4 text-sm leading-8 text-white/90">
                                    La dimensión académica del colegio fortalece una experiencia que no solo prepara al
                                    estudiante
                                    para concluir su etapa escolar, sino también para proyectarse con mayor claridad
                                    hacia estudios
                                    superiores y decisiones vocacionales.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </section>

            {{-- SECCIÓN ESPECIALIDADES --}}
            <section id="especialidades" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">

                    {{-- Encabezado --}}
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-title text-sm font-semibold uppercase text-emerald-700">
                            Especialidades
                        </span>

                        <h2
                            class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                            Especialidades técnicas que fortalecen talento, práctica y proyección.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            La propuesta técnica del colegio permite al estudiante explorar distintas áreas de
                            formación,
                            desarrollar habilidades específicas y construir una experiencia educativa más conectada con
                            sus intereses
                            y su futuro profesional.
                        </p>
                    </div>

                    {{-- bloque superior --}}
                    <div class="mt-12 grid gap-8 lg:grid-cols-[1.02fr_.98fr] lg:items-start">
                        <div
                            class="scroll-reveal-left rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:p-9">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 6.253v11.494m-5.747-8.62h11.494M5.253 16.747h13.494" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                        Formación técnica
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-bold text-slate-950">
                                        Una oferta que conecta aprendizaje y vocación
                                    </h3>

                                    <p class="mt-4 text-[1rem] leading-8 text-slate-600">
                                        Cada especialidad representa una oportunidad para fortalecer habilidades
                                        concretas,
                                        explorar intereses y construir una proyección más clara hacia estudios
                                        superiores,
                                        emprendimientos o espacios laborales relacionados con el área técnica elegida.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="scroll-reveal-right rounded-[2rem] bg-gradient-to-br from-emerald-600 to-sky-600 p-8 text-white shadow-2xl shadow-emerald-600/20">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-100">
                                Proyección formativa
                            </p>

                            <h3 class="font-display mt-3 text-2xl font-bold">
                                Especialidades que amplían el horizonte del estudiante
                            </h3>

                            <p class="mt-4 text-sm leading-8 text-white/90">
                                La diversidad de áreas técnicas fortalece la identidad del colegio y permite que cada
                                estudiante
                                viva una experiencia más práctica, diferenciada y conectada con sus capacidades.
                            </p>
                        </div>
                    </div>

                    @php
                        $especialidades = [
                            [
                                'nombre' => 'Sistemas Informáticos',
                                'imagen' => 'image/esp-sistemas.jpg',
                                'etiqueta' => 'Tecnología',
                                'descripcion' => 'Fortalece pensamiento lógico, organización digital y capacidades vinculadas con herramientas tecnológicas.',
                            ],
                            [
                                'nombre' => 'Electrónica',
                                'imagen' => 'image/esp-electronica.jpg',
                                'etiqueta' => 'Técnica',
                                'descripcion' => 'Desarrolla capacidades de análisis, precisión y comprensión del funcionamiento de sistemas electrónicos.',
                            ],
                            [
                                'nombre' => 'Contabilidad',
                                'imagen' => 'image/esp-contabilidad.jpg',
                                'etiqueta' => 'Gestión',
                                'descripcion' => 'Promueve orden, análisis numérico y comprensión de procesos relacionados con administración y control.',
                            ],
                            [
                                'nombre' => 'Gastronomía',
                                'imagen' => 'image/esp-gastronomia.jpg',
                                'etiqueta' => 'Creatividad',
                                'descripcion' => 'Integra creatividad, técnica y disciplina dentro de una experiencia formativa práctica y aplicada.',
                            ],
                            [
                                'nombre' => 'Textiles y Confecciones',
                                'imagen' => 'image/esp-textiles.jpg',
                                'etiqueta' => 'Diseño',
                                'descripcion' => 'Fortalece capacidades de elaboración, detalle, diseño aplicado y trabajo técnico dentro del área textil.',
                            ],
                            [
                                'nombre' => 'Mecánica Industrial',
                                'imagen' => 'image/esp-mecanica-industrial.jpg',
                                'etiqueta' => 'Industria',
                                'descripcion' => 'Desarrolla habilidades orientadas al análisis de maquinaria, procesos y soluciones técnicas de tipo industrial.',
                            ],
                            [
                                'nombre' => 'Mecánica Automotriz',
                                'imagen' => 'image/esp-mecanica-automotriz.jpg',
                                'etiqueta' => 'Automotriz',
                                'descripcion' => 'Permite comprender sistemas del automóvil, diagnóstico técnico y procesos de mantenimiento aplicado.',
                            ],
                            [
                                'nombre' => 'Carpintería en Madera y Metal',
                                'imagen' => 'image/esp-carpinteria.jpg',
                                'etiqueta' => 'Producción',
                                'descripcion' => 'Integra creatividad, diseño técnico y elaboración práctica en materiales como madera y metal.',
                            ],
                            [
                                'nombre' => 'Belleza Integral',
                                'imagen' => 'image/esp-belleza.jpg',
                                'etiqueta' => 'Estética',
                                'descripcion' => 'Refuerza capacidades técnicas, presentación, atención especializada y proyección hacia servicios estéticos.',
                            ],
                        ];
                    @endphp

                    {{-- Grid completo de especialidades --}}
                    <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($especialidades as $item)
                            <article
                                class="group scroll-reveal overflow-hidden rounded-[2rem] border border-slate-200/90 soft-panel card-shadow transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset($item['imagen']) }}" alt="Especialidad de {{ $item['nombre'] }}"
                                        class="h-60 w-full object-cover transition duration-500 group-hover:scale-[1.05]">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent">
                                    </div>

                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span
                                            class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                            {{ $item['etiqueta'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        {{ $item['nombre'] }}
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        {{ $item['descripcion'] }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
            {{-- SECCIÓN VIDA ESTUDIANTIL --}}
            <section id="vida" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">

                    {{-- Encabezado --}}
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-title text-sm font-semibold uppercase text-sky-700">
                            Vida estudiantil
                        </span>

                        <h2
                            class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                            Una experiencia educativa que también se vive en comunidad.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            La identidad del colegio también se fortalece a través de actividades que promueven
                            expresión,
                            participación, creatividad y sentido de pertenencia, ampliando la experiencia estudiantil
                            más allá del aula.
                        </p>
                    </div>

                    {{-- Contenido principal --}}
                    <div class="mt-12 grid gap-8 lg:grid-cols-[.92fr_1.08fr] lg:items-start">

                        {{-- Texto principal --}}
                        <div class="scroll-reveal-left space-y-6">

                            <div class="rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:p-9">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                                            Comunidad educativa
                                        </p>

                                        <h3 class="font-display mt-2 text-2xl font-bold text-slate-950">
                                            Espacios donde el estudiante participa, crea y se proyecta
                                        </h3>

                                        <p class="mt-4 text-[1rem] leading-8 text-slate-600">
                                            La vida estudiantil del colegio se refleja en actividades que fortalecen la
                                            identidad
                                            institucional, la expresión artística, la participación cultural y el
                                            vínculo de los
                                            estudiantes con su comunidad educativa.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-6 card-shadow">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 19V6l12-3v13M9 19c0 1.105-1.79 2-4 2s-4-.895-4-2 1.79-2 4-2 4 .895 4 2zm12-3c0 1.105-1.79 2-4 2s-4-.895-4-2 1.79-2 4-2 4 .895 4 2z" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-bold text-slate-950">
                                        Expresión y talento
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Actividades culturales, artísticas y audiovisuales permiten visibilizar
                                        capacidades,
                                        creatividad e identidad estudiantil.
                                    </p>
                                </article>

                                <article class="rounded-[2rem] border border-slate-200/90 soft-panel p-6 card-shadow">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-bold text-slate-950">
                                        Identidad institucional
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Las actividades fortalecen el sentido de pertenencia y el vínculo entre
                                        estudiantes,
                                        docentes e institución.
                                    </p>
                                </article>
                            </div>
                        </div>

                        {{-- Galería de actividades --}}
                        <div class="scroll-reveal-right grid gap-5 sm:grid-cols-2">

                            <article
                                class="group overflow-hidden rounded-[2rem] border border-slate-200/90 soft-panel card-shadow transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset('image/actividad-revista-2018.jpg') }}"
                                        alt="Revista institucional El Gran Tamayo 2018"
                                        class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.05]">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent">
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span
                                            class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                            Revista
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        El Gran Tamayo 2018
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Publicación institucional que refleja identidad, participación y expresión
                                        estudiantil.
                                    </p>
                                </div>
                            </article>

                            <article
                                class="group overflow-hidden rounded-[2rem] border border-slate-200/90 soft-panel card-shadow transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset('image/actividad-revista-2019.jpg') }}"
                                        alt="Revista institucional El Gran Tamayo 2019"
                                        class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.05]">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent">
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span
                                            class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                            Comunidad
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        El Gran Tamayo 2019
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Continuidad de una propuesta institucional que fortalece memoria, identidad y
                                        participación.
                                    </p>
                                </div>
                            </article>

                            <article
                                class="group overflow-hidden rounded-[2rem] border border-slate-200/90 soft-panel card-shadow transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset('image/teatro-historico.jpg') }}"
                                        alt="Actividad de teatro histórico"
                                        class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.05]">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent">
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span
                                            class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                            Cultura
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        Teatro Histórico
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Espacio de representación, expresión escénica y construcción de experiencia
                                        cultural estudiantil.
                                    </p>
                                </div>
                            </article>

                            <article
                                class="group overflow-hidden rounded-[2rem] border border-slate-200/90 soft-panel card-shadow transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset('image/feria-sin-fronteras.jpg') }}"
                                        alt="Actividad Feria sin Fronteras"
                                        class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.05]">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent">
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span
                                            class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                            Participación
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="font-display text-xl font-bold text-slate-950">
                                        Feria sin Fronteras
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">
                                        Actividad que fortalece visibilidad institucional, creatividad y presencia de la
                                        comunidad educativa.
                                    </p>
                                </div>
                            </article>

                        </div>
                    </div>
                </div>
            </section>
            {{-- SECCIÓN VIDA ESTUDIANTIL --}}
            <section id="vida" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">

                    {{-- Encabezado --}}
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-title text-sm font-semibold uppercase text-sky-700">
                            Vida estudiantil
                        </span>

                        <h2
                            class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                            Una experiencia educativa que también se vive desde la creatividad y la participación.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            La vida estudiantil del colegio también se fortalece en espacios donde los estudiantes
                            expresan ideas,
                            participan activamente y construyen experiencias significativas que amplían su formación más
                            allá del aula.
                        </p>
                    </div>

                    {{-- Actividad protagonista --}}
                    <div class="mt-12 scroll-reveal">
                        <article
                            class="overflow-hidden rounded-[2.2rem] border border-slate-200/90 soft-panel card-shadow">
                            <div class="grid lg:grid-cols-[1.08fr_.92fr] lg:items-stretch">

                                {{-- Imagen principal --}}
                                <div class="relative min-h-[320px] overflow-hidden lg:min-h-[460px]">
                                    <img src="{{ asset('image/cortometrajes.jpg') }}" alt="Actividad de cortometrajes"
                                        class="absolute inset-0 h-full w-full object-cover">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-950/35 to-transparent">
                                    </div>

                                    <div class="absolute left-6 top-6">
                                        <span
                                            class="rounded-full bg-white/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm">
                                            Actividad destacada
                                        </span>
                                    </div>

                                    <div class="absolute bottom-6 left-6 right-6 max-w-xl text-white">
                                        <p class="text-sm font-medium text-sky-100">Expresión audiovisual</p>

                                        <h3 class="font-display mt-2 text-3xl font-bold leading-tight sm:text-4xl">
                                            Cortometrajes
                                        </h3>

                                        <p class="mt-4 text-sm leading-7 text-white/90 sm:text-base">
                                            Un espacio donde la creatividad, la narrativa visual y la producción
                                            audiovisual
                                            permiten al estudiante expresarse, colaborar y proyectar ideas dentro de una
                                            experiencia
                                            formativa visible y significativa.
                                        </p>
                                    </div>
                                </div>

                                {{-- Contenido lateral --}}
                                <div class="flex flex-col justify-between p-8 lg:p-10">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                                            Comunidad educativa
                                        </p>

                                        <h4 class="font-display mt-3 text-2xl font-bold text-slate-950">
                                            Una actividad que conecta identidad, participación y talento
                                        </h4>

                                        <p class="mt-5 text-[1rem] leading-8 text-slate-600">
                                            La actividad de cortometrajes fortalece capacidades de expresión, trabajo
                                            colaborativo,
                                            creatividad y comunicación, mostrando cómo la vida estudiantil también puede
                                            convertirse
                                            en un espacio de formación con impacto cultural e institucional.
                                        </p>
                                    </div>

                                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                Enfoque
                                            </p>
                                            <p class="mt-2 text-lg font-bold text-slate-950">
                                                Creatividad audiovisual
                                            </p>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                Valor formativo
                                            </p>
                                            <p class="mt-2 text-lg font-bold text-slate-950">
                                                Expresión e identidad
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-8 rounded-[1.6rem] bg-gradient-to-r from-slate-950 to-slate-800 p-6 text-white">
                                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-300">
                                            Experiencia estudiantil
                                        </p>

                                        <p class="mt-3 text-sm leading-8 text-slate-300">
                                            La participación en actividades como esta complementa la formación académica
                                            y técnica,
                                            aportando vivencias que fortalecen la presencia del estudiante dentro de la
                                            comunidad educativa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
    </div>
    </section>

    {{-- SECCIÓN SISTEMA / INNOVACIÓN --}}
    <section id="sistema" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">

            {{-- Encabezado --}}
            <div class="scroll-reveal max-w-3xl">
                <span class="section-title text-sm font-semibold uppercase text-emerald-700">
                    Sistema e innovación
                </span>

                <h2
                    class="font-display mt-3 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl lg:text-[2.8rem]">
                    Una propuesta tecnológica orientada a fortalecer la proyección educativa del estudiante.
                </h2>

                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                    Además de su propuesta institucional y académica, el colegio se proyecta hacia una visión
                    innovadora,
                    donde la orientación educativa puede fortalecerse con herramientas tecnológicas que acompañen de
                    forma
                    más clara el descubrimiento de intereses, capacidades y posibilidades futuras.
                </p>
            </div>

            {{-- Contenido principal --}}
            <div
                class="mt-12 grid gap-8 rounded-[2rem] border border-slate-200/90 soft-panel p-8 card-shadow lg:grid-cols-[1.05fr_.95fr] lg:p-12">

                {{-- Lado izquierdo --}}
                <div class="scroll-reveal-left">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9.75 3v2.25M14.25 3v2.25M9.75 18.75V21M14.25 18.75V21M3 9.75h2.25M3 14.25h2.25M18.75 9.75H21M18.75 14.25H21M7.5 7.5h9v9h-9v-9z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Innovación educativa
                            </p>

                            <h3 class="font-display mt-2 text-2xl font-bold text-slate-950">
                                Un sistema pensado para acompañar decisiones con mayor claridad
                            </h3>

                            <p class="mt-4 text-[1rem] leading-8 text-slate-600">
                                Esta propuesta busca integrar tecnología al proceso de orientación educativa,
                                permitiendo que el estudiante pueda reconocer con mayor claridad su perfil, sus
                                intereses
                                y su relación con posibles trayectorias formativas futuras.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Propósito
                            </p>
                            <p class="mt-2 text-lg font-bold text-slate-950">
                                Orientación vocacional
                            </p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Acompañar al estudiante en la comprensión de su perfil y sus posibilidades futuras.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Enfoque
                            </p>
                            <p class="mt-2 text-lg font-bold text-slate-950">
                                Tecnología con sentido educativo
                            </p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Integrar herramientas visuales y de análisis dentro de una experiencia comprensible.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[1.7rem] border border-slate-200 bg-slate-50/80 p-6">
                        <h4 class="font-display text-xl font-bold text-slate-950">
                            ¿Qué aporta esta propuesta?
                        </h4>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                                <p class="text-sm font-semibold text-emerald-700">Exploración</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Ayuda a identificar intereses y afinidades.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                                <p class="text-sm font-semibold text-sky-700">Visualización</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Presenta información de forma más clara y accesible.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                                <p class="text-sm font-semibold text-emerald-700">Proyección</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Vincula resultados con decisiones futuras.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lado derecho visual --}}
                <div
                    class="scroll-reveal-right rounded-[1.8rem] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 text-white shadow-2xl lg:p-9">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-300">Vista conceptual</p>
                            <h3 class="font-display mt-1 text-2xl font-bold">
                                Orientación inteligente
                            </h3>
                        </div>

                        <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-300">
                            Innovación
                        </span>
                    </div>

                    <div class="mt-8 space-y-4">

                        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-slate-300">Compatibilidad vocacional</p>
                                <p class="text-sm font-semibold text-emerald-300">Visual</p>
                            </div>

                            <div class="mt-4 h-3 rounded-full bg-white/10">
                                <div class="h-3 w-[82%] rounded-full bg-gradient-to-r from-emerald-400 to-sky-400">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                <p class="text-sm text-slate-300">Perfil</p>
                                <p class="mt-2 text-xl font-bold">Académico</p>
                            </div>

                            <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                <p class="text-sm text-slate-300">Intereses</p>
                                <p class="mt-2 text-xl font-bold">Explorados</p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                            <p class="text-sm text-slate-300">Ruta conceptual</p>

                            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-300">
                                <span class="rounded-full bg-white/10 px-3 py-1">Colegio</span>
                                <span>→</span>
                                <span class="rounded-full bg-white/10 px-3 py-1">Especialidad</span>
                                <span>→</span>
                                <span
                                    class="rounded-full bg-emerald-400/15 px-3 py-1 text-emerald-300">Proyección</span>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                            <p class="text-sm text-slate-300">Enfoque</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs text-emerald-300">
                                    Orientación
                                </span>
                                <span class="rounded-full bg-sky-400/15 px-3 py-1 text-xs text-sky-300">
                                    Tecnología
                                </span>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-slate-200">
                                    Innovación educativa
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-[1.5rem] bg-white/5 p-5 ring-1 ring-white/10">
                        <p class="text-sm text-slate-300">
                            Esta propuesta tecnológica no reemplaza el acompañamiento educativo,
                            sino que lo fortalece con una experiencia más visual, comprensible y orientada al futuro.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECCIÓN CONTACTO / UBICACIÓN --}}
    <section id="contacto" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <div
                class="rounded-[2rem] bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-8 py-14 text-white shadow-2xl lg:px-14">
                <div class="grid gap-10 lg:grid-cols-[1.05fr_.95fr] lg:items-center">

                    {{-- Lado izquierdo --}}
                    <div class="scroll-reveal-left">
                        <span class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-100">
                            Ubicación y presencia institucional
                        </span>

                        <h2 class="font-display mt-4 text-3xl font-bold leading-tight sm:text-4xl">
                            Conoce dónde se encuentra la institución y dónde seguir su actividad.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-white/90">
                            La Unidad Educativa Técnico Humanístico Franz Tamayo N°3 se proyecta como una institución
                            con
                            identidad, trayectoria y presencia educativa. Aquí puedes ubicarla físicamente y explorar
                            sus
                            espacios de difusión institucional.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-100">
                                    Dirección
                                </p>
                                <p class="mt-3 text-sm leading-7 text-white/95">
                                    Villa Victoria, calle Virrey Toledo esquina Murguía s/n, La Paz.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-100">
                                    Presencia digital
                                </p>
                                <p class="mt-3 text-sm leading-7 text-white/95">
                                    Blog institucional y página oficial en Facebook para conocer actividades,
                                    publicaciones
                                    y novedades del colegio.
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                            <a href="https://franztamayo3.blogspot.com/" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-4 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                Visitar blog institucional
                            </a>

                            <a href="https://www.facebook.com/p/Unidad-Educativa-Franz-Tamayo-Nro-3-100027191873862/?locale=es_LA"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/30 bg-white/10 px-6 py-4 text-sm font-semibold text-white transition hover:bg-white/20">
                                Ver Facebook oficial
                            </a>
                        </div>
                    </div>

                    {{-- Lado derecho --}}
                    <div
                        class="scroll-reveal-right rounded-[1.8rem] border border-white/20 bg-white/10 p-6 backdrop-blur-sm">
                        <h3 class="font-display text-2xl font-bold">Ubicación institucional</h3>

                        <p class="mt-4 text-sm leading-7 text-white/90">
                            La institución se encuentra en una zona con presencia educativa activa dentro de la ciudad
                            de La Paz,
                            consolidando su rol como espacio formativo técnico y humanístico.
                        </p>

                        <div class="mt-6 overflow-hidden rounded-2xl border border-white/20 bg-white/10">
                            <iframe
                                src="https://www.google.com/maps?q=Virrey%20Toledo%20esquina%20Murguia%20La%20Paz%20Bolivia&output=embed"
                                width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" title="Mapa de ubicación Franz Tamayo N°3"
                                class="h-64 w-full"></iframe>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <a href="https://franztamayo3.blogspot.com/" target="_blank" rel="noopener noreferrer"
                                class="rounded-2xl border border-white/20 bg-white/10 p-4 text-sm font-semibold text-white transition hover:bg-white/20">
                                Blog institucional
                            </a>

                            <a href="https://www.facebook.com/p/Unidad-Educativa-Franz-Tamayo-Nro-3-100027191873862/?locale=es_LA"
                                target="_blank" rel="noopener noreferrer"
                                class="rounded-2xl border border-white/20 bg-white/10 p-4 text-sm font-semibold text-white transition hover:bg-white/20">
                                Facebook oficial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-white/60 bg-white/72 py-10 glass">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                {{-- Marca institucional --}}
                <div>
                    <p class="font-display text-sm font-bold text-slate-950">
                        Unidad Educativa Franz Tamayo N°3
                    </p>
                    <p class="text-xs text-slate-500">
                        Formación técnica y humanística con proyección educativa
                    </p>
                </div>

                {{-- Proyecto --}}
                <div class="text-left lg:text-center">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">
                        Sistema desarrollado
                    </p>
                    <p class="font-display text-sm font-semibold text-emerald-700">
                        SAVP – TIS 3
                    </p>
                </div>

                {{-- Derechos --}}
                <div class="text-left lg:text-right">
                    <p class="text-sm text-slate-500">
                        © {{ date('Y') }} Todos los derechos reservados.
                    </p>
                    <p class="text-xs text-slate-400">
                        Proyecto académico de ingeniería de sistemas
                    </p>
                </div>

            </div>
        </div>
    </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
            });

            const revealItems = document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right');

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -40px 0px'
            });

            revealItems.forEach(item => revealObserver.observe(item));
        });
    </script>
</body>

</html>