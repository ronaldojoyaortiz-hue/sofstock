{{--
    Uso: <x-badge status="Activo" /> | <x-badge status="Pendiente" /> | <x-badge status="Inactivo" />
--}}
@props(['status'])

@php
    $map = [
        'activo'   => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'pendiente'=> 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'inactivo' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    ];
    $key = strtolower($status);
    $classes = $map[$key] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20';
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset {{ $classes }}">
    {{ $status }}
</span>
