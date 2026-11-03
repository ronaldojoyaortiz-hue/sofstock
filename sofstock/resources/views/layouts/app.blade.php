<!DOCTYPE html>
<html lang="{{ $idiomaSistema ?? 'es' }}" class="h-full bg-slate-50" style="--sofstock-font-size: {{ $tamanoLetra ?? 16 }}px; font-size: var(--sofstock-font-size)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inicio') - {{ $nombreSistema ?? config('app.name', 'SofStock') }}</title>

    <script>
        (() => {
            const theme = localStorage.getItem('sofstock-theme') || 'light';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
        window.setSofstockTheme = (theme) => {
            const selectedTheme = theme === 'dark' ? 'dark' : 'light';
            localStorage.setItem('sofstock-theme', selectedTheme);
            document.documentElement.classList.toggle('dark', selectedTheme === 'dark');
            document.body?.classList.toggle('dark', selectedTheme === 'dark');
            window.dispatchEvent(new CustomEvent('sofstock-theme-changed', { detail: selectedTheme }));
        };
    </script>

    {{-- Tailwind CSS. Reemplazar por tu build de Vite/PostCSS en producción (ver README). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Chart.js (solo se usa donde se necesita) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: var(--sofstock-font-size, 16px); }
        html.dark body { background-color: #0f172a; color: #e2e8f0; }
        html.dark .bg-white { background-color: #1e293b !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; }
        html.dark .bg-slate-100 { background-color: #334155 !important; }
        html.dark .bg-slate-200 { background-color: #475569 !important; }
        html.dark .text-slate-900, html.dark .text-slate-800 { color: #f1f5f9 !important; }
        html.dark .text-slate-700, html.dark .text-slate-600 { color: #cbd5e1 !important; }
        html.dark .text-slate-500 { color: #94a3b8 !important; }
        html.dark .border-slate-100, html.dark .border-slate-200,
        html.dark .border-slate-300 { border-color: #475569 !important; }
        html.dark input:not([type='checkbox']):not([type='radio']),
        html.dark select, html.dark textarea { background-color: #1e293b; color: #f1f5f9; border-color: #475569; }
        html.dark input::placeholder, html.dark textarea::placeholder { color: #94a3b8; }
        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: #334155; }
        html.dark .hover\:bg-slate-50:hover { background-color: #334155 !important; }
        html.dark .hover\:bg-slate-100:hover { background-color: #475569 !important; }
    </style>

    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased">

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

        {{-- Overlay móvil --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
            aria-hidden="true"
        ></div>

        @include('layouts.partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            @include('layouts.partials.navbar')

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
                @endif

                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

    @stack('scripts')
</body>
</html>
