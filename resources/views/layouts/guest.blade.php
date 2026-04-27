<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Franz Tamayo N°3 | Acceso</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .auth-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 15% 20%, rgba(16, 185, 129, 0.14), transparent 24%),
                radial-gradient(circle at 85% 18%, rgba(14, 165, 233, 0.12), transparent 22%),
                radial-gradient(circle at 80% 85%, rgba(59, 130, 246, 0.10), transparent 24%),
                linear-gradient(135deg, #f8fafc 0%, #f1f5f9 45%, #eef2f7 100%);
        }

        .auth-grid {
            position: absolute;
            inset: 0;
            opacity: .05;
            background-image:
                linear-gradient(rgba(15, 23, 42, 0.9) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, 0.9) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: radial-gradient(circle at center, black 45%, transparent 100%);
            pointer-events: none;
        }

        .auth-noise {
            position: absolute;
            inset: 0;
            opacity: .08;
            pointer-events: none;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.6) 0.5px, transparent 1px),
                radial-gradient(circle at 80% 30%, rgba(255,255,255,0.5) 0.5px, transparent 1px),
                radial-gradient(circle at 60% 80%, rgba(255,255,255,0.45) 0.5px, transparent 1px);
            background-size: 120px 120px, 160px 160px, 140px 140px;
        }

        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            opacity: .55;
            pointer-events: none;
            animation: orbFloat 16s ease-in-out infinite;
            will-change: transform;
        }

        .orb.one {
            width: 340px;
            height: 340px;
            top: -80px;
            left: -60px;
            background: rgba(16, 185, 129, 0.18);
            animation-delay: 0s;
        }

        .orb.two {
            width: 300px;
            height: 300px;
            top: 8%;
            right: -70px;
            background: rgba(14, 165, 233, 0.16);
            animation-delay: -4s;
        }

        .orb.three {
            width: 360px;
            height: 360px;
            bottom: -110px;
            left: 10%;
            background: rgba(45, 212, 191, 0.12);
            animation-delay: -7s;
        }

        .orb.four {
            width: 320px;
            height: 320px;
            right: 8%;
            bottom: -90px;
            background: rgba(59, 130, 246, 0.14);
            animation-delay: -10s;
        }

        .ring-line {
            position: absolute;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 9999px;
            pointer-events: none;
            animation: spinSlow linear infinite;
        }

        .ring-line.one {
            width: 540px;
            height: 540px;
            top: 6%;
            left: -120px;
            animation-duration: 34s;
        }

        .ring-line.two {
            width: 680px;
            height: 680px;
            right: -180px;
            bottom: -40px;
            animation-duration: 46s;
            animation-direction: reverse;
        }

        .ring-line::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 9999px;
            top: 16%;
            left: 84%;
            background: linear-gradient(135deg, #10b981, #38bdf8);
            box-shadow: 0 0 18px rgba(16, 185, 129, .25);
        }

        .floating-dot {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
            animation: dotFloat linear infinite;
        }

        .floating-dot.one {
            width: 10px;
            height: 10px;
            left: 12%;
            top: 26%;
            background: rgba(16, 185, 129, 0.35);
            animation-duration: 10s;
        }

        .floating-dot.two {
            width: 8px;
            height: 8px;
            right: 18%;
            top: 32%;
            background: rgba(14, 165, 233, 0.34);
            animation-duration: 12s;
        }

        .floating-dot.three {
            width: 6px;
            height: 6px;
            left: 22%;
            bottom: 18%;
            background: rgba(52, 211, 153, 0.30);
            animation-duration: 9s;
        }

        .floating-dot.four {
            width: 12px;
            height: 12px;
            right: 12%;
            bottom: 22%;
            background: rgba(56, 189, 248, 0.26);
            animation-duration: 14s;
        }

        .floating-dot.five {
            width: 7px;
            height: 7px;
            left: 52%;
            top: 14%;
            background: rgba(16, 185, 129, 0.22);
            animation-duration: 11s;
        }

        .floating-dot.six {
            width: 9px;
            height: 9px;
            right: 34%;
            bottom: 14%;
            background: rgba(59, 130, 246, 0.24);
            animation-duration: 13s;
        }

        .wave-band {
            position: absolute;
            left: -10%;
            width: 120%;
            height: 220px;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(10px);
            opacity: .16;
            background: linear-gradient(90deg, rgba(16,185,129,.10), rgba(14,165,233,.10));
            animation: waveMove 18s ease-in-out infinite;
        }

        .wave-band.one {
            bottom: 7%;
        }

        .wave-band.two {
            bottom: 1%;
            animation-delay: -7s;
        }

        .center-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at center, rgba(255,255,255,0.45), transparent 45%);
            opacity: .65;
        }

        .slot-wrap {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
        }

        @keyframes orbFloat {
            0%, 100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            25% {
                transform: translate3d(24px, -18px, 0) scale(1.03);
            }
            50% {
                transform: translate3d(-16px, 22px, 0) scale(0.98);
            }
            75% {
                transform: translate3d(18px, 10px, 0) scale(1.01);
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

        @keyframes dotFloat {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
            }
            20% {
                transform: translateY(-8px) translateX(6px);
            }
            50% {
                transform: translateY(-18px) translateX(-4px);
            }
            80% {
                transform: translateY(-6px) translateX(7px);
            }
        }

        @keyframes waveMove {
            0%, 100% {
                transform: translateX(0) scaleX(1);
            }
            50% {
                transform: translateX(3%) scaleX(1.03);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .orb,
            .ring-line,
            .floating-dot,
            .wave-band {
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="auth-shell">
        <div class="auth-grid"></div>
        <div class="auth-noise"></div>
        <div class="center-glow"></div>

        <div class="orb one"></div>
        <div class="orb two"></div>
        <div class="orb three"></div>
        <div class="orb four"></div>

        <div class="ring-line one"></div>
        <div class="ring-line two"></div>

        <div class="floating-dot one"></div>
        <div class="floating-dot two"></div>
        <div class="floating-dot three"></div>
        <div class="floating-dot four"></div>
        <div class="floating-dot five"></div>
        <div class="floating-dot six"></div>

        <div class="wave-band one"></div>
        <div class="wave-band two"></div>

        <div class="slot-wrap">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>