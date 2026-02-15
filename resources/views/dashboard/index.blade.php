@extends('layouts.app')
@section('content_title', 'Dashboard')
@section('content')
<div class="row">
    <!-- Today's Transactions -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $todayTransactions }}</h3>
                <p>Transaksi Hari Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="{{ route('transaction.sales.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- Today's Revenue -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
                <p>Omzet Hari Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
             <a href="{{ route('report.profit-loss') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- Month's Revenue -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>Rp {{ number_format($monthRevenue, 0, ',', '.') }}</h3>
                <p>Omzet Bulan Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
             <a href="{{ route('report.profit-loss') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- Low Stock -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ count($lowStockProducts) }}</h3>
                <p>Stok Menipis</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#low-stock-card" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">Grafik Penjualan (7 Hari Terakhir)</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="sales-chart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
         <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Transaksi Terakhir</h3>
                <div class="card-tools">
                    <a href="{{ route('transaction.sales.index') }}" class="btn btn-tool btn-sm">
                        <i class="fas fa-bars"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                    <tr>
                        <th>No Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Waktu</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentTransactions as $trx)
                    <tr>
                        <td>{{ $trx->no_transaksi }}</td>
                        <td>{{ $trx->customer->nama ?? 'Umum' }}</td>
                        <td>Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                        <td>{{ $trx->created_at->format('H:i') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Low Stock List -->
    <div class="col-lg-5">
        <div class="card" id="low-stock-card">
            <div class="card-header">
                <h3 class="card-title">Stok Menipis (<= Min)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th style="width: 40px">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->nama_produk }}</td>
                            <td><span class="badge bg-danger">{{ $product->stok }}</span></td>
                        </tr>
                        @endforeach
                        @if(count($lowStockProducts) == 0)
                            <tr><td colspan="2" class="text-center">Stok Aman</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        var ctx = document.getElementById('sales-chart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Omzet',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: 3,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: @json($chartData)
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                         ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }]
                }
            }
        });
    });
</script>
@endsection
