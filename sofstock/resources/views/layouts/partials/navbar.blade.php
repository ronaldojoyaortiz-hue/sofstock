<header class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">

        <div class="flex items-center gap-4 min-w-0">
            {{-- Botón menú móvil --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                type="button"
                class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Abrir menú lateral"
            >
                <span class="w-6 h-6 block">@include('layouts.partials.icons', ['icon' => 'menu'])</span>
            </button>

            <h1 class="text-lg sm:text-xl font-semibold text-slate-800 truncate">
                @yield('page-title', 'Inicio')
            </h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-4">

            <button
                type="button"
                onclick="setSofstockTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')"
                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Cambiar modo claro u oscuro"
                title="Cambiar modo claro u oscuro"
            >
                <span class="block h-5 w-5" aria-hidden="true">◐</span>
            </button>

            {{-- Buscador --}}
            <form method="GET" action="{{ route('search.index') }}" class="relative flex min-w-0" role="search">
                <label for="global-search" class="sr-only">Buscar</label>
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 w-4 h-4 pointer-events-none">
                    @include('layouts.partials.icons', ['icon' => 'search'])
                </span>
                <input
                    id="global-search"
                    type="search"
                    name="q"
                    value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                    placeholder="Buscar..."
                    class="w-20 sm:w-36 lg:w-56 pl-8 sm:pl-9 pr-2 sm:pr-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                >
                <button type="submit" class="ml-1 sm:ml-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" aria-label="Buscar" title="Buscar">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
                        <path d="m20 20-4-4" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                </button>
            </form>

            {{-- Notificaciones --}}
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    @click.outside="open = false"
                    type="button"
                    class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    aria-label="Ver notificaciones"
                >
                    <span class="w-5 h-5 block">@include('layouts.partials.icons', ['icon' => 'bell'])</span>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden"
                >
                    <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">
                        Notificaciones
                    </div>

                    <ul class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                        @forelse (($notifications ?? []) as $notification)
                            <li class="px-4 py-3 text-sm hover:bg-slate-50">
                                <p class="text-slate-700">{{ $notification['message'] }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $notification['time'] }}</p>
                            </li>
                        @empty
                            <li class="px-4 py-6 text-sm text-slate-400 text-center">
                                Sin notificaciones nuevas
                            </li>
                        @endforelse
                    </ul>

                    @php
                        $usuarioActual = auth()->user();
                        $puedeCrearNotificaciones = $usuarioActual->hasRole('administrador')
                            || mb_strtolower(trim($usuarioActual->rol?->nombre_rol ?? '')) === 'administrador'
                            || $usuarioActual->can('crear notificaciones');
                    @endphp
                    @if ($puedeCrearNotificaciones)
                        <div class="border-t border-slate-100 p-3">
                            <a
                                href="{{ route('notificaciones.create') }}"
                                class="block rounded-lg bg-primary-600 px-3 py-2 text-center text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                            >
                                Crear notificación
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Menú de usuario --}}
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    @click.outside="open = false"
                    type="button"
                    class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()"
                >
                    <img
                        class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200"
                        src="{{ auth()->user()?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()?->nombre ?? 'Usuario') }}"
                        alt="Avatar del usuario"
                    >

                    {{-- Mostrar el rol en lugar del nombre --}}
                    <span class="hidden sm:block text-sm font-medium text-slate-700">
                        {{ auth()->user()?->rol?->nombre_rol ?? 'Sin rol asignado' }}
                    </span>

                    <span class="w-4 h-4 text-slate-400 hidden sm:block">
                        @include('layouts.partials.icons', ['icon' => 'chevron-down'])
                    </span>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden py-1"
                >
                    <a
                        href="{{ route('password.change') }}"
                        class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Cambiar contraseña
                    </a>

                    <a
                        href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}"
                        class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
                    >
                        Configuración
                    </a>

                    <hr class="my-1 border-slate-100">

                    <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                        >
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
