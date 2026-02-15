@extends('layouts.app')
@section('content_title', 'Detail Pembelian')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Faktur: {{ $purchase->no_faktur ?? '-' }} 
            <small class="text-muted">({{ $purchase->tanggal }})</small>
        </h3>
        <div class="card-tools">
            <a href="{{ route('transaction.purchase.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Suplier:</strong> {{ $purchase->supplier->nama }} <br>
                <strong>Dibuat Oleh:</strong> {{ $purchase->user->name ?? '-' }}
            </div>
            <div class="col-md-6 text-right">
                <h4 class="text-primary">Total: Rp. {{ number_format($purchase->total_harga) }}</h4>
            </div>
        </div>
        
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->details as $detail)
                    <tr>
                        <td>{{ optional($detail->product)->nama_produk ?? 'Produk Dihapus (ID: '.$detail->product_id.')' }}</td>
                        <td>{{ $detail->unit->satuan_besar ?? '-' }}</td>
                        <td>{{ $detail->jumlah + 0 }}</td>
                        <td>Rp. {{ number_format($detail->harga_satuan) }}</td>
                        <td>Rp. {{ number_format($detail->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($purchase->keterangan)
        <div class="mt-3">
            <strong>Keterangan:</strong> <br>
            {{ $purchase->keterangan }}
        </div>
        @endif
    </div>
</div>
@endsection
