@extends('layouts.app')

@section('title', 'Detalle de permiso asignado')
@section('page-title', 'Detalle de permiso asignado')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Permiso asignado</h1>
            <p class="mt-1 text-sm text-slate-500">Detalle de este permiso dentro del rol.</p>
        </div>
        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('seguridad.permisos.assignments') }}">Volver a permisos asignados</x-button>
            <x-button href="{{ route('seguridad.permisos.roles', ['role_id' => $role->id, 'gestionar' => 1]) }}">Editar asignación</x-button>
        </div>
    </div>

    <x-card>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">ID del rol</p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $role->id }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">Rol</p>
                <p class="mt-1 font-medium capitalize text-slate-800">{{ $role->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">ID del permiso</p>
                <p class="mt-1 font-mono font-medium text-slate-800">{{ $permission->id }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">Permiso</p>
                <p class="mt-1 font-medium text-slate-800">{{ $permission->name }}</p>
            </div>
        </div>
    </x-card>
@endsection
