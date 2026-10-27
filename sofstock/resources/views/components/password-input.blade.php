@props(['name', 'label', 'hint' => null, 'required' => false, 'autocomplete' => 'new-password'])

<div x-data="{ visible: false }">
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            x-bind:type="visible ? 'text' : 'password'"
            @required($required)
            autocomplete="{{ $autocomplete }}"
            {{ $attributes->merge([
                'class' => 'w-full rounded-lg border bg-white px-3.5 py-2.5 pr-11 text-sm transition placeholder:text-slate-400 focus:border-transparent focus:outline-none focus:ring-2 ' .
                    ($errors->has($name) ? 'border-red-300 focus:ring-red-500' : 'border-slate-200 focus:ring-primary-500')
            ]) }}
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        >
        <button
            type="button"
            @click="visible = !visible"
            :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            :title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
        >
            <svg x-show="!visible" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <svg x-show="visible" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 5.09A10.94 10.94 0 0112 4.9c4.48 0 8.27 2.94 9.54 7a10.97 10.97 0 01-3.15 4.55M6.61 6.61A11.03 11.03 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.05 0 2.06-.16 3-.46"></path>
            </svg>
        </button>
    </div>
    @if ($hint && !$errors->has($name))
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
