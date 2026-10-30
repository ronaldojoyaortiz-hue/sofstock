@extends('layouts.app')

@section('title', 'Permisos por rol')
@section('page-title', 'Permisos por rol')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Permisos por rol</h1>
            <p class="mt-1 text-sm text-slate-500">Administra qué permisos tiene cada rol.</p>
        </div>
        @if ($showMatrix && $canManage)
            <x-button variant="secondary" href="{{ route('seguridad.permisos.roles') }}">Volver a roles</x-button>
        @else
            <x-button href="{{ route('seguridad.permisos.roles', ['gestionar' => 1]) }}">Crear permiso</x-button>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @unless ($showMatrix && $canManage)
    <x-card>
        @if ($roles->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                No hay roles registrados.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Permisos asignados</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($roles as $role)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-slate-600">{{ $role->id }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium capitalize text-slate-800">{{ $role->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $role->permissions->count() }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('seguridad.permisos.roles.show', $role->id) }}"
                                           class="rounded-md bg-slate-100 p-2 text-slate-700 hover:bg-slate-200 hover:text-slate-900"
                                           title="Ver información completa"
                                           aria-label="Ver información completa del rol {{ $role->name }}">
                                            <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                        </a>
                                        <a href="{{ route('seguridad.permisos.roles', ['role_id' => $role->id, 'gestionar' => 1]) }}"
                                           class="rounded-md p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-900"
                                           title="Editar permisos"
                                           aria-label="Editar permisos de {{ $role->name }}">
                                            <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'pencil'])</span>
                                        </a>
                                        @if ($role->name !== 'administrador')
                                            <form action="{{ route('seguridad.permisos.roles.destroy', $role->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Deseas eliminar el rol {{ $role->name }}? No se puede eliminar si tiene usuarios asignados.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-900"
                                                        title="Eliminar rol {{ $role->name }}"
                                                        aria-label="Eliminar rol {{ $role->name }}">
                                                    <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'trash'])</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-3 py-2 text-sm text-slate-400" title="El rol administrador está protegido">
                                                Protegido
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
    @endunless

    @if ($showMatrix && $selectedRole)
        <x-card class="mt-6" :padding="false">
            <form action="{{ route('seguridad.permisos.roles') }}" method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
                <input type="hidden" name="gestionar" value="1">
                <div class="min-w-56 flex-1">
                    <label for="permission-role" class="mb-1.5 block text-sm font-medium text-slate-700">Selecciona el rol</label>
                    <select id="permission-role" name="role_id" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected($selectedRole->id === $role->id)>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-button type="submit">Mostrar módulos</x-button>
            </form>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold capitalize text-slate-800">Permisos de {{ $selectedRole->name }}</h2>
                    @if (! $canManage)
                        <p class="mt-1 text-sm text-slate-500">Consulta los permisos asignados a este rol.</p>
                    @elseif ($selectedRole->name === 'administrador')
                        <p class="mt-1 text-sm text-slate-500">Selecciona los permisos que tendrá este rol. El administrador siempre podrá volver a esta pantalla.</p>
                    @else
                        <p class="mt-1 text-sm text-slate-500">Selecciona las acciones que este rol podrá realizar en cada módulo.</p>
                    @endif
                </div>
                <a href="{{ route('seguridad.permisos.roles') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">Cerrar</a>
            </div>

            @if ($canManage)
                <form action="{{ route('seguridad.permisos.update', $selectedRole) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5 p-5 sm:p-6">
                        @forelse ($permissions as $module => $modulePermissions)
                            <section class="rounded-lg border border-slate-200">
                                <h3 class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold capitalize text-slate-700">
                                    {{ $module }}
                                </h3>
                                <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($modulePermissions as $permission)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 px-3 py-3 text-sm text-slate-700 transition hover:border-violet-300 hover:bg-violet-50">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                @checked($selectedRole->permissions->contains('id', $permission->id))
                                                class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                                aria-label="{{ $permission->name }}">
                                            <span>{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @empty
                            <p class="text-sm text-slate-500">No hay permisos registrados para asignar.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-end border-t border-slate-100 px-5 py-4">
                        <x-button type="submit">Guardar permisos</x-button>
                    </div>
                </form>
            @else
                <div class="space-y-5 p-5 sm:p-6">
                    @forelse ($permissions as $module => $modulePermissions)
                        @php
                            $assignedPermissions = $modulePermissions
                                ->filter(fn ($permission) => $selectedRole->permissions->contains('id', $permission->id));
                        @endphp
                        <section class="rounded-lg border border-slate-200">
                            <h3 class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold capitalize text-slate-700">
                                {{ $module }}
                            </h3>
                            <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                                @forelse ($assignedPermissions as $permission)
                                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-800">
                                        {{ $permission->name }}
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Sin permisos asignados en este módulo.</p>
                                @endforelse
                            </div>
                        </section>
                    @empty
                        <p class="text-sm text-slate-500">No hay permisos registrados.</p>
                    @endforelse
                </div>
            @endif
        </x-card>
    @endif
@endsection
