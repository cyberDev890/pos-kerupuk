@extends('layouts.app')
@section('content_title', 'Retur Barang')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Retur</h3>
        <div class="card-tools">
            <a href="{{ route('return.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Retur</a>
        </div>
    </div>
    <div class="card-body">
        <x-alert :errors="$errors" />
        <x-alert :type="'success'" :errors="session('success')" />
        <x-alert :type="'danger'" :errors="session('error')" />

        <table class="table table-bordered table-striped" id="table1">
            <thead>
                <tr>
                    <th>No Retur</th>
                    <th>Transaksi / Faktur</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Pihak Terkait</th>
                    <th>Total</th>
                    <th>Keterangan</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returns as $retur)
                <tr>
                    <td>{{ $retur->no_retur }}</td>
                    <td>
                        @if($retur->jenis_retur == 'penjualan')
                            {{ $retur->transaction->no_transaksi ?? '-' }}
                        @else
                            {{ $retur->purchase->no_faktur ?? '-' }}
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d/m/Y') }}</td>
                    <td>
                        @if($retur->jenis_retur == 'penjualan')
                            <span class="badge badge-warning">Retur Penjualan</span>
                            <small class="d-block text-muted">Barang Masuk</small>
                        @else
                            <span class="badge badge-info">Retur Pembelian</span>
                            <small class="d-block text-muted">Barang Keluar</small>
                        @endif
                    </td>
                    <td>
                        @if($retur->jenis_retur == 'penjualan')
                            <i class="fas fa-user"></i> {{ $retur->customer->nama ?? 'Umum' }}
                        @else
                            <i class="fas fa-truck"></i> {{ $retur->supplier->nama_supplier ?? '-' }}
                        @endif
                    </td>
                    <td>Rp {{ number_format($retur->total_harga) }}</td>
                    <td>{{ $retur->keterangan }}</td>
                    <td>
                        <form action="{{ route('return.destroy', $retur->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus retur ini? Stok akan dikembalikan ke kondisi sebelum retur.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // DataTable already initialized in layout
</script>
@endsection
