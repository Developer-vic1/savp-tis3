<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-icono-institucional />
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
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --landing-bg: var(--ui-bg);
            --landing-bg-soft: var(--ui-bg-soft);
            --landing-surface: var(--ui-surface);
            --landing-surface-strong: var(--ui-surface);
            --landing-surface-soft: var(--ui-surface-soft);
            --landing-text: var(--ui-text);
            --landing-text-soft: var(--ui-text-soft);
            --landing-muted: var(--ui-muted);
            --landing-border: var(--ui-border);
            --landing-primary: var(--ui-primary);
            --landing-primary-strong: var(--ui-primary-hover);
            --landing-primary-soft: var(--ui-primary-soft);
            --landing-sky: var(--ui-info);
            --landing-sky-soft: var(--ui-info-soft);
            --landing-violet: var(--ui-violet);
            --landing-violet-soft: var(--ui-violet-soft);
            --landing-warning: var(--ui-warning);
            --landing-warning-soft: var(--ui-warning-soft);
            --landing-shadow: var(--ui-shadow-lg);
            --landing-shadow-soft: var(--ui-shadow-md);
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: var(--landing-bg);
            color: var(--landing-text);
        }

        .font-display {
            font-family: 'Figtree', sans-serif;
        }

        .site-bg .grid > * { min-width: 0; }
        .site-bg p, .site-bg h1, .site-bg h2, .site-bg h3 { overflow-wrap: anywhere; }
        @media (max-width: 639px) {
            #sistema .grid-cols-2 { grid-template-columns: minmax(0, 1fr); }
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
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid var(--landing-border);
            background: color-mix(in srgb, var(--landing-surface-strong) 86%, transparent);
            border-color: var(--landing-border);
            box-shadow: 0 10px 35px rgba(15, 23, 42, .08);
        }

        .landing-header-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: .5rem 1.5rem;
            width: 100%;
            max-width: var(--ui-content-width);
            margin-inline: auto;
            padding: .625rem var(--ui-page-gutter);
        }

        .landing-brand { display: flex; min-width: 0; align-items: center; gap: .75rem; }
        .landing-brand p { overflow-wrap: normal; }
        .landing-brand-name { white-space: nowrap; }
        .landing-nav { display: none; }
        .landing-access { display: none; grid-column: 2; grid-row: 1; align-items: center; gap: .75rem; white-space: nowrap; }
        .landing-mobile-actions { display: flex; grid-column: 2; grid-row: 1; align-items: center; gap: .5rem; }
        .landing-header .theme-btn, .landing-header .icon-btn { width: 2.75rem; height: 2.75rem; flex: none; }
        .site-bg main > section { scroll-margin-top: calc(var(--landing-header-height, 6.75rem) + 1rem); }
        .site-bg main > section > .mx-auto { width: 100%; max-width: var(--ui-content-width); }
        .site-bg main > section { padding-block: 3.5rem; padding-inline: var(--ui-page-gutter); }
        .site-bg footer > .mx-auto { width: 100%; max-width: var(--ui-content-width); padding-inline: var(--ui-page-gutter); }
        .site-bg #inicio { padding-block: 1.5rem 2.5rem; }
        .landing-hero-grid { gap: 1.5rem; }
        .site-bg .landing-hero-copy { margin-top: 1rem; padding: 1.5rem; }
        .site-bg .landing-hero-title { max-width: none; margin-top: .75rem; font-size: clamp(1.875rem, 1.55rem + .75vw, 2.25rem); line-height: 1.15; text-wrap: balance; }
        .site-bg .landing-hero-description { margin-top: 1rem; font-size: 1rem; line-height: 1.65; }
        .site-bg .landing-hero-actions { margin-top: 1.25rem; gap: .75rem; }
        .landing-hero-actions a { display: inline-flex; align-items: center; justify-content: center; min-height: 2.75rem; padding: .625rem 1rem; }
        .site-bg .landing-hero-stats { margin-top: 1.25rem; gap: .625rem; }
        .site-bg .landing-hero-stats { max-width: none; }
        .landing-hero-stats > div { padding: .875rem .5rem; }
        .landing-hero-stats > div > p:first-child { font-size: 1.5rem; }
        .landing-hero-stats > div > p:last-child { font-size: .75rem; }
        .site-bg .landing-hero-preview { padding: 1rem; }
        .landing-hero-preview > div { padding: 1.5rem; }
        .landing-hero-preview h2 { font-size: 1.5rem; }
        .landing-hero-preview .relative > p { margin-top: 1rem; line-height: 1.65; }
        .landing-hero-preview .grid { margin-top: 1.25rem; gap: .75rem; }
        .landing-hero-preview .grid > div { padding: 1rem; }
        .landing-hero-preview .grid p { line-height: 1.5; }
        .landing-hero-preview .grid p:nth-child(2) { font-size: 1.125rem; }
        .landing-hero-preview .grid p:nth-child(3) { margin-top: .625rem; }
        .landing-hero-preview .flex.flex-wrap { margin-top: 1.25rem; }
        .site-bg .landing-section-title { font-size: clamp(1.5rem, 1.25rem + .75vw, 2rem); line-height: 1.25; text-wrap: balance; }
        .landing-card-grid { grid-template-columns: repeat(auto-fit, minmax(min(100%, 18rem), 1fr)); }
        .site-bg main :is(h1, h2)[tabindex="-1"]:focus { outline: none; }
        .site-bg main > section > .mx-auto > .scroll-reveal > p { margin-top: 1rem; font-size: 1rem; line-height: 1.7; }
        .landing-header #mobile-menu { max-height: calc(100dvh - 5rem); overflow-y: auto; }

        .landing-welcome, .landing-discovery, .landing-innovation { position: relative; border-radius: 1.75rem; padding: clamp(1.25rem, 2.5vw, 2.75rem); }
        .landing-welcome { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        .landing-welcome h1 { max-width: 48rem; }
        .landing-welcome .landing-hero-copy { margin: 0; padding: 0; }
        .landing-welcome .landing-hero-stats { margin: 0; align-self: center; }
        .landing-intro-grid { display: grid; grid-template-columns: minmax(0,1fr); gap: 1.5rem; }
        .landing-intro-grid .landing-identity { margin-top: 0; }
        .landing-discovery { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; margin-top: 1.5rem; background: linear-gradient(120deg, var(--landing-primary-soft), var(--landing-sky-soft)); border: 1px solid var(--landing-border); }
        .landing-campus { position: relative; margin: 0; min-height: 15rem; border-radius: 1.25rem; overflow: hidden; }
        .landing-campus img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .landing-campus figcaption { position: absolute; inset: auto 0 0; padding: 3rem 1.25rem 1.25rem; color: white; background: linear-gradient(transparent, rgba(0,0,0,.8)); }
        .landing-discovery-copy { align-self: center; }
        .landing-identity { margin-top: 1.5rem; }
        .landing-identity-content { display: grid; grid-template-columns: minmax(0,1fr); gap: 1.5rem; align-items: center; }
        .landing-identity-details { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 1rem; }
        .landing-identity-content p { line-height: 1.7; }
        .landing-identity-footer { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1.5rem; }
        .landing-discovery h2, .landing-innovation h2 { margin-top: .75rem; }
        .landing-discovery p, .landing-innovation p { line-height: 1.7; }
        .landing-journey { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; padding: 0; margin: 1.5rem 0 0; list-style: none; }
        .landing-journey li { position: relative; padding: 1.125rem; border-radius: 1rem; background: var(--landing-surface); border: 1px solid var(--landing-border); }
        .landing-journey h3 { margin-top: .75rem; font-weight: 800; }
        .landing-journey p { margin-top: .375rem; font-size: .875rem; }
        .landing-symbol { display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 1rem; color: var(--landing-primary); background: var(--landing-primary-soft); font-size: 1.65rem; flex-shrink: 0; }
        .landing-symbol.sky { color: var(--landing-sky); background: var(--landing-sky-soft); }
        .landing-symbol.violet { color: var(--landing-violet); background: var(--landing-violet-soft); }
        .landing-link { display: inline-flex; align-items: center; gap: .625rem; min-height: 2.75rem; margin-top: 1.25rem; font-weight: 800; color: var(--landing-primary); }
        .landing-innovation { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; background: linear-gradient(130deg, var(--landing-surface), var(--landing-primary-soft)); border: 1px solid var(--landing-border); }
        .landing-innovation-copy { align-self: center; }
        .landing-innovation-copy > p { max-width: 42rem; margin-top: 1rem; }
        .landing-innovation-copy h2 { max-width: 36rem; font-size: clamp(1.875rem, 1.5rem + 1vw, 2.75rem); line-height: 1.15; }
        .landing-illustration { position: relative; align-self: center; padding: 1.5rem; border-radius: 1.5rem; background: var(--landing-surface); border: 1px solid var(--landing-border); }
        .landing-illustration-header { display: flex; align-items: center; gap: .75rem; }
        .landing-illustration-header h3 { font-size: 1.125rem; font-weight: 800; }
        .landing-illustration-header p { font-size: .8125rem; color: var(--landing-muted); }
        .landing-map { position: relative; padding: 1rem 0; }
        .landing-map svg { width: 100%; height: 9rem; overflow: visible; }
        .landing-map-line { fill: none; stroke: var(--landing-primary); stroke-width: 3; stroke-linecap: round; stroke-dasharray: 5 9; }
        .landing-map-trace { fill: none; stroke: var(--landing-sky); stroke-width: 3; stroke-linecap: round; stroke-dasharray: 100; stroke-dashoffset: 100; opacity: .8; }
        .landing-route-marker { fill: var(--landing-sky); stroke: var(--landing-surface); stroke-width: 3; }
        .landing-map-node { fill: var(--landing-surface); stroke: var(--landing-primary); stroke-width: 2; }
        .landing-map-labels { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); text-align: center; gap: .75rem; font-size: .8125rem; font-weight: 800; }
        .landing-floating-symbol { position: absolute; top: 1.625rem; left: calc(50% - 1.5rem); box-shadow: var(--landing-shadow-soft); }
        .landing-floating-symbol:last-of-type { top: 4rem; left: calc(84% - 1.5rem); }
        .site-bg[data-landing-section="sistema"] .landing-floating-symbol { animation: landing-symbol-drift 4s ease-in-out; }
        .site-bg[data-landing-section="sistema"] .landing-map-node { animation: landing-route-pulse 1800ms ease; }
        .landing-personal-note { display: flex; gap: .75rem; padding: .875rem 1rem; border-left: 3px solid var(--landing-primary); background: var(--landing-primary-soft); border-radius: 0 .875rem .875rem 0; font-size: .9375rem; }
        .landing-personal-note .ph-duotone { flex-shrink: 0; color: var(--landing-primary); font-size: 1.5rem; }
        .landing-map-note { margin-top: 1.25rem; padding: .875rem 1rem; border-radius: .875rem; background: var(--landing-primary-soft); font-size: .875rem; color: var(--landing-text-soft); }
        .landing-route-stations { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .5rem; }
        .landing-route-stations button { min-height: 3.75rem; display: grid; justify-items: center; align-content: center; gap: .25rem; padding: .5rem .375rem; border-radius: .875rem; border: 1px solid var(--landing-border); font-size: .8125rem; font-weight: 800; color: var(--landing-text-soft); background: var(--landing-surface); transition: transform 180ms ease, background 180ms ease, border-color 180ms ease; }
        .landing-route-stations button .ph-duotone { font-size: 1.25rem; }
        .landing-route-stations button:hover { transform: translateY(-3px); border-color: var(--landing-primary); }
        .landing-route-stations button[aria-pressed="true"] { background: var(--landing-primary-soft); color: var(--landing-primary); border-color: var(--landing-primary); }
        .landing-route-panel { min-height: 15rem; margin-top: 1rem; padding: 1rem; border-radius: 1rem; background: var(--landing-surface-soft); border: 1px solid var(--landing-border); }
        .landing-route-panel h4 { font-size: 1rem; font-weight: 800; line-height: 1.4; }
        .landing-route-panel > p { margin-top: .5rem; font-size: .8125rem; color: var(--landing-text-soft); }
        .landing-route-options { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }
        .landing-route-options button { min-height: 2.75rem; padding: .625rem .75rem; border-radius: .75rem; border: 1px solid var(--landing-border); background: var(--landing-surface); font-size: .8125rem; font-weight: 700; color: var(--landing-text-soft); transition: border-color 180ms ease, transform 180ms ease; }
        .landing-route-options button:hover { border-color: var(--landing-sky); transform: translateY(-2px); }
        .landing-route-options button[aria-pressed="true"] { border-color: var(--landing-sky); background: var(--landing-sky-soft); color: var(--landing-sky); }
        .landing-route-feedback { min-height: 3rem; }
        .landing-route-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; }
        .landing-route-footer > p { font-size: .75rem; color: var(--landing-muted); }
        .landing-route-footer button { min-height: 2.75rem; padding: .625rem .875rem; display: inline-flex; align-items: center; gap: .5rem; border-radius: .75rem; font-size: .8125rem; font-weight: 800; }
        .landing-route-progress { width: 100%; height: .375rem; display: block; margin-top: .75rem; accent-color: var(--landing-primary); }
        .landing-route-progress::-webkit-progress-bar { background: var(--landing-primary-soft); border-radius: 1rem; }
        .landing-route-progress::-webkit-progress-value { background: var(--landing-primary); border-radius: 1rem; }
        .landing-route-progress::-moz-progress-bar { background: var(--landing-primary); border-radius: 1rem; }
        .landing-illustration:not([data-route-ready]) :is(.landing-route-panel, .landing-route-footer, .landing-route-progress) { display: none; }
        .landing-benefits { display: grid; grid-template-columns: repeat(auto-fit,minmax(min(100%,18rem),1fr)); gap: 1.25rem; margin-top: 1.5rem; }
        .landing-benefit { padding: 1.5rem; border-radius: 1.25rem; }
        .landing-benefit h3 { margin-top: 1rem; font-size: 1.125rem; font-weight: 800; }
        .landing-benefit p { margin-top: .625rem; line-height: 1.7; color: var(--landing-text-soft); }
        .landing-benefit .landing-link { font-size: .875rem; }
        .landing-link .ph-duotone, .landing-hero-actions a .ph-duotone { transition: transform 220ms ease; }
        .landing-link:hover .ph-arrow-right, .landing-hero-actions a:hover .ph-arrow-right { transform: translateX(4px); }
        .landing-benefit { transition: transform 240ms ease, border-color 240ms ease, box-shadow 240ms ease; }
        .landing-benefit:hover { transform: translateY(-4px); border-color: var(--landing-primary); box-shadow: var(--landing-shadow); }
        .landing-benefit .landing-symbol { transition: transform 240ms ease; }
        .landing-benefit:hover .landing-symbol { transform: rotate(-5deg); }
        .landing-invitation { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem; padding: 1.5rem; border-radius: 1.25rem; background: var(--landing-primary-soft); border: 1px solid var(--landing-border); }
        .landing-invitation > div { display: flex; align-items: center; gap: 1rem; }
        .landing-invitation h3 { font-size: 1.125rem; font-weight: 800; }
        .landing-invitation p { margin-top: .375rem; line-height: 1.6; color: var(--landing-text-soft); }
        .landing-invitation a { min-height: 2.75rem; display: inline-flex; align-items: center; gap: .625rem; padding: .875rem 1.25rem; }
        @keyframes landing-symbol-drift { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px) rotate(4deg); } }
        @keyframes landing-route-pulse { 0%,100% { stroke-width: 2; } 45% { stroke-width: 5; } }
        .landing-section-controls { display: none; position: fixed; z-index: 45; right: var(--ui-page-gutter); bottom: 1rem; align-items: center; gap: .625rem; border: 1px solid var(--landing-border); border-radius: 1.25rem; padding: .5rem; background: var(--landing-surface); box-shadow: var(--landing-shadow-soft); }
        .landing-section-controls button { width: 2.75rem; height: 2.75rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .875rem; background: var(--landing-primary-soft); color: var(--landing-primary); font-size: 1.25rem; transition: transform 180ms ease, background 180ms ease; }
        .landing-section-controls button:hover:not(:disabled) { transform: translateY(-2px); background: var(--landing-sky-soft); }
        .landing-section-controls button:disabled { opacity: .4; cursor: default; }
        .landing-section-position { display: grid; gap: .125rem; min-width: 7rem; text-align: center; font-size: .8125rem; }
        .landing-section-position strong { color: var(--landing-text); }
        .landing-section-position span { color: var(--landing-muted); font-size: .75rem; }
        @media (min-width: 1024px) {
            .site-bg[data-landing-section] .landing-section-controls { display: flex; }
            .landing-intro-grid { grid-template-columns: minmax(0,1.02fr) minmax(0,.98fr); align-items: stretch; }
            .landing-intro-grid .landing-welcome { grid-template-columns: minmax(0,1fr); gap: 1.5rem; }
            .landing-intro-grid .landing-hero-stats { grid-template-columns: repeat(4,minmax(0,1fr)); }
            .landing-intro-grid .landing-identity { display: flex; }
            .landing-intro-grid .landing-identity > div { display: flex; flex-direction: column; justify-content: center; width: 100%; }
            .landing-discovery { grid-template-columns: minmax(0,.75fr) minmax(0,1.25fr); }
            .landing-innovation { grid-template-columns: minmax(0,1.1fr) minmax(0,.9fr); }
            .landing-identity-content { grid-template-columns: minmax(0,1fr); gap: 1.5rem; }
        }
        @media (min-width: 2200px) {
            .landing-intro-grid { grid-template-columns: minmax(0,1fr); }
            .landing-intro-grid .landing-welcome { grid-template-columns: minmax(0,1.4fr) minmax(0,.6fr); gap: 3rem; }
            .landing-intro-grid .landing-hero-stats { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .landing-identity-content { grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 3rem; }
        }
        @media (max-width: 639px) {
            .landing-journey { grid-template-columns: minmax(0,1fr); }
            .landing-identity-details { grid-template-columns: minmax(0,1fr); }
            .landing-invitation > div { align-items: flex-start; }
            .landing-invitation a { width: 100%; justify-content: center; }
            .landing-map svg { height: 7rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .site-bg[data-landing-section="sistema"] :is(.landing-floating-symbol, .landing-map-trace, .landing-map-node) { animation: none; }
            .landing-benefit, .landing-benefit .landing-symbol, .landing-link .ph-duotone, .landing-hero-actions a .ph-duotone { transition: none; }
            .landing-benefit:hover, .landing-benefit:hover .landing-symbol, .landing-link:hover .ph-arrow-right, .landing-hero-actions a:hover .ph-arrow-right { transform: none; }
            .landing-section-controls button { transition: none; }
            .landing-section-controls button:hover:not(:disabled) { transform: none; }
            .landing-route-stations button, .landing-route-options button { transition: none; }
            .landing-route-stations button:hover, .landing-route-options button:hover { transform: none; }
        }

        @media (min-width: 1024px) {
            .landing-access { display: flex; }
            .landing-mobile-actions { display: none; }
            .landing-nav {
                display: flex;
                grid-column: 1 / -1;
                grid-row: 2;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                gap: .5rem 1.75rem;
                border-top: 1px solid var(--landing-border);
                padding-top: .25rem;
            }
            .landing-nav a { display: inline-flex; align-items: center; min-height: 1.75rem; }
            .landing-header #mobile-menu { display: none; }
        }

        @media (max-width: 479px) {
            .landing-header-layout { padding-inline: 1rem; column-gap: .5rem; }
            .landing-brand { gap: .5rem; }
        }

        @media (max-width: 359px) {
            .landing-header-layout { padding-inline: .75rem; column-gap: .25rem; }
            .landing-brand > div:first-child { width: 2rem; height: 2rem; }
            .landing-brand img { width: 1.75rem; height: 1.75rem; }
            .landing-brand-name { font-size: .8125rem; }
        }

        @media (min-width: 1024px) and (max-height: 760px) {
            .site-bg #inicio { padding-block: 1rem; }
            .site-bg .landing-hero-copy { margin-top: .5rem; padding: 1.25rem; }
            .site-bg .landing-hero-title { font-size: 2rem; }
            .site-bg .landing-hero-description { margin-top: .75rem; font-size: .9375rem; line-height: 1.6; }
            .site-bg .landing-hero-actions, .site-bg .landing-hero-stats { margin-top: 1rem; }
            .landing-hero-stats > div { padding-block: .625rem; }
            .site-bg .landing-hero-preview { padding: .75rem; }
            .landing-hero-preview > div { padding: 1.25rem; }
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

        .landing-nav .nav-link { padding: .25rem .625rem; border-radius: .625rem; }
        .nav-link[aria-current="location"] { color: var(--landing-primary); background: var(--landing-primary-soft); }
        .site-bg a:focus-visible, .site-bg button:focus-visible { outline: 2px solid var(--landing-primary); outline-offset: 3px; }

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
            /* El contenido siempre es visible, incluso sin JavaScript o al capturar toda la página. */
            opacity: 1;
            transform: none;
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
        <header class="landing-header glass">
            <div class="landing-header-layout">
                <a href="#inicio" class="landing-brand">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md">
                        <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo N°3"
                            class="h-9 w-9 object-contain">
                    </div>

                    <div class="min-w-0 leading-tight">
                        <p class="landing-brand-name font-display text-sm font-black text-main">
                            Franz Tamayo N°3
                        </p>
                        <p class="hidden text-xs text-muted-custom sm:block">
                            Unidad Educativa Técnico Humanístico
                        </p>
                        <p class="text-xs text-muted-custom sm:hidden">SAVP · TIS 3</p>
                    </div>
                </a>

                <nav class="landing-nav" aria-label="Navegación institucional">
                    <a href="#inicio" class="nav-link text-sm font-semibold">Inicio</a>
                    <a href="#institucional" class="nav-link text-sm font-semibold">Institucional</a>
                    <a href="#academico" class="nav-link text-sm font-semibold">Académico</a>
                    <a href="#especialidades" class="nav-link text-sm font-semibold">Especialidades</a>
                    <a href="#vida" class="nav-link text-sm font-semibold">Vida estudiantil</a>
                    <a href="#sistema" class="nav-link text-sm font-semibold">Sistema</a>
                    <a href="#contacto" class="nav-link text-sm font-semibold">Contacto</a>
                </nav>

                <div class="landing-access">
                    <button type="button" id="theme-toggle" data-theme-toggle class="theme-btn inline-flex items-center justify-center rounded-xl"
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
                        @can('Acceso_Aula_Virtual')
                            <a href="{{ route('aula-virtual.inicio') }}"
                                class="rounded-2xl px-5 py-3 text-sm font-bold text-white"
                                style="background: var(--landing-primary); box-shadow: 0 18px 35px color-mix(in srgb, var(--landing-primary) 24%, transparent);">
                                Ingresar al Aula Virtual
                            </a>
                        @else
                            <a href="{{ route('aula-virtual.login') }}"
                                class="btn-secondary rounded-2xl px-5 py-3 text-sm font-bold">
                                Ingresar al Aula Virtual
                            </a>
                        @endcan
                    @else
                        <a href="{{ route('login') }}"
                            class="btn-primary rounded-2xl px-5 py-3 text-sm font-bold">
                            Ingresar
                        </a>
                        <a href="{{ route('aula-virtual.login') }}"
                            class="rounded-2xl px-5 py-3 text-sm font-bold text-white"
                            style="background: var(--landing-primary); box-shadow: 0 18px 35px color-mix(in srgb, var(--landing-primary) 24%, transparent);">
                            Ingresar al Aula Virtual
                        </a>
                    @endauth
                </div>

                <div class="landing-mobile-actions">
                    <button type="button" id="theme-toggle-mobile" data-theme-toggle
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
                        aria-label="Abrir menú" aria-controls="mobile-menu" aria-expanded="false">
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
                        @can('Acceso_Aula_Virtual')
                            <a href="{{ route('aula-virtual.inicio') }}" class="mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold text-white"
                                style="background: var(--landing-primary);">
                                Ingresar al Aula Virtual
                            </a>
                        @else
                            <a href="{{ route('aula-virtual.login') }}" class="btn-secondary mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold">
                                Ingresar al Aula Virtual
                            </a>
                        @endcan
                    @else
                        <a href="{{ route('login') }}" class="btn-primary mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold">
                            Ingresar
                        </a>
                        <a href="{{ route('aula-virtual.login') }}" class="mt-2 rounded-2xl px-5 py-3 text-center text-sm font-bold text-white"
                            style="background: var(--landing-primary);">
                            Ingresar al Aula Virtual
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            {{-- HERO --}}
            <section id="inicio" class="section-grid">
                <div class="mx-auto max-w-7xl">
                    <div class="landing-intro-grid">
                    <div class="landing-welcome strong-panel scroll-reveal">
                        <div class="landing-hero-copy">
                            <span class="section-kicker text-xs font-black uppercase">Franz Tamayo N°3 · Formación técnica y humanística</span>
                            <h1 class="landing-hero-title font-display font-black tracking-tight text-main">
                                Tu historia empieza aquí.<br><span class="title-gradient">Tu futuro lo construyes tú.</span>
                            </h1>
                            <p class="landing-hero-description max-w-[35rem] text-soft">
                                Aprende haciendo, descubre lo que te apasiona y prepárate para el siguiente paso.
                                Una comunidad educativa que une formación técnica, valores y nuevas oportunidades.
                            </p>
                            <div class="landing-hero-actions flex flex-col sm:flex-row">
                                <a href="#especialidades" class="btn-primary rounded-2xl text-sm font-bold"><i class="ph-duotone ph-toolbox" aria-hidden="true"></i>&nbsp;Descubrir especialidades</a>
                                <a href="#institucional" class="btn-secondary rounded-2xl text-sm font-bold">Conocer el colegio<i class="ph-duotone ph-arrow-right ml-2" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="landing-hero-stats grid grid-cols-2 sm:grid-cols-4" aria-label="Identidad institucional">
                            <div class="mini-panel rounded-2xl text-center"><p class="font-black leading-none" style="color: var(--landing-primary);">1957</p><p class="mt-2 text-muted-custom">Desde nuestra fundación</p></div>
                            <div class="mini-panel rounded-2xl text-center"><p class="font-black leading-none" style="color: var(--landing-primary);">9</p><p class="mt-2 text-muted-custom">Especialidades para explorar</p></div>
                            <div class="mini-panel rounded-2xl text-center"><p class="font-black leading-none" style="color: var(--landing-sky);">BTH</p><p class="mt-2 text-muted-custom">Formación técnica y humanística</p></div>
                            <div class="mini-panel rounded-2xl text-center"><p class="font-black leading-none" style="color: var(--landing-sky);">2</p><p class="mt-2 text-muted-custom">Turnos de estudio</p></div>
                        </div>
                    </div>
                    <div class="landing-identity landing-hero-preview strong-panel rounded-[2rem] scroll-reveal">
                        <div class="relative overflow-hidden rounded-[1.7rem] bg-gradient-to-br from-slate-950 via-emerald-950 to-sky-900 text-white">
                            <div class="landing-identity-content">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-2xl" aria-hidden="true"><i class="ph-duotone ph-graduation-cap"></i></span>
                                        <p class="text-sm text-emerald-200">Unidad Educativa</p>
                                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">Villa Victoria</span>
                                    </div>
                                    <h2 class="font-display mt-3 font-bold">Franz Tamayo N°3</h2>
                                    <p class="mt-4 max-w-[35rem] text-slate-200">Una propuesta educativa que fortalece conocimientos, valores y habilidades técnicas dentro de una experiencia formativa integral.</p>
                                </div>
                                <div class="landing-identity-details">
                                    <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                                        <p class="text-xs uppercase tracking-widest text-emerald-200">Identidad</p>
                                        <h3 class="mt-2 text-xl font-bold">Bachillerato Técnico</h3>
                                        <p class="mt-3 text-sm text-slate-300">Formación humanística y técnica integrada.</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                                        <p class="text-xs uppercase tracking-widest text-sky-200">Propuesta</p>
                                        <h3 class="mt-2 text-xl font-bold">Especialidades</h3>
                                        <p class="mt-3 text-sm text-slate-300">Áreas técnicas con proyección profesional.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="landing-identity-footer">
                                <div class="flex flex-wrap gap-2 text-xs text-slate-200"><span class="rounded-full bg-white/10 px-3 py-1.5">Historia</span><span class="rounded-full bg-white/10 px-3 py-1.5">Formación</span><span class="rounded-full bg-emerald-400/20 px-3 py-1.5 text-emerald-300">Futuro</span></div>
                                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3"><p class="text-xs font-black uppercase tracking-widest text-emerald-200">SAVP</p><p class="mt-1 text-sm font-bold">Sistema académico administrativo</p></div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="landing-discovery scroll-reveal">
                        <figure class="landing-campus">
                            <img src="{{ asset('image/infra-edificio1.jpg') }}" alt="Edificio de la Unidad Educativa Franz Tamayo N°3 en Villa Victoria" fetchpriority="high">
                            <figcaption><p class="text-xs font-bold uppercase tracking-widest">Villa Victoria · La Paz</p><p class="mt-1 text-lg font-black">Un lugar para aprender y crecer juntos.</p></figcaption>
                        </figure>
                        <div class="landing-discovery-copy">
                            <span class="section-kicker text-xs font-black uppercase">Del colegio a lo que viene</span>
                            <h2 class="landing-section-title font-black text-main">Hay mucho por descubrir después de graduarte.</h2>
                            <p class="mt-3 text-soft">Tu especialidad es un punto de partida. Con SAVP puedes conocer mejor tus intereses y explorar opciones para continuar tus estudios.</p>
                            <ol class="landing-journey" aria-label="Camino hacia tu futuro académico">
                                <li><span class="landing-symbol" aria-hidden="true"><i class="ph-duotone ph-heart"></i></span><h3>Descubre</h3><p class="text-soft">Reconoce lo que disfrutas y te interesa aprender.</p></li>
                                <li><span class="landing-symbol sky" aria-hidden="true"><i class="ph-duotone ph-compass"></i></span><h3>Explora</h3><p class="text-soft">Conoce carreras y qué se estudia en ellas.</p></li>
                                <li><span class="landing-symbol violet" aria-hidden="true"><i class="ph-duotone ph-graduation-cap"></i></span><h3>Da el siguiente paso</h3><p class="text-soft">Investiga universidades y construye tu decisión.</p></li>
                            </ol>
                            <a class="landing-link" href="#sistema">Conocer cómo me acompaña SAVP<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
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

                        <h2 class="landing-section-title font-display mt-3 font-black text-main">
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

                        <h2 class="landing-section-title font-display mt-3 font-black text-main">
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

                        <h2 class="landing-section-title font-display mt-3 font-black text-main">
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

                    <div class="landing-card-grid mt-12 grid gap-6">
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

                        <h2 class="landing-section-title font-display mt-3 font-black text-main">
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

                    <div class="landing-card-grid mt-8 grid gap-6">
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
            <section id="sistema" class="section-grid">
                <div class="mx-auto max-w-7xl">
                    <div class="landing-innovation scroll-reveal">
                        <div class="landing-innovation-copy">
                            <span class="section-kicker text-xs font-black uppercase">Sistema e innovación · SAVP</span>
                            <h2 class="font-display font-black text-main">¿Qué quieres ser?<br><span class="title-gradient">Empieza por descubrirte.</span></h2>
                            <p class="text-soft">No necesitas tener todas las respuestas hoy. Lo que te gusta, lo que despierta tu curiosidad y lo que quieres aprender pueden abrirte un camino hacia tu futura carrera.</p>
                            <p class="landing-personal-note"><i class="ph-duotone ph-user-focus" aria-hidden="true"></i><span><strong>Una orientación que parte de ti.</strong> Tus respuestas e intereses se relacionan con información de carreras y planes de estudio para acercarte opciones que pueden encajar contigo.</span></p>
                            <div class="landing-hero-actions flex flex-col sm:flex-row">
                                <a href="{{ route('aula-virtual.login') }}" class="btn-primary rounded-2xl text-sm font-bold"><i class="ph-duotone ph-compass" aria-hidden="true"></i>&nbsp;Ingresar y explorar mi futuro</a>
                                <a href="#contacto" class="btn-secondary rounded-2xl text-sm font-bold">Quiero conocer cómo inscribirme</a>
                            </div>
                            <p class="text-sm text-muted-custom">Los espacios de orientación se consultan con tu cuenta de estudiante.</p>
                        </div>
                        <div class="landing-illustration">
                            <div class="landing-illustration-header">
                                <span class="landing-symbol" aria-hidden="true"><i class="ph-duotone ph-sparkle"></i></span>
                                <div><h3>Tu próximo capítulo</h3><p>Recorrido de ejemplo · pulsa una estación</p></div>
                            </div>
                            <div class="landing-map">
                                <svg viewBox="0 0 420 130" preserveAspectRatio="none" aria-hidden="true"><path class="landing-map-line" d="M65 100 C125 100 140 40 210 40 S305 85 355 85"/><path class="landing-map-trace" pathLength="100" d="M65 100 C125 100 140 40 210 40 S305 85 355 85"/><circle class="landing-map-node" cx="65" cy="100" r="9"/><circle class="landing-map-node" cx="210" cy="40" r="9"/><circle class="landing-map-node" cx="355" cy="85" r="9"/><circle class="landing-route-marker" cx="65" cy="100" r="6"/></svg>
                                <span class="landing-floating-symbol landing-symbol sky" aria-hidden="true"><i class="ph-duotone ph-buildings"></i></span>
                                <span class="landing-floating-symbol landing-symbol violet" aria-hidden="true"><i class="ph-duotone ph-graduation-cap"></i></span>
                                <div class="landing-route-stations" aria-label="Estaciones del recorrido de ejemplo">
                                    <button type="button" data-route-step="0" aria-pressed="true" aria-controls="landing-route-panel" disabled><i class="ph-duotone ph-heart" aria-hidden="true"></i>Mis intereses</button>
                                    <button type="button" data-route-step="1" aria-pressed="false" aria-controls="landing-route-panel" disabled><i class="ph-duotone ph-buildings" aria-hidden="true"></i>Mis opciones</button>
                                    <button type="button" data-route-step="2" aria-pressed="false" aria-controls="landing-route-panel" disabled><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i>Mi decisión</button>
                                </div>
                            </div>
                            <div id="landing-route-panel" class="landing-route-panel">
                                <h4 id="landing-route-question">¿Qué te da curiosidad?</h4>
                                <p id="landing-route-hint">Prueba con una actividad que te gustaría hacer.</p>
                                <div id="landing-route-options" class="landing-route-options" role="group" aria-labelledby="landing-route-question"></div>
                                <p id="landing-route-feedback" class="landing-route-feedback" role="status">Este recorrido es un ejemplo. Tu orientación personal se construye en tu cuenta de estudiante.</p>
                            </div>
                            <div class="landing-route-footer"><p id="landing-route-counter">1 de 3 estaciones exploradas</p><button id="landing-route-next" class="btn-secondary" type="button">Siguiente estación<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button></div>
                            <progress id="landing-route-progress" class="landing-route-progress" value="1" max="3" aria-label="Estaciones exploradas en esta demostración"></progress>
                            <p class="landing-map-note"><strong>Tú llevas el rumbo.</strong> La orientación te ayuda a investigar y comprender tus opciones; la elección sigue siendo tuya.</p>
                        </div>
                    </div>
                    <div class="landing-benefits">
                        <article class="landing-benefit soft-panel scroll-reveal">
                            <span class="landing-symbol" aria-hidden="true"><i class="ph-duotone ph-heart"></i></span>
                            <h3>Conócete un poco más</h3>
                            <p>Un cuestionario de intereses te ayuda a poner en palabras lo que te gusta. Desde ahí puedes explorar carreras relacionadas con tu perfil.</p>
                            <a href="{{ route('aula-virtual.login') }}" class="landing-link">Entrar a Mis intereses<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article class="landing-benefit soft-panel scroll-reveal">
                            <span class="landing-symbol sky" aria-hidden="true"><i class="ph-duotone ph-buildings"></i></span>
                            <h3>Mira más allá del nombre de una carrera</h3>
                            <p>Explora universidades, materias y planes de estudio con la información disponible y sus fuentes. Descubre qué aprenderías antes de elegir.</p>
                            <a href="{{ route('aula-virtual.login') }}" class="landing-link">Entrar a Mi futuro académico<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
                        </article>
                        <article class="landing-benefit soft-panel scroll-reveal">
                            <span class="landing-symbol violet" aria-hidden="true"><i class="ph-duotone ph-chats-circle"></i></span>
                            <h3>Un espacio para tus preguntas</h3>
                            <p>El asistente de estudio está pensado para ayudarte a comprender temas, practicar con ejemplos y consultar dudas. Sus respuestas dependen de la información disponible.</p>
                            <a href="{{ route('aula-virtual.login') }}" class="landing-link">Ingresar al Aula Virtual<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>
                        </article>
                    </div>
                    <div class="landing-invitation scroll-reveal">
                        <div><span class="landing-symbol" aria-hidden="true"><i class="ph-duotone ph-student"></i></span><div><h3>Tu futuro también puede empezar aquí.</h3><p>Conoce el colegio y consulta en la institución los requisitos y fechas de inscripción.</p></div></div>
                        <a href="#contacto" class="btn-primary rounded-2xl text-sm font-bold">Consultar la inscripción<i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></a>
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

        <nav class="landing-section-controls glass" aria-label="Recorrer secciones de la página">
            <button type="button" id="landing-previous-section" aria-label="Sección anterior" aria-describedby="landing-section-position"><i class="ph-duotone ph-arrow-up" aria-hidden="true"></i></button>
            <div id="landing-section-position" class="landing-section-position"><strong>Inicio</strong><span>1 de 7</span></div>
            <button type="button" id="landing-next-section" aria-label="Sección siguiente" aria-describedby="landing-section-position"><i class="ph-duotone ph-arrow-down" aria-hidden="true"></i></button>
        </nav>

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
                mobileMenuButton.setAttribute('aria-expanded', String(!isHidden));
                mobileMenuButton.setAttribute('aria-label', isHidden ? 'Abrir menú' : 'Cerrar menú');

                if (menuOpenIcon && menuCloseIcon) {
                    menuOpenIcon.classList.toggle('hidden', !isHidden);
                    menuCloseIcon.classList.toggle('hidden', isHidden);
                }
            };

            const closeMobileMenu = () => {
                if (!mobileMenu) return;

                mobileMenu.classList.add('hidden');
                mobileMenuButton.setAttribute('aria-expanded', 'false');
                mobileMenuButton.setAttribute('aria-label', 'Abrir menú');

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

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            const surfaceSelector = '.strong-panel, .soft-panel, .mini-panel, .media-card';
            const cards = [...document.querySelectorAll(`main ${surfaceSelector.replaceAll(', ', ', main ')}`)]
                .filter(card => !card.querySelector(surfaceSelector) && !card.closest('.landing-intro-grid'));
            const visualItems = [...document.querySelectorAll('.landing-intro-grid > div, .landing-campus, .landing-illustration, .landing-innovation-copy')];
            const sectionHeadings = [...document.querySelectorAll('main h1, main > section > .mx-auto > .scroll-reveal')]
                .filter(item => !item.closest('.landing-intro-grid') && !item.querySelector('.landing-illustration'));
            const entryItems = [...new Set([...cards, ...sectionHeadings,
                ...visualItems, ...[...revealItems].filter(item => !item.querySelector(surfaceSelector) &&
                    !item.querySelector('.landing-campus, .landing-illustration') && !item.closest('.landing-intro-grid'))])];
            const animations = new Set();
            const itemAnimations = new WeakMap();
            const enteredItems = new WeakSet();
            const animateEntry = (item, index = 0) => {
                if (reducedMotion.matches || typeof item.animate !== 'function') return;
                itemAnimations.get(item)?.cancel();
                const fromLeft = item.matches('.landing-welcome, .landing-innovation-copy') || item.closest('.scroll-reveal-left');
                const fromRight = item.matches('.landing-identity, .landing-illustration') || item.closest('.scroll-reveal-right');
                const isImage = item.matches('.landing-campus, .media-card');
                const initialTransform = isImage ? 'scale(1.035)' : fromLeft ? 'translate(-24px, 10px)' : fromRight ? 'translate(24px, 10px)' : 'translateY(22px) scale(.99)';
                const animation = item.animate([
                    { opacity: .65, transform: initialTransform },
                    { opacity: 1, transform: 'none' }
                ], { duration: isImage || fromLeft || fromRight ? 700 : 560, delay: Math.min(index * 55, 220), easing: 'cubic-bezier(.16,1,.3,1)' });
                animations.add(animation);
                itemAnimations.set(item, animation);
                animation.onfinish = animation.oncancel = () => {
                    animations.delete(animation);
                    if (itemAnimations.get(item) === animation) itemAnimations.delete(item);
                };
            };
            reducedMotion.addEventListener('change', () => {
                if (reducedMotion.matches) [...animations].forEach(animation => animation.cancel());
            });

            const routeIllustration = document.querySelector('.landing-illustration');
            if (routeIllustration) {
                const routeSteps = [
                    {
                        question: '¿Qué te da curiosidad?',
                        hint: 'Prueba con una actividad que te gustaría hacer.',
                        options: [
                            { label: 'Crear algo nuevo', detail: 'Crear puede empezar con una idea, un dibujo o una solución. Piensa en qué actividad te gustaría probar de verdad.' },
                            { label: 'Resolver un reto', detail: '¿Disfrutas entender cómo funciona algo? Explorar problemas y proyectos puede ayudarte a reconocer lo que despierta tu curiosidad.' },
                            { label: 'Ayudar a alguien', detail: 'Piensa en las situaciones en las que te gusta acompañar a otras personas. Esa curiosidad también merece un espacio en tu exploración.' }
                        ],
                        feedback: 'Este recorrido es un ejemplo. Tu orientación personal se construye en tu cuenta de estudiante.'
                    },
                    {
                        question: '¿Qué descubrirías de una carrera?',
                        hint: 'Elige una pregunta para abrir una nueva pista.',
                        options: [
                            { label: 'Lo que se aprende', detail: 'Mira las materias y los proyectos del plan de estudio. Te ayudan a imaginar lo que aprenderías durante la carrera.' },
                            { label: 'Dónde se estudia', detail: 'Explora las universidades que ofrecen la carrera y consulta sus fuentes. Luego puedes decidir cuál investigar con más detalle.' },
                            { label: 'Cómo prepararme', detail: 'Identifica los temas que necesitas fortalecer y empieza con una actividad pequeña. Prepararte también forma parte del camino.' }
                        ],
                        feedback: 'El nombre de una carrera es solo el comienzo: descubrir qué se estudia te ayuda a entenderla mejor.'
                    },
                    {
                        question: '¿Cuál será tu primer pequeño paso?',
                        hint: 'No necesitas decidirlo todo para empezar a explorar.',
                        options: [
                            { label: 'Probar una especialidad', detail: 'Conoce las especialidades del colegio y busca una actividad práctica que te dé curiosidad.' },
                            { label: 'Conocer mis intereses', detail: 'En tu cuenta puedes responder el cuestionario de intereses y explorar lo que te gusta con más detalle.' },
                            { label: 'Investigar universidades', detail: 'En Mi futuro académico puedes consultar carreras, universidades y planes de estudio con la información disponible.' }
                        ],
                        feedback: 'Has llegado a la última estación. Elige un pequeño paso y sigue explorando: tú llevas el rumbo.'
                    }
                ];
                const stationButtons = [...routeIllustration.querySelectorAll('[data-route-step]')];
                const routePanel = document.getElementById('landing-route-panel');
                const routeQuestion = document.getElementById('landing-route-question');
                const routeHint = document.getElementById('landing-route-hint');
                const routeOptions = document.getElementById('landing-route-options');
                const routeFeedback = document.getElementById('landing-route-feedback');
                const routeNext = document.getElementById('landing-route-next');
                const routeCounter = document.getElementById('landing-route-counter');
                const routeProgress = document.getElementById('landing-route-progress');
                const routePath = routeIllustration.querySelector('.landing-map-line');
                const routeTrace = routeIllustration.querySelector('.landing-map-trace');
                const routeMarker = routeIllustration.querySelector('.landing-route-marker');
                const pathLength = routePath.getTotalLength();
                const stationPositions = [0, .52, 1];
                const visitedStations = new Set([0]);
                const selections = [null, null, null];
                let currentRouteStep = 0;
                let routePosition = 0;
                let routeFrame;
                const positionMarker = (position) => {
                    routePosition = position;
                    const point = routePath.getPointAtLength(pathLength * position);
                    routeMarker.setAttribute('cx', point.x);
                    routeMarker.setAttribute('cy', point.y);
                    routeTrace.style.strokeDashoffset = String(100 - position * 100);
                };
                const moveMarker = (step, animate = true) => {
                    cancelAnimationFrame(routeFrame);
                    const destination = stationPositions[step];
                    if (reducedMotion.matches || !animate) {
                        positionMarker(destination);
                        return;
                    }
                    const origin = routePosition;
                    const started = performance.now();
                    const advance = (time) => {
                        const progress = Math.min(1, (time - started) / 650);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        positionMarker(origin + (destination - origin) * eased);
                        if (progress < 1) routeFrame = requestAnimationFrame(advance);
                    };
                    routeFrame = requestAnimationFrame(advance);
                };
                const showRouteStep = (step, animate = true) => {
                    currentRouteStep = step;
                    visitedStations.add(step);
                    const content = routeSteps[step];
                    stationButtons.forEach((button, index) => {
                        button.disabled = false;
                        button.setAttribute('aria-pressed', String(index === step));
                    });
                    routeQuestion.textContent = content.question;
                    routeHint.textContent = content.hint;
                    routeOptions.replaceChildren();
                    content.options.forEach((option, index) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = option.label;
                        button.setAttribute('aria-pressed', String(selections[step] === index));
                        button.addEventListener('click', () => {
                            selections[step] = index;
                            [...routeOptions.children].forEach((choice, choiceIndex) => choice.setAttribute('aria-pressed', String(choiceIndex === index)));
                            routeFeedback.textContent = option.detail;
                            animateEntry(routeFeedback);
                        });
                        routeOptions.append(button);
                    });
                    routeFeedback.textContent = selections[step] === null ? content.feedback : content.options[selections[step]].detail;
                    routeCounter.textContent = `${visitedStations.size} de 3 estaciones exploradas`;
                    routeProgress.value = visitedStations.size;
                    routeNext.textContent = step < 2 ? 'Siguiente estación' : 'Reiniciar recorrido';
                    moveMarker(step, animate);
                    if (animate) animateEntry(routePanel);
                };
                stationButtons.forEach((button, index) => button.addEventListener('click', () => showRouteStep(index)));
                routeNext.addEventListener('click', () => {
                    if (currentRouteStep < 2) showRouteStep(currentRouteStep + 1);
                    else {
                        selections.fill(null);
                        visitedStations.clear();
                        showRouteStep(0);
                    }
                });
                reducedMotion.addEventListener('change', () => {
                    if (reducedMotion.matches) moveMarker(currentRouteStep, false);
                });
                routeIllustration.dataset.routeReady = 'true';
                showRouteStep(0, false);
            }
            const sections = [...document.querySelectorAll('main > section[id]')];
            const navigationLinks = [...document.querySelectorAll('.landing-header .nav-link[href^="#"]')];
            const header = document.querySelector('.landing-header');
            let activeSection;
            const previousSection = document.getElementById('landing-previous-section');
            const nextSection = document.getElementById('landing-next-section');
            const sectionPosition = document.getElementById('landing-section-position');
            const setActiveSection = (section) => {
                if (activeSection === section) return;
                activeSection = section;
                document.body.dataset.landingSection = section.id;
                navigationLinks.forEach(link => {
                    if (link.hash === `#${section.id}`) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
                const index = sections.indexOf(section);
                previousSection.disabled = index === 0;
                nextSection.disabled = index === sections.length - 1;
                sectionPosition.querySelector('strong').textContent = navigationLinks.find(link => link.hash === `#${section.id}`)?.textContent.trim() || section.id;
                sectionPosition.querySelector('span').textContent = `${index + 1} de ${sections.length}`;
            };
            const updateScrollState = () => {
                const boundary = header.getBoundingClientRect().bottom;
                const atPageEnd = scrollY + innerHeight >= document.documentElement.scrollHeight - 4;
                let current = sections[0];
                let visibleArea = 0;
                sections.forEach(section => {
                    const bounds = section.getBoundingClientRect();
                    const visible = Math.max(0, Math.min(bounds.bottom, innerHeight) - Math.max(bounds.top, boundary));
                    if (visible > visibleArea) {
                        visibleArea = visible;
                        current = section;
                    }
                });
                if (atPageEnd) current = sections.at(-1);
                setActiveSection(current);
                if (scrollProgress) {
                    const available = document.documentElement.scrollHeight - innerHeight;
                    scrollProgress.style.width = `${available > 0 ? Math.min(100, scrollY / available * 100) : 0}%`;
                }
            };
            const updateHeaderHeight = () => {
                document.body.style.setProperty('--landing-header-height', `${header.getBoundingClientRect().height}px`);
                updateScrollState();
            };
            sections.forEach(section => {
                section.hidden = false;
                const heading = section.querySelector('h1, h2');
                if (heading) {
                    heading.id ||= `titulo-${section.id}`;
                    heading.tabIndex = -1;
                    section.setAttribute('aria-labelledby', heading.id);
                }
            });
            updateHeaderHeight();
            if ('ResizeObserver' in window) new ResizeObserver(updateHeaderHeight).observe(header);
            else window.addEventListener('resize', updateHeaderHeight);

            const navigateToSection = (id, { updateHistory = true, focus = true, smooth = true } = {}) => {
                const section = sections.find(item => item.id === id) || sections[0];
                closeMobileMenu();
                updateHeaderHeight();
                if (updateHistory && location.hash !== `#${section.id}`) history.pushState(null, '', `#${section.id}`);
                if (focus) section.querySelector('h1, h2')?.focus({ preventScroll: true });
                section.scrollIntoView({ block: 'start', behavior: smooth && !reducedMotion.matches ? 'smooth' : 'instant' });
                // Repetir un destino visible conserva una respuesta visual breve.
                entryItems.filter(item => section.contains(item) &&
                    item.getBoundingClientRect().top < innerHeight && item.getBoundingClientRect().bottom > header.getBoundingClientRect().bottom)
                    .forEach((item, index) => {
                        enteredItems.add(item);
                        animateEntry(item, index);
                    });
            };
            document.querySelectorAll('.landing-header a[href^="#"], main a[href^="#"]').forEach(link => {
                link.addEventListener('click', event => {
                    if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                    if (!sections.some(section => section.id === link.hash.slice(1))) return;
                    event.preventDefault();
                    navigateToSection(link.hash.slice(1));
                });
            });
            const restoreSection = () => navigateToSection(location.hash.slice(1), { updateHistory: false, smooth: false });
            previousSection.addEventListener('click', () => {
                const section = sections[sections.indexOf(activeSection) - 1];
                if (section) navigateToSection(section.id);
            });
            nextSection.addEventListener('click', () => {
                const section = sections[sections.indexOf(activeSection) + 1];
                if (section) navigateToSection(section.id);
            });
            window.addEventListener('popstate', restoreSection);
            window.addEventListener('hashchange', restoreSection);
            if (location.hash) navigateToSection(location.hash.slice(1), { updateHistory: false, focus: false, smooth: false });
            let scrollFrame;
            window.addEventListener('scroll', () => {
                if (scrollFrame) return;
                scrollFrame = requestAnimationFrame(() => {
                    scrollFrame = undefined;
                    updateScrollState();
                });
            }, { passive: true });
            updateScrollState();

            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries) => {
                    let stagger = 0;
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            if (!enteredItems.has(entry.target)) {
                                enteredItems.add(entry.target);
                                animateEntry(entry.target, stagger++);
                            }
                        } else if (entry.boundingClientRect.bottom <= 0 || entry.boundingClientRect.top >= innerHeight) {
                            enteredItems.delete(entry.target);
                            itemAnimations.get(entry.target)?.cancel();
                        }
                    });
                }, { threshold: .08, rootMargin: '0px 0px -24px 0px' });
                entryItems.forEach(item => revealObserver.observe(item));
            }

            window.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMobileMenu();
                }
            });
        });
    </script>
</body>

</html>
