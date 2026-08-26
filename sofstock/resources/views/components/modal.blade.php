{{--
    Uso:
    <x-button x-data @click="$dispatch('open-modal', 'confirm-delete')">Eliminar</x-button>

    <x-modal name="confirm-delete" title="Eliminar registro">
        <p class="text-sm text-slate-500">¿Estás seguro? Esta acción no se puede deshacer.</p>
        <x-slot:footer>
            <x-button variant="secondary" @click="show = false">Cancelar</x-button>
            <x-button variant="danger">Eliminar</x-button>
        </x-slot:footer>
    </x-modal>
--}}
@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'];
    $width = $widths[$maxWidth] ?? $widths['md'];
@endphp

<div
    x-data="{ show: false }"
    x-show="show"
    x-cloak
    @open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title-{{ $name }}"
>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div
            x-show="show" x-transition.opacity
            @click="show = false"
            class="fixed inset-0 bg-slate-900/50"
            aria-hidden="true"
        ></div>

        <div
            x-show="show"
            x-transition
            class="relative bg-white rounded-xl shadow-xl w-full {{ $width }} overflow-hidden"
        >
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="modal-title-{{ $name }}" class="text-base font-semibold text-slate-800">{{ $title }}</h3>
                <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 focus:outline-none" aria-label="Cerrar modal">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 sm:px-6 py-5 space-y-3">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="px-5 sm:px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
