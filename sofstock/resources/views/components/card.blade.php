{{--
    Uso:
    <x-card title="Título" subtitle="Opcional">
        Contenido...
    </x-card>
--}}
@props(['title' => null, 'subtitle' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm']) }}>
    @if ($title)
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="text-sm text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
