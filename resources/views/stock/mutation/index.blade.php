@extends('layouts.app')

@section('content_title', 'Riwayat Mutasi Stok')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Riwayat Mutasi Stok</h3>
        <div class="card-tools">
            <a href="{{ route('stock.mutation.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Mutasi Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <x-alert :type="'success'" :errors="session('success')" />
        <x-alert :type="'danger'" :errors="session('error')" />

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Dari -> Ke</th>
                    <th>User</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mutations as $index => $mutation)
                    <tr>
                        <td>{{ $mutations->firstItem() + $index }}</td>
                        <td>{{ $mutation->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $mutation->product->nama_produk ?? '-' }}</td>
                        <td>
                            {{ $mutation->unit_info }}
                            <small class="text-muted d-block">({{ number_format($mutation->amount) }} Pcs)</small>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($mutation->source) }}</span>
                            <i class="fas fa-arrow-right mx-1"></i>
                            <span class="badge badge-success">{{ ucfirst($mutation->destination) }}</span>
                        </td>
                        <td>{{ $mutation->user->name ?? '-' }}</td>
                        <td>{{ $mutation->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data mutasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-3">
            {{ $mutations->links() }}
        </div>
    </div>
</div>
@endsection
