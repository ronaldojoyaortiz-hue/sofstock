{{--
    Uso:
    <x-input name="email" label="Correo electrónico" type="email" :value="old('email')" required />
--}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1.5">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value }}"
        {{ $attributes->merge([
            'class' => 'w-full px-3.5 py-2.5 text-sm rounded-lg border ' .
                       ($errors->has($name) ? 'border-red-300 focus:ring-red-500' : 'border-slate-200 focus:ring-primary-500') .
                       ' bg-white focus:outline-none focus:ring-2 focus:border-transparent transition placeholder:text-slate-400'
        ]) }}
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
    >

    @if ($hint && !$errors->has($name))
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
