<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        // 1. Today's Stats - Optimized for index usage
        $todayTransactions = \App\Models\Transaction::where('tanggal', $today)->count();
        // Calculate Net Revenue (Excluding Shipping/Fees)
        $todayRevenue = \App\Models\Transaction::where('tanggal', $today)
                        ->sum(\Illuminate\Support\Facades\DB::raw('total_harga - biaya_kirim - biaya_tambahan'));

        // 2. This Month's Profit ( Simplified Logic )
        $monthRevenue = \App\Models\Transaction::where('tanggal', '>=', $startOfMonth)
                                ->where('tanggal', '<=', $endOfMonth)
                                ->sum(\Illuminate\Support\Facades\DB::raw('total_harga - biaya_kirim - biaya_tambahan'));

        // 3. Low Stock Items (Threshold based on Product Setting)
        $lowStockProducts = \App\Models\Product::with('unit')
                                ->whereColumn('stok', '<=', 'stok_min')
                                ->orderBy('stok', 'asc')
                                ->take(5)
                                ->get();

        // 4. Sales Chart (Last 7 Days) - Optimized to 1 Query
        $startDate = now()->subDays(6)->startOfDay();
        $salesData = \App\Models\Transaction::where('tanggal', '>=', $startDate)
                        ->selectRaw('DATE(tanggal) as date, SUM(total_harga) as total')
                        ->groupBy('date')
                        ->pluck('total', 'date')
                        ->toArray();

        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d M');
            $chartLabels[] = $label;
            $chartData[] = $salesData[$date] ?? 0;
        }

        // 5. Recent Transactions
        $recentTransactions = \App\Models\Transaction::with('customer')
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();

        return view('dashboard.index', compact(
            'todayTransactions', 
            'todayRevenue', 
            'monthRevenue', 
            'lowStockProducts', 
            'chartLabels', 
            'chartData',
            'recentTransactions'
        ));
    }
}
