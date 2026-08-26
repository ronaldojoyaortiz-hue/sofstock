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
                @yield('page-title', 'Dashboard')
            </h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-4">

            {{-- Buscador --}}
            <form method="GET" action="{{ url()->current() }}" class="relative hidden md:block" role="search">
                <label for="global-search" class="sr-only">Buscar</label>
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 w-4 h-4 pointer-events-none">
                    @include('layouts.partials.icons', ['icon' => 'search'])
                </span>
                <input
                    id="global-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar..."
                    class="w-56 lg:w-72 pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                >
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
                    x-show="open" x-cloak
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
                            <li class="px-4 py-6 text-sm text-slate-400 text-center">Sin notificaciones nuevas</li>
                        @endforelse
                    </ul>
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
                        src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name ?? 'Admin') }}"
                        alt="Avatar de {{ auth()->user()->name ?? 'Usuario' }}"
                    >
                    <span class="hidden sm:block text-sm font-medium text-slate-700">
                        {{ auth()->user()->name ?? 'Administrador' }}
                    </span>
                    <span class="w-4 h-4 text-slate-400 hidden sm:block">@include('layouts.partials.icons', ['icon' => 'chevron-down'])</span>
                </button>

                <div
                    x-show="open" x-cloak
                    x-transition
                    class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden py-1"
                >
                    <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Mi perfil</a>
                    <a href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Configuración</a>
                    <hr class="my-1 border-slate-100">
                    <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
