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
            <img
                src="{{ asset('images/sofstock-logo.png') }}"
                alt="Logo de SofStock"
                class="w-10 h-10 flex-shrink-0 rounded-full object-cover"
            >
            <span class="text-white font-semibold text-lg tracking-tight">{{ $nombreSistema ?? config('app.name', 'SofStock') }}</span>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-1" aria-label="Navegación lateral">

            @php
                $links = [
                    ['label' => 'Inicio', 'route' => 'dashboard.index', 'icon' => 'home', 'module' => 'dashboard'],
                    ['label' => 'Usuarios', 'route' => 'usuarios.index', 'icon' => 'users', 'module' => 'usuarios'],
                    ['label' => 'Perfiles/Roles', 'route' => 'roles.index', 'icon' => 'shield', 'module' => 'roles'],
                    ['label' => 'Gestionar permisos', 'route' => 'seguridad.permisos.index', 'icon' => 'shield', 'module' => 'seguridad', 'adminOnly' => true],
                    ['label' => 'Categorías', 'route' => 'categorias.index', 'icon' => 'tag', 'module' => 'categorias'],
                    ['label' => 'Productos', 'route' => 'productos.index', 'icon' => 'box', 'module' => 'productos'],
                    ['label' => 'Detalles de factura', 'route' => 'detallefacturas.index', 'icon' => 'chart', 'module' => 'detallefacturas'],
                    ['label' => 'Facturas', 'route' => 'facturas.index', 'icon' => 'chart', 'module' => 'facturas'],
                    ['label' => 'Proveedores', 'route' => 'proveedores.index', 'icon' => 'users', 'module' => 'proveedores'],
                    ['label' => 'Inmobiliarios', 'route' => 'inmuebles.index', 'icon' => 'home', 'module' => 'inmuebles'],
                    ['label' => 'Contratos', 'route' => 'contratos.index', 'icon' => 'history', 'module' => 'contratos'],
                    ['label' => 'Actas', 'route' => 'actas.index', 'icon' => 'history', 'module' => 'actas'],
                    ['label' => 'Reportes', 'route' => 'reports.index', 'icon' => 'chart', 'module' => 'reportes'],
                    ['label' => 'Auditorías', 'route' => 'auditorias.index', 'icon' => 'history', 'module' => 'auditorias'],
                ];
                $usuarioActual = auth()->user();
                $esAdministrador = $usuarioActual?->esAdministrador() ?? false;
            @endphp

            @foreach ($links as $link)
                @php
                    $tienePermisoModulo = $esAdministrador || (
                        $usuarioActual?->can('acceder ' . $link['module'])
                        && $usuarioActual?->can('ver ' . $link['module'])
                    );
                @endphp
                @continue(($link['adminOnly'] ?? false) ? ! $esAdministrador : ! $tienePermisoModulo)
                @php
                    $active = Route::has($link['route']) && (
                        $link['route'] === 'home'
                            ? request()->routeIs('home')
                            : request()->routeIs(explode('.', $link['route'])[0] . '.*')
                    );
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
