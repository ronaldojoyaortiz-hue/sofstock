{{--
    Tabla genérica para listados con avatar, estado y acciones.
    Uso:
    <x-table :headers="['Nombre', 'Descripción', 'Estado', 'Fecha', 'Acciones']">
        @foreach ($items as $item)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                        <span class="font-medium text-slate-700">{{ $item->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-500">{{ $item->description }}</td>
                <td class="px-5 py-3.5"><x-badge :status="$item->status" /></td>
                <td class="px-5 py-3.5 text-slate-500">{{ $item->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-1">
                        <x-button variant="ghost" href="#" aria-label="Ver">...</x-button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
--}}
@props(['headers' => []])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100">
        <thead>
            <tr class="bg-slate-50">
                @foreach ($headers as $header)
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
