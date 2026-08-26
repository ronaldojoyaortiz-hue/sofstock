{{--
    Uso:
    <x-stat-card
        label="Total de usuarios"
        :value="$totalUsers"
        change="+12.4%"
        trend="up"
        icon="users"
    />
    trend: "up" | "down"
--}}
@props(['label', 'value', 'change' => null, 'trend' => 'up', 'icon' => 'chart'])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 sm:p-6">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">{{ $value }}</p>
        </div>
        <div class="w-11 h-11 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center flex-shrink-0">
            <span class="w-5 h-5 block">@include('layouts.partials.icons', ['icon' => $icon])</span>
        </div>
    </div>

    @if ($change)
        <div class="mt-4 flex items-center gap-1.5 text-sm">
            <span class="inline-flex items-center gap-1 font-medium {{ $trend === 'up' ? 'text-emerald-600' : 'text-red-600' }}">
                @if ($trend === 'up')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                @else
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                @endif
                {{ $change }}
            </span>
            <span class="text-slate-400">vs. mes anterior</span>
        </div>
    @endif
</div>
