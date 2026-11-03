@extends('layouts.app')

@section('title', 'Redactar notificación')
@section('page-title', 'Redactar notificación')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Crear notificación</h1>
        <p class="mt-1 text-sm text-slate-500">Envía el aviso a los usuarios de uno o varios roles, o selecciona destinatarios individuales.</p>
    </div>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('notificaciones.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="titulo" class="mb-1 block text-sm font-medium text-slate-700">Título</label>
                <input id="titulo" name="titulo" type="text" maxlength="150" required value="{{ old('titulo') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                @error('titulo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="mensaje" class="mb-1 block text-sm font-medium text-slate-700">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="5" maxlength="10000" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('mensaje') }}</textarea>
                @error('mensaje') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="fecha_evento" class="mb-1 block text-sm font-medium text-slate-700">Fecha y hora del evento <span class="font-normal text-slate-400">(opcional)</span></label>
                <input id="fecha_evento" name="fecha_evento" type="datetime-local" value="{{ old('fecha_evento') }}" class="w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                @error('fecha_evento') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="lugar" class="mb-1 block text-sm font-medium text-slate-700">Lugar <span class="font-normal text-slate-400">(opcional)</span></label>
                <input id="lugar" name="lugar" type="text" maxlength="200" value="{{ old('lugar') }}" placeholder="Ej. Sala de juntas principal" class="w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                @error('lugar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <fieldset class="rounded-lg border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-semibold text-slate-700">Enviar a roles</legend>
                    <p class="mb-3 text-xs text-slate-500">Solo aparecen roles registrados. Se incluirán sus usuarios activos.</p>
                    @error('roles') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('roles.*') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mb-2 flex justify-end">
                        <button id="seleccionar-todos-roles" type="button" class="text-xs font-medium text-primary-700 hover:text-primary-900">
                            Seleccionar todos
                        </button>
                    </div>
                    @forelse ($roles as $rol)
                        <label class="flex items-start gap-2 py-1.5 text-sm text-slate-700">
                            <input type="checkbox" name="roles[]" value="{{ $rol->id_rol }}" data-rol-destinatario @checked(in_array((string) $rol->id_rol, old('roles', []), true)) class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <span>{{ $rol->nombre_rol }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-500">No hay roles disponibles.</p>
                    @endforelse
                </fieldset>

                <fieldset class="rounded-lg border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-semibold text-slate-700">Enviar a usuarios</legend>
                    <p id="ayuda-usuarios" class="mb-3 text-xs text-slate-500">Selecciona uno o varios roles para ver sus usuarios.</p>
                    @error('usuarios') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('usuarios.*') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mb-2 flex justify-end">
                        <button id="seleccionar-todos-usuarios" type="button" class="text-xs font-medium text-primary-700 hover:text-primary-900">
                            Seleccionar todos los visibles
                        </button>
                    </div>
                    <div id="usuarios-destinatarios" class="max-h-64 space-y-1 overflow-y-auto">
                        @forelse ($usuarios as $usuario)
                            <label data-usuario-rol="{{ $usuario->id_rol }}" @class([
                                'hidden' => ! in_array((string) $usuario->id_rol, old('roles', []), true),
                                'flex items-start gap-2 py-1.5 text-sm text-slate-700',
                            ])>
                                <input type="checkbox" name="usuarios[]" value="{{ $usuario->id }}" @checked(in_array((string) $usuario->id, old('usuarios', []), true)) class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <span>
                                    {{ trim($usuario->nombre . ' ' . $usuario->apellido) }}
                                    <span class="block text-xs text-slate-400">{{ $usuario->correo }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">No hay usuarios activos disponibles.</p>
                        @endforelse
                    </div>
                    <p id="selecciona-rol-usuarios" class="mt-2 text-sm text-slate-500">Selecciona un rol para mostrar sus usuarios.</p>
                    <p id="sin-usuarios-rol" class="mt-2 hidden text-sm text-slate-500">No hay usuarios activos con los roles seleccionados.</p>
                </fieldset>
            </div>

            @error('destinatarios')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Crear notificación</x-button>
                <a href="{{ route('notificaciones.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </a>
            </div>
        </form>
    </x-card>
@endsection

@push('scripts')
    <script>
        (() => {
            const roles = [...document.querySelectorAll('[data-rol-destinatario]')];
            const contenedorUsuarios = document.getElementById('usuarios-destinatarios');
            const mensajeVacio = document.getElementById('sin-usuarios-rol');
            const mensajeSeleccionaRol = document.getElementById('selecciona-rol-usuarios');
            const ayudaUsuarios = document.getElementById('ayuda-usuarios');
            const botonTodosRoles = document.getElementById('seleccionar-todos-roles');
            const botonTodosUsuarios = document.getElementById('seleccionar-todos-usuarios');

            if (!contenedorUsuarios || !mensajeVacio) {
                return;
            }

            const filasUsuarios = [...contenedorUsuarios.querySelectorAll('[data-usuario-rol]')];

            const actualizarUsuarios = (limpiarOcultos = false) => {
                const rolesSeleccionados = roles.filter((rol) => rol.checked).map((rol) => rol.value);
                let visibles = 0;

                filasUsuarios.forEach((fila) => {
                    const mostrar = rolesSeleccionados.includes(fila.dataset.usuarioRol);
                    fila.classList.toggle('hidden', !mostrar);

                    if (!mostrar && limpiarOcultos) {
                        const casilla = fila.querySelector('input[type="checkbox"]');
                        if (casilla) {
                            casilla.checked = false;
                        }
                    }

                    if (mostrar) {
                        visibles += 1;
                    }
                });

                mensajeSeleccionaRol?.classList.toggle('hidden', rolesSeleccionados.length > 0);
                mensajeVacio.classList.toggle('hidden', rolesSeleccionados.length === 0 || visibles > 0);
                if (ayudaUsuarios) {
                    ayudaUsuarios.textContent = rolesSeleccionados.length > 0
                        ? 'Selecciona los usuarios activos que recibirán la notificación.'
                        : 'Selecciona uno o varios roles para ver sus usuarios.';
                }
            };

            roles.forEach((rol) => {
                rol.addEventListener('change', () => actualizarUsuarios(true));
            });

            botonTodosRoles?.addEventListener('click', () => {
                const seleccionarTodos = roles.some((rol) => !rol.checked);
                roles.forEach((rol) => {
                    rol.checked = seleccionarTodos;
                });
                actualizarUsuarios(true);
                botonTodosRoles.textContent = seleccionarTodos ? 'Quitar selección de roles' : 'Seleccionar todos';
            });

            botonTodosUsuarios?.addEventListener('click', () => {
                const usuariosVisibles = filasUsuarios.filter((fila) => !fila.classList.contains('hidden'));
                const seleccionarTodos = usuariosVisibles.some((fila) => !fila.querySelector('input[type="checkbox"]')?.checked);

                usuariosVisibles.forEach((fila) => {
                    const casilla = fila.querySelector('input[type="checkbox"]');
                    if (casilla) {
                        casilla.checked = seleccionarTodos;
                    }
                });

                botonTodosUsuarios.textContent = seleccionarTodos
                    ? 'Quitar selección de usuarios visibles'
                    : 'Seleccionar todos los visibles';
            });

            actualizarUsuarios();
        })();
    </script>
@endpush
