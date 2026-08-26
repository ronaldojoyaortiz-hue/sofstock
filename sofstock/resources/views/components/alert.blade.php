{{--
    Uso: <x-alert type="success">Guardado correctamente</x-alert>
    type: success | error | warning | info
--}}
@props(['type' => 'info'])

@php
    $map = [
        'success' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'text-emerald-500'],
        'error'   => ['bg-red-50 text-red-700 border-red-200', 'text-red-500'],
        'warning' => ['bg-amber-50 text-amber-700 border-amber-200', 'text-amber-500'],
        'info'    => ['bg-primary-50 text-primary-700 border-primary-200', 'text-primary-500'],
    ];
    [$classes, $iconColor] = $map[$type] ?? $map['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    role="alert"
    {{ $attributes->merge(['class' => "flex items-start gap-3 border rounded-lg px-4 py-3 text-sm $classes"]) }}
>
    <div class="flex-1">{{ $slot }}</div>
    <button
        @click="show = false"
        type="button"
        class="{{ $iconColor }} hover:opacity-70 focus:outline-none"
        aria-label="Cerrar alerta"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
