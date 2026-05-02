<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Franz Tamayo N°3 | Acceso</title>

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
    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|poppins:500,600,700,800,900&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --auth-bg: #f6fbf8;
            --auth-bg-soft: #f8fafc;
            --auth-surface: rgba(255, 255, 255, .72);
            --auth-border: rgba(203, 213, 225, .72);
            --auth-text: #0f172a;
            --auth-muted: #64748b;
            --auth-primary: #059669;
            --auth-primary-soft: rgba(16, 185, 129, .16);
            --auth-sky: #0284c7;
            --auth-sky-soft: rgba(14, 165, 233, .14);
            --auth-shadow: 0 28px 90px rgba(15, 23, 42, .12);
        }

        html.dark {
            --auth-bg: #07111f;
            --auth-bg-soft: #0f172a;
            --auth-surface: rgba(15, 23, 42, .72);
            --auth-border: rgba(71, 85, 105, .72);
            --auth-text: #f8fafc;
            --auth-muted: #94a3b8;
            --auth-primary: #34d399;
            --auth-primary-soft: rgba(52, 211, 153, .14);
            --auth-sky: #38bdf8;
            --auth-sky-soft: rgba(56, 189, 248, .14);
            --auth-shadow: 0 28px 95px rgba(0, 0, 0, .38);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--auth-text);
        }

        .font-display {
            font-family: 'Poppins', sans-serif;
        }

        .auth-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 14% 18%, color-mix(in srgb, var(--auth-primary) 18%, transparent), transparent 27%),
                radial-gradient(circle at 86% 16%, color-mix(in srgb, var(--auth-sky) 17%, transparent), transparent 26%),
                radial-gradient(circle at 72% 84%, color-mix(in srgb, var(--auth-primary) 10%, transparent), transparent 28%),
                linear-gradient(135deg, var(--auth-bg), var(--auth-bg-soft));
        }

        .auth-grid {
            position: absolute;
            inset: 0;
            opacity: .55;
            pointer-events: none;
            background-image:
                linear-gradient(to right, color-mix(in srgb, var(--auth-muted) 12%, transparent) 1px, transparent 1px),
                linear-gradient(to bottom, color-mix(in srgb, var(--auth-muted) 12%, transparent) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at center, black 36%, transparent 86%);
        }

        .auth-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at center, color-mix(in srgb, white 42%, transparent), transparent 46%);
            opacity: .55;
        }

        html.dark .auth-glow {
            background:
                radial-gradient(circle at center, rgba(15, 23, 42, .16), transparent 46%);
        }

        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(64px);
            opacity: .58;
            pointer-events: none;
            animation: orbFloat 16s ease-in-out infinite;
        }

        .orb.one {
            width: 360px;
            height: 360px;
            top: -90px;
            left: -80px;
            background: var(--auth-primary-soft);
        }

        .orb.two {
            width: 330px;
            height: 330px;
            top: 10%;
            right: -90px;
            background: var(--auth-sky-soft);
            animation-delay: -4s;
        }

        .orb.three {
            width: 390px;
            height: 390px;
            bottom: -130px;
            left: 12%;
            background: rgba(45, 212, 191, .13);
            animation-delay: -7s;
        }

        .orb.four {
            width: 340px;
            height: 340px;
            right: 8%;
            bottom: -110px;
            background: rgba(59, 130, 246, .14);
            animation-delay: -10s;
        }

        .ring-line {
            position: absolute;
            border: 1px solid color-mix(in srgb, var(--auth-muted) 20%, transparent);
            border-radius: 9999px;
            pointer-events: none;
            animation: spinSlow linear infinite;
        }

        .ring-line.one {
            width: 560px;
            height: 560px;
            top: 4%;
            left: -120px;
            animation-duration: 38s;
        }

        .ring-line.two {
            width: 720px;
            height: 720px;
            right: -210px;
            bottom: -70px;
            animation-duration: 52s;
            animation-direction: reverse;
        }

        .ring-line::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            top: 16%;
            left: 84%;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--auth-primary), var(--auth-sky));
            box-shadow: 0 0 20px color-mix(in srgb, var(--auth-primary) 42%, transparent);
        }

        .slot-wrap {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.25rem;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            25% {
                transform: translate3d(26px, -20px, 0) scale(1.03);
            }

            50% {
                transform: translate3d(-18px, 24px, 0) scale(.98);
            }

            75% {
                transform: translate3d(20px, 10px, 0) scale(1.01);
            }
        }

        @keyframes spinSlow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .orb,
            .ring-line {
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="auth-shell">
        <div class="auth-grid"></div>
        <div class="auth-glow"></div>

        <div class="orb one"></div>
        <div class="orb two"></div>
        <div class="orb three"></div>
        <div class="orb four"></div>

        <div class="ring-line one"></div>
        <div class="ring-line two"></div>

        <div class="slot-wrap">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>

</html>