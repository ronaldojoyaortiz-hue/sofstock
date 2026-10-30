@extends('layouts.app')

@section('title', 'Detalle del rol')
@section('page-title', 'Detalle del rol')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold capitalize text-slate-800">{{ $role->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Información completa del rol y sus permisos asignados.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-button variant="secondary" href="{{ route('seguridad.permisos.index') }}">Volver a permisos</x-button>
        </div>
    </div>

    <x-card class="mb-6">
        <h2 class="mb-5 text-lg font-semibold text-slate-800">Información del rol</h2>
        <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">ID del rol de seguridad</dt>
                <dd class="mt-1 font-mono font-medium text-slate-800">{{ $role->id }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Nombre</dt>
                <dd class="mt-1 font-medium capitalize text-slate-800">{{ $role->name }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Guard</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $role->guard_name }}</dd>
            </div>
            @if ($legacyRole)
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">ID del rol en usuarios</dt>
                    <dd class="mt-1 font-mono font-medium text-slate-800">{{ $legacyRole->id_rol }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Descripción</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $legacyRole->descripcion ?: 'Sin descripción.' }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Creado</dt>
                <dd class="mt-1 text-slate-800">{{ $role->created_at?->format('d/m/Y H:i') ?? 'No disponible' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Última actualización</dt>
                <dd class="mt-1 text-slate-800">{{ $role->updated_at?->format('d/m/Y H:i') ?? 'No disponible' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Permisos asignados</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $role->permissions->count() }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Usuarios asociados</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $users->count() }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card class="mb-6">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-800">Permisos asignados</h2>
            <p class="mt-1 text-sm text-slate-500">Se muestran todos los permisos que tiene este rol.</p>
        </div>
        @if ($permissions->isEmpty())
            <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm text-slate-500">
                Este rol todavía no tiene permisos asignados.
            </p>
        @else
            <div class="space-y-5">
                @foreach ($permissions as $module => $modulePermissions)
                    <section class="rounded-lg border border-slate-200">
                        <h3 class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold capitalize text-slate-700">
                            {{ $module }}
                        </h3>
                        <ul class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($modulePermissions as $permission)
                                <li class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 text-sm text-emerald-800">
                                    <span class="font-medium">{{ $permission->name }}</span>
                                    <span class="mt-1 block text-xs text-emerald-700">ID: {{ $permission->id }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    </x-card>

    <x-card>
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-800">Usuarios asociados</h2>
            <p class="mt-1 text-sm text-slate-500">Usuarios que tienen asignado este rol.</p>
        </div>
        @if ($users->isEmpty())
            <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-sm text-slate-500">
                No hay usuarios asociados a este rol.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Nombre</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Correo</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($users as $user)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-sm text-slate-600">{{ $user->id }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">{{ trim("{$user->nombre} {$user->apellido}") }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">{{ $user->correo }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                    {{ $user->activo === null ? 'No disponible' : ($user->activo ? 'Activo' : 'Inactivo') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
