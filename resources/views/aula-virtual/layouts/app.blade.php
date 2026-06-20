<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Aula Virtual | SAVP - TIS 3')</title>

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
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }

        .aula-bg {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.10), transparent 24%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.10), transparent 24%),
                linear-gradient(to bottom, var(--ui-bg), var(--ui-bg-soft));
            color: var(--ui-text);
        }

        .aula-shell {
            background: color-mix(in srgb, var(--ui-surface) 90%, transparent);
            border-color: var(--ui-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased ui-page">
    <x-banner />

    <div x-data="{ sidebarOpen: true, mobileSidebar: false, openUser: false }" class="aula-bg">
        <div class="flex min-h-screen">
            @include('aula-virtual.layouts.sidebar')

            <div class="flex min-h-screen flex-1 flex-col transition-all duration-300"
                :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-20'">
                @include('aula-virtual.layouts.topbar')

                <main class="flex-1 px-5 py-5 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>

</html>
