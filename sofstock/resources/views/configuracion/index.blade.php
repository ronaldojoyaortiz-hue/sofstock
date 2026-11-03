@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Configuración general</h1>
    <p class="mt-1 text-sm text-slate-500">Personaliza el nombre, el idioma, el tamaño de letra y la apariencia de SofStock.</p>
    <a href="{{ route('password.change') }}" class="mt-3 inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
        Cambiar contraseña
    </a>
</div>

<x-card title="Apariencia" class="mb-6 max-w-3xl">
    <p class="mb-4 text-sm text-slate-500">Elige cómo quieres ver el sistema en este navegador.</p>
    <div class="flex flex-wrap gap-3">
        <button type="button" onclick="setSofstockTheme('light')"
            class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
            ☀️ Modo claro
        </button>
        <button type="button" onclick="setSofstockTheme('dark')"
            class="rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-medium text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500">
            🌙 Modo oscuro
        </button>
    </div>
</x-card>

@unless ($tablaDisponible)
    <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Para guardar estos cambios, aplica las migraciones pendientes con PHP 8.4.1 o superior y ejecuta <code>php artisan migrate</code>.
    </div>
@endunless

<x-card title="Datos de la aplicación" class="max-w-3xl">
        <form id="configuracion-form" method="POST" action="{{ route('settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="nombre_empresa" class="mb-1 block text-sm font-medium text-slate-700">Nombre de la empresa o sistema</label>
                <input id="nombre_empresa" name="nombre_empresa" type="text" maxlength="120" required value="{{ old('nombre_empresa', $configuracion->nombre_empresa) }}" @disabled(! $tablaDisponible) class="w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                @error('nombre_empresa') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-slate-500">Se muestra en el título, el menú y el pie de página.</p>
            </div>

            <div>
                <label for="idioma" class="mb-1 block text-sm font-medium text-slate-700">Idioma</label>
                <select id="idioma" name="idioma" required @disabled(! $tablaDisponible) class="w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    @foreach ($idiomas as $codigo => $nombre)
                        <option value="{{ $codigo }}" @selected(old('idioma', $configuracion->idioma ?? 'es') === $codigo)>{{ $nombre }}</option>
                    @endforeach
                </select>
                @error('idioma') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-slate-500">El cambio se aplica a los formatos y mensajes traducibles. Algunas pantallas aún conservan textos en español.</p>
            </div>

            <div>
                <label for="tamano_letra" class="mb-1 block text-sm font-medium text-slate-700">Tamaño de letra</label>
                <select id="tamano_letra" name="tamano_letra" required @disabled(! $tablaDisponible) class="w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    @foreach ($tamanosLetra as $valor => $nombre)
                        <option value="{{ $valor }}" @selected((int) old('tamano_letra', $configuracion->tamano_letra ?? 16) === $valor)>{{ $nombre }} ({{ $valor }} px)</option>
                    @endforeach
                </select>
                @error('tamano_letra') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <p class="border-t border-slate-100 pt-4 text-sm text-slate-500" role="status">
                Los cambios se guardan automáticamente al modificar cada campo.
            </p>
        </form>
</x-card>
@endsection

@push('scripts')
    @if ($tablaDisponible)
        <script>
            document.getElementById('configuracion-form')?.addEventListener('change', (event) => {
                if (event.target.matches('input, select')) {
                    event.currentTarget.requestSubmit();
                }
            });
        </script>
    @endif
@endpush
