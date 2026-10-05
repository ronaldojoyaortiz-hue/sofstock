<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Facturas;
use App\Models\Producto;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $months = collect(range(5, 0))->map(
            fn(int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo)
        );
        $categories = Categoria::withCount('productos')->get();

        $chartData = [
            'months' => $months->map(fn($month) => $month->format('M'))->values(),
            'sales' => $months->map(
                fn($month) => Facturas::query()
                    ->whereBetween('fecha', [
                        $month->copy()->startOfMonth()->toDateString(),
                        $month->copy()->endOfMonth()->toDateString(),
                    ])
                    ->where('estado', '!=', 'anulada')
                    ->sum('total')
            )->values(),
            'users' => $months->map(
                fn($month) => User::query()
                    ->whereBetween('created_at', [
                        $month->copy()->startOfMonth(),
                        $month->copy()->endOfMonth(),
                    ])
                    ->count()
            )->values(),
            'categoryLabels' => $categories->pluck('nombre')->values(),
            'categoryValues' => $categories->pluck('productos_count')->values(),
        ];

        return view('dashboard.index', [
            'totalUsers' => User::count(),
            'totalProducts' => Producto::count(),
            'totalSales' => Facturas::query()
                ->whereBetween('fecha', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                ->where('estado', '!=', 'anulada')
                ->count(),
            'totalRevenue' => Facturas::where('estado', '!=', 'anulada')->sum('total'),
            'chartData' => $chartData,
            'recentActivity' => [],
        ]);
    }
}
