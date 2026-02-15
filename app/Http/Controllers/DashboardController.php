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

        // 1. Today's Stats
        $todayTransactions = \App\Models\Transaction::whereDate('tanggal', $today)->count();
        // Calculate Net Revenue (Excluding Shipping/Fees)
        $todayRevenue = \App\Models\Transaction::whereDate('tanggal', $today)
                        ->sum(\Illuminate\Support\Facades\DB::raw('total_harga - biaya_kirim - biaya_tambahan'));

        // 2. This Month's Profit ( Simplified Logic )
        $monthRevenue = \App\Models\Transaction::whereDate('tanggal', '>=', $startOfMonth)
                                ->whereDate('tanggal', '<=', $endOfMonth)
                                ->sum(\Illuminate\Support\Facades\DB::raw('total_harga - biaya_kirim - biaya_tambahan'));

        // 3. Low Stock Items (Threshold based on Product Setting)
        $lowStockProducts = \App\Models\Product::with('unit')
                                ->whereColumn('stok', '<=', 'stok_min')
                                ->orderBy('stok', 'asc')
                                ->take(5)
                                ->get();

        // 4. Sales Chart (Last 7 Days)
        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyRevenue = \App\Models\Transaction::whereDate('tanggal', $date)->sum('total_harga');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[] = $dailyRevenue;
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
