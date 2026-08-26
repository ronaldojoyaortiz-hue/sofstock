@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- Tarjetas estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
        <x-stat-card
            label="Total de usuarios"
            :value="number_format($totalUsers)"
            change="+12.4%"
            trend="up"
            icon="users"
        />
        <x-stat-card
            label="Total de productos"
            :value="number_format($totalProducts)"
            change="+4.1%"
            trend="up"
            icon="box"
        />
        <x-stat-card
            label="Ventas del mes"
            :value="number_format($totalSales)"
            change="-2.3%"
            trend="down"
            icon="chart"
        />
        <x-stat-card
            label="Ingresos"
            :value="'$' . number_format($totalRevenue, 2)"
            change="+8.7%"
            trend="up"
            icon="tag"
        />
    </div>

    {{-- Gráficos --}}
    <div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        <x-card title="Ventas mensuales" subtitle="Últimos 6 meses" class="xl:col-span-2">
            <canvas id="salesChart" height="110" role="img" aria-label="Gráfico de ventas mensuales"></canvas>
        </x-card>

        <x-card title="Productos por categoría">
            <canvas id="categoryChart" height="200" role="img" aria-label="Gráfico de productos por categoría"></canvas>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
        <x-card title="Usuarios registrados" subtitle="Últimos 6 meses" class="xl:col-span-3">
            <canvas id="usersChart" height="90" role="img" aria-label="Gráfico de usuarios registrados"></canvas>
        </x-card>
    </div>

    {{-- Tabla de actividad reciente --}}
    <div class="mt-6">
        <x-card title="Actividad reciente" subtitle="Últimos registros del sistema" :padding="false">
            <x-slot:actions>
                <x-button variant="secondary" href="#">Ver todo</x-button>
            </x-slot:actions>

            <x-table :headers="['Usuario', 'Descripción', 'Estado', 'Fecha', 'Acciones']">
                @forelse ($recentActivity as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $item['avatar'] }}"
                                    class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-100"
                                    alt="Avatar de {{ $item['name'] }}"
                                >
                                <span class="font-medium text-slate-700 whitespace-nowrap">{{ $item['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate">{{ $item['description'] }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$item['status']" /></td>
                        <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">{{ $item['date'] }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="#" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Ver {{ $item['name'] }}">
                                    <span class="w-4 h-4 block">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                </a>
                                <a href="#" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Editar {{ $item['name'] }}">
                                    <span class="w-4 h-4 block">@include('layouts.partials.icons', ['icon' => 'pencil'])</span>
                                </a>
                                <button
                                    type="button"
                                    @click="$dispatch('open-modal', 'delete-{{ $loop->index }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                                    aria-label="Eliminar {{ $item['name'] }}"
                                >
                                    <span class="w-4 h-4 block">@include('layouts.partials.icons', ['icon' => 'trash'])</span>
                                </button>

                                <x-modal name="delete-{{ $loop->index }}" title="Eliminar registro" maxWidth="sm">
                                    <p class="text-sm text-slate-500">
                                        ¿Seguro que deseas eliminar a <strong>{{ $item['name'] }}</strong>? Esta acción no se puede deshacer.
                                    </p>
                                    <x-slot:footer>
                                        <x-button variant="secondary" @click="show = false">Cancelar</x-button>
                                        <x-button variant="danger">Eliminar</x-button>
                                    </x-slot:footer>
                                </x-modal>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                            No hay actividad reciente.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthLabels = @json($chartData['months']);

        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Ventas',
                    data: @json($chartData['sales']),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.08)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('usersChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Usuarios registrados',
                    data: @json($chartData['users']),
                    backgroundColor: '#818cf8',
                    borderRadius: 6,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: @json($chartData['categoryLabels']),
                datasets: [{
                    data: @json($chartData['categoryValues']),
                    backgroundColor: ['#4f46e5', '#818cf8', '#c7d2fe', '#a5b4fc', '#312e81'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 16, font: { size: 11 } } } }
            }
        });
    });
</script>
@endpush
