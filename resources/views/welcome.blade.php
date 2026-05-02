<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Franz Tamayo N°3 | SAVP</title>

    <meta name="description"
        content="Landing institucional de la Unidad Educativa Técnico Humanístico Franz Tamayo N°3 y sistema SAVP.">

    {{-- Evita parpadeo al cargar modo oscuro --}}
    <script>
        (function () {
            const theme = localStorage.getItem('savp-theme') || 'light';

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|poppins:500,600,700,800,900&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --landing-bg: #f5fbf8;
            --landing-bg-soft: #f8fafc;
            --landing-surface: rgba(255, 255, 255, .84);
            --landing-surface-strong: rgba(255, 255, 255, .94);
            --landing-surface-soft: rgba(248, 250, 252, .88);
            --landing-text: #0f172a;
            --landing-text-soft: #334155;
            --landing-muted: #64748b;
            --landing-border: rgba(203, 213, 225, .78);
            --landing-primary: #059669;
            --landing-primary-strong: #047857;
            --landing-primary-soft: rgba(16, 185, 129, .12);
            --landing-sky: #0284c7;
            --landing-sky-soft: rgba(14, 165, 233, .12);
            --landing-violet: #7c3aed;
            --landing-violet-soft: rgba(124, 58, 237, .12);
            --landing-warning: #d97706;
            --landing-warning-soft: rgba(245, 158, 11, .13);
            --landing-shadow: 0 24px 70px rgba(15, 23, 42, .10);
            --landing-shadow-soft: 0 18px 45px rgba(15, 23, 42, .08);
        }

        html.dark {
            --landing-bg: #07111f;
            --landing-bg-soft: #0f172a;
            --landing-surface: rgba(15, 23, 42, .78);
            --landing-surface-strong: rgba(15, 23, 42, .92);
            --landing-surface-soft: rgba(30, 41, 59, .70);
            --landing-text: #f8fafc;
            --landing-text-soft: #dbeafe;
            --landing-muted: #94a3b8;
            --landing-border: rgba(71, 85, 105, .75);
            --landing-primary: #34d399;
            --landing-primary-strong: #10b981;
            --landing-primary-soft: rgba(52, 211, 153, .13);
            --landing-sky: #38bdf8;
            --landing-sky-soft: rgba(56, 189, 248, .13);
            --landing-violet: #a78bfa;
            --landing-violet-soft: rgba(167, 139, 250, .14);
            --landing-warning: #fbbf24;
            --landing-warning-soft: rgba(251, 191, 36, .13);
            --landing-shadow: 0 24px 80px rgba(0, 0, 0, .36);
            --landing-shadow-soft: 0 18px 55px rgba(0, 0, 0, .28);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--landing-bg);
            color: var(--landing-text);
        }

        .font-display {
            font-family: 'Poppins', sans-serif;
        }

        .site-bg {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 8% 6%, color-mix(in srgb, var(--landing-primary) 18%, transparent), transparent 26%),
                radial-gradient(circle at 90% 8%, color-mix(in srgb, var(--landing-sky) 18%, transparent), transparent 28%),
                radial-gradient(circle at 50% 105%, color-mix(in srgb, var(--landing-violet) 10%, transparent), transparent 30%),
                linear-gradient(180deg, var(--landing-bg), var(--landing-bg-soft));
        }

        .site-bg::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                linear-gradient(to right, color-mix(in srgb, var(--landing-muted) 10%, transparent) 1px, transparent 1px),
                linear-gradient(to bottom, color-mix(in srgb, var(--landing-muted) 10%, transparent) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: linear-gradient(to bottom, black, transparent 86%);
            opacity: .56;
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }

        .glass {
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .landing-header {
            background: color-mix(in srgb, var(--landing-surface-strong) 86%, transparent);
            border-color: var(--landing-border);
            box-shadow: 0 10px 35px rgba(15, 23, 42, .08);
        }

        html.dark .landing-header {
            box-shadow: 0 10px 35px rgba(0, 0, 0, .24);
        }

        .soft-panel {
            background: var(--landing-surface);
            border: 1px solid var(--landing-border);
            box-shadow: var(--landing-shadow-soft);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .strong-panel {
            background: var(--landing-surface-strong);
            border: 1px solid var(--landing-border);
            box-shadow: var(--landing-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .mini-panel {
            background: var(--landing-surface-soft);
            border: 1px solid var(--landing-border);
        }

        .section-grid {
            position: relative;
            isolation: isolate;
        }

        .section-grid::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 15% 10%, color-mix(in srgb, var(--landing-primary) 8%, transparent), transparent 24%),
                radial-gradient(circle at 86% 6%, color-mix(in srgb, var(--landing-sky) 8%, transparent), transparent 26%);
            opacity: .72;
        }

        .nav-link {
            color: var(--landing-muted);
            transition: color .2s ease, transform .2s ease;
        }

        .nav-link:hover {
            color: var(--landing-primary);
            transform: translateY(-1px);
        }

        .theme-btn,
        .icon-btn {
            background: var(--landing-surface-strong);
            border: 1px solid var(--landing-border);
            color: var(--landing-text-soft);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .06);
            transition: transform .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
        }

        .theme-btn:hover,
        .icon-btn:hover {
            transform: translateY(-2px);
            color: var(--landing-primary);
            border-color: color-mix(in srgb, var(--landing-primary) 35%, var(--landing-border));
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--landing-primary-strong), var(--landing-sky));
            color: white;
            box-shadow: 0 18px 35px color-mix(in srgb, var(--landing-primary) 24%, transparent);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            filter: saturate(1.08);
            box-shadow: 0 22px 45px color-mix(in srgb, var(--landing-primary) 34%, transparent);
        }

        .btn-secondary {
            background: var(--landing-surface-strong);
            border: 1px solid var(--landing-border);
            color: var(--landing-text-soft);
            transition: transform .2s ease, border-color .2s ease, color .2s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--landing-sky) 42%, var(--landing-border));
            color: var(--landing-sky);
        }

        .section-kicker {
            color: var(--landing-primary);
            letter-spacing: .18em;
        }

        .title-gradient {
            background: linear-gradient(135deg, var(--landing-primary), var(--landing-sky));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .text-main {
            color: var(--landing-text);
        }

        .text-soft {
            color: var(--landing-text-soft);
        }

        .text-muted-custom {
            color: var(--landing-muted);
        }

        .media-card img {
            transition: transform .65s cubic-bezier(.2, .8, .2, 1);
        }

        .media-card:hover img {
            transform: scale(1.055);
        }

        .media-card {
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .media-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--landing-shadow);
            border-color: color-mix(in srgb, var(--landing-primary) 22%, var(--landing-border));
        }

        .scroll-reveal,
        .scroll-reveal-left,
        .scroll-reveal-right,
        .scroll-reveal-scale {
            opacity: 0;
            will-change: transform, opacity;
            transition:
                opacity .85s cubic-bezier(.2, .8, .2, 1),
                transform .85s cubic-bezier(.2, .8, .2, 1);
        }

        .scroll-reveal {
            transform: translateY(28px);
        }

        .scroll-reveal-left {
            transform: translateX(-32px);
        }

        .scroll-reveal-right {
            transform: translateX(32px);
        }

        .scroll-reveal-scale {
            transform: translateY(24px) scale(.98);
        }

        .is-visible {
            opacity: 1;
            transform: translate(0, 0) scale(1);
        }

        .floating {
            animation: floating 7s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        #scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 80;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, var(--landing-primary), var(--landing-sky));
            box-shadow: 0 0 16px color-mix(in srgb, var(--landing-primary) 45%, transparent);
        }

        @media (prefers-reduced-motion: reduce) {
            .scroll-reveal,
            .scroll-reveal-left,
            .scroll-reveal-right,
            .scroll-reveal-scale {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .floating {
                animation: none;
            }

            html {
                scroll-behavior: auto;
            }
        }
    </style>
</head>

<body class="site-bg antialiased">
    <div id="scroll-progress"></div>

    <div class="content-layer min-h-screen">
        {{-- HEADER --}}
        <header class="landing-header glass fixed inset-x-0 top-0 z-50 border-b">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <a href="#inicio" class="flex items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md">
                        <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                            class="h-12 w-12 object-contain">
                    </div>

                    <div class="leading-tight">
                        <p class="font-display text-sm font-black text-main">
                            Franz Tamayo N°3
                        </p>
                        <p class="text-xs text-muted-custom">
                            Unidad Educativa Técnico Humanístico
                        </p>
                    </div>
                </a>

                <nav class="hidden items-center gap-7 lg:flex">
                    <a href="#inicio" class="nav-link text-sm font-semibold">Inicio</a>
                    <a href="#institucional" class="nav-link text-sm font-semibold">Institucional</a>
                    <a href="#academico" class="nav-link text-sm font-semibold">Académico</a>
                    <a href="#especialidades" class="nav-link text-sm font-semibold">Especialidades</a>
                    <a href="#vida" class="nav-link text-sm font-semibold">Vida estudiantil</a>
                    <a href="#sistema" class="nav-link text-sm font-semibold">Sistema</a>
                    <a href="#contacto" class="nav-link text-sm font-semibold">Contacto</a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <button type="button" id="theme-toggle" class="theme-btn inline-flex h-12 w-12 items-center justify-center rounded-2xl"
                        title="Cambiar tema">
                        <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>

                        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="btn-primary rounded-2xl px-5 py-3 text-sm font-bold">
                            Ir al panel
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="btn-primary rounded-2xl px-5 py-3 text-sm font-bold">
                            Ingresar
                        </a>
                    @endauth
                </div>

                <div class="flex items-center gap-2 lg:hidden">
                    <button type="button" id="theme-toggle-mobile"
                        class="theme-btn inline-flex h-11 w-11 items-center justify-center rounded-xl"
                        title="Cambiar tema">
                        <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>

                        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>

                    <button id="mobile-menu-button" type="button"
                        class="icon-btn inline-flex h-11 w-11 items-center justify-center rounded-xl"
                        aria-label="Abrir menú">
                        <svg id="menu-open-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>

                        <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div id="mobile-menu"
                class="hidden border-t px-6 py-5 lg:hidden"
                style="background: var(--landing-surface-strong); border-color: var(--landing-border);">
                <div class="flex flex-col gap-4">
                    <a href="#inicio" class="nav-link text-sm font-semibold">Inicio</a>
                    <a href="#institucional" class="nav-link text-sm font-semibold">Institucional</a>
                    <a href="#academico" class="nav-link text-sm font-semibold">Académico</a>
                    <a href="#especialidades" class="nav-link text-sm font-semibold">Especialidades</a>
                    <a href="#vida" class="nav-link text-sm font-semibold">Vida estudiantil</a>
                    <a href="#sistema" class="nav-link text-sm font-semibold">Sistema</a>
                    <a href="#contacto" class="nav-link text-sm font-semibold">Contacto</a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold">
                            Ir al panel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold">
                            Ingresar
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{-- HERO --}}
            <section id="inicio" class="section-grid overflow-hidden px-6 pb-16 pt-32 lg:px-8 lg:pb-24 lg:pt-36">
                <div class="mx-auto max-w-7xl">
                    <div class="grid gap-10 lg:grid-cols-[1.02fr_.98fr] lg:items-center">
                        <div class="scroll-reveal-left">
                            <div class="inline-flex items-center rounded-full px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] ring-1"
                                style="background: var(--landing-primary-soft); color: var(--landing-primary); --tw-ring-color: color-mix(in srgb, var(--landing-primary) 28%, transparent);">
                                Formación técnica • BTH • SAVP
                            </div>

                            <div class="strong-panel mt-6 rounded-[2rem] p-8 lg:p-10">
                                <p class="text-sm font-semibold text-muted-custom">
                                    Unidad Educativa Técnico Humanístico
                                </p>

                                <h1 class="font-display mt-4 max-w-[12ch] text-4xl font-black leading-[1.06] tracking-[-0.04em] text-main sm:text-[3.35rem] lg:text-[4.35rem]">
                                    Franz Tamayo N°3:
                                    <span class="title-gradient">
                                        formación con identidad
                                    </span>
                                    y visión de futuro
                                </h1>

                                <p class="mt-6 max-w-[35rem] text-[1.08rem] leading-8 text-soft">
                                    Descubre una institución con trayectoria, especialidades técnicas y una propuesta
                                    educativa que integra formación humanística, desarrollo práctico y proyección hacia
                                    la educación superior.
                                </p>

                                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                                    <a href="#especialidades"
                                        class="btn-primary rounded-2xl px-6 py-4 text-center text-sm font-bold">
                                        Ver especialidades
                                    </a>

                                    <a href="#institucional"
                                        class="btn-secondary rounded-2xl px-6 py-4 text-center text-sm font-bold">
                                        Conocer el colegio
                                    </a>
                                </div>

                                <div class="mt-10 grid max-w-[35rem] grid-cols-2 gap-4 sm:grid-cols-4">
                                    <div class="mini-panel rounded-2xl px-4 py-5 text-center">
                                        <p class="text-[1.9rem] font-black leading-none" style="color: var(--landing-primary);">1957</p>
                                        <p class="mt-2 text-sm text-muted-custom">Fundación</p>
                                    </div>

                                    <div class="mini-panel rounded-2xl px-4 py-5 text-center">
                                        <p class="text-[1.9rem] font-black leading-none" style="color: var(--landing-primary);">9</p>
                                        <p class="mt-2 text-sm text-muted-custom">Especialidades</p>
                                    </div>

                                    <div class="mini-panel rounded-2xl px-4 py-5 text-center">
                                        <p class="text-[1.9rem] font-black leading-none" style="color: var(--landing-sky);">BTH</p>
                                        <p class="mt-2 text-sm text-muted-custom">Modalidad</p>
                                    </div>

                                    <div class="mini-panel rounded-2xl px-4 py-5 text-center">
                                        <p class="text-[1.9rem] font-black leading-none" style="color: var(--landing-sky);">2</p>
                                        <p class="mt-2 text-sm text-muted-custom">Turnos</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="scroll-reveal-right relative lg:pl-6">
                            <div class="floating strong-panel rounded-[2rem] p-5 lg:p-6">
                                <div class="relative overflow-hidden rounded-[1.7rem] bg-gradient-to-br from-slate-950 via-emerald-950 to-sky-900 p-8 text-white lg:p-9">
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,189,248,0.14),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.16),transparent_30%)]"></div>

                                    <div class="relative z-10">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-sm text-emerald-200">Unidad Educativa</p>
                                                <h2 class="font-display mt-1 text-[2rem] font-bold leading-tight">
                                                    Franz Tamayo N°3
                                                </h2>
                                            </div>

                                            <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/10">
                                                Villa Victoria
                                            </span>
                                        </div>

                                        <p class="mt-6 max-w-[30rem] text-[0.97rem] leading-7 text-slate-200/95">
                                            Una propuesta educativa que fortalece conocimientos, valores y habilidades
                                            técnicas dentro de una experiencia formativa integral.
                                        </p>

                                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                                <p class="text-xs uppercase tracking-[0.14em] text-emerald-200">Identidad</p>
                                                <p class="mt-2 text-[1.35rem] font-bold leading-tight">Bachillerato Técnico</p>
                                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                                    Formación humanística y técnica integrada.
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                                <p class="text-xs uppercase tracking-[0.14em] text-sky-200">Propuesta</p>
                                                <p class="mt-2 text-[1.35rem] font-bold leading-tight">Especialidades</p>
                                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                                    Áreas técnicas con proyección profesional.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-8 flex flex-wrap gap-3 text-xs text-slate-300">
                                            <span class="rounded-full bg-white/10 px-3 py-1.5">Historia</span>
                                            <span class="rounded-full bg-white/10 px-3 py-1.5">Formación</span>
                                            <span class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-300">Futuro</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="absolute -bottom-5 -right-4 hidden rounded-3xl p-5 shadow-2xl lg:block"
                                style="background: var(--landing-surface-strong); border: 1px solid var(--landing-border);">
                                <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--landing-primary);">
                                    SAVP
                                </p>
                                <p class="mt-1 text-sm font-bold text-main">
                                    Sistema académico administrativo
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- INSTITUCIONAL --}}
            <section id="institucional" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-kicker text-sm font-black uppercase">
                            Institucional
                        </span>

                        <h2 class="font-display mt-3 text-3xl font-black leading-tight text-main sm:text-4xl lg:text-[2.8rem]">
                            Trayectoria, identidad educativa y formación con compromiso social.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-soft">
                            La Unidad Educativa Técnico Humanístico Franz Tamayo N°3 se proyecta como una institución
                            con identidad técnica, formación humanística y visión de futuro.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-8 lg:grid-cols-[1.02fr_.98fr] lg:items-start">
                        <div class="scroll-reveal-left space-y-6">
                            <div class="soft-panel rounded-[2rem] p-8 lg:p-9">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                                        style="background: var(--landing-primary-soft); color: var(--landing-primary);">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M3.75 21h16.5M6 21V6.75L12 3l6 3.75V21M9 9.75h.01M12 9.75h.01M15 9.75h.01M9 13.5h.01M12 13.5h.01M15 13.5h.01M10.5 21v-3h3v3" />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.18em]" style="color: var(--landing-primary);">
                                            Historia institucional
                                        </p>

                                        <h3 class="font-display mt-2 text-2xl font-black text-main">
                                            Una institución con trayectoria en la educación paceña
                                        </h3>

                                        <p class="mt-4 text-[1rem] leading-8 text-soft">
                                            El colegio fue fundado el <strong>5 de abril de 1957</strong>, consolidando
                                            una presencia educativa de varias décadas dentro de la ciudad de La Paz.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <article class="soft-panel rounded-[2rem] p-6">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl"
                                        style="background: var(--landing-sky-soft); color: var(--landing-sky);">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-black text-main">Ubicación</h3>
                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        Villa Victoria, calle Virrey Toledo esquina Murguía s/n, ciudad de La Paz.
                                    </p>
                                </article>

                                <article class="soft-panel rounded-[2rem] p-6">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl"
                                        style="background: var(--landing-primary-soft); color: var(--landing-primary);">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
                                        </svg>
                                    </div>

                                    <h3 class="font-display mt-4 text-xl font-black text-main">Presencia educativa</h3>
                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        Unidad Educativa Plena y Núcleo Tecnológico con carácter técnico-humanístico.
                                    </p>
                                </article>
                            </div>
                        </div>

                        <div class="scroll-reveal-right">
                            <div class="soft-panel rounded-[2rem] p-5 lg:p-6">
                                <div class="media-card relative overflow-hidden rounded-[1.8rem]">
                                    <img src="{{ asset('image/infra-edificio1.jpg') }}"
                                        alt="Infraestructura de la Unidad Educativa Franz Tamayo N°3"
                                        class="h-[18rem] w-full object-cover">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/65 to-transparent"></div>

                                    <div class="absolute bottom-4 left-4 text-white">
                                        <p class="text-sm">Infraestructura</p>
                                        <h3 class="text-lg font-bold">Edificio principal</h3>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-[1.5rem] bg-gradient-to-r from-emerald-600 to-sky-600 p-5 text-white">
                                    <p class="text-sm font-black uppercase tracking-[0.16em] text-emerald-100">
                                        Infraestructura
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-bold">
                                        Espacios que fortalecen la experiencia educativa
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-white/90">
                                        Ambientes que acompañan la formación académica, técnica e institucional.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-2">
                        <article class="scroll-reveal-left rounded-[2rem] bg-gradient-to-br from-emerald-600 to-emerald-500 p-8 text-white shadow-2xl shadow-emerald-600/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 21s7.5-4.5 7.5-11.25A7.5 7.5 0 0 0 4.5 9.75C4.5 16.5 12 21 12 21Z" />
                                    </svg>
                                </div>
                                <h3 class="font-display text-2xl font-black">Misión</h3>
                            </div>

                            <p class="mt-6 text-sm leading-8 text-white/95">
                                Formar bachilleres técnico-humanísticos idóneos, con valores humanos, sólida formación
                                académica, capacidad productiva y vocación de servicio.
                            </p>
                        </article>

                        <article class="scroll-reveal-right rounded-[2rem] bg-gradient-to-br from-sky-700 to-sky-500 p-8 text-white shadow-2xl shadow-sky-600/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </div>
                                <h3 class="font-display text-2xl font-black">Visión</h3>
                            </div>

                            <p class="mt-6 text-sm leading-8 text-white/95">
                                Ser una institución de calidad y calidez, reconocida por su liderazgo académico,
                                formación integral, técnica, tecnológica e investigativa.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            {{-- ACADÉMICO --}}
            <section id="academico" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-kicker text-sm font-black uppercase" style="color: var(--landing-sky);">
                            Académico
                        </span>

                        <h2 class="font-display mt-3 text-3xl font-black leading-tight text-main sm:text-4xl lg:text-[2.8rem]">
                            Una propuesta académica centrada en el Bachillerato Técnico Humanístico.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-soft">
                            Integra formación humanística, desarrollo técnico y orientación hacia decisiones futuras.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-8 lg:grid-cols-[1.05fr_.95fr] lg:items-start">
                        <div class="scroll-reveal-left soft-panel rounded-[2rem] p-8 lg:p-9">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                                    style="background: var(--landing-sky-soft); color: var(--landing-sky);">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black uppercase tracking-[0.18em]" style="color: var(--landing-sky);">
                                        Modalidad formativa
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-black text-main">
                                        Bachillerato Técnico Humanístico
                                    </h3>

                                    <p class="mt-4 text-[1rem] leading-8 text-soft">
                                        Combina base humanística, fortalecimiento técnico, pensamiento crítico y mayor
                                        claridad en la proyección educativa.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="mini-panel rounded-2xl p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Enfoque</p>
                                    <p class="mt-2 text-lg font-black text-main">Técnico + humanístico</p>
                                </div>

                                <div class="mini-panel rounded-2xl p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Turnos</p>
                                    <p class="mt-2 text-lg font-black text-main">Mañana y tarde</p>
                                </div>

                                <div class="mini-panel rounded-2xl p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Proyección</p>
                                    <p class="mt-2 text-lg font-black text-main">Educación superior</p>
                                </div>
                            </div>

                            <div class="mt-10 rounded-[1.8rem] p-6 ring-1"
                                style="background: var(--landing-surface-soft); --tw-ring-color: var(--landing-border);">
                                <h4 class="font-display text-xl font-black text-main">
                                    Recorrido académico del estudiante
                                </h4>

                                <p class="mt-3 text-sm leading-7 text-soft">
                                    El proceso integra conocimientos generales, orientación técnica, especialización
                                    progresiva y proyección futura.
                                </p>

                                <div class="mt-8 grid gap-4 md:grid-cols-2">
                                    @foreach ([
                                        ['n' => '1', 't' => 'Base académica', 'd' => 'Consolidación de conocimientos generales y desarrollo formativo.', 'c' => 'var(--landing-primary)'],
                                        ['n' => '2', 't' => 'Orientación técnica', 'd' => 'Acercamiento a áreas técnicas y fortalecimiento de habilidades prácticas.', 'c' => 'var(--landing-sky)'],
                                        ['n' => '3', 't' => 'Especialización', 'd' => 'Desarrollo de competencias dentro del enfoque técnico elegido.', 'c' => 'var(--landing-primary)'],
                                        ['n' => '4', 't' => 'Proyección futura', 'd' => 'Vinculación con aspiraciones académicas y decisiones vocacionales.', 'c' => 'var(--landing-sky)'],
                                    ] as $paso)
                                        <div class="mini-panel rounded-2xl p-5">
                                            <div class="flex items-start gap-4">
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl font-black text-white"
                                                    style="background: {{ $paso['c'] }};">
                                                    {{ $paso['n'] }}
                                                </div>

                                                <div>
                                                    <h5 class="font-display text-lg font-black text-main">
                                                        {{ $paso['t'] }}
                                                    </h5>
                                                    <p class="mt-2 text-sm leading-7 text-soft">
                                                        {{ $paso['d'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="scroll-reveal-right space-y-6">
                            <article class="soft-panel rounded-[2rem] p-7">
                                <h3 class="font-display text-xl font-black text-main">Organización académica</h3>
                                <p class="mt-4 text-sm leading-7 text-soft">
                                    Horarios, calendario académico, estructura docente, turnos y oferta formativa
                                    respaldan la continuidad del proceso educativo.
                                </p>
                            </article>

                            <article class="soft-panel rounded-[2rem] p-7">
                                <h3 class="font-display text-xl font-black text-main">Formación integral</h3>
                                <p class="mt-4 text-sm leading-7 text-soft">
                                    Fortalece valores, capacidades personales, visión social y preparación para
                                    contextos futuros.
                                </p>
                            </article>

                            <div class="rounded-[2rem] bg-gradient-to-br from-sky-700 to-emerald-600 p-7 text-white shadow-2xl shadow-sky-600/20">
                                <p class="text-sm font-black uppercase tracking-[0.16em] text-sky-100">
                                    Proyección educativa
                                </p>

                                <h3 class="font-display mt-3 text-2xl font-black">
                                    Una base que conecta aprendizaje, técnica y futuro.
                                </h3>

                                <p class="mt-4 text-sm leading-8 text-white/90">
                                    La dimensión académica prepara al estudiante para concluir su etapa escolar y
                                    proyectarse con claridad hacia estudios superiores.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ESPECIALIDADES --}}
            <section id="especialidades" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-kicker text-sm font-black uppercase">
                            Especialidades
                        </span>

                        <h2 class="font-display mt-3 text-3xl font-black leading-tight text-main sm:text-4xl lg:text-[2.8rem]">
                            Especialidades técnicas que fortalecen talento, práctica y proyección.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-soft">
                            La propuesta técnica permite explorar áreas de formación, desarrollar habilidades específicas
                            y construir una experiencia conectada con el futuro profesional.
                        </p>
                    </div>

                    @php
                        $especialidades = [
                            ['nombre' => 'Sistemas Informáticos', 'imagen' => 'image/esp-sistemas.jpg', 'etiqueta' => 'Tecnología', 'descripcion' => 'Pensamiento lógico, organización digital y herramientas tecnológicas.'],
                            ['nombre' => 'Electrónica', 'imagen' => 'image/esp-electronica.jpg', 'etiqueta' => 'Técnica', 'descripcion' => 'Análisis, precisión y comprensión de sistemas electrónicos.'],
                            ['nombre' => 'Contabilidad', 'imagen' => 'image/esp-contabilidad.jpg', 'etiqueta' => 'Gestión', 'descripcion' => 'Orden, análisis numérico y procesos de administración y control.'],
                            ['nombre' => 'Gastronomía', 'imagen' => 'image/esp-gastronomia.jpg', 'etiqueta' => 'Creatividad', 'descripcion' => 'Creatividad, técnica y disciplina en una experiencia práctica.'],
                            ['nombre' => 'Textiles y Confecciones', 'imagen' => 'image/esp-textiles.jpg', 'etiqueta' => 'Diseño', 'descripcion' => 'Elaboración, detalle, diseño aplicado y trabajo técnico textil.'],
                            ['nombre' => 'Mecánica Industrial', 'imagen' => 'image/esp-mecanica-industrial.jpg', 'etiqueta' => 'Industria', 'descripcion' => 'Procesos, maquinaria y soluciones técnicas de tipo industrial.'],
                            ['nombre' => 'Mecánica Automotriz', 'imagen' => 'image/esp-mecanica-automotriz.jpg', 'etiqueta' => 'Automotriz', 'descripcion' => 'Sistemas del automóvil, diagnóstico técnico y mantenimiento aplicado.'],
                            ['nombre' => 'Carpintería en Madera y Metal', 'imagen' => 'image/esp-carpinteria.jpg', 'etiqueta' => 'Producción', 'descripcion' => 'Diseño técnico y elaboración práctica en madera y metal.'],
                            ['nombre' => 'Belleza Integral', 'imagen' => 'image/esp-belleza.jpg', 'etiqueta' => 'Estética', 'descripcion' => 'Técnica, presentación, atención especializada y servicios estéticos.'],
                        ];
                    @endphp

                    <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($especialidades as $item)
                            <article class="media-card scroll-reveal overflow-hidden rounded-[2rem] border soft-panel">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset($item['imagen']) }}" alt="Especialidad de {{ $item['nombre'] }}"
                                        class="h-60 w-full object-cover">

                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/78 via-slate-950/10 to-transparent"></div>

                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm">
                                            {{ $item['etiqueta'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="font-display text-xl font-black text-main">
                                        {{ $item['nombre'] }}
                                    </h3>

                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        {{ $item['descripcion'] }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- VIDA ESTUDIANTIL --}}
            <section id="vida" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-kicker text-sm font-black uppercase" style="color: var(--landing-sky);">
                            Vida estudiantil
                        </span>

                        <h2 class="font-display mt-3 text-3xl font-black leading-tight text-main sm:text-4xl lg:text-[2.8rem]">
                            Una experiencia educativa que también se vive en comunidad.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-soft">
                            Actividades que promueven expresión, participación, creatividad y sentido de pertenencia.
                        </p>
                    </div>

                    <div class="mt-12 scroll-reveal-scale">
                        <article class="soft-panel overflow-hidden rounded-[2.2rem]">
                            <div class="grid lg:grid-cols-[1.08fr_.92fr] lg:items-stretch">
                                <div class="relative min-h-[320px] overflow-hidden lg:min-h-[460px]">
                                    <img src="{{ asset('image/cortometrajes.jpg') }}" alt="Actividad de cortometrajes"
                                        class="absolute inset-0 h-full w-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/82 via-slate-950/35 to-transparent"></div>

                                    <div class="absolute left-6 top-6">
                                        <span class="rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-white backdrop-blur-sm">
                                            Actividad destacada
                                        </span>
                                    </div>

                                    <div class="absolute bottom-6 left-6 right-6 max-w-xl text-white">
                                        <p class="text-sm font-semibold text-sky-100">Expresión audiovisual</p>

                                        <h3 class="font-display mt-2 text-3xl font-black leading-tight sm:text-4xl">
                                            Cortometrajes
                                        </h3>

                                        <p class="mt-4 text-sm leading-7 text-white/90 sm:text-base">
                                            Creatividad, narrativa visual y producción audiovisual como parte de la
                                            experiencia formativa.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-between p-8 lg:p-10">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.18em]" style="color: var(--landing-sky);">
                                            Comunidad educativa
                                        </p>

                                        <h4 class="font-display mt-3 text-2xl font-black text-main">
                                            Una actividad que conecta identidad, participación y talento
                                        </h4>

                                        <p class="mt-5 text-[1rem] leading-8 text-soft">
                                            Fortalece expresión, trabajo colaborativo, creatividad y comunicación,
                                            mostrando cómo la vida estudiantil puede convertirse en formación con impacto.
                                        </p>
                                    </div>

                                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                        <div class="mini-panel rounded-2xl p-5">
                                            <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Enfoque</p>
                                            <p class="mt-2 text-lg font-black text-main">Creatividad audiovisual</p>
                                        </div>

                                        <div class="mini-panel rounded-2xl p-5">
                                            <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Valor formativo</p>
                                            <p class="mt-2 text-lg font-black text-main">Expresión e identidad</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    @php
                        $actividades = [
                            ['titulo' => 'El Gran Tamayo 2018', 'imagen' => 'image/actividad-revista-2018.jpg', 'tag' => 'Revista', 'desc' => 'Publicación institucional que refleja identidad y participación estudiantil.'],
                            ['titulo' => 'El Gran Tamayo 2019', 'imagen' => 'image/actividad-revista-2019.jpg', 'tag' => 'Comunidad', 'desc' => 'Continuidad de una propuesta institucional con memoria y participación.'],
                            ['titulo' => 'Teatro Histórico', 'imagen' => 'image/teatro-historico.jpg', 'tag' => 'Cultura', 'desc' => 'Espacio de representación, expresión escénica y experiencia cultural.'],
                            ['titulo' => 'Feria sin Fronteras', 'imagen' => 'image/feria-sin-fronteras.jpg', 'tag' => 'Participación', 'desc' => 'Actividad que fortalece creatividad y presencia de la comunidad educativa.'],
                        ];
                    @endphp

                    <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($actividades as $actividad)
                            <article class="media-card scroll-reveal overflow-hidden rounded-[2rem] border soft-panel">
                                <div class="relative overflow-hidden">
                                    <img src="{{ asset($actividad['imagen']) }}" alt="{{ $actividad['titulo'] }}"
                                        class="h-52 w-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent"></div>
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm">
                                            {{ $actividad['tag'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="font-display text-xl font-black text-main">
                                        {{ $actividad['titulo'] }}
                                    </h3>
                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        {{ $actividad['desc'] }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- SISTEMA --}}
            <section id="sistema" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="scroll-reveal max-w-3xl">
                        <span class="section-kicker text-sm font-black uppercase">
                            Sistema e innovación
                        </span>

                        <h2 class="font-display mt-3 text-3xl font-black leading-tight text-main sm:text-4xl lg:text-[2.8rem]">
                            Una propuesta tecnológica orientada a fortalecer la proyección educativa.
                        </h2>

                        <p class="mt-5 max-w-2xl text-lg leading-8 text-soft">
                            SAVP acompaña la gestión institucional con una visión académica, administrativa e innovadora.
                        </p>
                    </div>

                    <div class="soft-panel mt-12 grid gap-8 rounded-[2rem] p-8 lg:grid-cols-[1.05fr_.95fr] lg:p-12">
                        <div class="scroll-reveal-left">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                                    style="background: var(--landing-primary-soft); color: var(--landing-primary);">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9.75 3v2.25M14.25 3v2.25M9.75 18.75V21M14.25 18.75V21M3 9.75h2.25M3 14.25h2.25M18.75 9.75H21M18.75 14.25H21M7.5 7.5h9v9h-9v-9Z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black uppercase tracking-[0.18em]" style="color: var(--landing-primary);">
                                        Innovación educativa
                                    </p>

                                    <h3 class="font-display mt-2 text-2xl font-black text-main">
                                        Un sistema pensado para acompañar la gestión con mayor claridad
                                    </h3>

                                    <p class="mt-4 text-[1rem] leading-8 text-soft">
                                        Integra tecnología al proceso educativo y administrativo para organizar datos,
                                        usuarios, docentes, asignaciones y decisiones institucionales.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                <div class="mini-panel rounded-2xl p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Propósito</p>
                                    <p class="mt-2 text-lg font-black text-main">Gestión institucional</p>
                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        Centralizar información y fortalecer procesos internos.
                                    </p>
                                </div>

                                <div class="mini-panel rounded-2xl p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-muted-custom">Enfoque</p>
                                    <p class="mt-2 text-lg font-black text-main">Tecnología con sentido educativo</p>
                                    <p class="mt-3 text-sm leading-7 text-soft">
                                        Herramientas visuales, claras y útiles para el entorno académico.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="scroll-reveal-right rounded-[1.8rem] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 text-white shadow-2xl lg:p-9">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-300">Vista conceptual</p>
                                    <h3 class="font-display mt-1 text-2xl font-black">
                                        SAVP – TIS 3
                                    </h3>
                                </div>

                                <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold text-emerald-300">
                                    Innovación
                                </span>
                            </div>

                            <div class="mt-8 space-y-4">
                                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-slate-300">Gestión académica</p>
                                        <p class="text-sm font-bold text-emerald-300">Activa</p>
                                    </div>

                                    <div class="mt-4 h-3 rounded-full bg-white/10">
                                        <div class="h-3 w-[82%] rounded-full bg-gradient-to-r from-emerald-400 to-sky-400"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                        <p class="text-sm text-slate-300">Usuarios</p>
                                        <p class="mt-2 text-xl font-black">Roles</p>
                                    </div>

                                    <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                        <p class="text-sm text-slate-300">Docentes</p>
                                        <p class="mt-2 text-xl font-black">Asignaciones</p>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                                    <p class="text-sm text-slate-300">Ruta conceptual</p>

                                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-300">
                                        <span class="rounded-full bg-white/10 px-3 py-1">Personas</span>
                                        <span>→</span>
                                        <span class="rounded-full bg-white/10 px-3 py-1">Usuarios</span>
                                        <span>→</span>
                                        <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-emerald-300">Gestión</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CONTACTO --}}
            <section id="contacto" class="section-grid scroll-mt-24 px-6 py-20 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="rounded-[2rem] bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-600 px-8 py-14 text-white shadow-2xl lg:px-14">
                        <div class="grid gap-10 lg:grid-cols-[1.05fr_.95fr] lg:items-center">
                            <div class="scroll-reveal-left">
                                <span class="text-sm font-black uppercase tracking-[0.2em] text-emerald-100">
                                    Ubicación y presencia institucional
                                </span>

                                <h2 class="font-display mt-4 text-3xl font-black leading-tight sm:text-4xl">
                                    Conoce dónde se encuentra la institución y dónde seguir su actividad.
                                </h2>

                                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/90">
                                    La Unidad Educativa Técnico Humanístico Franz Tamayo N°3 se proyecta como una
                                    institución con identidad, trayectoria y presencia educativa.
                                </p>

                                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-100">
                                            Dirección
                                        </p>
                                        <p class="mt-3 text-sm leading-7 text-white/95">
                                            Villa Victoria, calle Virrey Toledo esquina Murguía s/n, La Paz.
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-100">
                                            Presencia digital
                                        </p>
                                        <p class="mt-3 text-sm leading-7 text-white/95">
                                            Blog institucional y página oficial en Facebook.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                                    <a href="https://franztamayo3.blogspot.com/" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-4 text-sm font-black text-emerald-700 transition hover:bg-emerald-50">
                                        Visitar blog institucional
                                    </a>

                                    <a href="https://www.facebook.com/p/Unidad-Educativa-Franz-Tamayo-Nro-3-100027191873862/?locale=es_LA"
                                        target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center rounded-2xl border border-white/30 bg-white/10 px-6 py-4 text-sm font-black text-white transition hover:bg-white/20">
                                        Ver Facebook oficial
                                    </a>
                                </div>
                            </div>

                            <div class="scroll-reveal-right rounded-[1.8rem] border border-white/20 bg-white/10 p-6 backdrop-blur-sm">
                                <h3 class="font-display text-2xl font-black">Ubicación institucional</h3>

                                <p class="mt-4 text-sm leading-7 text-white/90">
                                    La institución se encuentra en una zona con presencia educativa activa dentro de la
                                    ciudad de La Paz.
                                </p>

                                <div class="mt-6 overflow-hidden rounded-2xl border border-white/20 bg-white/10">
                                    <iframe
                                        src="https://www.google.com/maps?q=Virrey%20Toledo%20esquina%20Murguia%20La%20Paz%20Bolivia&output=embed"
                                        width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        title="Mapa de ubicación Franz Tamayo N°3"
                                        class="h-64 w-full"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        {{-- FOOTER --}}
        <footer class="glass border-t py-10"
            style="background: var(--landing-surface-strong); border-color: var(--landing-border);">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="font-display text-sm font-black text-main">
                            Unidad Educativa Franz Tamayo N°3
                        </p>
                        <p class="text-xs text-muted-custom">
                            Formación técnica y humanística con proyección educativa
                        </p>
                    </div>

                    <div class="text-left lg:text-center">
                        <p class="text-xs uppercase tracking-[0.18em] text-muted-custom">
                            Sistema desarrollado
                        </p>
                        <p class="font-display text-sm font-black" style="color: var(--landing-primary);">
                            SAVP – TIS 3
                        </p>
                    </div>

                    <div class="text-left lg:text-right">
                        <p class="text-sm text-muted-custom">
                            © {{ date('Y') }} Todos los derechos reservados.
                        </p>
                        <p class="text-xs text-muted-custom">
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
            const menuOpenIcon = document.getElementById('menu-open-icon');
            const menuCloseIcon = document.getElementById('menu-close-icon');
            const themeButtons = [
                document.getElementById('theme-toggle'),
                document.getElementById('theme-toggle-mobile')
            ].filter(Boolean);
            const scrollProgress = document.getElementById('scroll-progress');

            const toggleMobileMenu = () => {
                if (!mobileMenu) return;

                const isHidden = mobileMenu.classList.toggle('hidden');

                if (menuOpenIcon && menuCloseIcon) {
                    menuOpenIcon.classList.toggle('hidden', !isHidden);
                    menuCloseIcon.classList.toggle('hidden', isHidden);
                }
            };

            const closeMobileMenu = () => {
                if (!mobileMenu) return;

                mobileMenu.classList.add('hidden');

                if (menuOpenIcon && menuCloseIcon) {
                    menuOpenIcon.classList.remove('hidden');
                    menuCloseIcon.classList.add('hidden');
                }
            };

            const toggleTheme = () => {
                if (window.themeManager && typeof window.themeManager.toggle === 'function') {
                    window.themeManager.toggle();
                    return;
                }

                const isDark = document.documentElement.classList.toggle('dark');
                document.documentElement.dataset.theme = isDark ? 'dark' : 'light';
                localStorage.setItem('savp-theme', isDark ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: {
                        theme: isDark ? 'dark' : 'light'
                    }
                }));
            };

            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', toggleMobileMenu);
            }

            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });

            themeButtons.forEach(button => {
                button.addEventListener('click', toggleTheme);
            });

            const revealItems = document.querySelectorAll(
                '.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right, .scroll-reveal-scale'
            );

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.14,
                rootMargin: '0px 0px -50px 0px'
            });

            revealItems.forEach((item, index) => {
                item.style.transitionDelay = `${Math.min(index * 35, 260)}ms`;
                revealObserver.observe(item);
            });

            const updateScrollProgress = () => {
                if (!scrollProgress) return;

                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const progress = height > 0 ? (scrollTop / height) * 100 : 0;

                scrollProgress.style.width = `${progress}%`;
            };

            updateScrollProgress();
            window.addEventListener('scroll', updateScrollProgress, {
                passive: true
            });

            window.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMobileMenu();
                }
            });
        });
    </script>
</body>

</html>