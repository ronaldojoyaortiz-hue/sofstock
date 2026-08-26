{{--
    Sidebar principal.
    Usa la variable de ruta actual (Route::currentRouteName() o request()->routeIs())
    para resaltar el enlace activo.
--}}
<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transform transition-transform duration-200 ease-in-out lg:translate-x-0"
    aria-label="Menú principal"
>
    <div class="h-full flex flex-col">

        {{-- Logo --}}
        <div class="flex items-center gap-3 h-16 px-6 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold">
                A
            </div>
            <span class="text-white font-semibold text-lg tracking-tight">{{ config('app.name', 'AdminPanel') }}</span>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-1" aria-label="Navegación lateral">

            @php
                $links = [
                    ['label' => 'Dashboard',   'route' => 'dashboard.index',  'icon' => 'home'],
                    ['label' => 'Usuarios',    'route' => 'users.index',      'icon' => 'users'],
                    ['label' => 'Perfiles/Roles', 'route' => 'roles.index',   'icon' => 'shield'],
                    ['label' => 'Categorías',  'route' => 'categories.index', 'icon' => 'tag'],
                    ['label' => 'Productos',   'route' => 'producto.index',   'icon' => 'box'],
                    ['label' => 'Reportes',    'route' => 'reports.index',    'icon' => 'chart'],
                    ['label' => 'Configuración', 'route' => 'settings.index', 'icon' => 'cog'],
                ];
            @endphp

            @foreach ($links as $link)
                @php
                    $active = Route::has($link['route']) && request()->routeIs(explode('.', $link['route'])[0] . '.*');
                @endphp
                <a
                    href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                    @if($active) aria-current="page" @endif
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                        {{ $active
                            ? 'bg-primary-600 text-white shadow-sm'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    <span class="w-5 h-5 flex-shrink-0" aria-hidden="true">
                        @include('layouts.partials.icons', ['icon' => $link['icon']])
                    </span>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Cerrar sesión --}}
        <div class="px-3 py-4 border-t border-slate-800">
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                    aria-label="Cerrar sesión"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</aside>
