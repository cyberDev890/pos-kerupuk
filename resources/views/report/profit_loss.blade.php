@extends('layouts.app')
@section('content_title', 'Laporan Laba Rugi')
@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('report.profit-loss') }}" method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="end_date" class="mr-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
                    <a href="{{ route('report.profit-loss') }}" class="btn btn-secondary ml-2"><i class="fas fa-sync"></i> Reset Today</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h5>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h5>
                <p>Total Omzet (Kotor)</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h5>Rp {{ number_format($totalReturnRevenue, 0, ',', '.') }}</h5>
                <p>Total Barang Retur</p>
            </div>
            <div class="icon">
                <i class="fas fa-undo"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h5>Rp {{ number_format($totalCOGS - $totalReturnCOGS, 0, ',', '.') }}</h5>
                <p>Total Modal (HPP)</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h5>Rp {{ number_format($netProfit, 0, ',', '.') }}</h5>
                <p>Keuntungan Bersih</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h5>{{ number_format($totalSalary, 0, ',', '.') }}</h5>
                <p>Total Gaji</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h5>{{ number_format($totalOperationalCost, 0, ',', '.') }}</h5>
                <p>Biaya Ops</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-trx-tab" data-toggle="pill" href="#tab-trx" role="tab" aria-controls="tab-trx" aria-selected="true">Rincian Penjualan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-retur-tab" data-toggle="pill" href="#tab-retur" role="tab" aria-controls="tab-retur" aria-selected="false">Rincian Retur</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-gaji-tab" data-toggle="pill" href="#tab-gaji" role="tab" aria-controls="tab-gaji" aria-selected="false">Rincian Gaji</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-operasional-tab" data-toggle="pill" href="#tab-operasional" role="tab" aria-controls="tab-operasional" aria-selected="false">Rincian Operasional</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="custom-tabsContent">
            <div class="tab-pane fade show active" id="tab-trx" role="tabpanel" aria-labelledby="tab-trx-tab">
                <table id="tableTrx" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Total Belanja</th>
                            <th>Biaya Lain</th>
                            <th>Total Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trx)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($trx->tanggal)) }}</td>
                            <td>{{ $trx->no_transaksi }}</td>
                            <td>{{ $trx->customer->nama ?? 'Umum' }}</td>
                            <td>Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($trx->biaya_kirim + $trx->biaya_tambahan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($trx->total_harga - $trx->biaya_kirim - $trx->biaya_tambahan, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="tab-retur" role="tabpanel" aria-labelledby="tab-retur-tab">
                <table id="tableRetur" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No Retur</th>
                            <th>Pelanggan</th>
                            <th>Transaksi Asal</th>
                            <th>Item</th>
                            <th>Total Refund</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $ret)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($ret->tanggal)) }}</td>
                            <td>{{ $ret->no_retur ?? '-' }}</td>
                            <td>{{ $ret->customer->nama ?? 'Umum' }}</td>
                            <td>{{ $ret->transaction->no_transaksi ?? '-' }}</td>
                            <td>
                                <ul class="pl-3 mb-0">
                                @foreach($ret->details as $detail)
                                    <li>
                                        {{ optional($detail->product)->nama_produk ?? 'Item Terhapus' }} 
                                        ({{ $detail->jumlah }} {{ optional($detail->unit)->nama_satuan ?? '' }})
                                    </li>
                                @endforeach
                                </ul>
                            </td>
                            <td>Rp {{ number_format($ret->total_harga, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Salary Tab -->
            <div class="tab-pane fade" id="tab-gaji" role="tabpanel" aria-labelledby="tab-gaji-tab">
                <table id="tableGaji" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $salary)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($salary->date)) }}</td>
                            <td>{{ $salary->name }}</td>
                            <td>{{ $salary->description }}</td>
                            <td>Rp {{ number_format($salary->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Operational Cost Tab -->
            <div class="tab-pane fade" id="tab-operasional" role="tabpanel" aria-labelledby="tab-operasional-tab">
                <table id="tableOperasional" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operationalCosts as $cost)
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($cost->date)) }}</td>
                            <td>{{ $cost->description }}</td>
                            <td>Rp {{ number_format($cost->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $("#tableTrx").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tableTrx_wrapper .col-md-6:eq(0)');
        
        $("#tableRetur").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tableRetur_wrapper .col-md-6:eq(0)');

        $("#tableGaji").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tableGaji_wrapper .col-md-6:eq(0)');

        $("#tableOperasional").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#tableOperasional_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection
